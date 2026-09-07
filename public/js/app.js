(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    initMobileNav();
    initScrollReveal();
    initToasts();
    initQtySteppers();
    initVariantPicker();
    initProductLightbox();
    initAdminVariantRows();
    initImagePreview();
    initAutoHideAlerts();
    initCharts();
    initRevenueChartTabs();
  });

  /* ---------------------------------------------------------
     Mobile nav
  --------------------------------------------------------- */
  function initMobileNav() {
    var toggle = document.querySelector("[data-mobile-toggle]");
    var menu = document.querySelector("[data-mobile-menu]");
    if (!toggle || !menu) return;

    toggle.addEventListener("click", function () {
      var isOpen = menu.classList.toggle("is-open");
      toggle.classList.toggle("is-open", isOpen);
      toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
  }

  /* ---------------------------------------------------------
     Scroll reveal animations
  --------------------------------------------------------- */
  function initScrollReveal() {
    var targets = document.querySelectorAll(".reveal, .product-card");
    if (!targets.length) return;

    if (!("IntersectionObserver" in window)) {
      targets.forEach(function (el) {
        el.classList.add("in-view");
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("in-view");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: "0px 0px -40px 0px" }
    );

    targets.forEach(function (el) {
      observer.observe(el);
    });
  }

  /* ---------------------------------------------------------
     Toast notifications (reads window.flashStatus set by Blade)
  --------------------------------------------------------- */
  function initToasts() {
    if (!window.flashStatus) return;

    var container = document.getElementById("toast-container");
    if (!container) return;

    var toast = document.createElement("div");
    toast.className = "toast";
    toast.textContent = window.flashStatus;
    container.appendChild(toast);

    setTimeout(function () {
      toast.remove();
    }, 2800);
  }

  function initAutoHideAlerts() {
    document.querySelectorAll("[data-auto-hide]").forEach(function (el) {
      setTimeout(function () {
        el.style.transition = "opacity 300ms ease";
        el.style.opacity = "0";
        setTimeout(function () {
          el.remove();
        }, 300);
      }, 3500);
    });
  }

  /* ---------------------------------------------------------
     Quantity steppers
  --------------------------------------------------------- */
  function initQtySteppers() {
    document.querySelectorAll(".qty-stepper").forEach(function (stepper) {
      var input = stepper.querySelector("input");
      var min = parseInt(input.getAttribute("min") || "1", 10);
      var max = parseInt(input.getAttribute("max") || "9999", 10);

      stepper.querySelectorAll("button").forEach(function (btn) {
        btn.addEventListener("click", function () {
          var value = parseInt(input.value || "1", 10);
          if (btn.dataset.step === "up") {
            value = Math.min(max, value + 1);
          } else {
            value = Math.max(min, value - 1);
          }
          input.value = value;
          input.dispatchEvent(new Event("change"));
        });
      });
    });
  }

  /* ---------------------------------------------------------
     Product detail variant picker
  --------------------------------------------------------- */
  function initVariantPicker() {
    var root = document.querySelector("[data-variant-picker]");
    if (!root) return;

    var dataEl = document.getElementById("variant-data");
    var variants = JSON.parse(dataEl.textContent);

    var state = {};
    var groups = root.querySelectorAll("[data-option-group]");

    var priceEl = document.querySelector("[data-current-price]");
    var stockNote = document.querySelector("[data-stock-note]");
    var addForm = document.querySelector("[data-add-to-cart-form]");
    var variantIdInput = document.querySelector("[data-variant-id-input]");
    var addButton = document.querySelector("[data-add-to-cart-button]");
    var qtyInput = document.querySelector("[data-qty-input]");
    var basePrice = parseFloat(root.dataset.basePrice);
    var productDiscountPercent = parseFloat(root.dataset.discountPercent || "0");
    var originalPriceEl = document.querySelector("[data-original-price]");
    var discountBadge = document.querySelector("[data-discount-badge]");
    var mediaLayers = Array.prototype.slice.call(document.querySelectorAll("[data-media-img]"));
    var mediaShimmer = document.querySelector("[data-media-shimmer]");
    var activeLayerIndex = 0;
    var baseImageSrc = mediaLayers.length ? mediaLayers[0].src : null;
    var currentImageSrc = baseImageSrc;

    function effectiveDiscount(variant) {
      if (variant && variant.discount_percent !== null && variant.discount_percent !== undefined) {
        return parseFloat(variant.discount_percent);
      }
      return productDiscountPercent;
    }

    function discountedPrice(price, pct) {
      if (!pct) return price;
      return Math.round(price * (1 - pct / 100) * 100) / 100;
    }

    function renderPrice(price, pct) {
      var finalPrice = discountedPrice(price, pct);
      if (priceEl) priceEl.textContent = "$" + finalPrice.toFixed(2);
      if (originalPriceEl) {
        if (pct) {
          originalPriceEl.textContent = "$" + price.toFixed(2);
          originalPriceEl.hidden = false;
        } else {
          originalPriceEl.hidden = true;
        }
      }
      if (discountBadge) {
        if (pct) {
          discountBadge.textContent = "-" + pct + "%";
          discountBadge.hidden = false;
        } else {
          discountBadge.hidden = true;
        }
      }
    }

    function matchVariant() {
      if (groups.length === 0) {
        return variants.length === 1 ? variants[0] : undefined;
      }
      return variants.find(function (v) {
        return Object.keys(state).length === groups.length && Object.keys(state).every(function (key) {
          return (v[key] || "") === state[key];
        });
      });
    }

    function refreshButtons() {
      groups.forEach(function (group) {
        var field = group.dataset.optionGroup;
        group.querySelectorAll(".option-btn").forEach(function (btn) {
          var testState = Object.assign({}, state, {});
          testState[field] = btn.dataset.value;
          var candidate = groups.length && variants.some(function (v) {
            return Object.keys(testState).every(function (key) {
              return !testState[key] || (v[key] || "") === testState[key];
            });
          });
          btn.classList.toggle("is-unavailable", !candidate);
          btn.classList.toggle("is-selected", state[field] === btn.dataset.value);
        });
      });
    }

    function updateMainImage(variant) {
      if (!mediaLayers.length) return;

      var targetSrc = null;
      if (variant && variant.image) {
        targetSrc = variant.image;
      } else if (state.color) {
        var swatch = variants.find(function (v) {
          return v.color === state.color && v.image;
        });
        targetSrc = swatch ? swatch.image : null;
      }

      targetSrc = targetSrc || baseImageSrc;
      if (!targetSrc || currentImageSrc === targetSrc) return;
      currentImageSrc = targetSrc;

      var nextIndex = 1 - activeLayerIndex;
      var current = mediaLayers[activeLayerIndex];
      var next = mediaLayers[nextIndex];

      if (mediaShimmer) mediaShimmer.classList.add("is-active");

      var preload = new Image();
      preload.onload = function () {
        if (currentImageSrc !== targetSrc) return; // a newer swap started meanwhile
        next.src = targetSrc;
        next.classList.add("is-active");
        current.classList.remove("is-active");
        activeLayerIndex = nextIndex;
        if (mediaShimmer) mediaShimmer.classList.remove("is-active");
      };
      preload.onerror = function () {
        if (mediaShimmer) mediaShimmer.classList.remove("is-active");
      };
      preload.src = targetSrc;
    }

    function refreshSummary() {
      var variant = matchVariant();
      groups.forEach(function (group) {
        var field = group.dataset.optionGroup;
        var label = group.querySelector(".selected-value");
        if (label) label.textContent = state[field] ? "— " + state[field] : "";
      });

      updateMainImage(variant);

      if (!variant) {
        renderPrice(basePrice, productDiscountPercent);
        if (stockNote) {
          stockNote.textContent = groups.length ? "Select all options to see availability" : "";
          stockNote.className = "stock-note";
        }
        if (addButton) addButton.disabled = true;
        if (variantIdInput) variantIdInput.value = "";
        if (qtyInput) qtyInput.setAttribute("max", 1);
        return;
      }

      var price = variant.price_override ? parseFloat(variant.price_override) : basePrice;
      renderPrice(price, effectiveDiscount(variant));
      if (variantIdInput) variantIdInput.value = variant.id;

      if (stockNote) {
        if (variant.stock_qty <= 0) {
          stockNote.textContent = "Out of stock";
          stockNote.className = "stock-note out";
        } else if (variant.stock_qty <= 5) {
          stockNote.textContent = "Only " + variant.stock_qty + " left in stock";
          stockNote.className = "stock-note low";
        } else {
          stockNote.textContent = "In stock";
          stockNote.className = "stock-note ok";
        }
      }

      if (addButton) addButton.disabled = variant.stock_qty <= 0;
      if (qtyInput) qtyInput.setAttribute("max", Math.max(1, variant.stock_qty));
    }

    groups.forEach(function (group) {
      var field = group.dataset.optionGroup;
      var buttons = group.querySelectorAll(".option-btn");

      if (buttons.length === 1) {
        state[field] = buttons[0].dataset.value;
      }

      buttons.forEach(function (btn) {
        btn.addEventListener("click", function () {
          if (btn.classList.contains("is-unavailable")) return;
          state[field] = state[field] === btn.dataset.value ? undefined : btn.dataset.value;
          if (!state[field]) delete state[field];
          refreshButtons();
          refreshSummary();
        });
      });
    });

    refreshButtons();
    refreshSummary();
  }

  /* ---------------------------------------------------------
     Product detail: click-to-enlarge lightbox
  --------------------------------------------------------- */
  function initProductLightbox() {
    var trigger = document.querySelector("[data-zoom-trigger]");
    var lightbox = document.querySelector("[data-lightbox]");
    if (!trigger || !lightbox) return;

    var lightboxImg = lightbox.querySelector("[data-lightbox-img]");
    var closeBtn = lightbox.querySelector("[data-lightbox-close]");

    function open() {
      var activeImg = trigger.querySelector(".media-img.is-active");
      if (!activeImg || !activeImg.getAttribute("src")) return;

      lightboxImg.src = activeImg.src;
      lightbox.hidden = false;
      requestAnimationFrame(function () {
        lightbox.classList.add("is-open");
      });
      document.body.style.overflow = "hidden";
    }

    function close() {
      lightbox.classList.remove("is-open");
      document.body.style.overflow = "";
      setTimeout(function () {
        lightbox.hidden = true;
      }, 280);
    }

    trigger.addEventListener("click", open);
    lightbox.addEventListener("click", function (event) {
      if (event.target === lightbox) close();
    });
    if (closeBtn) {
      closeBtn.addEventListener("click", function (event) {
        event.stopPropagation();
        close();
      });
    }

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && !lightbox.hidden) close();
    });
  }

  /* ---------------------------------------------------------
     Admin: dynamic variant rows
  --------------------------------------------------------- */
  function initAdminVariantRows() {
    var container = document.querySelector("[data-variant-rows]");
    if (!container) return;

    var addBtn = document.querySelector("[data-add-variant]");
    var deletedInput = document.querySelector("[data-deleted-variants]");
    var deletedIds = [];
    var index = container.querySelectorAll(".variant-row").length;

    function rowTemplate(i) {
      return (
        '<div class="variant-row">' +
        '<input type="hidden" name="variants[' + i + '][id]" value="">' +
        '<div class="variant-row__swatch">' +
        '<img class="variant-swatch-preview" data-variant-preview src="' + placeholderUrl + '" alt="">' +
        '<label class="btn btn-outline btn-sm variant-swatch-upload">Photo' +
        '<input type="file" accept="image/*" class="visually-hidden" data-variant-image-input name="variants[' + i + '][image_file]">' +
        '</label>' +
        '</div>' +
        '<div><label>Size</label><input type="text" name="variants[' + i + '][size]" placeholder="e.g. M / 9"></div>' +
        '<div><label>Color</label><input type="text" name="variants[' + i + '][color]" placeholder="e.g. Black"></div>' +
        '<div><label>Stock qty</label><input type="number" name="variants[' + i + '][stock_qty]" min="0" value="0" required data-variant-stock-input><span class="variant-stock-status" data-variant-stock-status></span></div>' +
        '<div><label>Price override</label><input type="number" step="0.01" min="0" name="variants[' + i + '][price_override]" placeholder="optional"></div>' +
        '<div><label>Discount %</label><input type="number" min="0" max="100" name="variants[' + i + '][discount_percent]" placeholder="same as product"></div>' +
        '<button type="button" class="btn btn-danger btn-sm remove-variant" data-remove-variant>Remove</button>' +
        "</div>"
      );
    }

    var placeholderUrl = container.dataset.placeholder || "";
    var lowStockThreshold = parseInt(container.dataset.lowStockThreshold || "5", 10);

    function updateStockStatus(row) {
      var input = row.querySelector("[data-variant-stock-input]");
      var status = row.querySelector("[data-variant-stock-status]");
      if (!input || !status) return;

      var qty = parseInt(input.value || "0", 10);
      row.classList.remove("is-low-stock", "is-out-of-stock");

      if (qty <= 0) {
        status.textContent = "Out of stock";
        status.className = "variant-stock-status out";
        row.classList.add("is-out-of-stock");
      } else if (qty <= lowStockThreshold) {
        status.textContent = "Low stock";
        status.className = "variant-stock-status low";
        row.classList.add("is-low-stock");
      } else {
        status.textContent = "In stock";
        status.className = "variant-stock-status ok";
      }
    }

    container.querySelectorAll(".variant-row").forEach(updateStockStatus);

    if (addBtn) {
      addBtn.addEventListener("click", function () {
        var wrapper = document.createElement("div");
        wrapper.innerHTML = rowTemplate(index);
        var row = wrapper.firstElementChild;
        container.appendChild(row);
        updateStockStatus(row);
        index++;
      });
    }

    container.addEventListener("input", function (event) {
      var input = event.target.closest("[data-variant-stock-input]");
      if (!input) return;
      updateStockStatus(input.closest(".variant-row"));
    });

    container.addEventListener("change", function (event) {
      var input = event.target.closest("[data-variant-image-input]");
      if (!input) return;

      var file = input.files && input.files[0];
      if (!file) return;

      var preview = input.closest(".variant-row__swatch").querySelector("[data-variant-preview]");
      if (!preview) return;

      var reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
      };
      reader.readAsDataURL(file);
    });

    container.addEventListener("click", function (event) {
      var btn = event.target.closest("[data-remove-variant]");
      if (!btn) return;

      var row = btn.closest(".variant-row");
      var idInput = row.querySelector('input[name$="[id]"]');
      if (idInput && idInput.value) {
        deletedIds.push(idInput.value);
        if (deletedInput) deletedInput.value = deletedIds.join(",");
      }
      row.remove();
    });
  }

  /* ---------------------------------------------------------
     Image upload preview
  --------------------------------------------------------- */
  function initImagePreview() {
    var input = document.querySelector("[data-image-input]");
    var preview = document.querySelector("[data-image-preview]");
    if (!input || !preview) return;

    input.addEventListener("change", function () {
      var file = input.files && input.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
      };
      reader.readAsDataURL(file);
    });
  }

  /* ---------------------------------------------------------
     Admin dashboard charts (crosshair + tooltip)
  --------------------------------------------------------- */
  function initCharts() {
    document.querySelectorAll('[data-chart="line"]').forEach(initLineChart);
    document.querySelectorAll('[data-chart="bar"]').forEach(initBarChart);
  }

  /* ---------------------------------------------------------
     Admin dashboard: revenue chart period tabs (AJAX swap)
  --------------------------------------------------------- */
  function initRevenueChartTabs() {
    var tabs = document.querySelector("[data-period-tabs]");
    var body = document.querySelector("[data-revenue-chart-body]");
    if (!tabs || !body) return;

    var baseUrl = tabs.dataset.revenueUrl;

    tabs.addEventListener("click", function (event) {
      var btn = event.target.closest("[data-period]");
      if (!btn || btn.classList.contains("is-active") || tabs.classList.contains("is-loading")) return;

      var period = btn.dataset.period;
      tabs.classList.add("is-loading");

      fetch(baseUrl + "?period=" + encodeURIComponent(period), {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      })
        .then(function (response) {
          return response.text();
        })
        .then(function (html) {
          body.innerHTML = html;
          tabs.querySelectorAll("[data-period]").forEach(function (t) {
            t.classList.toggle("is-active", t === btn);
          });
          var chartWrap = body.querySelector('[data-chart="line"]');
          if (chartWrap) initLineChart(chartWrap);
        })
        .catch(function () {})
        .finally(function () {
          tabs.classList.remove("is-loading");
        });
    });
  }

  function positionTooltip(tooltip, container, anchorRect) {
    var containerRect = container.getBoundingClientRect();
    tooltip.style.left = anchorRect.left + anchorRect.width / 2 - containerRect.left + "px";
    tooltip.style.top = anchorRect.top - containerRect.top - 8 + "px";
  }

  function initLineChart(container) {
    var svg = container.querySelector(".chart-svg");
    var tooltip = container.querySelector("[data-tooltip]");
    var crosshair = svg.querySelector("[data-crosshair]");
    if (!svg || !tooltip || !crosshair) return;

    var hits = svg.querySelectorAll(".chart-hit");

    hits.forEach(function (hit) {
      hit.addEventListener("pointerenter", function () {
        var cx = hit.getAttribute("cx");
        crosshair.setAttribute("x1", cx);
        crosshair.setAttribute("x2", cx);
        crosshair.setAttribute("opacity", "1");

        tooltip.innerHTML = "";
        var strong = document.createElement("strong");
        strong.textContent = hit.dataset.value;
        var span = document.createElement("span");
        span.textContent = hit.dataset.label;
        tooltip.appendChild(strong);
        tooltip.appendChild(span);
        tooltip.hidden = false;

        positionTooltip(tooltip, container, hit.getBoundingClientRect());
      });
    });

    container.addEventListener("pointerleave", function () {
      crosshair.setAttribute("opacity", "0");
      tooltip.hidden = true;
    });
  }

  function initBarChart(container) {
    var svg = container.querySelector(".chart-svg");
    var tooltip = container.querySelector("[data-tooltip]");
    if (!svg || !tooltip) return;

    var hits = svg.querySelectorAll(".chart-hit-bar");

    hits.forEach(function (hit) {
      var status = hit.dataset.status;
      var bar = svg.querySelector('.chart-bar[data-status="' + status + '"]');
      var valueLabel = svg.querySelector('.chart-value-label[data-status="' + status + '"]');

      hit.addEventListener("pointerenter", function () {
        if (bar) bar.classList.add("is-hovered");

        tooltip.innerHTML = "";
        var strong = document.createElement("strong");
        strong.textContent = hit.dataset.count + (hit.dataset.count === "1" ? " order" : " orders");
        var span = document.createElement("span");
        span.textContent = status;
        tooltip.appendChild(strong);
        tooltip.appendChild(span);
        tooltip.hidden = false;

        var anchor = valueLabel || hit;
        positionTooltip(tooltip, container, anchor.getBoundingClientRect());
      });

      hit.addEventListener("pointerleave", function () {
        if (bar) bar.classList.remove("is-hovered");
        tooltip.hidden = true;
      });
    });
  }
})();
