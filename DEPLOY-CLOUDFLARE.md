# Publishing the app through Cloudflare Tunnel

Cloudflare can't run PHP/MySQL. Your Docker stack keeps running on your laptop;
Cloudflare Tunnel gives it a public HTTPS address on your domain with **no port
forwarding and no public IP**. If the laptop sleeps or goes offline, the site is
down — that's the trade-off of hosting from a laptop.

```
visitor ──HTTPS──> Cloudflare edge ──tunnel──> cloudflared container ──HTTP──> app:80 (nginx) ──> php-fpm
                                                                                        └──> db:3306 (mysql)
```

---

## 1. Put the domain on Cloudflare (registrar stays Namecheap)

Namecheap only sold you the name; DNS can be run by Cloudflare.

1. Cloudflare dashboard → **Add a site** → type your domain → pick the **Free** plan.
2. Cloudflare shows **2 nameservers**, e.g. `dana.ns.cloudflare.com` / `rob.ns.cloudflare.com`.
3. Namecheap → **Domain List** → **Manage** → **Nameservers** → choose **Custom DNS** →
   paste both Cloudflare nameservers → tick the green check to save.
4. Wait until the Cloudflare site status flips to **Active** (usually minutes, up to 24 h).
   Nothing below works until it's Active.

## 2. Attach your domain to the tunnel

The tunnel already exists (you have its token). Now give it a public hostname.

1. Cloudflare **Zero Trust** dashboard → **Networks → Tunnels** → open your tunnel
   (id `03ec78d4-69bd-4ed9-85f5-5bc4d8addbcb`).
2. **Public Hostname** tab → **Add a public hostname**:
    - **Subdomain:** leave blank for the root domain, or `www` / `shop`.
    - **Domain:** your domain.
    - **Type:** `HTTP`
    - **URL:** `app:80` ← the compose service name, not localhost
3. Save. Cloudflare auto-creates the proxied DNS record.
4. (Optional) Add a second hostname `www` → same `app:80`, or set a redirect rule.

## 3. Fill in `.env` (already gitignored)

```dotenv
APP_DOMAIN=your-real-domain.com
CLOUDFLARE_TUNNEL_TOKEN=eyJhIjoi...        # already added for you
```

`APP_DOMAIN` feeds `APP_URL=https://your-real-domain.com` into the app container so
Laravel builds correct absolute links, and the app already trusts the Cloudflare
proxy headers (`trustProxies(at: '*')` in `bootstrap/app.php`), so HTTPS URLs work.

## 4. Start it

```bash
docker compose up -d --build
docker compose logs -f cloudflared      # look for: "Registered tunnel connection"
```

Then open `https://your-domain.com`.

---

## Notes / hardening

- **Keep it running:** set Docker Desktop to _Start on login_, disable laptop sleep
  (`Settings → System → Power`), or the site drops when you close the lid.
- **Stop exposing port 8081 publicly:** it's still bound on the laptop for local
  testing. That's fine on a home network. If you don't want it, delete the
  `ports:` block under `app` — the tunnel doesn't need it.
- **Force HTTPS cookies** once you're only using the domain: add
  `SESSION_SECURE_COOKIE: "true"` under `app.environment` (breaks plain
  `http://localhost:8081`, so only do this when you've stopped using that).
- **Uploaded images** (product/variant photos) live in the `storage_data` Docker
  volume. They survive `up --build` but not `docker compose down -v`. Back that
  volume up, or move uploads to Cloudflare R2 / S3 later.
- **Token safety:** the tunnel token was shared in plain text. If that channel
  isn't private, rotate it: Zero Trust → the tunnel → **Refresh token**, then
  update `.env` and `docker compose up -d`.
- **DB backups:** `docker compose exec db mysqldump -ustylehub -pstylehub stylehub > backup.sql`
