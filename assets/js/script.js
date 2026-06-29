/**
 * Partner logos for the about-section marquee.
 * Add files under assets/images/partners/, then list them here (src + alt).
 */
const PARTNER_LOGOS = [
    { src: 'assets/images/partners/sas-supermarket.png', alt: 'SAS Supermarket' },
    { src: 'assets/images/partners/parma-supermarket.png', alt: 'Parma Supermarket' },
    { src: 'assets/images/partners/eurika-supermarkets.png', alt: 'Eurika Supermarkets' },
    { src: 'assets/images/partners/sber.png', alt: 'Sber' },
    { src: 'assets/images/partners/carrefour.png', alt: 'Carrefour' },
];

/** Shared scroll scheduler — one rAF per frame for all scroll-driven effects. */
const VimpelScroll = (() => {
    const jobs = new Set();
    let rafId = 0;

    function flush() {
        rafId = 0;
        jobs.forEach((job) => job());
    }

    function schedule() {
        if (rafId) return;
        rafId = requestAnimationFrame(flush);
    }

    return {
        on(fn) {
            jobs.add(fn);
            return () => jobs.delete(fn);
        },
        schedule,
    };
})();

function vimpelPrefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/** Lighter effects on low-power devices / data-saver / reduced-motion. */
function vimpelIsLiteMode() {
    if (vimpelPrefersReducedMotion()) return true;
    const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    if (conn?.saveData) return true;
    const cores = navigator.hardwareConcurrency || 0;
    if (cores > 0 && cores <= 4) return true;
    const mem = navigator.deviceMemory || 0;
    if (mem > 0 && mem <= 4) return true;
    return false;
}

/** Static gallery grid — mobile and reduced-motion only (desktop keeps parallax columns). */
function vimpelUseGalleryGrid() {
    if (vimpelPrefersReducedMotion()) return true;
    if (window.matchMedia('(max-width: 900px)').matches) return true;
    return false;
}

function vimpelObserveInView(element, callback, rootMargin = '120px 0px') {
    if (!element || typeof IntersectionObserver === 'undefined') {
        callback(true);
        return () => {};
    }
    let inView = false;
    const observer = new IntersectionObserver(
        (entries) => {
            inView = entries.some((entry) => entry.isIntersecting);
            callback(inView);
        },
        { root: null, rootMargin, threshold: 0 }
    );
    observer.observe(element);
    return () => observer.disconnect();
}

let partnerMarqueeRafId = 0;
let partnerMarqueeInView = false;
let partnerMarqueePaused = false;
let partnerMarqueeOffset = 0;
let partnerMarqueeHalfWidth = 0;

function initPartnerMarquee() {
    const wrap = document.getElementById('partner-marquee');
    const track = document.getElementById('partner-marquee-track');
    const viewport = track?.parentElement;
    if (!wrap || !track || !PARTNER_LOGOS.length) return;

    if (partnerMarqueeRafId) {
        cancelAnimationFrame(partnerMarqueeRafId);
        partnerMarqueeRafId = 0;
    }

    track.innerHTML = '';
    track.style.removeProperty('transform');

    const buildItem = (logo, hidden) => {
        const item = document.createElement('div');
        item.className = 'partner-marquee__item';
        if (hidden) item.setAttribute('aria-hidden', 'true');

        const img = document.createElement('img');
        img.src = logo.src;
        img.alt = hidden ? '' : logo.alt;
        img.loading = 'lazy';
        img.decoding = 'async';
        img.className = 'partner-marquee__logo';
        item.appendChild(img);
        return item;
    };

    const appendSet = (hidden) => {
        PARTNER_LOGOS.forEach((logo) => track.appendChild(buildItem(logo, hidden)));
    };

    appendSet(false);
    appendSet(true);

    wrap.hidden = false;

    if (vimpelPrefersReducedMotion()) return;

    const measurePartnerMarquee = () => {
        partnerMarqueeHalfWidth = track.scrollWidth / 2;
    };

    measurePartnerMarquee();
    track.querySelectorAll('img').forEach((img) => {
        if (img.complete) return;
        img.addEventListener('load', measurePartnerMarquee, { once: true });
    });

    if (viewport) {
        viewport.addEventListener('mouseenter', () => {
            partnerMarqueePaused = true;
        });
        viewport.addEventListener('mouseleave', () => {
            partnerMarqueePaused = false;
        });

        vimpelObserveInView(viewport, (visible) => {
            partnerMarqueeInView = visible;
        }, '60px 0px');
        const rect = viewport.getBoundingClientRect();
        partnerMarqueeInView = rect.bottom > 0 && rect.top < window.innerHeight;
    } else {
        partnerMarqueeInView = true;
    }

    let lastTs = 0;
    const pxPerSecond = 48;

    const tick = (ts) => {
        partnerMarqueeRafId = requestAnimationFrame(tick);
        if (!partnerMarqueeInView || partnerMarqueePaused || partnerMarqueeHalfWidth <= 0) {
            lastTs = ts;
            return;
        }

        const dt = lastTs ? Math.min(ts - lastTs, 48) : 16.67;
        lastTs = ts;
        partnerMarqueeOffset -= (pxPerSecond * dt) / 1000;
        while (partnerMarqueeOffset <= -partnerMarqueeHalfWidth) {
            partnerMarqueeOffset += partnerMarqueeHalfWidth;
        }
        track.style.transform = `translate3d(${partnerMarqueeOffset.toFixed(2)}px, 0, 0)`;
    };

    partnerMarqueeRafId = requestAnimationFrame(tick);
    window.addEventListener('resize', measurePartnerMarquee, { passive: true });
}

document.addEventListener('DOMContentLoaded', () => {
    const liteMode = vimpelIsLiteMode();
    if (liteMode) {
        document.documentElement.classList.add('vimpel-lite');
    }
    if (vimpelUseGalleryGrid()) {
        document.documentElement.classList.add('vimpel-gallery-grid');
    }

    window.addEventListener('scroll', VimpelScroll.schedule, { passive: true });

    // AOS — always init so [data-aos] nodes are not left hidden (especially on mobile / lite)
    const isMobileLayout = window.matchMedia('(max-width: 767px)').matches;
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: liteMode ? 0 : 800,
            once: true,
            offset: isMobileLayout ? 40 : 100,
            disable: isMobileLayout || liteMode,
        });
    }

    // Preloader + popup ad (once per browser session)
    const preloader = document.getElementById('preloader');
    const popupAd = document.getElementById('popup-ad');
    const popupAdVideo = document.getElementById('popup-ad-video');
    const POPUP_AD_KEY = 'vimpel-popup-ad-seen';
    let popupAdOpened = false;
    let popupScrollLockY = 0;

    function onPopupBackgroundScroll(e) {
        if (popupAd?.contains(e.target)) return;
        e.preventDefault();
    }

    function lockPageScrollForPopup() {
        popupScrollLockY = window.scrollY;
        document.documentElement.classList.add('popup-ad-open');
        document.body.classList.add('popup-ad-open');
        document.body.style.position = 'fixed';
        document.body.style.top = `-${popupScrollLockY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        document.addEventListener('touchmove', onPopupBackgroundScroll, { passive: false });
        document.addEventListener('wheel', onPopupBackgroundScroll, { passive: false });
    }

    function unlockPageScrollForPopup() {
        document.documentElement.classList.remove('popup-ad-open');
        document.body.classList.remove('popup-ad-open');
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        document.removeEventListener('touchmove', onPopupBackgroundScroll);
        document.removeEventListener('wheel', onPopupBackgroundScroll);
        window.scrollTo(0, popupScrollLockY);
    }

    function closePopupAd() {
        if (!popupAd || !popupAdVideo) return;
        if (!popupAd.classList.contains('is-open')) return;

        popupAd.classList.remove('is-open');
        unlockPageScrollForPopup();
        popupAdVideo.pause();
        sessionStorage.setItem(POPUP_AD_KEY, '1');

        setTimeout(() => {
            popupAd.hidden = true;
        }, 350);
    }

    function playPopupAdVideo() {
        if (!popupAdVideo) return;

        const unmuteBtn = document.getElementById('popup-ad-unmute');

        // Stop the gesture-handler pre-play (muted), rewind, then play with sound.
        if (!popupAdVideo.paused) popupAdVideo.pause();
        popupAdVideo.currentTime = 0;
        popupAdVideo.volume = 1;
        popupAdVideo.muted = false;
        if (unmuteBtn) unmuteBtn.hidden = true;

        if (unmuteBtn) {
            unmuteBtn.addEventListener('click', () => {
                popupAdVideo.muted = false;
                popupAdVideo.volume = 1;
                unmuteBtn.hidden = true;
            }, { once: true });
        }

        const showUnmute = () => {
            if (unmuteBtn) unmuteBtn.hidden = false;
        };

        const attempt = () => {
            const playPromise = popupAdVideo.play();
            if (!playPromise || typeof playPromise.then !== 'function') return;

            playPromise.catch(() => {
                // Browser still blocked sound — fall back to muted + unmute button
                popupAdVideo.muted = true;
                popupAdVideo.play()
                    .then(showUnmute)
                    .catch(showUnmute);
            });
        };

        if (popupAdVideo.readyState >= 2) {
            attempt();
        } else {
            popupAdVideo.addEventListener('canplay', attempt, { once: true });
            if (popupAdVideo.readyState === 0) popupAdVideo.load();
        }
    }

    function openPopupAd() {
        if (!popupAd || !popupAdVideo || popupAdOpened) return;
        if (sessionStorage.getItem(POPUP_AD_KEY)) return;

        popupAdOpened = true;
        popupAd.removeAttribute('hidden');

        requestAnimationFrame(() => {
            popupAd.classList.add('is-open');
            lockPageScrollForPopup();
            playPopupAdVideo();
        });
    }

    // Age Gate — must be defined before onAppReady so it's available even if readyState is already 'complete'
    const AGE_GATE_KEY = 'vimpel-age-ok';
    const ageGate = document.getElementById('age-gate');
    const ageGateBtns = document.getElementById('age-gate-btns');
    const ageGateYes = document.getElementById('age-gate-yes');
    const ageGateNo = document.getElementById('age-gate-no');
    const ageGateSorry = document.getElementById('age-gate-sorry');

    function closeAgeGate() {
        if (!ageGate || !ageGate.classList.contains('is-open')) return;
        ageGate.classList.remove('is-open');
        unlockPageScrollForPopup();
        setTimeout(() => { ageGate.hidden = true; }, 450);
    }

    function openAgeGate() {
        if (!ageGate) { openPopupAd(); return; }
        if (sessionStorage.getItem(AGE_GATE_KEY)) { openPopupAd(); return; }

        // Pre-load the popup video while user reads the age gate so it's
        // ready (readyState >= 2) the moment they click Yes.
        if (popupAdVideo && !sessionStorage.getItem(POPUP_AD_KEY)) {
            popupAdVideo.preload = 'auto';
            popupAdVideo.load();
        }

        ageGate.removeAttribute('hidden');
        // Double rAF ensures the browser has painted the display:flex state
        // before adding is-open, so the CSS opacity transition actually fires.
        requestAnimationFrame(() => requestAnimationFrame(() => {
            ageGate.classList.add('is-open');
            lockPageScrollForPopup();
        }));
    }

    if (ageGateYes) {
        ageGateYes.addEventListener('click', () => {
            sessionStorage.setItem(AGE_GATE_KEY, '1');

            // iOS Safari requires play() to be called in the same synchronous
            // call stack as the user gesture. Play muted here to permanently
            // unlock audio for this video element; playPopupAdVideo will
            // unmute and restart from the beginning.
            if (popupAdVideo && !sessionStorage.getItem(POPUP_AD_KEY)) {
                popupAdVideo.muted = true;
                popupAdVideo.play().catch(() => {});
            }

            closeAgeGate();
            setTimeout(() => { openPopupAd(); }, 500);
        });
    }

    if (ageGateNo) {
        ageGateNo.addEventListener('click', () => {
            if (ageGateBtns) ageGateBtns.hidden = true;
            if (ageGateSorry) ageGateSorry.hidden = false;
        });
    }

    let appReadyDone = false;
    function onAppReady() {
        if (appReadyDone) return;
        appReadyDone = true;

        if (preloader) {
            preloader.classList.add('preloader-done');
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.visibility = 'hidden';
                openAgeGate();
            }, 500);
        } else {
            openAgeGate();
        }

        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    }

    if (document.readyState === 'complete') {
        onAppReady();
    } else {
        window.addEventListener('load', onAppReady);
    }

    // Never leave a blank white screen if load/AOS hangs
    setTimeout(onAppReady, 3500);

    if (popupAdVideo) {
        popupAdVideo.addEventListener('ended', closePopupAd);
        // Close popup immediately if video format is unsupported (e.g. .mov on non-Apple browsers)
        popupAdVideo.addEventListener('error', closePopupAd, { once: true });
    }

    if (popupAd) {
        popupAd.querySelectorAll('[data-close-popup-ad]').forEach((el) => {
            el.addEventListener('click', closePopupAd);
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && popupAd.classList.contains('is-open')) {
                closePopupAd();
            }
        });
    }

    // Header Scroll Effect
    const header = document.getElementById('header');
    if (header) {
        VimpelScroll.on(() => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // Hero — bento zooms in on scroll (starts small / “far”, ends full grid); short track via --bento-hero-track-height
    (function initBentoHero() {
        const section = document.getElementById('home');
        const scaleEl = document.getElementById('bento-hero-scale');
        const cells = document.querySelectorAll('.bento-cell');
        if (!section || !scaleEl || cells.length === 0) return;

        const staticHero = vimpelPrefersReducedMotion();
        const heroMobileMq = window.matchMedia('(max-width: 767px)');
        let heroInView = true;

        function heroTextFadeRange() {
            return heroMobileMq.matches
                ? { fadeStart: 0.04, fadeEnd: 0.3 }
                : { fadeStart: 0, fadeEnd: 0.48 };
        }

        function setStaticHero() {
            cells.forEach((cell) => {
                cell.style.transform = 'none';
            });
            scaleEl.style.opacity = staticHero && vimpelPrefersReducedMotion() ? '0' : '1';
            scaleEl.style.visibility = scaleEl.style.opacity === '0' ? 'hidden' : 'visible';
            scaleEl.style.transform = 'none';
            scaleEl.style.pointerEvents = scaleEl.style.opacity === '0' ? 'none' : 'auto';
        }

        if (staticHero) {
            setStaticHero();
            return;
        }

        vimpelObserveInView(section, (visible) => {
            heroInView = visible;
        });

        function mapRange(value, inMin, inMax, outMin, outMax) {
            const t = Math.max(0, Math.min(1, (value - inMin) / (inMax - inMin)));
            return outMin + (outMax - outMin) * t;
        }

        function heroScrollMetrics() {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const viewportHeight = window.innerHeight;
            const scrollable = sectionHeight - viewportHeight;
            if (scrollable <= 0) return null;
            return { sectionTop, scrollable };
        }

        function update() {
            if (!heroInView) return;

            const m = heroScrollMetrics();

            if (!m) {
                cells.forEach((cell) => {
                    cell.style.transform = 'none';
                });
                scaleEl.style.opacity = '0';
                scaleEl.style.visibility = 'hidden';
                scaleEl.style.transform = 'none';
                scaleEl.style.pointerEvents = 'none';
                return;
            }

            const { sectionTop, scrollable } = m;
            const progress = Math.max(0, Math.min(1,
                (window.scrollY - sectionTop) / scrollable
            ));

            const tx = Math.round(mapRange(progress, 0.02, 0.98, -26, 0));
            const startScale = heroMobileMq.matches ? 0.62 : 0.56;
            const cellScale = Math.round(mapRange(progress, 0, 1, startScale, 1) * 100) / 100;
            cells.forEach((cell) => {
                cell.style.transform = tx === 0 && cellScale === 1
                    ? 'none'
                    : `translate3d(${tx}%, 0, 0) scale(${cellScale})`;
            });

            const { fadeStart, fadeEnd } = heroTextFadeRange();
            const opacity = mapRange(progress, fadeStart, fadeEnd, 1, 0);
            const scaleVal = Math.round(mapRange(progress, fadeStart, fadeEnd, 1, 0.88) * 100) / 100;
            scaleEl.style.opacity = String(opacity);
            scaleEl.style.transform = scaleVal === 1 ? 'none' : `scale(${scaleVal})`;
            scaleEl.style.pointerEvents = opacity > 0.06 ? 'auto' : 'none';
            scaleEl.style.visibility = opacity < 0.02 ? 'hidden' : 'visible';
        }

        VimpelScroll.on(update);
        window.addEventListener('resize', () => VimpelScroll.schedule(), { passive: true });
        update();
    })();

    // Full-screen menu (MENU control in header)
    const menuToggle = document.querySelector('.header-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu-overlay');
    const mobileLinks = document.querySelectorAll('.mobile-nav a');
    const menuBackdrop = mobileMenu?.querySelector('[data-close-menu]');
    const menuCloseBtn = mobileMenu?.querySelector('.menu-close-btn');

    function setMenuOpen(open) {
        if (!menuToggle || !mobileMenu) return;
        mobileMenu.classList.toggle('active', open);
        menuToggle.classList.toggle('is-open', open);
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = open ? 'hidden' : '';
    }

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            setMenuOpen(!mobileMenu.classList.contains('active'));
        });

        menuBackdrop?.addEventListener('click', () => setMenuOpen(false));
        menuCloseBtn?.addEventListener('click', () => setMenuOpen(false));

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                setMenuOpen(false);
            }
        });
    }

    // Language Switcher
    const translations = {
        hy: {
            nav_home: "Գլխավոր",
            nav_about: "Մեր մասին",
            nav_beers: "Գարեջուրներ",
            nav_history: "Մեր լեգենդը",
            nav_gallery: "Պատկերասրահ",
            nav_contact: "Կապ",
            nav_menu: "Մենյու",
            nav_menu_label: "Նավիգացիա",
            hero_title: "Ասպետների Ըմպելիք",
            hero_subtitle: "Vimpel Beer. Ձեր բաժակի մեջ՝ ամրոցի ոգին ու ասպետի արժանապատվությունը:",
            about_title: "Մեր մասին",
            about_heading: "ԵՐԲ ՀԱՄԸ ԴԱՌՆՈՒՄ Է ԼԵԳԵՆԴ",
            about_body: "Հնագույն գարեջրատան սրտում, որտեղ առաջին անգամ ծնվեց իսկական գարեջրի առաջին նմուշը, մշակվեց ըմպելիքի պատրաստման ոսկե կանոնները ներառող բաղադրատոմս: Դարերի ընթացքում այն չենթարկվեց փոփոխության, պահվեց գաղտնի և արհեստի լավագույնների կողմից որպես սրբություն փոխանցվեց սերնդեսերունդ:",
            partners_marquee_label: "Մեր գործընկերներ",
            partners_find_us: "Կարող եք գտնել մեզ այստեղ՝",
            beers_section_title: "Մեր գարեջուրները",
            beer1_title: "Lager",
            beer1_abv: "Ալկ. 4.8%",
            beer2_title: "Pilsner",
            beer2_abv: "Ալկ. 5%",
            gallery_title: "Պատկերասրահ",
            contact_title: "Կապ մեզ հետ",
            contact_phone: "Հեռախոս",
            contact_email: "Էլ. հասցե",
            contact_social: "Սոցիալական ցանցեր",
            form_name: "Անուն ազգանուն",
            form_phone: "Հեռախոսահամար",
            form_email: "Էլ. հասցե",
            form_subject: "Թեմա",
            form_message: "Հաղորդագրություն",
            form_sending: "Ուղարկվում է...",
            form_success: "Շնորհակալություն։ Ձեր հաղորդագրությունն ընդունված է։ Մենք շուտով կկապնվենք Ձեզ հետ։",
            form_error: "Չհաջողվեց ուղարկել հաղորդագրությունը։ Խնդրում ենք փորձել կրկին կամ գրել մեզ էլ. փոստով։",
            form_session_error: "Նիստի ժամկետը լրացել է։ Թարմացրեք էջը և փորձեք կրկին։",
            form_validation_error: "Խնդրում ենք լրացնել բոլոր դաշտերը ճիշտ։",
            btn_submit: "Ուղարկել",
            footer_rights: "Բոլոր իրավունքները պաշտպանված են:",
            footer_built_by: "Կայքը պատրաստված է",
            footer_motto: "Հյուսիսի պահապաններ · Հիմն. MMXXVI",
            menu_crest: "Հյուսիսի պահապաններ",
            history_title: "Մեր լեգենդը",
            nav_reviews: "Կարծիքներ",
            reviews_title: "Ասպետների Խոսքը",
            review1_text: "Շատ թեթև գարեջուր է, ընկերական ամառային երեկոների համար միշտ Vimpel ենք նախընտրում։",
            review1_location: "Երևան",
            review2_text: "Լագերը դարձավ իմ սիրելին, երկար ժամանակ է միայն Vimpel-ի լագերն եմ գնում։",
            review2_location: "Գյումրի",
            review3_text: "Ես շատ ծանր ու դառը գարեջրերի սիրահար չեմ, էնպես որ գտա ինձ համար իդեալական թեթև գարեջուրը։ Շնորհակալ եմ))",
            review3_location: "Երևան",
            history_s1_body: "Լեգենդն ասում է, որ երբ թշնամի թագավորության բանակը ցանկացավ գողանալ բաղադրատոմսն, ու դրա պահպանումն այլևս դարձավ անհնար, վարպետները դիմեցին մի խիզախ զինվորի, որը հայտնի էր իր արիությամբ ու անհաղթահարելի ուժով:",
            history_s2_body: "Այդ ժամանակներից ի վեր խիզախ ձիավորը դարձավ իրական, մաքուր գարեջրի բաղադրատոմսի պահապանն ու գարեջրագործության վարպետների անտեսանելի օգնականը։ Նա՝ դրոշակը ձեռքին, պահպանում է հեռավոր անցյալից փոխանցված ավանդույթներն ու հետևում, որ ամեն բաժակ գարեջուրն իր մեջ կրի նույն ոգին ու տրամադրությունը, այնպես, ինչպես եղել է դարեր առաջ:",
            page_title: "Vimpel Armenia - Սառնություն, որը քեզ հաճելի է",
            aria_close_menu: "Փակել մենյուն",
            aria_close_popup: "Փակել",
            age_gate_are_you: "Տարեկան եք",
            age_gate_age: "18",
            age_gate_legal: "Մուտք թույլատրված է միայն 18 տարեկան և ավելի մեծ անձանց",
            age_gate_yes: "Այո",
            age_gate_no: "Ոչ",
            age_gate_sorry: "Ցավոք, մուտքը թույլատրված է միայն 18 տարեկանից բարձր այցելուների համար։"
        },
        en: {
            nav_home: "Home",
            nav_about: "About",
            nav_beers: "Beers",
            nav_history: "Our Legend",
            nav_gallery: "Gallery",
            nav_contact: "Contact",
            nav_menu: "MENU",
            nav_menu_label: "Navigation",
            hero_title: "Brewed by the Guardian",
            hero_subtitle: "Vimpel Beer — forged in the ancient spirit of the knight, guardian of every recipe.",
            about_title: "About Us",
            about_heading: "WHEN TASTE BECOMES LEGEND",
            about_body: "In the heart of an ancient brewery, where the first true specimen of beer was born, a recipe was crafted that embodied the golden rules of brewing. Through the centuries it remained unchanged, kept secret, and passed down from generation to generation by master craftsmen as a sacred tradition.",
            partners_marquee_label: "Our partners",
            partners_find_us: "You can find us at:",
            beers_section_title: "Our beers",
            beer1_title: "Lager",
            beer1_abv: "4.8% ABV",
            beer2_title: "Pilsner",
            beer2_abv: "5% ABV",
            gallery_title: "Gallery",
            contact_title: "Contact Us",
            contact_phone: "Phone",
            contact_email: "Email",
            contact_social: "Social Media",
            form_name: "Full Name",
            form_phone: "Phone Number",
            form_email: "Email",
            form_subject: "Subject",
            form_message: "Message",
            form_sending: "Sending...",
            form_success: "Thank you! Your message has been received. We will get back to you soon.",
            form_error: "We could not send your message. Please try again or email us directly.",
            form_session_error: "Your session expired. Refresh the page and try again.",
            form_validation_error: "Please fill in all fields correctly.",
            btn_submit: "Send",
            footer_rights: "All rights reserved.",
            footer_built_by: "Website built by",
            footer_motto: "Guardians of the North · Est. MMXXVI",
            menu_crest: "Guardians of the North",
            history_title: "Our Legend",
            nav_reviews: "Reviews",
            reviews_title: "Words of Our Knights",
            review1_text: "A very light beer — for friendly summer evenings we always choose Vimpel.",
            review1_location: "Yerevan",
            review2_text: "Lager became my favorite — for a long time now I've only been buying Vimpel's lager.",
            review2_location: "Gyumri",
            review3_text: "I'm not a fan of very heavy and bitter beers, so I found the perfect light beer for me. Thank you))",
            review3_location: "Yerevan",
            history_s1_body: "Legend has it that when the enemy kingdom's army sought to steal the recipe, and keeping it safe was no longer possible, the masters turned to a brave warrior known for his courage and invincible strength.",
            history_s2_body: "From that time on, the brave horseman became the guardian of the true, pure beer recipe and the unseen helper of the brewing masters. With the flag in his hand, he preserves traditions passed down from the distant past and ensures that every glass of beer carries the same spirit and mood, as it did centuries ago.",
            page_title: "Vimpel Armenia - Coolness you'll enjoy",
            aria_close_menu: "Close menu",
            aria_close_popup: "Close",
            age_gate_are_you: "Are you",
            age_gate_age: "18",
            age_gate_legal: "You must be 18 or older to enter this site.",
            age_gate_yes: "Yes",
            age_gate_no: "No",
            age_gate_sorry: "Sorry, you must be 18 or older to enter this site."
        },
        ru: {
            nav_home: "Главная",
            nav_about: "О нас",
            nav_beers: "Сорта",
            nav_history: "Наша легенда",
            nav_gallery: "Галерея",
            nav_contact: "Контакт",
            nav_menu: "МЕНЮ",
            nav_menu_label: "Навигация",
            hero_title: "Пиво Хранителя",
            hero_subtitle: "Vimpel Beer — в каждом стакане дух рыцаря и стража старинного рецепта.",
            about_title: "О нас",
            about_heading: "КОГДА ВКУС СТАНОВИТСЯ ЛЕГЕНДОЙ",
            about_body: "В сердце древней пивоварни, где впервые родился подлинный образец пива, был создан рецепт, включающий золотые правила приготовления напитка. На протяжении веков он не подвергался изменениям, хранился в тайне и передавался из поколения в поколение лучшими мастерами как священная традиция.",
            partners_marquee_label: "Наши партнёры",
            partners_find_us: "Нас можно найти в:",
            beers_section_title: "Наше пиво",
            beer1_title: "Lager",
            beer1_abv: "Алк. 4.8%",
            beer2_title: "Pilsner",
            beer2_abv: "Алк. 5%",
            gallery_title: "Галерея",
            contact_title: "Свяжитесь с нами",
            contact_phone: "Телефон",
            contact_email: "Эл. почта",
            contact_social: "Социальные сети",
            form_name: "Полное имя",
            form_phone: "Телефон",
            form_email: "Эл. почта",
            form_subject: "Тема",
            form_message: "Сообщение",
            form_sending: "Отправка...",
            form_success: "Спасибо! Ваше сообщение получено. Мы скоро свяжемся с вами.",
            form_error: "Не удалось отправить сообщение. Попробуйте снова или напишите нам на почту.",
            form_session_error: "Сессия истекла. Обновите страницу и попробуйте снова.",
            form_validation_error: "Пожалуйста, заполните все поля правильно.",
            btn_submit: "Отправить",
            footer_rights: "Все права защищены.",
            footer_built_by: "Сайт создан",
            footer_motto: "Хранители Севера · Осн. MMXXVI",
            menu_crest: "Хранители Севера",
            history_title: "Наша легенда",
            nav_reviews: "Отзывы",
            reviews_title: "Слова Наших Рыцарей",
            review1_text: "Очень лёгкое пиво — для дружеских летних вечеров мы всегда выбираем Vimpel.",
            review1_location: "Ереван",
            review2_text: "Лагер стал моим любимым — уже долгое время покупаю только лагер Vimpel.",
            review2_location: "Гюмри",
            review3_text: "Я не любитель очень тяжёлого и горького пива, поэтому нашла для себя идеальное лёгкое пиво. Спасибо))",
            review3_location: "Ереван",
            history_s1_body: "Легенда гласит, что когда армия вражеского королевства хотела украсть рецепт, а сохранить его стало невозможно, мастера обратились к храброму воину, известному своей отвагой и непобедимой силой.",
            history_s2_body: "С тех пор храбрый всадник стал хранителем подлинного, чистого пивного рецепта и незримым помощником пивоваров. С флагом в руке он хранит традиции, переданные из далёкого прошлого, и следит, чтобы каждая кружка пива несла тот же дух и настроение, что и века назад.",
            page_title: "Vimpel Armenia - Прохлада, которая вам понравится",
            aria_close_menu: "Закрыть меню",
            aria_close_popup: "Закрыть",
            age_gate_are_you: "Вам есть",
            age_gate_age: "18 лет?",
            age_gate_legal: "Вход разрешён только лицам не моложе 18 лет.",
            age_gate_yes: "Да",
            age_gate_no: "Нет",
            age_gate_sorry: "К сожалению, вход разрешён только совершеннолетним."
        }
    };

    const langBtns = document.querySelectorAll('.lang-btn');
    const vimpelLangConfig = window.VIMPEL_LANG || {};
    const serverLang = vimpelLangConfig.current;
    const LANG_COOKIE = 'vimpel_lang';

    function vimpelSiteBasePath() {
        const baseHref = document.querySelector('base')?.getAttribute('href');
        if (!baseHref) return '';
        try {
            const u = new URL(baseHref, window.location.origin);
            let p = u.pathname.replace(/\/$/, '');
            return p === '/' ? '' : p;
        } catch (_) {
            return '';
        }
    }

    function resolveSiteUrl(relativePath) {
        if (!relativePath) return window.location.pathname + window.location.search;
        if (/^https?:\/\//i.test(relativePath)) return relativePath;
        const base = vimpelSiteBasePath();
        const joined = relativePath.startsWith('/')
            ? relativePath
            : (base ? `${base}/` : '/') + relativePath.replace(/^\//, '');
        const u = new URL(joined, window.location.origin);
        return u.pathname + u.search;
    }

    function langUrlFor(code) {
        const urls = vimpelLangConfig.urls;
        let path = `index.php?lang=${code}`;
        if (urls && urls[code]) {
            const href = urls[code];
            if (/^index\.php\?lang=/.test(href)) {
                path = href;
            } else if (/\/(hy|en|ru)\/?$/.test(href)) {
                path = `index.php?lang=${code}`;
            } else {
                path = href;
            }
        }
        return resolveSiteUrl(path);
    }

    function langFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const q = params.get('lang');
        if (q === 'hy' || q === 'en' || q === 'ru') return q;
        const pathMatch = window.location.pathname.match(/\/(hy|en|ru)(?:\/|$)/);
        if (pathMatch) return pathMatch[1];
        return null;
    }

    function langFromCookie() {
        const match = document.cookie.match(/(?:^|;\s*)vimpel_lang=([^;]+)/);
        const value = match?.[1];
        return value === 'hy' || value === 'en' || value === 'ru' ? value : null;
    }

    function getActiveLang() {
        return vimpelLangConfig.current
            || langFromCookie()
            || document.querySelector('.lang-btn.active')?.getAttribute('data-lang')
            || 'hy';
    }

    function langCookiePath() {
        const base = document.querySelector('base')?.getAttribute('href') || '/';
        try {
            const u = new URL(base, window.location.origin);
            const dir = u.pathname.replace(/\/[^/]*$/, '');
            return (dir || '/') + '/';
        } catch (_) {
            return '/';
        }
    }

    function persistLanguage(lang, hash = window.location.hash) {
        vimpelLangConfig.current = lang;
        document.cookie = `${LANG_COOKIE}=${lang};path=${langCookiePath()};max-age=${365 * 86400};SameSite=Lax`;

        const next = langUrlFor(lang) + hash;
        const current = window.location.pathname + window.location.search + window.location.hash;
        if (current !== next) {
            history.replaceState({ vimpelLang: lang }, '', next);
        }
    }

    function navigateToHash(hash) {
        if (!hash || !hash.startsWith('#')) return;
        const target = document.querySelector(hash);
        if (!target) return;

        const lang = getActiveLang();
        updateLanguage(lang);
        persistLanguage(lang, hash);
        target.scrollIntoView({ behavior: 'smooth' });
    }

    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        if (link.closest('#age-gate')) return;
        link.addEventListener('click', (e) => {
            const hash = link.getAttribute('href');
            if (!hash || hash === '#') return;
            if (!document.querySelector(hash)) return;
            e.preventDefault();
            navigateToHash(hash);
            if (mobileMenu?.classList.contains('active')) {
                setMenuOpen(false);
            }
        });
    });

    function updateLanguage(lang) {
        const dict = translations[lang] || translations.hy;
        if (!dict) return;

        document.querySelectorAll('[data-i18n]').forEach(element => {
            if (element.closest('#age-gate')) return;
            const key = element.getAttribute('data-i18n');
            if (dict[key]) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.placeholder = dict[key];
                } else if (element.tagName === 'OPTION') {
                    element.textContent = dict[key];
                } else if (element.hasAttribute('data-i18n-html')) {
                    element.innerHTML = dict[key];
                } else {
                    element.textContent = dict[key];
                }
            }
        });

        document.querySelectorAll('[data-i18n-alt]').forEach(element => {
            if (element.closest('#age-gate')) return;
            const key = element.getAttribute('data-i18n-alt');
            if (dict[key]) {
                element.alt = dict[key];
            }
        });

        document.querySelectorAll('[data-i18n-aria]').forEach(element => {
            if (element.closest('#age-gate')) return;
            const key = element.getAttribute('data-i18n-aria');
            if (dict[key]) {
                element.setAttribute('aria-label', dict[key]);
            }
        });

        if (dict.page_title) {
            document.title = dict.page_title;
        }

        // Update HTML lang attribute
        document.documentElement.lang = lang;
        
        // Update active button
        langBtns.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
        });
    }

    langBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const lang = btn.getAttribute('data-lang');
            if (!lang) return;

            const current = vimpelLangConfig.current
                || document.querySelector('.lang-btn.active')?.getAttribute('data-lang')
                || 'hy';

            if (lang === current) return;

            updateLanguage(lang);
            persistLanguage(lang);
        });
    });

    window.addEventListener('popstate', () => {
        const lang = langFromUrl() || langFromCookie() || 'hy';
        updateLanguage(lang);
        vimpelLangConfig.current = lang;
    });

    const initialLang = langFromUrl()
        || langFromCookie()
        || serverLang
        || 'hy';
    updateLanguage(initialLang);
    vimpelLangConfig.current = initialLang;
    if (langFromUrl() !== initialLang) {
        persistLanguage(initialLang);
    }

    // Beer cards: scroll-scrubbed reveal left → right. Progress tracks the grid passing through
    // the viewport (p = 0…1), so all cards can reach full opacity without extra “invisible” scroll.
    const revealRoot = document.querySelector('[data-reveal-cards]');
    const revealGrid = revealRoot?.querySelector('.beer-cards-grid');
    const revealCards = document.querySelectorAll('.reveal-card');
    if (revealRoot && revealGrid && revealCards.length) {
        const staticReveal = liteMode || vimpelPrefersReducedMotion();
        let beersInView = true;

        function showAllBeerCards() {
            revealCards.forEach((card) => {
                card.style.opacity = '1';
                card.style.transform = 'none';
            });
        }

        if (staticReveal) {
            showAllBeerCards();
        } else {
            vimpelObserveInView(revealRoot, (visible) => {
                beersInView = visible;
            });

            function beerGridScrollProgress() {
                const rect = revealGrid.getBoundingClientRect();
                const gridTop = rect.top + window.scrollY;
                const gridH = Math.max(rect.height, 1);
                const vh = window.innerHeight;
                const range = gridH + vh;
                if (range <= 0) return 0;
                return Math.max(0, Math.min(1, (window.scrollY - gridTop + vh) / range));
            }

            function updateBeerRevealCards() {
                if (!beersInView) return;

                const p = beerGridScrollProgress();
                const stagger = 0.09;
                const slice = 0.28;
                const slidePx = 100;

                revealCards.forEach((card, i) => {
                    const t = Math.max(0, Math.min(1, (p - i * stagger) / slice));
                    const x = Math.round((1 - t) * -slidePx);
                    card.style.opacity = String(t);
                    card.style.transform = x === 0 ? 'none' : `translate3d(${x}px, 0, 0)`;
                });
            }

            VimpelScroll.on(updateBeerRevealCards);
            window.addEventListener('resize', () => VimpelScroll.schedule(), { passive: true });
            updateBeerRevealCards();
        }
    }

    // Gallery Injection — gallery-* images only
    const galleryImages = [
        "assets/images/gallery/gallery-01.webp",
        "assets/images/gallery/gallery-03.webp",
        "assets/images/gallery/gallery-06.webp?v=9",
        "assets/images/gallery/gallery-04.webp",
        "assets/images/gallery/gallery-02.webp",
        "assets/images/gallery/gallery-05.webp",
        "assets/images/gallery/gallery-10.webp",
        "assets/images/gallery/gallery-08.webp",
        "assets/images/gallery/gallery-15.webp"
    ];

    const columns = document.querySelectorAll('.gallery-column');
    const galleryParallaxMq = window.matchMedia('(max-width: 900px)');
    const galleryReduceMotionMq = window.matchMedia('(prefers-reduced-motion: reduce)');

    /** Mobile scroll reveal — no AOS (AOS + dynamic nodes often stuck at opacity 0). */
    let galleryRevealObserver = null;

    function initGalleryMobileReveal() {
        const items = document.querySelectorAll('.gallery-item');
        if (galleryRevealObserver) {
            galleryRevealObserver.disconnect();
            galleryRevealObserver = null;
        }

        items.forEach((el) => {
            el.classList.remove('gallery-item--revealed');
            el.style.removeProperty('--gallery-reveal-delay');
        });

        if (vimpelUseGalleryGrid()) {
            items.forEach((el) => el.classList.add('gallery-item--revealed'));
            return;
        }

        items.forEach((el, i) => {
            el.style.setProperty('--gallery-reveal-delay', `${Math.min(i * 42, 320)}ms`);
        });

        galleryRevealObserver = new IntersectionObserver(
            (entries, obs) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('gallery-item--revealed');
                    obs.unobserve(entry.target);
                });
            },
            { root: null, rootMargin: '0px 0px 8% 0px', threshold: 0.04 }
        );

        items.forEach((el) => galleryRevealObserver.observe(el));

        requestAnimationFrame(() => {
            items.forEach((el) => {
                if (el.classList.contains('gallery-item--revealed')) return;
                const r = el.getBoundingClientRect();
                if (r.top < window.innerHeight * 0.92 && r.bottom > -40) {
                    el.classList.add('gallery-item--revealed');
                    galleryRevealObserver.unobserve(el);
                }
            });
        });
    }

    galleryImages.forEach((src, index) => {
        const columnIndex = index % columns.length;
        const item = document.createElement('div');
        item.className = 'gallery-item';

        item.innerHTML = `
            <img src="${src}" alt="Vimpel Dish ${index + 1}" loading="${index < 4 ? 'eager' : 'lazy'}" decoding="async">
            <div class="gallery-overlay">
                <i class="fas fa-search-plus"></i>
            </div>
        `;

        item.addEventListener('click', () => openLightbox(src));
        columns[columnIndex].appendChild(item);
    });

    /** Mobile gallery — lead images shown first, then the rest in catalog order. */
    const mobileGalleryLead = [
        "assets/images/gallery/gallery-01.webp",
        "assets/images/gallery/gallery-10.webp",
        "assets/images/gallery/gallery-06.webp?v=9",
        "assets/images/gallery/gallery-05.webp",
        "assets/images/gallery/gallery-02.webp",
    ];

    function getMobileGalleryOrder() {
        const leadSet = new Set(mobileGalleryLead);
        const rest = galleryImages.filter((src) => !leadSet.has(src));
        return [...mobileGalleryLead, ...rest];
    }

    // Mobile marquee — two infinite rows scrolling in opposite directions
    const marqueeWrap = document.getElementById('gallery-marquee-wrap');
    const mobileGalleryLiftImages = new Set(['assets/images/gallery/gallery-06.webp?v=9']);

    function buildMarqueeRow(images, direction, eagerCount) {
        const row = document.createElement('div');
        row.className = 'gallery-marquee-row';

        const viewport = document.createElement('div');
        viewport.className = 'gallery-marquee-viewport';

        const track = document.createElement('div');
        track.className = `gallery-marquee-track gallery-marquee-track--${direction}`;

        const appendSet = (hidden) => {
            images.forEach((src, index) => {
                const item = document.createElement('div');
                item.className = mobileGalleryLiftImages.has(src)
                    ? 'gallery-marquee-item gallery-marquee-item--lift'
                    : 'gallery-marquee-item';
                if (hidden) item.setAttribute('aria-hidden', 'true');
                item.innerHTML = `<img src="${src}" alt="Vimpel" loading="${!hidden && index < eagerCount ? 'eager' : 'lazy'}" decoding="async">`;
                item.addEventListener('click', () => openLightbox(src));
                track.appendChild(item);
            });
        };

        appendSet(false);
        appendSet(true);

        viewport.appendChild(track);
        row.appendChild(viewport);
        return row;
    }

    function buildMobileMarquee() {
        if (!marqueeWrap) return;
        marqueeWrap.innerHTML = '';

        const ordered = getMobileGalleryOrder();
        const splitAt = Math.ceil(ordered.length / 2);
        const topImages = ordered.slice(0, splitAt);
        const bottomImages = ordered.slice(splitAt);

        marqueeWrap.appendChild(buildMarqueeRow(topImages, 'left', 3));
        marqueeWrap.appendChild(buildMarqueeRow(bottomImages.length ? bottomImages : topImages, 'right', 2));
    }

    let galleryMarqueeRafId = 0;
    let galleryMarqueeInView = false;
    let galleryMarqueeStates = [];

    function stopGalleryMarqueeMotion() {
        if (galleryMarqueeRafId) {
            cancelAnimationFrame(galleryMarqueeRafId);
            galleryMarqueeRafId = 0;
        }
        galleryMarqueeStates = [];
    }

    function measureGalleryMarqueeTracks() {
        galleryMarqueeStates.forEach((state) => {
            state.halfWidth = state.track.scrollWidth / 2;
            if (state.direction > 0 && state.offset === 0 && state.halfWidth > 0) {
                state.offset = -state.halfWidth;
            }
        });
    }

    function initGalleryMarqueeMotion() {
        stopGalleryMarqueeMotion();
        if (!marqueeWrap || !vimpelUseGalleryGrid() || vimpelPrefersReducedMotion()) return;

        const tracks = Array.from(marqueeWrap.querySelectorAll('.gallery-marquee-track'));
        if (!tracks.length) return;

        galleryMarqueeStates = tracks.map((track) => ({
            track,
            direction: track.classList.contains('gallery-marquee-track--right') ? 1 : -1,
            offset: 0,
            halfWidth: 0,
        }));

        measureGalleryMarqueeTracks();
        marqueeWrap.querySelectorAll('.gallery-marquee-track img').forEach((img) => {
            if (img.complete) return;
            img.addEventListener('load', measureGalleryMarqueeTracks, { once: true });
        });

        let lastTs = 0;
        const pxPerSecond = 36;

        const tick = (ts) => {
            galleryMarqueeRafId = requestAnimationFrame(tick);
            if (!galleryMarqueeInView || galleryMarqueeStates.length === 0) {
                lastTs = ts;
                return;
            }

            const dt = lastTs ? Math.min(ts - lastTs, 48) : 16.67;
            lastTs = ts;
            const delta = (pxPerSecond * dt) / 1000;

            galleryMarqueeStates.forEach((state) => {
                if (state.halfWidth <= 0) return;
                state.offset += state.direction * delta;
                if (state.direction < 0) {
                    while (state.offset <= -state.halfWidth) state.offset += state.halfWidth;
                } else {
                    while (state.offset >= 0) state.offset -= state.halfWidth;
                }
                state.track.style.transform = `translate3d(${state.offset.toFixed(2)}px, 0, 0)`;
            });
        };

        galleryMarqueeRafId = requestAnimationFrame(tick);
    }

    const gallerySection = document.getElementById('gallery');
    const galleryHeaderWrap = gallerySection?.querySelector('.container');

    function updateGalleryMobileHeader() {
        if (!vimpelUseGalleryGrid() || !gallerySection || !galleryHeaderWrap) return;

        const headerHeight = parseFloat(
            getComputedStyle(document.documentElement).getPropertyValue('--header-height')
        ) || 72;
        const rect = galleryHeaderWrap.getBoundingClientRect();
        const fadeStart = headerHeight + 12;
        const fadeEnd = headerHeight - 36;
        const fadeSpan = Math.max(fadeStart - fadeEnd, 1);
        const progress = Math.max(0, Math.min(1, (fadeStart - rect.bottom) / fadeSpan));

        galleryHeaderWrap.style.opacity = String(1 - progress);
        galleryHeaderWrap.style.visibility = progress > 0.98 ? 'hidden' : 'visible';
        galleryHeaderWrap.style.pointerEvents = progress > 0.85 ? 'none' : '';
    }

    function syncGalleryLayout() {
        const useGrid = vimpelUseGalleryGrid();
        document.documentElement.classList.toggle('vimpel-gallery-grid', useGrid);
        buildMobileMarquee();
        if (useGrid && marqueeWrap) {
            marqueeWrap.removeAttribute('aria-hidden');
            initGalleryMarqueeMotion();
            requestAnimationFrame(measureGalleryMarqueeTracks);
            updateGalleryMobileHeader();
        } else {
            stopGalleryMarqueeMotion();
            if (galleryHeaderWrap) {
                galleryHeaderWrap.style.removeProperty('opacity');
                galleryHeaderWrap.style.removeProperty('visibility');
                galleryHeaderWrap.style.removeProperty('pointer-events');
            }
        }
    }

    syncGalleryLayout();
    galleryParallaxMq.addEventListener('change', syncGalleryLayout);

    if (marqueeWrap) {
        vimpelObserveInView(marqueeWrap, (visible) => {
            galleryMarqueeInView = visible;
        }, '80px 0px');
        const mqRect = marqueeWrap.getBoundingClientRect();
        galleryMarqueeInView = mqRect.bottom > 0 && mqRect.top < window.innerHeight;
    }

    VimpelScroll.on(updateGalleryMobileHeader);
    window.addEventListener('resize', () => {
        measureGalleryMarqueeTracks();
        updateGalleryMobileHeader();
    }, { passive: true });

    initGalleryMobileReveal();
    galleryParallaxMq.addEventListener('change', initGalleryMobileReveal);
    galleryReduceMotionMq.addEventListener('change', initGalleryMobileReveal);

    /**
     * Skiper-style parallax columns — smoothed rAF loop with eased follow.
     */
    const galleryWrapper = document.getElementById('gallery-parallax');
    const parallaxMultipliers = [2, 3.3, 1.25, 3];
    const galleryColumnEls = Array.from(columns);
    const gallerySmoothing = 0.2;
    let galleryMetrics = { range: 0, vh: 0 };
    let galleryTargetProgress = 0;
    let galleryCurrentProgress = 0;
    let galleryInView = false;
    let galleryRafId = 0;
    let galleryParallaxActive = false;

    function refreshGalleryMetrics() {
        if (!galleryWrapper) return;
        galleryMetrics.vh = window.innerHeight;
        galleryMetrics.range = galleryWrapper.offsetHeight + galleryMetrics.vh;
    }

    function galleryScrollProgress() {
        if (!galleryWrapper || galleryMetrics.range <= 0) return 0;
        const top = galleryWrapper.getBoundingClientRect().top;
        return Math.max(0, Math.min(1, (galleryMetrics.vh - top) / galleryMetrics.range));
    }

    function resetGalleryParallax() {
        galleryColumnEls.forEach((col) => {
            col.style.setProperty('--gallery-y', '0px');
        });
        galleryTargetProgress = 0;
        galleryCurrentProgress = 0;
        galleryWrapper?.classList.remove('gallery-parallax--active');
        galleryParallaxActive = false;
        if (galleryRafId) {
            cancelAnimationFrame(galleryRafId);
            galleryRafId = 0;
        }
    }

    function applyGalleryColumnY(index, y) {
        const col = galleryColumnEls[index];
        if (!col) return;
        const val = Math.abs(y) < 0.05 ? '0px' : `${y.toFixed(2)}px`;
        col.style.setProperty('--gallery-y', val);
    }

    function syncGalleryTargets() {
        galleryTargetProgress = galleryScrollProgress();
    }

    function galleryParallaxFrame() {
        galleryRafId = 0;

        if (vimpelUseGalleryGrid() || !galleryWrapper || galleryColumnEls.length === 0) {
            resetGalleryParallax();
            return;
        }

        const progressDiff = galleryTargetProgress - galleryCurrentProgress;
        if (Math.abs(progressDiff) > 0.00015) {
            galleryCurrentProgress += progressDiff * gallerySmoothing;
        } else {
            galleryCurrentProgress = galleryTargetProgress;
        }

        const h = galleryMetrics.vh;
        const stillMoving = Math.abs(galleryTargetProgress - galleryCurrentProgress) > 0.00015;

        galleryColumnEls.forEach((_, index) => {
            const y = galleryCurrentProgress * h * parallaxMultipliers[index];
            applyGalleryColumnY(index, y);
        });

        if (stillMoving || (galleryInView && galleryCurrentProgress > 0 && galleryCurrentProgress < 1)) {
            if (!galleryParallaxActive) {
                galleryWrapper.classList.add('gallery-parallax--active');
                galleryParallaxActive = true;
            }
            galleryRafId = requestAnimationFrame(galleryParallaxFrame);
        } else {
            galleryWrapper.classList.remove('gallery-parallax--active');
            galleryParallaxActive = false;
        }
    }

    function scheduleGalleryParallax() {
        syncGalleryTargets();
        if (!galleryRafId) {
            galleryRafId = requestAnimationFrame(galleryParallaxFrame);
        }
    }

    if (galleryWrapper && !vimpelUseGalleryGrid()) {
        refreshGalleryMetrics();
        syncGalleryTargets();
        galleryCurrentProgress = galleryTargetProgress;
        galleryColumnEls.forEach((_, index) => {
            applyGalleryColumnY(index, galleryCurrentProgress * galleryMetrics.vh * parallaxMultipliers[index]);
        });

        vimpelObserveInView(
            galleryWrapper.closest('.gallery-parallax-container') || galleryWrapper,
            (visible) => {
                galleryInView = visible;
                if (visible) {
                    scheduleGalleryParallax();
                } else if (galleryRafId) {
                    cancelAnimationFrame(galleryRafId);
                    galleryRafId = 0;
                    galleryWrapper.classList.remove('gallery-parallax--active');
                    galleryParallaxActive = false;
                }
            },
            '200px 0px'
        );

        VimpelScroll.on(scheduleGalleryParallax);

        window.addEventListener('resize', () => {
            refreshGalleryMetrics();
            scheduleGalleryParallax();
        }, { passive: true });

        galleryParallaxMq.addEventListener('change', () => {
            syncGalleryLayout();
            refreshGalleryMetrics();
            scheduleGalleryParallax();
        });
        galleryReduceMotionMq.addEventListener('change', () => {
            syncGalleryLayout();
            if (vimpelUseGalleryGrid()) {
                resetGalleryParallax();
            } else {
                scheduleGalleryParallax();
            }
        });
    } else {
        resetGalleryParallax();
    }

    // Lightbox Logic
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const closeLightbox = document.querySelector('.close-lightbox');

    function openLightbox(src) {
        lightboxImg.src = src;
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    closeLightbox.addEventListener('click', () => {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
    });

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            lightbox.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    });

    initPartnerMarquee();

    // Contact form — sends name, phone, email, subject, message to site inbox
    const contactFormEl = document.getElementById('contact-form');
    const contactStatusEl = document.getElementById('contact-form-status');

    function setContactStatus(message, type) {
        if (!contactStatusEl) return;
        if (!message) {
            contactStatusEl.hidden = true;
            contactStatusEl.textContent = '';
            contactStatusEl.classList.remove('is-error', 'is-success');
            return;
        }
        contactStatusEl.hidden = false;
        contactStatusEl.textContent = message;
        contactStatusEl.classList.toggle('is-error', type === 'error');
        contactStatusEl.classList.toggle('is-success', type === 'success');
    }

    if (contactFormEl) {
        contactFormEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            const lang = document.querySelector('.lang-btn.active')?.getAttribute('data-lang') || 'hy';
            const t = translations[lang];
            const btn = contactFormEl.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            const endpoint = window.VIMPEL_CONTACT_ENDPOINT;

            if (!contactFormEl.reportValidity()) {
                setContactStatus(t.form_validation_error, 'error');
                return;
            }

            if (!endpoint) {
                setContactStatus(t.form_error, 'error');
                return;
            }

            const formData = new FormData(contactFormEl);
            const payload = {
                name: String(formData.get('name') || '').trim(),
                phone: String(formData.get('phone') || '').trim(),
                email: String(formData.get('email') || '').trim(),
                subject: String(formData.get('subject') || '').trim(),
                message: String(formData.get('message') || '').trim(),
                website: String(formData.get('website') || '').trim(),
            };

            btn.disabled = true;
            btn.textContent = t.form_sending;
            setContactStatus('', '');

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const text = await res.text();
                let data = {};
                try {
                    data = text ? JSON.parse(text) : {};
                } catch {
                    if (text.includes('login') || res.redirected) {
                        setContactStatus(t.form_session_error || t.form_error, 'error');
                        return;
                    }
                    throw new Error('invalid_json');
                }

                if (!res.ok || !data.success) {
                    const msg = res.status === 422 ? t.form_validation_error : t.form_error;
                    throw new Error(data.message || msg);
                }

                contactFormEl.reset();
                setContactStatus(t.form_success, 'success');
                updateLanguage(lang);
            } catch {
                setContactStatus(t.form_error, 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }

    initHistoryScrollVideo();

});

// ─── Our History — scroll scrubs video (Chrome desktop + others) ─────────────
function initHistoryScrollVideo() {
    const stage  = document.getElementById('history-scroll-track');
    const sticky = document.getElementById('history-sticky');
    const video  = document.getElementById('history-video');
    const slides = document.querySelectorAll('.history-slide');
    const dots   = document.querySelectorAll('#history-dots .history-dot');
    const dotsEl = document.getElementById('history-dots');

    if (!stage || !sticky || !video || slides.length === 0) return;

    const TOTAL = slides.length;
    const desktopMq = window.matchMedia('(min-width: 901px)');

    let slideIndex = 0;
    let videoReady = false;
    let decoderUnlocked = false;
    let rafPending = false;
    let scrollActive = false;
    let scrollIdleTimer = null;
    let lastTargetTime = -1;
    let pendingProgress = 0;

    video.removeAttribute('autoplay');
    video.muted = true;
    video.playsInline = true;
    video.loop = false;
    video.preload = 'auto';

    function headerH() {
        return parseInt(getComputedStyle(document.documentElement)
            .getPropertyValue('--header-height'), 10) || 72;
    }

    function isDesktopScrub() {
        return desktopMq.matches;
    }

    /** Progress from viewport position of the scroll track (stable in Chrome). */
    function scrollMetrics() {
        const hh = headerH();
        const rect = stage.getBoundingClientRect();
        const panelH = window.innerHeight - hh;
        const scrollRange = stage.offsetHeight - panelH;

        if (scrollRange <= 0) {
            return { progress: 0, isFixed: false, isPast: false };
        }

        const progress = Math.min(1, Math.max(0, (hh - rect.top) / scrollRange));
        const isFixed = rect.top <= hh + 2 && rect.bottom > window.innerHeight + 2;
        const isPast = rect.bottom <= window.innerHeight + 2;

        return { progress, isFixed, isPast };
    }

    function unlockDecoder() {
        if (decoderUnlocked || !isDesktopScrub()) return;
        decoderUnlocked = true;
        const wasTime = video.currentTime;
        video.play()
            .then(() => {
                video.pause();
                video.currentTime = wasTime || 0.001;
                applyVideoFrame(pendingProgress, true);
            })
            .catch(() => {
                decoderUnlocked = false;
            });
    }

    function applyVideoFrame(progress, force) {
        if (!isDesktopScrub()) return;

        pendingProgress = progress;

        if (!videoReady) return;

        const dur = video.duration;
        if (!dur || !Number.isFinite(dur) || dur <= 0) return;

        const target = Math.max(0.001, Math.min(progress * dur, dur - 0.04));
        if (!force && Math.abs(target - lastTargetTime) < 0.004) return;

        lastTargetTime = target;

        if (!video.paused) {
            video.pause();
        }

        if (video.readyState < 2) {
            video.addEventListener('loadeddata', () => applyVideoFrame(progress, true), { once: true });
            return;
        }

        try {
            video.currentTime = target;
        } catch (_) {
            /* Chrome may throw until enough data is buffered */
        }
    }

    function updatePanel() {
        const { progress, isFixed, isPast } = scrollMetrics();

        sticky.classList.toggle('is-fixed', isFixed);
        sticky.classList.toggle('is-past', isPast);
        dotsEl?.classList.toggle('visible', isFixed);

        if (isDesktopScrub()) {
            if (scrollActive || isFixed || isPast) {
                unlockDecoder();
                applyVideoFrame(progress, scrollActive);
            }
            if (!scrollActive) {
                video.pause();
            }
        }

        const idx = Math.min(Math.floor(progress * TOTAL), TOTAL - 1);
        if (idx !== slideIndex) {
            slides[slideIndex]?.classList.remove('active');
            dots[slideIndex]?.classList.remove('active');
            slideIndex = idx;
            slides[slideIndex]?.classList.add('active');
            dots[slideIndex]?.classList.add('active');
        }
    }

    function scheduleUpdate() {
        if (rafPending) return;
        rafPending = true;
        requestAnimationFrame(() => {
            rafPending = false;
            updatePanel();
        });
    }

    function markScrollActive() {
        scrollActive = true;
        clearTimeout(scrollIdleTimer);
        scrollIdleTimer = setTimeout(() => {
            scrollActive = false;
            video.pause();
            scheduleUpdate();
        }, 100);
        scheduleUpdate();
    }

    function enableMobilePlayback() {
        scrollActive = false;
        clearTimeout(scrollIdleTimer);
        sticky.classList.remove('is-fixed', 'is-past');
        dotsEl?.classList.remove('visible');
        lastTargetTime = -1;
        video.loop = true;
        video.play().catch(() => {});
    }

    function enableDesktopScrub() {
        video.loop = false;
        video.pause();
        lastTargetTime = -1;
        scheduleUpdate();
    }

    function onLayoutModeChange() {
        if (isDesktopScrub()) {
            enableDesktopScrub();
        } else {
            enableMobilePlayback();
        }
    }

    function markVideoReady() {
        const dur = video.duration;
        if (!dur || !Number.isFinite(dur) || dur <= 0) return;
        videoReady = true;
        lastTargetTime = -1;
        try {
            video.currentTime = 0.001;
        } catch (_) {
            /* ignore */
        }
        onLayoutModeChange();
    }

    video.addEventListener('loadedmetadata', markVideoReady);
    video.addEventListener('durationchange', markVideoReady);
    video.addEventListener('canplaythrough', markVideoReady, { once: true });

    if (video.readyState >= 1) {
        markVideoReady();
    }

    window.addEventListener('scroll', () => {
        if (!isDesktopScrub()) {
            scheduleUpdate();
            return;
        }
        markScrollActive();
    }, { passive: true });

    window.addEventListener('resize', scheduleUpdate, { passive: true });
    window.addEventListener('wheel', markScrollActive, { passive: true });
    desktopMq.addEventListener('change', onLayoutModeChange);

    onLayoutModeChange();
}
