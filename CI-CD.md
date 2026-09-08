# CI / CD with GitHub Actions

Workflow file: [`.github/workflows/ci-cd.yml`](.github/workflows/ci-cd.yml)

```
push / PR to main
   ├─ test          (ubuntu)  pint --test  +  php artisan test   (sqlite :memory:)
   ├─ build-assets  (ubuntu)  npm ci  +  npm run build
   ├─ docker-build  (ubuntu)  docker build .           (validate Dockerfile)
   └─ deploy        (self-hosted = your laptop)   ← only on push to main, after the 3 above pass
        1. checkout
        2. write .env from GitHub secrets
        3. docker compose up -d --build --remove-orphans
        4. wait until the app answers HTTP inside the container
        5. check the Cloudflare tunnel reconnected
```

On a **pull request** only the three CI jobs run. On a **push to `main`** the CI jobs run and,
if they all pass, `deploy` runs on your laptop.

### Files added/changed to make this work

| File | Why |
|---|---|
| `.github/workflows/ci-cd.yml` | the pipeline |
| `CI-CD.md` | this doc |
| `package-lock.json` | **new** — `npm ci` (CI) needs a lockfile for reproducible installs |
| `docker-compose.yml` | added `APP_KEY: ${APP_KEY:-}` to the `app` service so the key stays stable across rebuilds |
| `bootstrap/app.php` | `pint` auto-format (import ordering) so `pint --test` passes |
| `tests/Feature/ExampleTest.php` | uses `RefreshDatabase` + adds a `/up` health test; the stock test hit `/` with no tables and 500'd |

---

## 1. One-time: required GitHub secrets

Repo → **Settings → Secrets and variables → Actions → New repository secret**. Add all seven:

| Secret | Value | Where to get it |
|---|---|---|
| `APP_KEY` | `base64:....` | If the stack is already running with real data, copy the **current** key so nothing breaks: `docker compose exec app php artisan tinker --execute="echo config('app.key');"`. Starting fresh? Generate one: `docker compose exec app php artisan key:generate --show`. Either way, set it once and never change it (changing it logs everyone out and breaks encrypted columns). |
| `APP_DOMAIN` | `my-set.online` | Your domain, no `https://`, no trailing `/`. |
| `CLOUDFLARE_TUNNEL_TOKEN` | `eyJ...` | Zero Trust → Networks → Tunnels → your tunnel → Configure. One line, no quotes. |
| `MYSQL_DATABASE` | `stylehub` | Must match what the `db_data` volume was first created with. |
| `MYSQL_USER` | `stylehub` | Same. |
| `MYSQL_PASSWORD` | your DB password | Same value the volume already has (see note in §4). |
| `MYSQL_ROOT_PASSWORD` | your DB root password | Same. |

The `deploy` job does `cp .env.docker .env` then appends these seven lines, so the
committed `.env.docker` still supplies every normal Laravel setting.

---

## 2. One-time: install the self-hosted runner on the laptop

The runner is a small background service from GitHub that waits for jobs and runs them
on your machine. Docker Desktop must be running for the deploy job to work.

1. Repo → **Settings → Actions → Runners → New self-hosted runner** → **Windows / x64**.
2. GitHub shows a block of commands with a one-time registration token. In **PowerShell**,
   in a folder like `C:\actions-runner`:

   ```powershell
   mkdir C:\actions-runner ; cd C:\actions-runner
   # download URL + hash come from the GitHub page (version changes over time)
   Invoke-WebRequest -Uri https://github.com/actions/runner/releases/download/v2.XXX.X/actions-runner-win-x64-2.XXX.X.zip -OutFile runner.zip
   Expand-Archive -Path runner.zip -DestinationPath .

   # register — token is shown on the GitHub page, valid ~1 hour
   .\config.cmd --url https://github.com/Chombunthoeun/E-commerce_StyleHub --token <REGISTRATION_TOKEN>
   ```

   Prompts:
   - **runner group** → Enter (Default)
   - **name of runner** → Enter (defaults to the PC name), or type e.g. `laptop-deploy`
   - **additional labels** → Enter (you get `self-hosted, Windows, X64` — the workflow only needs `self-hosted`)
   - **work folder** → Enter (`_work`)
   - **run runner as service?** → `Y`
   - **user account for the service** → enter **your own Windows user** (`ASUS`) and password,
     *not* the default `NETWORK SERVICE` — the service must run as the user that Docker
     Desktop runs under, or `docker` won't be reachable.

3. Start / check the service:

   ```powershell
   .\svc.ps1 status
   .\svc.ps1 start      # if not already running
   ```

   Check: Repo → Settings → Actions → Runners → your runner shows **Idle** (green).

4. If you accepted the default `NETWORK SERVICE` account and deploy later fails with
   `docker: command not found` or `error during connect ... dockerDesktopLinuxEngine`,
   fix the log-on account after the fact:
   - `services.msc` → find **GitHub Actions Runner (...)** → Properties → **Log On** tab →
     *This account* → enter your Windows user + password → OK → restart the service.
   - Also turn on Docker Desktop → Settings → General → **Start Docker Desktop when you
     sign in**, and keep the laptop signed in (a locked screen is fine, signed-out is not).

---

## 3. Requirements on the runner machine

- **Docker Desktop** installed and running.
- **Git for Windows** (gives the runner `bash`, which the deploy steps use via `shell: bash`).
- Enough disk for image builds (a few GB).
- The tunnel is created and has a **Public Hostname → `app:80`** already (see
  `DEPLOY-CLOUDFLARE.md`). CD does not configure Cloudflare, only restarts the stack.

---

## 4. First automated deploy — volume / data notes

- `COMPOSE_PROJECT_NAME: website-app` is pinned in the workflow so the deploy reuses the
  **same** `website-app_db_data` and `website-app_storage_data` volumes as a manual
  `docker compose` run from a folder named `website-app`. If your manual folder has a
  different name, either rename it or change `COMPOSE_PROJECT_NAME` in the workflow to match.
- Because of that, `MYSQL_PASSWORD` / `MYSQL_ROOT_PASSWORD` in the secrets **must equal**
  the values the volume was first initialised with, or MySQL returns
  `Access denied`. If you don't know them and there's no real data yet:
  `docker compose down -v` once, then let CD recreate everything from the secrets.
- After CD is working, deploy **only** by pushing to `main`. Running `docker compose up`
  by hand from the desktop folder still works (same containers) but it's easy to get the
  two working copies out of sync.

---

## 5. Everyday use

```bash
git switch -c feature/x       # work on a branch
# ... commit ...
git push -u origin feature/x  # open a PR -> CI runs (no deploy)
# merge the PR into main      -> CI runs again, then deploy runs on the laptop
```

Watch a run: repo → **Actions** tab → click the run → `deploy` job logs.

### Rollback

```bash
git revert <bad-commit>   # then push to main -> CD redeploys the previous state
git push
```

or on the laptop, check out the last good commit and `docker compose up -d --build`.

### Skip CI for a docs-only commit

Put `[skip ci]` in the commit message.

---

## 6. Troubleshooting

| Symptom | Fix |
|---|---|
| `deploy` job stuck "Waiting for a runner" | Runner offline. `services.msc` → start **GitHub Actions Runner**, or check Docker Desktop is up. |
| `docker: command not found` in deploy | Runner service log-on account can't see Docker Desktop — see §2 step 4. |
| `Access denied for user` (MySQL) | Secret passwords don't match the existing `db_data` volume — §4. |
| Health check fails, `app` logs show migrate errors | Bad migration merged. `git revert` and push, or fix forward. |
| `did not see 'Registered tunnel connection'` warning | Tunnel token wrong/rotated, or Cloudflare down. `docker compose logs cloudflared`. |
| Tests pass locally, fail in CI on Pint | Run `./vendor/bin/pint` locally to auto-format, commit the result. |
| Deploy ran but site still old | Browser/Cloudflare cache. Hard refresh; Cloudflare dashboard → Caching → Purge Everything. |
