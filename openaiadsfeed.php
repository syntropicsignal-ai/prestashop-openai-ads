<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Openaiadsfeed extends Module
{
    private const API_BASE = 'https://openai-ads.syntropicsignal.ai';
    private const CONNECTOR_ID = 'OPENAIADSFEED_CONNECTOR_ID';
    private const API_TOKEN = 'OPENAIADSFEED_API_TOKEN';
    private const FEED_URL = 'OPENAIADSFEED_FEED_URL';
    private const CRON_TOKEN = 'OPENAIADSFEED_CRON_TOKEN';
    private const BATCH_SIZE = 100;
    private const PIXEL_ID = 'OPENAIADSFEED_PIXEL_ID';
    private const PIXEL_ENABLED = 'OPENAIADSFEED_PIXEL_ENABLED';
    private const PIXEL_CONSENT_BANNER = 'OPENAIADSFEED_CONSENT_BANNER';

    public function __construct()
    {
        $this->name = 'openaiadsfeed';
        $this->tab = 'advertising_marketing';
        $this->version = '0.2.0';
        $this->author = 'Syntropic Signal';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '1.7.8.0', 'max' => '9.99.99'];

        parent::__construct();

        $this->displayName = $this->l('OpenAI Ads & ChatGPT Ads for PrestaShop');
        $this->description = $this->l('Connect your PrestaShop catalog to OpenAI Ads for ChatGPT Ads. Product synchronization, data validation and Hosted URL.');
    }

    public function install()
    {
        return parent::install()
            && Configuration::updateValue(self::CONNECTOR_ID, bin2hex(random_bytes(16)))
            && Configuration::updateValue(self::API_TOKEN, bin2hex(random_bytes(32)))
            && Configuration::updateValue(self::CRON_TOKEN, bin2hex(random_bytes(32)))
            && Configuration::updateValue(self::PIXEL_ENABLED, false)
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayOrderConfirmation');
    }

    public function uninstall()
    {
        $connectorId = (string) Configuration::get(self::CONNECTOR_ID);
        $token = (string) Configuration::get(self::API_TOKEN);
        if ($connectorId !== '' && $token !== '') {
            try {
                $this->request(
                    '/prestashop/v1/connect?connector_id=' . rawurlencode($connectorId),
                    [],
                    'DELETE'
                );
            } catch (Exception $exception) {
                return false;
            }
        }

        return Configuration::deleteByName(self::CONNECTOR_ID)
            && Configuration::deleteByName(self::API_TOKEN)
            && Configuration::deleteByName(self::FEED_URL)
            && Configuration::deleteByName(self::PIXEL_CONSENT_BANNER)
            && Configuration::deleteByName(self::PIXEL_ID)
            && Configuration::deleteByName(self::PIXEL_ENABLED)
            && Configuration::deleteByName(self::CRON_TOKEN)
            && parent::uninstall();
    }

    public function getContent()
    {
        $message = '';
        if (Tools::isSubmit('submitOpenaiadsfeedConnect')) {
            if (!hash_equals(Tools::getAdminTokenLite('AdminModules'), (string) Tools::getValue('admin_token'))) {
                $message = $this->displayError($this->l('The request could not be verified.'));
            } else {
                try {
                    $feedUrl = $this->connectAndSync();
                    Configuration::updateValue(self::FEED_URL, $feedUrl);
                    $message = $this->displayConfirmation($this->l('The feed is connected and synchronized.'));
                } catch (Exception $exception) {
                    $message = $this->displayError($exception->getMessage());
                }
            }
        }

        if (Tools::isSubmit('submitOpenaiadsfeedPixel')) {
            if (!hash_equals(Tools::getAdminTokenLite('AdminModules'), (string) Tools::getValue('admin_token'))) {
                $message = $this->displayError($this->l('The request could not be verified.'));
            } else {
                $pixelId = trim((string) Tools::getValue('pixel_id'));
                $enabled = Tools::getValue('pixel_enabled') === '1';
                if (($pixelId !== '' && !preg_match('/^[A-Za-z0-9_-]{1,128}$/D', $pixelId)) || ($enabled && $pixelId === '')) {
                    $message = $this->displayError($this->l('Enter a valid Pixel ID before enabling the Pixel.'));
                } else {
                    Configuration::updateValue(self::PIXEL_ID, $pixelId);
                    Configuration::updateValue(self::PIXEL_ENABLED, $enabled);
                    Configuration::updateValue(self::PIXEL_CONSENT_BANNER, Tools::getValue('pixel_consent_banner') === '1');
                    $message = $this->displayConfirmation($this->l('Pixel settings saved.'));
                }
            }
        }

        $feedUrl = (string) Configuration::get(self::FEED_URL);
        $cronToken = (string) Configuration::get(self::CRON_TOKEN);
        $cronUrl = $this->context->link->getModuleLink(
            $this->name,
            'cron',
            ['token' => $cronToken],
            true
        );
        $status = null;
        $statusUnavailable = false;
        if ($feedUrl !== '') {
            try {
                $status = $this->request(
                    '/prestashop/v1/status?connector_id=' . rawurlencode((string) Configuration::get(self::CONNECTOR_ID)),
                    [],
                    'GET'
                );
            } catch (Exception $exception) {
                $statusUnavailable = true;
            }
        }
        $lastSync = null;
        if ($status && !empty($status['last_synced_at'])) {
            $lastSync = new DateTimeImmutable($status['last_synced_at']);
            $lastSync = $lastSync->setTimezone(new DateTimeZone((string) Configuration::get('PS_TIMEZONE') ?: 'UTC'));
        }
        $lastSyncLabel = null;
        if ($lastSync) {
            $today = new DateTimeImmutable('today', $lastSync->getTimezone());
            if ($lastSync->format('Y-m-d') === $today->format('Y-m-d')) {
                $lastSyncLabel = $this->l('today') . ', ' . $lastSync->format('H:i');
            } elseif ($lastSync->format('Y-m-d') === $today->modify('-1 day')->format('Y-m-d')) {
                $lastSyncLabel = $this->l('yesterday') . ', ' . $lastSync->format('H:i');
            } else {
                $lastSyncLabel = $lastSync->format('d.m.Y, H:i');
            }
        }
        $this->context->controller->addCSS($this->_path . 'views/css/configure.css?v=' . substr(hash_file('sha256', __DIR__ . '/views/css/configure.css'), 0, 12));
        $this->context->controller->addJS($this->_path . 'views/js/configure.js?v=' . substr(hash_file('sha256', __DIR__ . '/views/js/configure.js'), 0, 12));
        $this->context->smarty->assign([
            'feed_url' => $feedUrl,
            'feed_status' => $status,
            'status_unavailable' => $statusUnavailable,
            'last_sync' => $lastSyncLabel,
            'cron_url' => $cronUrl,
            'pixel_id' => (string) Configuration::get(self::PIXEL_ID),
            'pixel_enabled' => (bool) Configuration::get(self::PIXEL_ENABLED),
            'pixel_consent_banner' => (bool) Configuration::get(self::PIXEL_CONSENT_BANNER),
            'admin_token' => Tools::getAdminTokenLite('AdminModules'),
        ]);

        return $message . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    public function connectAndSync()
    {
        $shop = $this->shopDetails();
        $connectorId = (string) Configuration::get(self::CONNECTOR_ID);
        $token = (string) Configuration::get(self::API_TOKEN);
        $connection = $this->request('/prestashop/v1/connect', [
            'connector_id' => $connectorId,
            'store_url' => $shop['url'],
            'store_name' => $shop['name'],
            'store_country' => $shop['country'],
        ]);
        $feedUrl = isset($connection['feed_url']) && is_string($connection['feed_url'])
            ? $connection['feed_url']
            : '';
        if ($feedUrl === '') {
            throw new RuntimeException($this->l('The feed service returned an invalid response.'));
        }

        $this->syncCatalog();

        return $feedUrl;
    }

    public function syncCatalog()
    {
        $records = $this->productRecords();
        if (!$records) {
            throw new RuntimeException($this->l('No active products were found.'));
        }

        $response = $this->request('/prestashop/v1/sync', [
            'connector_id' => (string) Configuration::get(self::CONNECTOR_ID),
            'records' => $records,
        ]);

        return $response;
    }

    public function cronUrlIsAuthorized($token)
    {
        $configured = (string) Configuration::get(self::CRON_TOKEN);

        return $configured !== '' && is_string($token) && hash_equals($configured, $token);
    }

    public function hookDisplayFooter()
    {
        if (!$this->pixelIsEnabled()) {
            return '';
        }
        $product = $this->context->smarty->getTemplateVars('product');
        $controller = $this->context->controller->php_self;
        $config = [
            'pixelId' => (string) Configuration::get(self::PIXEL_ID),
            'checkout' => $controller === 'order',
            'product' => $controller === 'product' && isset($product['name'])
                ? ['type' => 'contents', 'contents' => [['name' => (string) $product['name'], 'content_type' => 'product']]]
                : null,
        ];
        $this->context->smarty->assign([
            'pixel_config' => json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'pixel_consent_banner' => (bool) Configuration::get(self::PIXEL_CONSENT_BANNER),
            'consent_script' => $this->_path . 'views/js/consent.js?v=' . substr(hash_file('sha256', __DIR__ . '/views/js/consent.js'), 0, 12),
            'consent_style' => $this->_path . 'views/css/consent.css?v=' . substr(hash_file('sha256', __DIR__ . '/views/css/consent.css'), 0, 12),
            'pixel_script' => $this->_path . 'views/js/pixel.js?v=' . substr(hash_file('sha256', __DIR__ . '/views/js/pixel.js'), 0, 12),
        ]);
        return $this->display(__FILE__, 'views/templates/hook/pixel.tpl');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (!$this->pixelIsEnabled() || !isset($params['order']) || !Validate::isLoadedObject($params['order'])) {
            return '';
        }
        $order = $params['order'];
        $currency = new Currency((int) $order->id_currency);
        $contents = [];
        foreach ($order->getProducts() as $product) {
            $contents[] = ['name' => (string) $product['product_name'], 'quantity' => (int) $product['product_quantity']];
        }
        $this->context->smarty->assign('pixel_order', json_encode([
            'key' => hash('sha256', $order->id . ':' . $order->secure_key),
            'total' => (float) $order->total_paid_tax_incl,
            'currency' => (string) $currency->iso_code,
            'contents' => $contents,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        return $this->display(__FILE__, 'views/templates/hook/order.tpl');
    }

    private function pixelIsEnabled()
    {
        return Configuration::get(self::PIXEL_ENABLED)
            && preg_match('/^[A-Za-z0-9_-]{1,128}$/D', (string) Configuration::get(self::PIXEL_ID));
    }

    private function productRecords()
    {
        $context = Context::getContext();
        $languageId = (int) Configuration::get('PS_LANG_DEFAULT');
        $shopId = (int) $context->shop->id;
        $context->language = new Language($languageId);
        $currencyId = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        if ($currencyId > 0) {
            $context->currency = new Currency($currencyId);
        }

        $records = [];
        $offset = 0;
        while (true) {
            $products = Product::getProducts(
                $languageId,
                $offset,
                self::BATCH_SIZE,
                'id_product',
                'ASC',
                false,
                true,
                $context
            );
            if (!$products) {
                break;
            }

            foreach ($products as $productRow) {
                $productId = (int) $productRow['id_product'];
                $product = new Product($productId, false, $languageId, $shopId);
                if (!Validate::isLoadedObject($product)) {
                    continue;
                }

                $combinations = $product->getAttributeCombinations($languageId);
                $groupedCombinations = [];
                foreach (is_array($combinations) ? $combinations : [] as $combination) {
                    $attributeId = (int) $combination['id_product_attribute'];
                    $groupedCombinations[$attributeId][] = $combination;
                }

                if (!$groupedCombinations) {
                    $records[] = $this->productRecord($product, $productRow, 0, []);
                    continue;
                }

                foreach ($groupedCombinations as $attributeId => $attributes) {
                    $options = [];
                    foreach ($attributes as $attribute) {
                        $group = trim((string) $attribute['group_name']);
                        $value = trim((string) $attribute['attribute_name']);
                        if ($group !== '' && $value !== '') {
                            $options[$group] = $value;
                        }
                    }
                    $records[] = $this->productRecord(
                        $product,
                        $productRow,
                        (int) $attributeId,
                        $options,
                        $attributes[0]
                    );
                }
            }
            $offset += count($products);
        }

        return $records;
    }

    private function productRecord($product, array $row, $attributeId, array $options, array $variant = [])
    {
        $context = Context::getContext();
        $productId = (int) $product->id;
        $shopId = (int) $context->shop->id;
        $cover = $attributeId > 0
            ? Product::getCombinationImageById($attributeId, (int) $context->language->id)
            : false;
        if (!$cover || empty($cover['id_image'])) {
            $cover = Image::getCover($productId, $shopId);
        }

        $imageUrl = '';
        if ($cover && !empty($cover['id_image'])) {
            $imageUrl = $context->link->getImageLink(
                $product->link_rewrite,
                (int) $cover['id_image'],
                'large_default'
            );
            if (strpos($imageUrl, 'http://') === 0) {
                $imageUrl = 'https://' . substr($imageUrl, 7);
            }
        }

        $quantity = (int) StockAvailable::getQuantityAvailableByProduct(
            $productId,
            $attributeId,
            $shopId
        );
        $name = trim((string) $product->name);
        $variantLabel = implode(', ', array_values($options));
        $title = $variantLabel === '' ? $name : $name . ' - ' . $variantLabel;
        $price = (string) Product::getPriceStatic($productId, true, $attributeId ?: null);
        $currencyCode = (string) Context::getContext()->currency->iso_code;
        $itemId = $attributeId > 0 ? $productId . ':' . $attributeId : (string) $productId;
        $description = trim(strip_tags((string) $product->description_short));
        if ($description === '') {
            $description = trim(strip_tags((string) $product->description));
        }
        $normalizedOptions = array_change_key_case($options, CASE_LOWER);
        $variantDict = $options ? json_encode($options, JSON_UNESCAPED_UNICODE) : '';
        if (!is_string($variantDict)) {
            throw new RuntimeException($this->l('Could not encode product variants.'));
        }

        $record = [
            'id' => $itemId,
            'title' => $title,
            'description' => $description,
            'link' => $context->link->getProductLink(
                $product,
                null,
                null,
                null,
                null,
                $shopId,
                $attributeId > 0 ? $attributeId : null
            ),
            'image_link' => $imageUrl,
            'availability' => $quantity > 0 ? 'in_stock' : 'out_of_stock',
            'price' => $price . ' ' . $currencyCode,
            'brand' => trim((string) ($row['manufacturer_name'] ?? '')),
            'gtin' => (string) ($variant['ean13'] ?? $product->ean13 ?? ''),
            'mpn' => (string) ($variant['reference'] ?? $product->reference ?? ''),
            'condition' => 'new',
            'product_type' => trim((string) ($row['category'] ?? '')),
            'color' => $normalizedOptions['color'] ?? $normalizedOptions['kolor'] ?? '',
            'size' => $normalizedOptions['size'] ?? $normalizedOptions['rozmiar'] ?? '',
            'material' => $normalizedOptions['material'] ?? $normalizedOptions['materiał'] ?? '',
            'offer_id' => (string) ($variant['reference'] ?? $product->reference ?? ''),
            'group_id' => $attributeId > 0 ? (string) $productId : '',
            'listing_has_variations' => $attributeId > 0 ? 'true' : '',
            'variant_dict' => $variantDict,
        ];

        return $record;
    }

    private function shopDetails()
    {
        $country = new Country((int) Configuration::get('PS_COUNTRY_DEFAULT'));

        return [
            'url' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__,
            'name' => (string) Configuration::get('PS_SHOP_NAME'),
            'country' => (string) $country->iso_code,
        ];
    }

    private function request($path, array $payload, $method = 'POST')
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($body === false) {
            throw new RuntimeException($this->l('Could not encode the product catalog.'));
        }

        $token = (string) Configuration::get(self::API_TOKEN);
        $url = self::API_BASE . $path;
        $lastError = $this->l('The feed service is unavailable.');
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $handle = curl_init($url);
            curl_setopt_array($handle, [
                CURLOPT_POST => $method === 'POST',
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ],
            ]);
            if ($method !== 'GET') {
                curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
            }
            $response = curl_exec($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            $curlError = curl_error($handle);
            curl_close($handle);

            if (is_string($response) && $status >= 200 && $status < 300) {
                $decoded = json_decode($response, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
                throw new RuntimeException($this->l('The feed service returned invalid JSON.'));
            }

            if ($curlError !== '') {
                $lastError = $this->l('Could not reach the feed service.');
            } else {
                $decoded = is_string($response) ? json_decode($response, true) : null;
                $lastError = is_array($decoded) && isset($decoded['detail'])
                    ? (string) $decoded['detail']
                    : $this->l('The feed service rejected the request.') . ' (' . $status . ')';
            }

            if ($status !== 0 && $status < 500 && $status !== 429) {
                break;
            }
            if ($attempt < 2) {
                usleep(500000 * (2 ** $attempt));
            }
        }

        throw new RuntimeException($lastError);
    }
}
