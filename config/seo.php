<?php
// ── Site Constants ─────────────────────────────────────────────────────────
define('SITE_URL',      'https://vimpelbeer.am');
define('SITE_NAME',     'Vimpel Armenia');
define('SITE_PHONE',    '+374091310481');
define('SITE_EMAIL',    'info@vimpelbeer.am');
define('SITE_FB',       'https://www.facebook.com/662121683654439');
define('SITE_IG',       'https://www.instagram.com/vimpelofficial');
define('OG_IMAGE',      SITE_URL . '/assets/images/hero/hero-1.webp');
define('OG_IMG_W',      '1200');
define('OG_IMG_H',      '800');

define('VIMPEL_DEFAULT_LANG', 'hy');
define('VIMPEL_LANG_COOKIE',  'vimpel_lang');
$_supported_langs = ['hy', 'ru', 'en'];

/** Site root path when deployed in a subdirectory (empty string at domain root). */
function vimpel_base_path(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir = str_replace('\\', '/', dirname($script));
    if ($dir === '/' || $dir === '.') {
        return '';
    }
    $leaf = basename($dir);
    if (in_array($leaf, ['hy', 'en', 'ru'], true)) {
        $parent = dirname($dir);
        return ($parent === '/' || $parent === '.') ? '' : rtrim($parent, '/');
    }
    return rtrim($dir, '/');
}

function vimpel_is_preview(): bool
{
    if (getenv('VIMPEL_PREVIEW') === '1') {
        return true;
    }
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($uri, '/preview/') !== false;
}

/** Site root prefix (empty at domain root, or e.g. `/preview/runs/CODE`). */
function vimpel_site_base(): string
{
    $forwardedPrefix = $_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? '';
    if (is_string($forwardedPrefix) && $forwardedPrefix !== '') {
        return rtrim($forwardedPrefix, '/');
    }

    if (vimpel_is_preview()) {
        $env = getenv('VIMPEL_PREVIEW_BASE');
        if (is_string($env) && $env !== '') {
            return rtrim($env, '/');
        }
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path = preg_replace('#/(?:hy|en|ru)(?:/|$)#', '', $path);
        $path = preg_replace('#/index\.php.*$#', '', $path);
        return rtrim($path, '/') ?: '';
    }
    return vimpel_base_path();
}

/** Request path relative to site base: `/`, `/hy`, `/en`, `/ru`. */
function vimpel_request_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = vimpel_site_base();
    if ($base !== '' && strpos($uri, $base) === 0) {
        $uri = substr($uri, strlen($base)) ?: '/';
    }
    $trimmed = trim($uri, '/');
    if ($trimmed === '') {
        return '/';
    }
    if (preg_match('#^(hy|en|ru)/index\.php$#', $trimmed, $m)) {
        return '/' . $m[1];
    }
    return '/' . $trimmed;
}

/** Root URL for static assets (CSS/JS/images). Language lives in the path, not asset dirs. */
function vimpel_asset_base(): string
{
    $base = vimpel_site_base();
    return ($base === '' ? '/' : $base . '/');
}

/** Canonical SEO path: `/hy/`, `/en/`, `/ru/`. */
function vimpel_lang_path(string $lang): string
{
    if (!in_array($lang, ['hy', 'ru', 'en'], true)) {
        $lang = VIMPEL_DEFAULT_LANG;
    }
    $base = vimpel_site_base();
    $prefix = ($base === '' ? '' : $base);
    return $prefix . '/' . $lang . '/';
}

/** Language switcher href — relative, resolved via <base> (works in preview + production). */
function vimpel_lang_switch_path(string $lang): string
{
    if (!in_array($lang, ['hy', 'ru', 'en'], true)) {
        $lang = VIMPEL_DEFAULT_LANG;
    }
    return 'index.php?lang=' . $lang;
}

/** Absolute canonical / hreflang URL for a language. */
function vimpel_lang_url(string $lang, string $hash = ''): string
{
    $url = rtrim(SITE_URL, '/') . vimpel_lang_path($lang);
    if ($hash !== '') {
        $url .= '#' . ltrim($hash, '#');
    }
    return $url;
}

// ── Legacy URL redirects → canonical /hy/ /en/ /ru/ ───────────────────────
$path = vimpel_request_path();

if (!vimpel_is_preview()) {
    if ($path === '/') {
        header('Location: ' . vimpel_lang_path('hy'), true, 301);
        exit;
    }

    if (isset($_GET['lang']) && in_array($_GET['lang'], $_supported_langs, true)) {
        $getLang = $_GET['lang'];
        if ($path !== '/' . $getLang) {
            header('Location: ' . vimpel_lang_path($getLang), true, 301);
            exit;
        }
    }

    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('#/index\.php/(hy|en|ru)(?:/|$)#i', $request_uri, $lang_match)) {
        header('Location: ' . vimpel_lang_path(strtolower($lang_match[1])), true, 301);
        exit;
    }
}

// ── Language Detection ─────────────────────────────────────────────────────
$lang = VIMPEL_DEFAULT_LANG;

if (preg_match('#^/(hy|en|ru)$#', $path, $m)) {
    $lang = $m[1];
} elseif (isset($_GET['lang']) && in_array($_GET['lang'], $_supported_langs, true)) {
    $lang = $_GET['lang'];
} elseif (!empty($_SERVER['PATH_INFO'])) {
    $path_info = trim($_SERVER['PATH_INFO'], '/');
    if (in_array($path_info, $_supported_langs, true)) {
        $lang = $path_info;
    }
}

if ($lang === VIMPEL_DEFAULT_LANG) {
    if (isset($_COOKIE[VIMPEL_LANG_COOKIE]) && in_array($_COOKIE[VIMPEL_LANG_COOKIE], $_supported_langs, true)) {
        $lang = $_COOKIE[VIMPEL_LANG_COOKIE];
    } else {
        $accept = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        if (strpos($accept, 'hy') !== false)       $lang = 'hy';
        elseif (strpos($accept, 'ru') !== false)   $lang = 'ru';
        elseif (strpos($accept, 'en') !== false)   $lang = 'en';
    }
}

if (!headers_sent()) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $cookie_path = vimpel_site_base() === '' ? '/' : vimpel_site_base() . '/';
    setcookie(
        VIMPEL_LANG_COOKIE,
        $lang,
        time() + 365 * 86400,
        $cookie_path,
        '',
        $secure,
        true
    );
}

$lang_urls = [
    'hy' => vimpel_lang_url('hy'),
    'en' => vimpel_lang_url('en'),
    'ru' => vimpel_lang_url('ru'),
];
$lang_paths = [
    'hy' => vimpel_lang_switch_path('hy'),
    'en' => vimpel_lang_switch_path('en'),
    'ru' => vimpel_lang_switch_path('ru'),
];

// ── Per-Language SEO Metadata ──────────────────────────────────────────────
$_seo_meta = [
    'hy' => [
        'html_lang'   => 'hy',
        'og_locale'   => 'hy_AM',
        'title'       => 'Vimpel Armenia — Հայկական Պրեմիում Գարեջուր | Lager & Pilsner',
        'description' => 'Vimpel Armenia-ն Հայաստանի ամենաբարձրորակ ձեռագործ գարեջրի բրենդն է՝ ասպետական ոգով պատրաստված Lager-ով ու Pilsner-ով։ Ամեն բաժակ կրում է ամրոցի ոգն ու ավանդույթը։ Կապ՝ +374 091 310481։',
        'keywords'    => 'Vimpel, Vimpel Armenia, հայկական գարեջուր, Armenian beer, Lager, Pilsner, Հայաստան, craft beer Armenia, Վիմփել, հայ գարեջուր',
        'og_title'    => 'Vimpel Armenia — Հայկական Պրեմիում Գարեջուր',
        'og_desc'     => 'Ամեն բաժակ Vimpel-ի կրում է ամրոցի ոգն ու ասպետի արժանապատվությունը։ Lager & Pilsner — ձեռագործ, Հայաստանում։',
        'twitter_title' => 'Vimpel Armenia — Հայկական Պրեմիում Գարեջուր',
        'twitter_desc'  => 'Lager & Pilsner — ձեռագործ ու ասպետական ոգով Հայաստանում։ Կապ՝ +374 091 310481',
    ],
    'en' => [
        'html_lang'   => 'en',
        'og_locale'   => 'en_US',
        'title'       => 'Vimpel Armenia — Premium Armenian Craft Beer | Lager & Pilsner',
        'description' => 'Vimpel Armenia is Armenia\'s premium craft beer brand. Our knight-inspired Lager and Pilsner are brewed with tradition and precision. Experience the spirit of the North in every glass. Call: +374 091 310481.',
        'keywords'    => 'Vimpel Armenia, Armenian beer, craft beer Armenia, Lager, Pilsner, Armenian brewery, premium beer Yerevan, beer Armenia',
        'og_title'    => 'Vimpel Armenia — Premium Armenian Craft Beer',
        'og_desc'     => 'Every glass of Vimpel carries the spirit of the castle and the honour of the knight. Lager & Pilsner — handcrafted in Armenia.',
        'twitter_title' => 'Vimpel Armenia — Premium Armenian Craft Beer',
        'twitter_desc'  => 'Knight-inspired Lager & Pilsner — handcrafted in Armenia. Call: +374 091 310481',
    ],
    'ru' => [
        'html_lang'   => 'ru',
        'og_locale'   => 'ru_RU',
        'title'       => 'Vimpel Armenia — Премиальное Армянское Крафтовое Пиво | Lager & Pilsner',
        'description' => 'Vimpel Armenia — лучший крафтовый пивной бренд Армении. Наш Lager и Pilsner созданы с рыцарской традицией и точностью. Почувствуйте дух Севера в каждом бокале. Тел: +374 091 310481.',
        'keywords'    => 'Vimpel Armenia, армянское пиво, крафтовое пиво Армения, Lager, Pilsner, пивоварня Армения, пиво Ереван, Vimpel пиво',
        'og_title'    => 'Vimpel Armenia — Премиальное Армянское Пиво',
        'og_desc'     => 'Каждый бокал Vimpel несёт дух замка и честь рыцаря. Lager и Pilsner — ручная работа, Армения.',
        'twitter_title' => 'Vimpel Armenia — Армянское Крафтовое Пиво',
        'twitter_desc'  => 'Lager & Pilsner — рыцарское пиво ручной работы из Армении. Тел: +374 091 310481',
    ],
];

$seo = $_seo_meta[$lang];

// ── Canonical URL (/ for hy, /en/ and /ru/ for other languages) ───────────
$canonical = vimpel_lang_url($lang);

// ── JSON-LD Structured Data ────────────────────────────────────────────────
$json_ld = [
    '@context' => 'https://schema.org',
    '@graph'   => [
        // Organization
        [
            '@type'  => 'Organization',
            '@id'    => SITE_URL . '/#organization',
            'name'   => SITE_NAME,
            'url'    => SITE_URL,
            'logo'   => [
                '@type'  => 'ImageObject',
                'url'    => SITE_URL . '/assets/images/brand/logo.webp',
                'width'  => 200,
                'height' => 200,
            ],
            'email'  => SITE_EMAIL,
            'telephone' => SITE_PHONE,
            'sameAs' => [SITE_FB, SITE_IG],
            'contactPoint' => [
                '@type'               => 'ContactPoint',
                'telephone'           => SITE_PHONE,
                'contactType'         => 'customer service',
                'availableLanguage'   => ['Armenian', 'Russian', 'English'],
            ],
        ],
        // FoodEstablishment / Brewery
        [
            '@type'           => ['FoodEstablishment', 'BarOrPub'],
            '@id'             => SITE_URL . '/#business',
            'name'            => SITE_NAME,
            'description'     => 'Premium Armenian craft beer brand offering Lager and Pilsner brewed with knightly tradition and precision.',
            'url'             => SITE_URL,
            'telephone'       => SITE_PHONE,
            'email'           => SITE_EMAIL,
            'image'           => OG_IMAGE,
            'servesCuisine'   => ['Beer', 'Craft Beer'],
            'menu'            => SITE_URL . '/#beers',
            'priceRange'      => '$$',
            'address'         => [
                '@type'           => 'PostalAddress',
                'addressCountry'  => 'AM',
                'addressLocality' => 'Yerevan',
            ],
            'geo' => [
                '@type'     => 'GeoCoordinates',
                'latitude'  => 40.1872,
                'longitude' => 44.5152,
            ],
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => '5',
                'bestRating'  => '5',
                'worstRating' => '1',
                'reviewCount' => '3',
            ],
            'review' => [
                [
                    '@type'      => 'Review',
                    'reviewBody' => 'Vimpel\'s Lager is among Armenia\'s finest beers — a clean taste, bold and knightly. I recommend it to everyone.',
                    'author'     => ['@type' => 'Person', 'name' => 'Arman H.'],
                    'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                ],
                [
                    '@type'      => 'Review',
                    'reviewBody' => 'The Pilsner is a revelation at every gathering. Bold yet delicate — Vimpel is a devoted brewer.',
                    'author'     => ['@type' => 'Person', 'name' => 'Davit M.'],
                    'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                ],
                [
                    '@type'      => 'Review',
                    'reviewBody' => 'Vimpel is not just beer — it\'s a way of life. For all my friends, it\'s now their go-to favourite.',
                    'author'     => ['@type' => 'Person', 'name' => 'Ani S.'],
                    'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                ],
            ],
        ],
        // Product List — Beer lineup
        [
            '@type'           => 'ItemList',
            'name'            => 'Vimpel Beer Selection',
            'description'     => 'Armenian craft beers by Vimpel Armenia',
            'url'             => SITE_URL . '/#beers',
            'itemListElement' => [
                [
                    '@type'    => 'ListItem',
                    'position' => 1,
                    'item'     => [
                        '@type'       => 'Product',
                        'name'        => 'Vimpel Lager',
                        'description' => 'A crisp lager with a clean malt profile, subtle herbal notes, and a bright, knightly finish.',
                        'image'       => SITE_URL . '/assets/images/beers/beers-lager.webp',
                        'brand'       => ['@type' => 'Brand', 'name' => SITE_NAME],
                        'offers'      => ['@type' => 'Offer', 'availability' => 'https://schema.org/InStock', 'priceCurrency' => 'AMD'],
                    ],
                ],
                [
                    '@type'    => 'ListItem',
                    'position' => 2,
                    'item'     => [
                        '@type'       => 'Product',
                        'name'        => 'Vimpel Pilsner',
                        'description' => 'A honey-amber ale with soft caramel sweetness, balanced bitterness, and a smooth, rounded body.',
                        'image'       => SITE_URL . '/assets/images/beers/beers-pilsner.webp',
                        'brand'       => ['@type' => 'Brand', 'name' => SITE_NAME],
                        'offers'      => ['@type' => 'Offer', 'availability' => 'https://schema.org/InStock', 'priceCurrency' => 'AMD'],
                    ],
                ],
            ],
        ],
        // BreadcrumbList for navigation
        [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',        'item' => SITE_URL . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'About',       'item' => SITE_URL . '/#about'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Beers',       'item' => SITE_URL . '/#beers'],
                ['@type' => 'ListItem', 'position' => 4, 'name' => 'Our Legend', 'item' => SITE_URL . '/#history'],
                ['@type' => 'ListItem', 'position' => 5, 'name' => 'Gallery',     'item' => SITE_URL . '/#gallery'],
                ['@type' => 'ListItem', 'position' => 6, 'name' => 'Reviews',     'item' => SITE_URL . '/#reviews'],
                ['@type' => 'ListItem', 'position' => 7, 'name' => 'Contact',     'item' => SITE_URL . '/#contact'],
            ],
        ],
        // WebSite with SearchAction (sitelinks searchbox eligibility)
        [
            '@type'            => 'WebSite',
            '@id'              => SITE_URL . '/#website',
            'url'              => SITE_URL,
            'name'             => SITE_NAME,
            'inLanguage'       => ['hy', 'ru', 'en'],
            'publisher'        => ['@id' => SITE_URL . '/#organization'],
        ],
    ],
];
