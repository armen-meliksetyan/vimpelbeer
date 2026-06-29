<?php
require_once __DIR__ . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($seo['html_lang'], ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= htmlspecialchars(vimpel_asset_base(), ENT_QUOTES, 'UTF-8') ?>">

    <!-- ── Primary Meta ──────────────────────────────────────────────────── -->
    <title><?= htmlspecialchars($seo['title'], ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($seo['description'], ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords"    content="<?= htmlspecialchars($seo['keywords'], ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots"      content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="author"      content="Vimpel Armenia">

    <!-- ── Canonical & hreflang ──────────────────────────────────────────── -->
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="alternate" hreflang="hy"        href="<?= htmlspecialchars($lang_urls['hy'], ENT_QUOTES, 'UTF-8') ?>">
    <link rel="alternate" hreflang="en"        href="<?= htmlspecialchars($lang_urls['en'], ENT_QUOTES, 'UTF-8') ?>">
    <link rel="alternate" hreflang="ru"        href="<?= htmlspecialchars($lang_urls['ru'], ENT_QUOTES, 'UTF-8') ?>">
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($lang_urls['hy'], ENT_QUOTES, 'UTF-8') ?>">
    <!-- Canonical language paths: /hy/ /en/ /ru/ -->

    <script>
    window.VIMPEL_LANG = <?= json_encode([
        'current' => $lang,
        'urls'    => $lang_paths,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.VIMPEL_CONTACT_ENDPOINT = <?= json_encode(vimpel_contact_endpoint(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>

    <!-- ── Open Graph ────────────────────────────────────────────────────── -->
    <meta property="og:type"         content="website">
    <meta property="og:url"          content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name"    content="Vimpel Armenia">
    <meta property="og:locale"       content="<?= htmlspecialchars($seo['og_locale'], ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title"        content="<?= htmlspecialchars($seo['og_title'], ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description"  content="<?= htmlspecialchars($seo['og_desc'], ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image"        content="<?= OG_IMAGE ?>">
    <meta property="og:image:width"  content="<?= OG_IMG_W ?>">
    <meta property="og:image:height" content="<?= OG_IMG_H ?>">
    <meta property="og:image:alt"    content="Vimpel Armenia — Premium Armenian Craft Beer">

    <!-- ── Twitter / X Card ──────────────────────────────────────────────── -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@vimpelofficial">
    <meta name="twitter:title"       content="<?= htmlspecialchars($seo['twitter_title'], ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seo['twitter_desc'], ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image"       content="<?= OG_IMAGE ?>">
    <meta name="twitter:image:alt"   content="Vimpel Armenia — Premium Armenian Craft Beer">

    <!-- ── Favicon & App Icons ───────────────────────────────────────────── -->
    <link rel="icon"             type="image/x-icon"  href="favicon.ico">
    <link rel="icon"             type="image/webp" sizes="32x32" href="assets/images/brand/favicon-32x32.webp">
    <link rel="icon"             type="image/webp" sizes="16x16" href="assets/images/brand/favicon-16x16.webp">
    <link rel="apple-touch-icon" sizes="180x180"               href="assets/images/brand/apple-touch-icon.webp">
    <link rel="manifest"                                        href="site.webmanifest">
    <meta name="theme-color" content="#1c0800">

    <!-- ── Structured Data (JSON-LD) ─────────────────────────────────────── -->
    <script type="application/ld+json">
    <?= json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>

    <link rel="preload" as="image" href="assets/images/hero/hero-1.webp" fetchpriority="high">

    <!-- Local fonts — preload by page language -->
    <?php if ($lang === 'en'): ?>
    <link rel="preload" href="assets/fonts/BystanderSerif-Light.ttf" as="font" type="font/ttf" crossorigin>
    <?php else: ?>
    <link rel="preload" href="assets/fonts/Mardoto-Regular.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="assets/fonts/Mardoto-Bold.ttf" as="font" type="font/ttf" crossorigin>
    <?php endif; ?>

    <!-- Font Awesome — async, non-blocking -->
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

    <!-- AOS — async, non-blocking (jsDelivr: faster, more reliable than unpkg) -->
    <link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.css" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.css" rel="stylesheet"></noscript>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css?v=95">

</head>
<body>
    <!-- Preloader -->
        <div id="preloader">
        <div class="loader-content">
            <div class="loader-icon"><i class="fas fa-dharmachakra" aria-hidden="true"></i></div>
            <div class="loader-bar"></div>
        </div>
    </div>

    <!-- Age Verification Gate (shown on first visit) -->
    <div id="age-gate" class="age-gate" lang="en" role="dialog" aria-modal="true" aria-labelledby="age-gate-title" hidden>
        <div class="age-gate__backdrop"></div>
        <div class="age-gate__panel">
            <div class="age-gate__banner">
                <img src="assets/images/brand/logo.webp" alt="Vimpel" class="age-gate__logo" width="72" height="72">
            </div>
            <div class="age-gate__body">
                <div class="age-gate__rule" aria-hidden="true"><span></span><i>✠</i><span></span></div>
                <h2 id="age-gate-title" class="age-gate__heading">
                    <em class="age-gate__heading-pre">Are you</em>
                    <strong class="age-gate__heading-main">18</strong>
                </h2>
                <p class="age-gate__legal">You must be 18 or older to enter this site.</p>
                <div class="age-gate__rule" aria-hidden="true"><span></span><i>✠</i><span></span></div>
                <div class="age-gate__btns" id="age-gate-btns">
                    <button type="button" class="age-gate__btn age-gate__btn--yes" id="age-gate-yes">Yes</button>
                    <button type="button" class="age-gate__btn age-gate__btn--no" id="age-gate-no">No</button>
                </div>
                <p class="age-gate__sorry" id="age-gate-sorry" hidden>Sorry, you must be 18 or older to enter this site.</p>
            </div>
        </div>
    </div>

    <!-- Header — minimal bar: MENU | logo | RU - EN - AM -->
    <header id="header">
        <div class="container header-container header-container--minimal">
            <div class="header-minimal-left">
                <button type="button" class="header-menu-btn" aria-expanded="false" aria-controls="site-menu-overlay" data-i18n="nav_menu">
                    MENU
                </button>
            </div>
            <div class="header-minimal-center">
                <a href="#home" class="header-minimal-logo" aria-label="Vimpel — home">
                    <img src="assets/images/brand/logo.webp" alt="" width="56" height="56" id="main-logo">
                </a>
            </div>
            <div class="header-minimal-right">
                <div class="lang-switcher lang-switcher--minimal" role="group" aria-label="Language">
                    <a href="<?= htmlspecialchars($lang_paths['ru'], ENT_QUOTES, 'UTF-8') ?>" class="lang-btn<?= $lang === 'ru' ? ' active' : '' ?>" data-lang="ru" hreflang="ru" lang="ru">RU</a>
                    <span class="lang-switcher__sep" aria-hidden="true"> - </span>
                    <a href="<?= htmlspecialchars($lang_paths['en'], ENT_QUOTES, 'UTF-8') ?>" class="lang-btn<?= $lang === 'en' ? ' active' : '' ?>" data-lang="en" hreflang="en" lang="en">EN</a>
                    <span class="lang-switcher__sep" aria-hidden="true"> - </span>
                    <a href="<?= htmlspecialchars($lang_paths['hy'], ENT_QUOTES, 'UTF-8') ?>" class="lang-btn<?= $lang === 'hy' ? ' active' : '' ?>" data-lang="hy" hreflang="hy" lang="hy">AM</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Full-screen menu (opened from MENU) -->
    <div class="mobile-menu-overlay" id="site-menu-overlay" role="dialog" aria-modal="true" aria-label="Site menu">
        <div class="mobile-menu-backdrop" data-close-menu tabindex="-1" aria-hidden="true"></div>
        <div class="mobile-menu-panel">
            <div class="mobile-menu-header">
                <p class="mobile-menu-eyebrow" data-i18n="nav_menu_label">Նավիգացիա</p>
                <button type="button" class="menu-close-btn" data-i18n-aria="aria_close_menu" aria-label="Close menu">
                    <span class="menu-close-btn__icon" aria-hidden="true"></span>
                </button>
            </div>
            <nav class="mobile-nav" aria-label="Primary">
                <ul>
                    <li><a href="#home" data-i18n="nav_home">Գլխավոր</a></li>
                    <li><a href="#about" data-i18n="nav_about">Մեր մասին</a></li>
                    <li><a href="#beers" data-i18n="nav_beers">Գարեջուրներ</a></li>
                    <li><a href="#history" data-i18n="nav_history">Մեր լեգենդը</a></li>
                    <li><a href="#gallery" data-i18n="nav_gallery">Պատկերասրահ</a></li>
                    <li><a href="#reviews" data-i18n="nav_reviews">Կարծիքներ</a></li>
                    <li><a href="#contact" data-i18n="nav_contact">Կապ</a></li>
                </ul>
            </nav>
            <div class="menu-crest" aria-hidden="true">
                <span class="menu-crest__line"></span>
                <span class="menu-crest__symbol">⚜&#xFE0E;</span>
                <span data-i18n="menu_crest">Guardians of the North</span>
                <span class="menu-crest__symbol">⚜&#xFE0E;</span>
                <span class="menu-crest__line"></span>
            </div>
        </div>
    </div>

    <main>
        <!-- Hero Section — Bento Grid Scroll Animation -->
        <section id="home" class="bento-hero-section">
            <!-- Sticky bento grid backdrop -->
            <div class="bento-grid-sticky" id="bento-grid-sticky">
                <div class="bento-grid__tiles">
                    <div class="bento-cell bento-cell--1">
                        <img src="assets/images/hero/hero-1.webp" alt="Vimpel" loading="eager" decoding="async" fetchpriority="high">
                    </div>
                    <div class="bento-cell bento-cell--2">
                        <img src="assets/images/hero/hero-2.webp" alt="Vimpel Beer" loading="eager" decoding="async">
                    </div>
                    <div class="bento-cell bento-cell--3">
                        <img src="assets/images/hero/hero-3.webp" alt="Vimpel Beer" loading="eager" decoding="async">
                    </div>
                    <div class="bento-cell bento-cell--4">
                        <img src="assets/images/hero/hero-4.webp" alt="Vimpel Beer" loading="eager" decoding="async">
                    </div>
                    <div class="bento-cell bento-cell--5">
                        <picture>
                            <source media="(max-width: 767px)" srcset="assets/images/hero/hero-2.webp">
                            <img src="assets/images/hero/hero-5.webp" alt="Vimpel Beer" loading="eager" decoding="async">
                        </picture>
                    </div>
                </div>

                <div class="bento-hero-scale" id="bento-hero-scale">
                    <div class="bento-hero-scale__inner">
                        <h1 data-i18n="hero_title">Ասպետների Ըմպելիք</h1>
                        <p data-i18n="hero_subtitle">Vimpel Beer. Ձեր բաժակի մեջ՝ ամրոցի ոգին ու ասպետի արժանապատվությունը:</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="about">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2 data-i18n="about_title">Մեր մասին</h2>
                    <div class="divider"><i class="fas fa-horse" aria-hidden="true"></i></div>
                </div>
                <div class="about-grid">
                    <div class="about-text" data-aos="fade-right">
                        <h3 data-i18n="about_heading">ԵՐԲ ՀԱՄԸ ԴԱՌՆՈՒՄ Է ԼԵԳԵՆԴ</h3>
                        <p data-i18n="about_body">Հնագույն գարեջրատան սրտում, որտեղ առաջին անգամ ծնվեց իսկական գարեջրի առաջին նմուշը, մշակվեց ըմպելիքի պատրաստման ոսկե կանոնները ներառող բաղադրատոմս: Դարերի ընթացքում այն չենթարկվեց փոփոխության, պահվեց գաղտնի և արհեստի լավագույնների կողմից որպես սրբություն փոխանցվեց սերնդեսերունդ:</p>

                        <div class="partner-marquee" id="partner-marquee" hidden>
                            <p class="partner-marquee__label" data-i18n="partners_find_us">Կարող եք գտնել մեզ այստեղ՝</p>
                            <div class="partner-marquee__viewport" aria-label="Retail partners">
                                <div class="partner-marquee__track" id="partner-marquee-track"></div>
                            </div>
                        </div>
                    </div>
                    <div class="about-image" data-aos="fade-left">
                        <div class="about-video-wrap">
                            <video
                                class="about-video"
                                src="assets/images/video/about-video.mp4"
                                playsinline
                                muted
                                loop
                                autoplay
                                preload="metadata"
                                aria-label="Vimpel — about us video"
                            ></video>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Beer lineup — scroll-reveal cards -->
        <section id="beers" class="beer-reveal-section" data-reveal-cards aria-labelledby="beers-heading">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2 id="beers-heading" data-i18n="beers_section_title">Մեր գարեջուրները</h2>
                    <div class="divider"><i class="fas fa-beer-mug-empty" aria-hidden="true"></i></div>
                </div>
                <div class="beer-cards-grid">
                    <article class="reveal-card">
                        <div class="reveal-card__media">
                            <img src="assets/images/beers/beers-pilsner.webp" alt="" data-i18n-alt="beer2_title" loading="lazy" width="800" height="600" decoding="async">
                        </div>
                        <div class="reveal-card__body">
                            <h3 data-i18n="beer2_title">Pilsner</h3>
                            <p class="reveal-card__abv" data-i18n="beer2_abv">Ալկ. 5%</p>
                        </div>
                    </article>
                    <article class="reveal-card">
                        <div class="reveal-card__media">
                            <img src="assets/images/beers/beers-lager.webp" alt="" data-i18n-alt="beer1_title" loading="lazy" width="800" height="600" decoding="async">
                        </div>
                        <div class="reveal-card__body">
                            <h3 data-i18n="beer1_title">Lager</h3>
                            <p class="reveal-card__abv" data-i18n="beer1_abv">Ալկ. 4.8%</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Our Legend — scroll-driven video + text -->
        <section id="history" class="history-section" aria-labelledby="history-heading">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2 id="history-heading" data-i18n="history_title">Մեր լեգենդը</h2>
                    <div class="divider"><i class="fas fa-scroll" aria-hidden="true"></i></div>
                </div>
            </div>

            <div class="history-scroll-track" id="history-scroll-track">
                <div class="history-sticky" id="history-sticky">

                    <div class="history-text-panel" id="history-text-panel">
                        <div class="history-slide active" data-index="0">
                            <img class="history-slide__art" src="assets/images/history/history-legend-title-cropped.png" alt="" aria-hidden="true">
                            <p class="history-slide__body" data-i18n="history_s1_body" data-i18n-html>Լեգենդն ասում է, որ երբ թշնամի թագավորության բանակը ցանկացավ գողանալ բաղադրատոմսն, ու դրա պահպանումն այլևս դարձավ անհնար, վարպետները դիմեցին մի խիզախ զինվորի, որը հայտնի էր իր արիությամբ ու անհաղթահարելի ուժով:</p>
                        </div>
                        <div class="history-slide" data-index="1">
                            <p class="history-slide__body" data-i18n="history_s2_body" data-i18n-html>Այդ ժամանակներից ի վեր խիզախ ձիավորը դարձավ իրական, մաքուր գարեջրի բաղադրատոմսի պահապանն ու գարեջրագործության վարպետների անտեսանելի օգնականը։ Նա՝ դրոշակը ձեռքին, պահպանում է հեռավոր անցյալից փոխանցված ավանդույթներն ու հետևում, որ ամեն բաժակ գարեջուրն իր մեջ կրի նույն ոգին ու տրամադրությունը, այնպես, ինչպես եղել է դարեր առաջ:</p>
                        </div>
                    </div>

                    <!-- Thin divider line -->
                    <div class="history-divider-line"></div>

                    <!-- RIGHT: video -->
                    <div class="history-video-panel">
                        <video
                            id="history-video"
                            preload="auto"
                            muted
                            playsinline
                            webkit-playsinline
                            disablepictureinpicture
                        >
                            <source src="assets/images/history/history-video.mp4" type="video/mp4">
                        </video>
                    </div>

                </div><!-- /.history-sticky -->
            </div><!-- /.history-scroll-track -->

            <!-- Nav dots -->
            <div class="history-dots" id="history-dots" aria-hidden="true">
                <div class="history-dot active"></div>
                <div class="history-dot"></div>
            </div>

        </section>

        <!-- Gallery Section -->
        <section id="gallery" class="gallery-parallax-container">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2 data-i18n="gallery_title">Պատկերասրահ</h2>
                    <div class="divider"><i class="fas fa-chess-knight" aria-hidden="true"></i></div>
                </div>
            </div>
            <div class="gallery-parallax-wrapper" id="gallery-parallax">
                <div class="gallery-column"></div>
                <div class="gallery-column"></div>
                <div class="gallery-column"></div>
                <div class="gallery-column"></div>
            </div>
            <div class="gallery-marquee-wrap" id="gallery-marquee-wrap" aria-hidden="true"></div>
        </section>

        <!-- Reviews Section -->
        <section id="reviews" class="reviews-section">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2 data-i18n="reviews_title">Ասպետների Խոսքը</h2>
                    <div class="divider"><i class="fas fa-shield-halved" aria-hidden="true"></i></div>
                </div>
                <div class="reviews-grid">

                    <div class="review-card" data-aos="fade-up" data-aos-delay="0">
                        <p class="review-card__text" data-i18n="review1_text">Շատ թեթև գարեջուր է, ընկերական ամառային երեկոների համար միշտ Vimpel ենք նախընտրում։</p>
                        <div class="review-card__rating" aria-label="5 out of 5">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                        </div>
                        <div class="review-card__author">
                            <strong class="review-card__name">Arman H.</strong>
                            <span class="review-card__location" data-i18n="review1_location">Երևան</span>
                        </div>
                    </div>

                    <div class="review-card" data-aos="fade-up" data-aos-delay="150">
                        <p class="review-card__text" data-i18n="review2_text">Լագերը դարձավ իմ սիրելին, երկար ժամանակ է միայն Vimpel-ի լագերն եմ գնում։</p>
                        <div class="review-card__rating" aria-label="5 out of 5">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                        </div>
                        <div class="review-card__author">
                            <strong class="review-card__name">Davit M.</strong>
                            <span class="review-card__location" data-i18n="review2_location">Գյումրի</span>
                        </div>
                    </div>

                    <div class="review-card" data-aos="fade-up" data-aos-delay="300">
                        <p class="review-card__text" data-i18n="review3_text">Ես շատ ծանր ու դառը գարեջրերի սիրահար չեմ, էնպես որ գտա ինձ համար իդեալական թեթև գարեջուրը։ Շնորհակալ եմ))</p>
                        <div class="review-card__rating" aria-label="5 out of 5">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <i class="fas fa-star" aria-hidden="true"></i>
                        </div>
                        <div class="review-card__author">
                            <strong class="review-card__name">Ani S.</strong>
                            <span class="review-card__location" data-i18n="review3_location">Երևան</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="contact">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2 data-i18n="contact_title">Կապ մեզ հետ</h2>
                    <div class="divider"><i class="fas fa-crown" aria-hidden="true"></i></div>
                </div>
                <div class="contact-grid">
                    <div class="contact-info" data-aos="fade-right">
                        <div class="info-item">
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            <div>
                                <h4 data-i18n="contact_phone">Հեռախոս</h4>
                                <p><a href="tel:<?= htmlspecialchars(SITE_PHONE, ENT_QUOTES, 'UTF-8') ?>">+374 91 310481</a></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <div>
                                <h4 data-i18n="contact_email">Էլ. հասցե</h4>
                                <p><a href="mailto:info@vimpelbeer.am">info@vimpelbeer.am</a></p>
                            </div>
                        </div>
                        <div class="info-item info-item--social">
                            <i class="fas fa-share-nodes" aria-hidden="true"></i>
                            <div class="info-item__body">
                                <h4 data-i18n="contact_social">Սոցիալական ցանցեր</h4>
                                <div class="social-links">
                                    <a href="<?= htmlspecialchars(SITE_FB, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                                    <a href="https://www.instagram.com/vimpelofficial?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw%3D%3D" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                                    <a href="https://www.tiktok.com/@vimpelofficial" target="_blank" rel="noopener noreferrer"><i class="fab fa-tiktok" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="contact-form contact-form--modern" data-aos="fade-left">
                        <form id="contact-form" novalidate>
                            <div class="form-field form-field--honeypot" aria-hidden="true">
                                <input type="text" name="website" tabindex="-1" autocomplete="off">
                            </div>
                            <div class="form-field">
                                <i class="fas fa-user form-field__icon" aria-hidden="true"></i>
                                <input type="text" id="contact-name" name="name" autocomplete="name" data-i18n="form_name" placeholder="Անուն ազգանուն" required>
                            </div>
                            <div class="form-field">
                                <i class="fas fa-mobile-screen-button form-field__icon" aria-hidden="true"></i>
                                <input type="tel" id="contact-phone" name="phone" autocomplete="tel" data-i18n="form_phone" placeholder="Հեռախոսահամար" required>
                            </div>
                            <div class="form-field">
                                <i class="fas fa-envelope form-field__icon" aria-hidden="true"></i>
                                <input type="email" id="contact-email" name="email" autocomplete="email" data-i18n="form_email" placeholder="Էլ. հասցե" required>
                            </div>
                            <div class="form-field">
                                <i class="fas fa-file-lines form-field__icon" aria-hidden="true"></i>
                                <input type="text" id="contact-subject" name="subject" data-i18n="form_subject" placeholder="Թեմա" required>
                            </div>
                            <div class="form-field form-field--textarea">
                                <i class="fas fa-pen form-field__icon" aria-hidden="true"></i>
                                <textarea id="contact-message" name="message" rows="5" data-i18n="form_message" placeholder="Հաղորդագրություն" required></textarea>
                            </div>
                            <p class="contact-form__status" id="contact-form-status" role="status" aria-live="polite" hidden></p>
                            <button type="submit" class="btn btn-primary btn-block contact-form__submit" data-i18n="btn_submit">Ուղարկել</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer Battlements -->
    <div class="footer-castle-top" aria-hidden="true"></div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <!-- Torches flanking the logo -->
                <div class="footer-torches-row">
                    <div class="footer-torch" aria-hidden="true">
                        <div class="torch-flames">
                            <div class="torch-flame-layer"></div>
                            <div class="torch-flame-layer"></div>
                            <div class="torch-flame-layer"></div>
                        </div>
                        <div class="torch-head"></div>
                        <div class="torch-pole"></div>
                    </div>
                    <div class="footer-logo">
                        <img src="assets/images/brand/logo.webp" alt="Vimpel Logo" width="68" height="68" loading="lazy" decoding="async">
                        <h3>Vimpel Armenia</h3>
                    </div>
                    <div class="footer-torch footer-torch-r" aria-hidden="true">
                        <div class="torch-flames">
                            <div class="torch-flame-layer"></div>
                            <div class="torch-flame-layer"></div>
                            <div class="torch-flame-layer"></div>
                        </div>
                        <div class="torch-head"></div>
                        <div class="torch-pole"></div>
                    </div>
                </div>
                <!-- Medieval motto -->
                <div class="footer-motto">
                    <span class="motto-divider">⚜&#xFE0E;</span>
                    <em class="motto-text" data-i18n="footer_motto">Guardians of the North · Est. MMXXVI</em>
                    <span class="motto-divider">⚜&#xFE0E;</span>
                </div>
                <p class="copyright">&copy; 2026 Vimpel Armenia. <span data-i18n="footer_rights">Բոլոր իրավունքները պաշտպանված են:</span></p>
                <p class="footer-credit">
                    <span data-i18n="footer_built_by">Կայքը պատրաստված է</span>
                    <a href="https://luphar.org/" target="_blank" rel="noopener noreferrer">Luphar</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Popup ad (on first visit) -->
    <div id="popup-ad" class="popup-ad" role="dialog" aria-modal="true" aria-labelledby="popup-ad-title" hidden>
        <div class="popup-ad__backdrop" data-close-popup-ad tabindex="-1" aria-hidden="true"></div>
        <div class="popup-ad__panel">
            <button type="button" class="popup-ad__close" data-close-popup-ad data-i18n-aria="aria_close_popup" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h2 id="popup-ad-title" class="visually-hidden">Vimpel</h2>
            <div class="popup-ad__video-wrap">
                <video
                    id="popup-ad-video"
                    class="popup-ad__video"
                    playsinline
                    webkit-playsinline
                    preload="none"
                >
                    <source src="assets/images/video/popup-ad-video.mp4" type="video/mp4">
                    <source src="assets/images/video/popup-ad-video.mov" type="video/quicktime">
                </video>
                <button type="button" class="popup-ad__unmute" id="popup-ad-unmute" aria-label="Unmute video" hidden>
                    <i class="fas fa-volume-xmark" aria-hidden="true"></i>
                    <span>Tap to unmute</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="lightbox">
        <span class="close-lightbox">&times;</span>
        <img class="lightbox-content" id="lightbox-img">
        <div id="caption"></div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/script.js?v=91"></script>
</body>
</html>






















