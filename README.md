# OpenAI Ads and ChatGPT Ads for PrestaShop

[English](README.md) · [Polski](README.pl.md)

Connect a PrestaShop product catalog to OpenAI Ads and prepare products for ChatGPT Ads campaigns. This PrestaShop module synchronizes product data with the Syntropic Signal feed service, which validates and converts the catalog and hosts the resulting product feed.

**Website:** [OpenAI Ads for PrestaShop](https://syntropicsignal.ai/prestashop-openai-ads/) · **Download:** [PrestaShop module ZIP](https://syntropicsignal.ai/downloads/openaiadsfeed-prestashop-0.2.0.zip)

## What the module does

- Sends active products, combinations, prices, availability, and public product and image URLs to the feed service.
- Provides a Hosted URL for adding the catalog to OpenAI Ads.
- Supports a scheduled daily refresh through a cron job on the shop's hosting.
- Includes an optional OpenAI Ads Pixel for page, product, cart, checkout, and order-created events. Pixel measurement is disabled by default and requires marketing consent.

Catalog synchronization does not send customer, cart, or order data. Pixel measurement is separate and optional. For an `order_created` event, it sends product names, quantities, currency, and the tax-inclusive order total. It does not send customer names, email addresses, delivery addresses, or order references. Creating an order does not confirm payment.

The product-feed conversion and hosting service is separate from this repository. The module contains the PrestaShop integration; it does not contain the backend's conversion logic.

## Install

1. Download the module ZIP from the [Syntropic website](https://syntropicsignal.ai/downloads/openaiadsfeed-prestashop-0.2.0.zip) or the [GitHub Releases page](https://github.com/syntropicsignal-ai/prestashop-openai-ads/releases).
2. In PrestaShop, open **Modules → Module Manager → Upload a module** and select the ZIP.
3. Open the module settings and select **Connect and synchronize now**.
4. Add the displayed synchronization URL to your hosting cron scheduler to refresh the catalog daily.
5. Add the displayed Hosted URL as a product source in OpenAI Ads.

The synchronization URL contains a secret token. Keep it private. The Hosted URL provides access to your catalog, so share it only with the ad platform.

## Optional OpenAI Ads Pixel

Pixel measurement is disabled by default. Enter a Pixel ID and enable the Pixel in the module settings. The Pixel waits for explicit marketing consent before loading the OpenAI SDK or sending events. You can use the module's consent banner or connect an existing cookie-consent manager. Read the [setup and consent notes](https://syntropicsignal.ai/prestashop-openai-ads/) before enabling measurement.

The module uses standard PrestaShop theme events for cart and product changes. Custom themes may need to emit `updateCart` and `updatedProduct`. Order confirmation uses `displayOrderConfirmation`; browser-side deduplication is best-effort and does not guarantee exactly-once delivery.

## Compatibility

Module version **0.2.0** declares compatibility with PrestaShop **1.7.8.0 through 9.99.99**. Compatibility declared in module metadata does not mean that every shop theme or third-party module has been tested.

## Data and privacy

Catalog synchronization sends product identifiers, titles, descriptions, public product and image URLs, prices, availability, manufacturer names, GTIN/MPN values, and variant options. It does not read customer or order records for catalog synchronization. Pixel measurement is separate and runs only after marketing consent.

For more information, see the [privacy policy](https://syntropicsignal.ai/privacy-policy/) and the [PrestaShop product page](https://syntropicsignal.ai/prestashop-openai-ads/).

## Development

The module has no Composer or JavaScript build step. This repository contains the PrestaShop integration layer. Product-feed conversion and hosting run in the separate Syntropic Signal service.
