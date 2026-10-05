# OpenAI Ads i ChatGPT Ads dla PrestaShop

[English](README.md) · [Polski](README.pl.md)

Połącz katalog produktów PrestaShop z OpenAI Ads i przygotuj produkty do kampanii ChatGPT Ads. Moduł synchronizuje dane produktów z usługą feedową Syntropic Signal, która sprawdza i przekształca katalog oraz hostuje gotowy feed produktowy.

**Strona:** [OpenAI Ads dla PrestaShop](https://syntropicsignal.ai/pl/prestashop-openai-ads/) · **Pobierz:** [ZIP modułu PrestaShop](https://github.com/syntropicsignal-ai/prestashop-openai-ads/releases/latest/download/openaiadsfeed-prestashop-0.2.0.zip)

## Co robi moduł

- Wysyła aktywne produkty, kombinacje, ceny, dostępność oraz publiczne adresy produktów i zdjęć do usługi feedowej.
- Udostępnia Hosted URL, który można dodać jako źródło katalogu w OpenAI Ads.
- Obsługuje zaplanowane, codzienne odświeżanie katalogu przez zadanie cron na hostingu sklepu.
- Zawiera opcjonalny Pixel OpenAI Ads do pomiaru wyświetleń stron i produktów, aktywności koszyka, rozpoczęcia zamówienia i utworzenia zamówienia. Pixel jest domyślnie wyłączony i wymaga zgody marketingowej.

Synchronizacja katalogu nie wysyła danych klientów, koszyków ani zamówień. Pomiar Pixelem jest osobną, opcjonalną funkcją. W zdarzeniu `order_created` wysyła nazwy produktów, ilości, walutę i kwotę zamówienia z podatkiem. Nie wysyła nazwisk, adresów e-mail, adresów dostawy ani numerów zamówień. Utworzenie zamówienia nie potwierdza płatności.

Usługa przekształcająca katalog do feedu i hostująca go jest oddzielna od tego repozytorium. Moduł zawiera integrację z PrestaShop, bez logiki konwersji z backendu.

## Instalacja

1. Pobierz ZIP z [GitHub Releases](https://github.com/syntropicsignal-ai/prestashop-openai-ads/releases/latest/download/openaiadsfeed-prestashop-0.2.0.zip). Link będzie dostępny publicznie po upublicznieniu repozytorium i Release.
2. W PrestaShop otwórz **Moduły → Menedżer modułów → Załaduj moduł** i wskaż pobrany ZIP.
3. Otwórz konfigurację modułu i wybierz **Connect and synchronize now**.
4. Dodaj wyświetlony adres synchronizacji do zadań cron na hostingu, aby codziennie odświeżać katalog.
5. Dodaj wyświetlony Hosted URL jako źródło produktów w OpenAI Ads.

Adres synchronizacji zawiera tajny token. Nie udostępniaj go. Hosted URL daje dostęp do katalogu, więc udostępnij go tylko platformie reklamowej.

## Opcjonalny Pixel OpenAI Ads

Pixel jest opcjonalny i domyślnie wyłączony. Wpisz ID Pixela i włącz go w konfiguracji modułu. Pixel czeka na jawną zgodę marketingową przed załadowaniem OpenAI SDK i wysyłaniem zdarzeń. Możesz użyć wbudowanego banera zgody albo połączyć istniejący moduł cookies. Przed włączeniem pomiaru przeczytaj [informacje o konfiguracji i zgodzie](https://syntropicsignal.ai/pl/prestashop-openai-ads/).

Moduł korzysta ze standardowych zdarzeń motywu PrestaShop dotyczących zmian produktów i koszyka. Niestandardowy motyw może wymagać emisji zdarzeń `updateCart` i `updatedProduct`. Potwierdzenie zamówienia wykorzystuje `displayOrderConfirmation`. Deduplikacja w przeglądarce jest najlepszym możliwym staraniem, ale nie gwarantuje dokładnie jednokrotnego wysłania zdarzenia.

## Zgodność

Moduł w wersji **0.2.0** deklaruje zgodność z PrestaShop **od 1.7.8.0 do 9.99.99**. Zgodność zadeklarowana w metadanych nie oznacza, że przetestowano każdy motyw sklepu i każdy moduł zewnętrzny.

## Dane i prywatność

Synchronizacja katalogu wysyła identyfikatory produktów, nazwy, opisy, publiczne adresy produktów i zdjęć, ceny, dostępność, nazwy producentów, wartości GTIN/MPN i opcje wariantów. Do synchronizacji katalogu nie odczytuje danych klientów ani zamówień. Opcjonalny Pixel działa osobno i dopiero po uzyskaniu zgody marketingowej.

Więcej informacji: [polityka prywatności](https://syntropicsignal.ai/pl/polityka-prywatnosci/) i [strona modułu PrestaShop](https://syntropicsignal.ai/pl/prestashop-openai-ads/).

## Rozwój

Moduł nie wymaga Composera ani kompilacji JavaScriptu. To repozytorium zawiera warstwę integracji z PrestaShop. Przekształcanie i hosting feedu odbywają się w osobnej usłudze Syntropic Signal.
