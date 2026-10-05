(function (root, factory) {
  'use strict';
  var api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root && root.document) api.boot(root);
})(typeof window === 'undefined' ? null : window, function () {
  'use strict';
  var key = 'openai-ads-marketing-consent:v1';
  var lifetime = 180 * 24 * 60 * 60 * 1000;

  function savedChoice(storage, now) {
    try {
      var value = JSON.parse(storage.getItem(key));
      if (value && typeof value.granted === 'boolean' && Number.isFinite(value.expires) && value.expires > now && value.expires <= now + lifetime) return value.granted;
    } catch (_) { /* Missing or invalid preferences require a new choice. */ }
    return null;
  }

  function boot(win) {
    var banner = win.document.getElementById('oai-consent');
    var settings = win.document.getElementById('oai-consent-settings');
    if (!banner || !settings || banner.dataset.booted) return;
    banner.dataset.booted = 'true';
    var saved = null;
    try { saved = savedChoice(win.localStorage, Date.now()); } catch (_) { /* Storage may be unavailable. */ }
    win.openaiAdsMarketingConsent = saved === true;
    banner.hidden = saved !== null;
    settings.hidden = saved === null;
    settings.addEventListener('click', function () {
      banner.hidden = false;
      settings.hidden = true;
      var first = banner.querySelector('[data-oai-consent]');
      if (first) first.focus();
    });
    banner.addEventListener('click', function (event) {
      var button = event.target.closest('[data-oai-consent]');
      if (!button || !banner.contains(button)) return;
      var granted = button.dataset.oaiConsent === 'true';
      try { win.localStorage.setItem(key, JSON.stringify({ granted: granted, expires: Date.now() + lifetime })); } catch (_) { /* The choice still applies for this page. */ }
      win.openaiAdsMarketingConsent = granted;
      win.dispatchEvent(new win.CustomEvent('openaiAdsMarketingConsentChanged', { detail: { granted: granted } }));
      banner.hidden = true;
      settings.hidden = false;
      settings.focus();
    });
  }
  return { boot: boot, savedChoice: savedChoice };
});
