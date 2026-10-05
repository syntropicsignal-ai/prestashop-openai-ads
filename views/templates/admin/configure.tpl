<div class="oai-feed">
  <header class="oai-brand">
    <div class="oai-brand-mark" aria-hidden="true"><span>OAI</span><span>Ads</span></div>
    <div><h1>{l s='OpenAI Ads & ChatGPT Ads for PrestaShop' mod='openaiadsfeed'}</h1><p class="oai-subhead">{l s='Daily product catalog synchronization' mod='openaiadsfeed'}</p></div>
  </header>
  <section class="oai-card">
    <div class="oai-heading">
      <div><span class="oai-eyebrow">{l s='Product catalog' mod='openaiadsfeed'}</span>
        <h2>{if $feed_status && $feed_status.output_items !== null}{$feed_status.output_items|escape:'html':'UTF-8'} {l s='items in the feed' mod='openaiadsfeed'}{elseif $feed_url}{l s='Your feed address' mod='openaiadsfeed'}{else}{l s='Connect your catalog' mod='openaiadsfeed'}{/if}</h2>
        <p>{if $status_unavailable}{l s='Could not retrieve the current catalog status. Try refreshing this page.' mod='openaiadsfeed'}{elseif $feed_status && $feed_status.last_error}{l s='The last synchronization failed. The previous feed remains available.' mod='openaiadsfeed'}{elseif $last_sync}{l s='Your catalog is ready to connect as a Hosted URL in OpenAI Ads.' mod='openaiadsfeed'}{else}{l s='Prepare your product catalog for import into OpenAI Ads.' mod='openaiadsfeed'}{/if}</p>
      </div>
      <span class="oai-pill {if $last_sync && !$feed_status.last_error && !$status_unavailable && $feed_status.output_items > 0}oai-pill-success{/if}"><i aria-hidden="true"></i>{if $status_unavailable}{l s='Status unavailable' mod='openaiadsfeed'}{elseif $feed_status && $feed_status.last_error}{l s='Needs attention' mod='openaiadsfeed'}{elseif $last_sync && $feed_status.output_items > 0}{l s='Feed ready' mod='openaiadsfeed'}{elseif $last_sync}{l s='Empty feed' mod='openaiadsfeed'}{elseif $feed_url}{l s='Feed URL available' mod='openaiadsfeed'}{else}{l s='Not connected' mod='openaiadsfeed'}{/if}</span>
    </div>
    {if $feed_url}
      <div class="oai-schedule-grid">
        <div><span>{l s='Last update' mod='openaiadsfeed'}</span><strong>{if $last_sync}{$last_sync|escape:'html':'UTF-8'}{else}{l s='Not available' mod='openaiadsfeed'}{/if}</strong></div>
        <div><span>{l s='Next update' mod='openaiadsfeed'}</span><strong>{l s='According to your schedule' mod='openaiadsfeed'}</strong></div>
      </div>
      {if $feed_status && $feed_status.source_items !== null}
        <div class="oai-catalog-summary">
          <div><span>{l s='Catalog items' mod='openaiadsfeed'}</span><strong>{$feed_status.source_items|escape:'html':'UTF-8'}</strong></div>
          <div><span>{l s='In the feed' mod='openaiadsfeed'}</span><strong>{$feed_status.output_items|escape:'html':'UTF-8'}</strong></div>
          <div><span>{l s='Skipped' mod='openaiadsfeed'}</span><strong>{$feed_status.excluded_items|escape:'html':'UTF-8'}</strong></div>
        </div>
      {/if}
    {/if}
    {if $feed_url}
      <div class="oai-steps">
        <div class="oai-step"><strong aria-hidden="true">1</strong><div>
          <b>{l s='Copy your feed address' mod='openaiadsfeed'}</b>
          <div class="oai-url-row">
            <input id="oai-feed-url" type="text" readonly value="{$feed_url|escape:'html':'UTF-8'}" aria-label="{l s='Hosted feed URL' mod='openaiadsfeed'}">
            <button class="oai-secondary" type="button" data-oai-copy="oai-feed-url" data-copied="{l s='Copied' mod='openaiadsfeed'}" data-copy-failed="{l s='Select and copy the address' mod='openaiadsfeed'}">{l s='Copy' mod='openaiadsfeed'}</button>
          </div>
          <p class="oai-url-help">{l s='This address gives access to your catalog. Keep it private.' mod='openaiadsfeed'}</p>
        </div></div>
        <div class="oai-step"><strong aria-hidden="true">2</strong><div>
          <b>{l s='Add Hosted URL in OpenAI Ads' mod='openaiadsfeed'}</b>
          <p>{l s='In Feeds, choose Create Feed, then Hosted URL and paste the address.' mod='openaiadsfeed'}</p>
          <a class="oai-external" href="https://ads.openai.com/manage/feeds" target="_blank" rel="noopener noreferrer">{l s='Open OpenAI Ads' mod='openaiadsfeed'} ↗</a>
        </div></div>
      </div>
    {else}
      <p class="oai-connect-help">{l s='Connect the module to generate your feed address.' mod='openaiadsfeed'}</p>
    {/if}
    <form method="post" class="oai-sync-form" data-processing="{l s='Synchronizing…' mod='openaiadsfeed'}">
      <input type="hidden" name="admin_token" value="{$admin_token|escape:'html':'UTF-8'}">
      <button{if $feed_url} class="oai-secondary"{/if} type="submit" name="submitOpenaiadsfeedConnect">{if $feed_url}{l s='Synchronize now' mod='openaiadsfeed'}{else}{l s='Connect and synchronize now' mod='openaiadsfeed'}{/if}</button>
    </form>
    <p class="oai-security-note"><span aria-hidden="true">✓</span>{l s='Product data is sent securely to our service for feed conversion.' mod='openaiadsfeed'}</p>
  </section>
  <section class="oai-card oai-schedule">
    <span class="oai-eyebrow">{l s='Catalog updates' mod='openaiadsfeed'}</span>
    <h2>{l s='Daily synchronization' mod='openaiadsfeed'}</h2>
    <p>{l s='Add this URL to your server cron to refresh the catalog once a day.' mod='openaiadsfeed'}</p>
    <details><summary>{l s='Configure automatic updates' mod='openaiadsfeed'}</summary>
      <p class="oai-url-help">{l s='The next update depends on the schedule configured on your server.' mod='openaiadsfeed'}</p>
      <label for="oai-cron-url">{l s='Synchronization URL' mod='openaiadsfeed'}</label>
      <div class="oai-url-row">
        <input id="oai-cron-url" type="text" readonly value="{$cron_url|escape:'html':'UTF-8'}">
        <button class="oai-secondary" type="button" data-oai-copy="oai-cron-url" data-copied="{l s='Copied' mod='openaiadsfeed'}" data-copy-failed="{l s='Select and copy the address' mod='openaiadsfeed'}">{l s='Copy' mod='openaiadsfeed'}</button>
      </div>
      <p class="oai-url-help">{l s='Keep this address private. Anyone with it can trigger synchronization.' mod='openaiadsfeed'}</p>
    </details>
  </section>
  <section class="oai-card">
    <span class="oai-eyebrow">{l s='Conversion measurement' mod='openaiadsfeed'}</span>
    <h2>OpenAI Ads Pixel</h2>
    <p>{l s='Measure page views, product views, cart additions, checkout starts and created orders after marketing consent.' mod='openaiadsfeed'}</p>
    <form method="post">
      <input type="hidden" name="admin_token" value="{$admin_token|escape:'html':'UTF-8'}">
      <label for="oai-pixel-id">{l s='Pixel ID' mod='openaiadsfeed'}</label>
      <div class="oai-url-row"><input id="oai-pixel-id" name="pixel_id" type="text" maxlength="128" autocomplete="off" value="{$pixel_id|escape:'html':'UTF-8'}"></div>
      <label class="oai-pixel-toggle"><input type="checkbox" name="pixel_enabled" value="1" {if $pixel_enabled}checked{/if}> {l s='Enable Pixel' mod='openaiadsfeed'}</label>
      <label class="oai-pixel-toggle"><input type="checkbox" name="pixel_consent_banner" value="1" {if $pixel_consent_banner}checked{/if}> {l s='Use the built-in consent banner for OpenAI Ads Pixel' mod='openaiadsfeed'}</label>
      <p class="oai-url-help">{l s='Use the built-in banner or connect your existing cookie consent module. Measurement starts only after marketing consent.' mod='openaiadsfeed'}</p>
      <button type="submit" name="submitOpenaiadsfeedPixel">{l s='Save Pixel settings' mod='openaiadsfeed'}</button>
    </form>
    <p class="oai-url-help">{l s='In OpenAI Ads, create a conversion based on order_created and connect it to your campaign. An order event does not confirm payment.' mod='openaiadsfeed'}</p>
  </section>
  <p class="oai-copy-status" role="status" aria-live="polite"></p>
</div>
