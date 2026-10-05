(function (root, factory) {
  'use strict';
  var api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root && root.document) api.boot(root);
})(typeof window === 'undefined' ? null : window, function () {
  'use strict';

  function toMinorUnits(value, currency) {
    if (!Number.isFinite(value) || typeof currency !== 'string' || !/^[A-Z]{3}$/.test(currency)) return undefined;
    try {
      if (typeof Intl.supportedValuesOf === 'function' && !Intl.supportedValuesOf('currency').includes(currency)) return undefined;
      var digits = new Intl.NumberFormat('en', { style: 'currency', currency: currency }).resolvedOptions().maximumFractionDigits;
      var amount = Math.round(value * Math.pow(10, digits));
      return Number.isSafeInteger(amount) && amount >= 0 ? amount : undefined;
    } catch (_) { return undefined; }
  }

  function mapOrder(order) {
    var payload = { type: 'contents', contents: order.contents };
    var amount = toMinorUnits(order.total, order.currency);
    if (amount !== undefined) {
      payload.amount = amount;
      payload.currency = order.currency;
    }
    return payload;
  }

  function boot(win) {
    if (win.__openaiAdsPrestaBooted) return;
    var configNode = win.document.getElementById('oai-pixel-config');
    if (!configNode) return;
    var config;
    try { config = JSON.parse(configNode.textContent); } catch (_) { return; }
    if (!config || !/^[A-Za-z0-9_-]{1,128}$/.test(config.pixelId)) return;
    win.__openaiAdsPrestaBooted = true;
    var consent = false;
    var ready = false;
    var pageSent = false;
    var productSent = false;
    var checkoutSent = false;
    var orderSent = false;
    var orderNode = win.document.getElementById('oai-pixel-order');
    var order = null;
    try { if (orderNode) order = JSON.parse(orderNode.textContent); } catch (_) { /* Invalid order data cannot be measured. */ }

    function send(name, payload) {
      if (!consent || !ready || typeof win.oaiq !== 'function') return false;
      win.oaiq('measureSingle', config.pixelId, name, payload);
      return true;
    }

    function updateConsent() {
      consent = win.openaiAdsMarketingConsent === true;
      if (!consent) {
        if (ready && typeof win.oaiq === 'function') win.oaiq('consent', false);
        ready = false;
        ['__oppref', '__obref'].forEach(function (name) {
          win.document.cookie = name + '=; Max-Age=0; path=/; SameSite=Lax';
        });
        return;
      }
      if (!ready) {
        if (typeof win.oaiq !== 'function') {
          var queue = function () { queue.q.push(arguments); };
          queue.q = [];
          win.oaiq = queue;
          win.oaiq('consent', false);
          var sdk = win.document.createElement('script');
          sdk.async = true;
          sdk.src = 'https://bzrcdn.openai.com/sdk/oaiq.min.js';
          win.document.head.appendChild(sdk);
        }
        win.oaiq('init', { pixelId: config.pixelId });
        win.oaiq('consent', true);
        ready = true;
      }
      if (!pageSent) pageSent = send('page_viewed', { type: 'contents', contents: [{ content_type: 'page', name: win.document.title }] });
      if (!productSent && config.product) productSent = send('contents_viewed', config.product);
      if (!checkoutSent && config.checkout) checkoutSent = send('checkout_started', { type: 'contents' });
      if (order && !orderSent) {
        var key = 'oai-order:' + config.pixelId + ':' + order.key;
        try {
          if (win.localStorage.getItem(key)) { orderSent = true; return; }
        } catch (_) { return; }
        orderSent = send('order_created', mapOrder(order));
        if (orderSent) {
          try { win.localStorage.setItem(key, '1'); } catch (_) { /* In-page deduplication remains active. */ }
        }
      }
    }

    win.addEventListener('openaiAdsMarketingConsentChanged', function (event) {
      win.openaiAdsMarketingConsent = !!event.detail && event.detail.granted === true;
      updateConsent();
    });
    if (win.prestashop && typeof win.prestashop.on === 'function') {
      win.prestashop.on('updateCart', function (event) {
        if (!event || !event.reason || event.reason.linkAction !== 'add-to-cart' || !event.resp || event.resp.hasError || event.resp.success === false) return;
        send('items_added', { type: 'contents' });
      });
      win.prestashop.on('updatedProduct', function () {
        if (config.product) send('contents_viewed', config.product);
      });
    }
    updateConsent();
  }

  return { boot: boot, toMinorUnits: toMinorUnits, mapOrder: mapOrder };
});
