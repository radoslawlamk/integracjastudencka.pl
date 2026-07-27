<?php
$pageTitle = $title ?? 'Integracja Studencka';
$defaultDescription = 'Kalendarz wydarzeń studenckich, integracji i clubbingów w największych miastach Polski.';
$pageDescription = $metaDescription ?? ($event['seo_description'] ?? $defaultDescription);
$pageImage = $metaImage ?? ($event['hero_image'] ?? '/assets/images/hero-students-party.png');
$pageType = $metaType ?? 'website';
$structuredData = $structuredData ?? null;
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = parse_url($currentUri, PHP_URL_PATH) ?: '/';
$canonicalPath = $canonicalPath ?? $currentPath;
$requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$requestHost = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000';
$requestBaseUrl = $requestScheme . '://' . $requestHost;
$envBaseUrl = rtrim(getenv('APP_URL') ?: '', '/');
$envHost = $envBaseUrl ? parse_url($envBaseUrl, PHP_URL_HOST) : null;
$isLocalEnvHost = in_array($envHost, ['127.0.0.1', 'localhost'], true);
$isLocalRequestHost = in_array(parse_url($requestBaseUrl, PHP_URL_HOST), ['127.0.0.1', 'localhost'], true);
$baseUrl = ($envBaseUrl && (!$isLocalEnvHost || $isLocalRequestHost)) ? $envBaseUrl : $requestBaseUrl;
$absoluteUrl = static function (?string $value) use ($baseUrl): string {
    $value = (string) $value;
    if ($value === '') {
        return $baseUrl . '/';
    }
    if (preg_match('~^https?://~i', $value)) {
        return $value;
    }
    return $baseUrl . '/' . ltrim($value, '/');
};
$pageUrl = $absoluteUrl($canonicalPath);
$canonicalUrl = $absoluteUrl($canonicalPath);
$pageImage = $absoluteUrl($pageImage);
$pageImageSecure = preg_replace('~^http://~i', 'https://', $pageImage);
$pageImagePath = parse_url($pageImage, PHP_URL_PATH) ?: '';
$pageImageLocalPath = $pageImagePath ? realpath(__DIR__ . '/../../../public' . $pageImagePath) : false;
$pageImageInfo = ($pageImageLocalPath && is_file($pageImageLocalPath)) ? @getimagesize($pageImageLocalPath) : false;
$pageImageWidth = $pageImageInfo[0] ?? 1200;
$pageImageHeight = $pageImageInfo[1] ?? 630;
$pageImageType = $pageImageInfo['mime'] ?? 'image/png';
$shareTitle = $pageTitle;
$shareText = trim($pageTitle . ' - ' . $pageDescription);
$encodedShareUrl = rawurlencode($pageUrl);
$encodedShareText = rawurlencode($shareText . ' ' . $pageUrl);
$schemaUrlKeys = ['url' => true, '@id' => true, 'item' => true];
$normalizeStructuredUrls = static function ($value) use (&$normalizeStructuredUrls, $absoluteUrl, $schemaUrlKeys) {
    if (!is_array($value)) {
        return $value;
    }

    foreach ($value as $key => $item) {
        if (is_array($item)) {
            $value[$key] = $normalizeStructuredUrls($item);
            continue;
        }

        if (is_string($item) && (isset($schemaUrlKeys[(string) $key]) || $key === 'image')) {
            $value[$key] = $absoluteUrl($item);
        }
    }

    return $value;
};
$structuredData = $structuredData ? $normalizeStructuredUrls($structuredData) : null;
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <meta property="og:locale" content="pl_PL">
    <meta property="og:type" content="<?= e($pageType) ?>">
    <meta property="og:site_name" content="Integracja Studencka">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($pageUrl) ?>">
    <meta property="og:image" content="<?= e($pageImage) ?>">
    <meta property="og:image:secure_url" content="<?= e($pageImageSecure) ?>">
    <meta property="og:image:type" content="<?= e($pageImageType) ?>">
    <meta property="og:image:width" content="<?= e((string) $pageImageWidth) ?>">
    <meta property="og:image:height" content="<?= e((string) $pageImageHeight) ?>">
    <meta property="og:image:alt" content="<?= e($pageTitle) ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($pageImage) ?>">
    <?php if ($structuredData): ?>
        <script type="application/ld+json"><?= json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1014924161372124');
    fbq('track', 'PageView');
    </script>

    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1014924161372124&ev=PageView&noscript=1"
alt=""></noscript>
<header class="site-header">
    <a class="brand" href="/">Integracja Studencka</a>
    <nav>
        <a href="/#wydarzenia">Wydarzenia</a>
        <a href="/#rodzaje-imprez">O nas</a>
        <a href="/miasta">Miasta</a>
        <a href="/#newsy">Newsy</a>
        <a href="/#partnerzy">Partnerzy</a>
        <a href="/#powiadomienia">Powiadomienia</a>
        <?php if (is_admin()): ?>
            <a href="/admin">CRM</a>
        <?php endif; ?>
    </nav>
</header>
<?= $content ?>
<section class="bottom-cta">
    <div>
        <p>Bilety i najważniejsze wydarzenia</p>
        <h2>Wybierz miasto akademickie i dołącz do największych imprez studenckich.</h2>
        <span>Kontakt z nami: <a href="mailto:kontakt@integracjastudencka.pl">kontakt@integracjastudencka.pl</a></span>
    </div>
    <a class="bottom-cta-button" href="/#wydarzenia">Sprawdź kalendarz</a>
</section>
<footer class="site-footer">
    <strong>Integracja Studencka</strong>
    <span>Największe i najważniejsze imprezy w miastach akademickich</span>
    <span>Kontakt: kontakt@integracjastudencka.pl</span>
</footer>
<nav class="legal-footer-links" aria-label="Linki prawne">
    <a href="/regulamin">Regulamin</a>
    <a href="/polityka-prywatnosci">Polityka prywatności</a>
    <a href="/polityka-cookies">Cookies</a>
    <a href="/wycofaj-zgody">Wycofaj zgody</a>
    <a href="/archiwum">Archiwum</a>
    <a href="/mapa-strony">Mapa strony</a>
</nav>
<aside class="share-widget" aria-label="Udostępnij stronę" data-share-widget data-share-url="<?= e($pageUrl) ?>" data-share-title="<?= e($shareTitle) ?>" data-share-text="<?= e($shareText) ?>">
    <button class="share-toggle" type="button" data-share-toggle aria-expanded="false">
        <span>Udostępnij</span>
    </button>
    <div class="share-panel" data-share-panel hidden>
        <a class="share-item share-facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?= e($encodedShareUrl) ?>" target="_blank" rel="noopener" aria-label="Udostępnij na Facebooku">
            <span class="share-logo">f</span><strong>Facebook</strong><small>Udostępnij post</small>
        </a>
        <a class="share-item share-messenger" href="fb-messenger://share?link=<?= e($encodedShareUrl) ?>" aria-label="Udostępnij w Messengerze">
            <span class="share-logo">M</span><strong>Messenger</strong><small>Wyślij znajomym</small>
        </a>
        <a class="share-item share-whatsapp" href="https://api.whatsapp.com/send?text=<?= e($encodedShareText) ?>" target="_blank" rel="noopener" aria-label="Udostępnij przez WhatsApp">
            <span class="share-logo">☎</span><strong>WhatsApp</strong><small>Wyślij link</small>
        </a>
        <button class="share-item share-instagram" type="button" data-share-copy aria-label="Skopiuj link do udostępnienia na Instagramie">
            <span class="share-logo">◎</span><strong>Instagram</strong><small>Skopiuj link</small>
        </button>
        <button class="share-item share-tiktok" type="button" data-share-copy aria-label="Skopiuj link do udostępnienia na TikToku">
            <span class="share-logo">♪</span><strong>TikTok</strong><small>Skopiuj link</small>
        </button>
        <button class="share-item share-copy" type="button" data-share-copy aria-label="Skopiuj link">
            <span class="share-logo">↗</span><strong>Kopiuj link</strong><small>Do schowka</small>
        </button>
        <button class="share-native" type="button" data-share-native>Udostępnij z telefonu</button>
    </div>
</aside>
<button class="back-to-top" type="button" aria-label="Wróć na górę strony" data-back-to-top>&uarr;</button>
<div class="cookie-banner" data-cookie-banner hidden>
    <div>
        <strong>Cookies i analityka</strong>
        <p>Używamy plików cookies oraz narzędzi analitycznych, żeby strona działała poprawnie i żeby mierzyć zainteresowanie wydarzeniami.</p>
    </div>
    <a href="/polityka-cookies">Więcej</a>
    <button type="button" data-cookie-accept>OK</button>
</div>
<script>
document.addEventListener('click', function (event) {
    const link = event.target.closest('[data-analytics-event][data-analytics-action]');
    if (!link) return;

    const action = link.getAttribute('data-analytics-action') || '';
    const payload = new URLSearchParams();
    payload.set('event_id', link.getAttribute('data-analytics-event') || '');
    payload.set('action', action);
    payload.set('target_url', link.href || '');

    if (window.fbq) {
        const pixelEvents = {
            ticket: 'InitiateCheckout',
            ticket_notify: 'Lead',
            details: 'ViewContent',
            facebook: 'Contact',
            fanpage: 'Contact'
        };
        window.fbq('track', pixelEvents[action] || 'ViewContent', {
            content_name: link.textContent.trim(),
            content_ids: [link.getAttribute('data-analytics-event') || ''],
            content_type: 'event',
            destination_url: link.href || ''
        });
    }

    if (navigator.sendBeacon) {
        navigator.sendBeacon('/analityka/klikniecie', payload);
        return;
    }

    fetch('/analityka/klikniecie', {
        method: 'POST',
        body: payload,
        keepalive: true,
        credentials: 'same-origin'
    }).catch(function () {});
});

(() => {
    const banner = document.querySelector('[data-cookie-banner]');
    if (!banner || localStorage.getItem('is_cookie_notice_ok') === '1') return;
    banner.hidden = false;
    const button = banner.querySelector('[data-cookie-accept]');
    if (button) {
        button.addEventListener('click', () => {
            localStorage.setItem('is_cookie_notice_ok', '1');
            banner.hidden = true;
        });
    }
})();

(() => {
    const button = document.querySelector('[data-back-to-top]');
    if (!button) return;
    button.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();

(() => {
    const widget = document.querySelector('[data-share-widget]');
    if (!widget) return;

    const toggle = widget.querySelector('[data-share-toggle]');
    const panel = widget.querySelector('[data-share-panel]');
    const url = widget.getAttribute('data-share-url') || window.location.href;
    const title = widget.getAttribute('data-share-title') || document.title;
    const text = widget.getAttribute('data-share-text') || title;

    if (toggle && panel) {
        toggle.addEventListener('click', () => {
            const isHidden = panel.hasAttribute('hidden');
            panel.toggleAttribute('hidden', !isHidden);
            toggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        });
    }

    const copyLink = (button) => {
        const done = () => {
            const previous = button.textContent;
            button.textContent = 'OK';
            button.classList.add('is-copied');
            window.setTimeout(() => {
                button.textContent = previous;
                button.classList.remove('is-copied');
            }, 1300);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(done).catch(() => {});
            return;
        }

        const input = document.createElement('textarea');
        input.value = url;
        input.setAttribute('readonly', 'readonly');
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        input.remove();
        done();
    };

    widget.querySelectorAll('[data-share-copy]').forEach((button) => {
        button.addEventListener('click', () => copyLink(button));
    });

    const nativeButton = widget.querySelector('[data-share-native]');
    if (nativeButton) {
        if (!navigator.share) {
            nativeButton.hidden = true;
        } else {
            nativeButton.addEventListener('click', () => {
                navigator.share({ title, text, url }).catch(() => {});
            });
        }
    }
})();
</script>
</body>
</html>
