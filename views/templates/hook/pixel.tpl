<script type="application/json" id="oai-pixel-config">{$pixel_config nofilter}</script>
{if $pixel_consent_banner}
<link rel="stylesheet" href="{$consent_style|escape:'html':'UTF-8'}">
<section class="oai-consent" id="oai-consent" aria-label="{l s='Marketing cookie settings' mod='openaiadsfeed'}" hidden>
  <div><strong>{l s='Allow marketing cookies?' mod='openaiadsfeed'}</strong>
    <p>{l s='With your permission, OpenAI Ads Pixel measures page and product views, cart activity and created orders. You can refuse or change your choice at any time.' mod='openaiadsfeed'}</p>
  </div>
  <div class="oai-consent-actions">
    <button type="button" data-oai-consent="false">{l s='Reject marketing cookies' mod='openaiadsfeed'}</button>
    <button type="button" data-oai-consent="true">{l s='Accept marketing cookies' mod='openaiadsfeed'}</button>
  </div>
</section>
<button class="oai-consent-settings" id="oai-consent-settings" type="button" hidden>{l s='Cookie settings' mod='openaiadsfeed'}</button>
<script defer src="{$consent_script|escape:'html':'UTF-8'}"></script>
{/if}
<script defer src="{$pixel_script|escape:'html':'UTF-8'}"></script>
