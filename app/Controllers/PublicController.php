<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Mailer;
use App\Models\Event;
use App\Models\EventAnalytics;
use App\Models\EventClub;
use App\Models\EventVideo;
use App\Models\MarketingContact;
use App\Models\NewsArticle;
use App\Models\Partner;
use App\Models\TicketWaitlist;

final class PublicController
{
    private const ACADEMIC_CITIES = [
        'bialystok' => 'Białystok',
        'bydgoszcz' => 'Bydgoszcz',
        'czestochowa' => 'Częstochowa',
        'katowice' => 'Katowice',
        'kielce' => 'Kielce',
        'krakow' => 'Kraków',
        'lodz' => 'Łódź',
        'lublin' => 'Lublin',
        'olsztyn' => 'Olsztyn',
        'opole' => 'Opole',
        'poznan' => 'Poznań',
        'radom' => 'Radom',
        'rzeszow' => 'Rzeszów',
        'szczecin' => 'Szczecin',
        'tarnow' => 'Tarnów',
        'torun' => 'Toruń',
        'trojmiasto' => 'Trójmiasto',
        'warszawa' => 'Warszawa',
        'wroclaw' => 'Wrocław',
        'zielona-gora' => 'Zielona Góra',
    ];

    private const EVENT_TYPE_PAGES = [
        'inauguracja-studencka' => [
            'name' => 'Inauguracja Studencka',
            'keyword' => 'inauguracja',
            'description' => 'Największe rozpoczęcia roku akademickiego, które otwierają sezon imprez studenckich w miastach akademickich.',
        ],
        'integracja-studencka' => [
            'name' => 'Integracja Studencka',
            'keyword' => 'integracja',
            'description' => 'Imprezy integracyjne dla pierwszych lat, starszych roczników i wszystkich studentów z miasta oraz województwa.',
        ],
        'studencki-clubbing' => [
            'name' => 'Studencki Clubbing',
            'keyword' => 'clubbing',
            'description' => 'Jeden bilet, kilka klubów i tysiące uczestników na trasie po najlepszych lokalach akademickiego miasta.',
        ],
        'otrzesiny-studenckie' => [
            'name' => 'Otrzęsiny Studenckie',
            'keyword' => 'otrzesiny',
            'description' => 'Najmocniejsze otrzęsiny dla nowych studentów, ekip z uczelni i osób, które chcą wejść w klimat roku akademickiego.',
        ],
        'polowinki-studenckie' => [
            'name' => 'Połowinki Studenckie',
            'keyword' => 'polowinki',
            'description' => 'Połowinki dla studentów świętujących środek studiów w klubowej, dużej i dobrze zorganizowanej formule.',
        ],
        'andrzejki-studenckie' => [
            'name' => 'Andrzejki Studenckie',
            'keyword' => 'andrzejki',
            'description' => 'Jesienne wydarzenia akademickie, które łączą taniec, ekipę z uczelni i mocny klubowy klimat.',
        ],
        'walentynki-studenckie' => [
            'name' => 'Walentynki Studenckie',
            'keyword' => 'walentynki',
            'description' => 'Studenckie walentynki dla par, singli i znajomych, którzy chcą spędzić wieczór na dużym evencie.',
        ],
        'mikolajki-studenckie' => [
            'name' => 'Mikołajki Studenckie',
            'keyword' => 'mikolajki',
            'description' => 'Grudniowe imprezy studenckie przed świętami, sesją i końcówką roku.',
        ],
        'sylwester-studencki' => [
            'name' => 'Sylwester Studencki',
            'keyword' => 'sylwester',
            'description' => 'Sylwestrowe wydarzenia dla studentów, którzy chcą wejść w nowy rok w akademickim stylu.',
        ],
        'juwenalia' => [
            'name' => 'Juwenalia',
            'keyword' => 'juwenalia',
            'description' => 'Największe święto studentów: koncerty, plener, wydarzenia towarzyszące i imprezy klubowe.',
        ],
        'student-party-travel' => [
            'name' => 'Student Party Travel',
            'keyword' => 'travel',
            'description' => 'Wyjazdy studenckie, imprezowe tripy i formaty łączące podróż z integracją.',
        ],
    ];

    public function home(): string
    {
        $events = Event::published($_GET['city'] ?? null, $_GET['month'] ?? null);
        return View::render('public/home', [
            'title' => 'Integracja Studencka - wydarzenia studenckie',
            'metaDescription' => 'Największe i najważniejsze imprezy w miastach akademickich. Sprawdź inauguracje, integracje, otrzęsiny, połowinki i juwenalia.',
            'metaImage' => '/assets/images/hero-students-party.png',
            'canonicalPath' => '/',
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'Integracja Studencka',
                'url' => '/',
                'description' => 'Kalendarz największych imprez studenckich w miastach akademickich.',
                'inLanguage' => 'pl-PL',
            ],
            'events' => $events,
            'cities' => Event::cities(),
            'months' => Event::months(),
            'newsArticles' => NewsArticle::published(),
        ]);
    }

    public function events(): string
    {
        return $this->home();
    }

    public function robots(): string
    {
        header('Content-Type: text/plain; charset=UTF-8');
        $baseUrl = $this->publicBaseUrl();

        return "User-agent: *\n"
            . "Allow: /\n"
            . "Disallow: /admin\n"
            . "Disallow: /admin/\n"
            . "Sitemap: {$baseUrl}/sitemap.xml\n";
    }

    public function sitemap(): string
    {
        header('Content-Type: application/xml; charset=UTF-8');
        $baseUrl = $this->publicBaseUrl();
        $urls = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/miasta', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/archiwum', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/mapa-strony', 'priority' => '0.55', 'changefreq' => 'monthly'],
        ];

        foreach (array_keys(self::EVENT_TYPE_PAGES) as $slug) {
            $urls[] = ['loc' => '/' . $slug, 'priority' => '0.78', 'changefreq' => 'weekly'];
        }

        foreach (self::ACADEMIC_CITIES as $slug => $city) {
            $urls[] = ['loc' => '/miasta/' . $slug, 'priority' => '0.85', 'changefreq' => 'weekly'];
        }

        foreach (Event::published(null, null, true) as $event) {
            $urls[] = [
                'loc' => '/wydarzenia/' . $event['slug'],
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => date('Y-m-d', strtotime($event['updated_at'] ?? $event['starts_at'] ?? 'now')),
            ];
        }

        foreach (NewsArticle::publishedAll() as $article) {
            $urls[] = [
                'loc' => '/news/' . $article['slug'],
                'priority' => '0.65',
                'changefreq' => 'monthly',
                'lastmod' => date('Y-m-d', strtotime($article['updated_at'] ?? $article['published_at'] ?? $article['created_at'] ?? 'now')),
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . e($baseUrl . $url['loc']) . "</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . e($url['lastmod']) . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . e($url['changefreq']) . "</changefreq>\n";
            $xml .= '    <priority>' . e($url['priority']) . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= "</urlset>\n";

        return $xml;
    }

    public function htmlSitemap(): string
    {
        return View::render('public/html-sitemap', [
            'title' => 'Mapa strony - Integracja Studencka',
            'metaDescription' => 'Mapa strony Integracja Studencka: miasta akademickie, typy imprez, wydarzenia i newsy.',
            'canonicalPath' => '/mapa-strony',
            'cities' => self::ACADEMIC_CITIES,
            'eventTypes' => self::EVENT_TYPE_PAGES,
            'events' => Event::published(null, null, true),
            'newsArticles' => NewsArticle::publishedAll(),
        ]);
    }

    public function archive(): string
    {
        return View::render('public/archive', [
            'title' => 'Archiwum wydarzeń studenckich - Integracja Studencka',
            'metaDescription' => 'Archiwum zakończonych wydarzeń studenckich, integracji, inauguracji, otrzęsin, clubbingów i juwenaliów.',
            'metaImage' => '/assets/images/hero-students-party.png',
            'canonicalPath' => '/archiwum',
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => 'Archiwum wydarzeń studenckich',
                'description' => 'Zakończone wydarzenia studenckie Integracja Studencka.',
                'url' => '/archiwum',
                'inLanguage' => 'pl-PL',
            ],
            'events' => Event::finishedPublished(60),
        ]);
    }

    public function eventTypePage(): string
    {
        $slug = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
        $type = self::EVENT_TYPE_PAGES[$slug] ?? null;
        if (!$type) {
            http_response_code(404);
            return View::render('public/404', ['title' => 'Nie znaleziono strony']);
        }

        $events = Event::publishedByKeyword($type['keyword']);
        $title = $type['name'] . ' - wydarzenia studenckie w miastach akademickich';
        $description = $type['description'] . ' Sprawdź kalendarz, bilety i powiadomienia Integracja Studencka.';

        return View::render('public/event-type', [
            'title' => $title,
            'metaDescription' => $description,
            'metaImage' => '/assets/images/hero-students-party.png',
            'canonicalPath' => '/' . $slug,
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $title,
                'description' => $description,
                'url' => '/' . $slug,
                'inLanguage' => 'pl-PL',
            ],
            'slug' => $slug,
            'type' => $type,
            'events' => $events,
            'cities' => self::ACADEMIC_CITIES,
            'eventTypes' => self::EVENT_TYPE_PAGES,
        ]);
    }

    public function cities(): string
    {
        return View::render('public/cities', [
            'title' => 'Miasta akademickie - imprezy studenckie',
            'metaDescription' => 'Wybierz miasto akademickie i sprawdź inauguracje studenckie, integracje, otrzęsiny, clubbing, juwenalia i największe wydarzenia dla studentów.',
            'metaImage' => '/assets/images/hero-students-party.png',
            'canonicalPath' => '/miasta',
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => 'Miasta akademickie - imprezy studenckie',
                'description' => 'Lista podstron miast akademickich z kalendarzem imprez studenckich.',
                'url' => '/miasta',
                'inLanguage' => 'pl-PL',
            ],
            'cities' => self::ACADEMIC_CITIES,
        ]);
    }

    private function requestBaseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000';
        return $scheme . '://' . $host;
    }

    private function publicBaseUrl(): string
    {
        $requestBaseUrl = $this->requestBaseUrl();
        $envBaseUrl = rtrim(getenv('APP_URL') ?: '', '/');
        $envHost = $envBaseUrl ? parse_url($envBaseUrl, PHP_URL_HOST) : null;
        $requestHost = parse_url($requestBaseUrl, PHP_URL_HOST);
        $isLocalEnvHost = in_array($envHost, ['127.0.0.1', 'localhost'], true);
        $isLocalRequestHost = in_array($requestHost, ['127.0.0.1', 'localhost'], true);

        if ($envBaseUrl && (!$isLocalEnvHost || $isLocalRequestHost)) {
            return $envBaseUrl;
        }

        return $requestBaseUrl;
    }

    private function academicCitySlug(string $city): ?string
    {
        $city = trim($city);
        $normalize = static fn (string $value): string => function_exists('mb_strtolower')
            ? mb_strtolower(trim($value), 'UTF-8')
            : strtolower(trim($value));
        $slugify = static function (string $value) use ($normalize): string {
            $value = strtr($normalize($value), [
                'ą' => 'a',
                'ć' => 'c',
                'ę' => 'e',
                'ł' => 'l',
                'ń' => 'n',
                'ó' => 'o',
                'ś' => 's',
                'ż' => 'z',
                'ź' => 'z',
            ]);
            $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?: '';
            return trim($value, '-');
        };

        foreach (self::ACADEMIC_CITIES as $slug => $name) {
            if ($normalize($name) === $normalize($city)) {
                return $slug;
            }
        }

        $fallbackSlug = $slugify($city);
        if (isset(self::ACADEMIC_CITIES[$fallbackSlug])) {
            return $fallbackSlug;
        }

        return null;
    }

    public function city(array $params): string
    {
        $slug = $params['slug'] ?? '';
        $city = self::ACADEMIC_CITIES[$slug] ?? null;
        if (!$city) {
            http_response_code(404);
            return View::render('public/404', ['title' => 'Nie znaleziono miasta']);
        }

        $events = Event::published($city);
        $cityPath = '/miasta/' . $slug;
        $cityTitle = 'Imprezy studenckie ' . $city . ' - Inauguracje, integracje, otrzęsiny i juwenalia';
        $cityDescription = 'Imprezy studenckie ' . $city . ': inauguracja studencka, integracja studencka, otrzęsiny, połowinki, studencki clubbing, juwenalia i największe wydarzenia akademickie.';
        $faqQuestions = [
            [
                'name' => 'Gdzie sprawdzić imprezy studenckie ' . $city . '?',
                'text' => 'Na tej stronie znajdziesz kalendarz wydarzeń studenckich w mieście ' . $city . ', w tym inauguracje, integracje, otrzęsiny, clubbing, połowinki i juwenalia.',
            ],
            [
                'name' => 'Czy mogę dostać powiadomienie o nowych wydarzeniach w mieście ' . $city . '?',
                'text' => 'Tak. W formularzu powiadomień możesz zostawić kontakt, a wydarzenia z miasta ' . $city . ' trafią do Twojej listy powiadomień.',
            ],
            [
                'name' => 'Jakie typy imprez studenckich są dostępne w mieście ' . $city . '?',
                'text' => 'Najczęściej są to inauguracje studenckie, integracje, otrzęsiny, połowinki, andrzejki, walentynki, mikołajki, sylwester studencki, juwenalia oraz studencki clubbing.',
            ],
        ];

        return View::render('public/city', [
            'title' => $cityTitle,
            'metaDescription' => $cityDescription,
            'metaImage' => '/assets/images/hero-students-party.png',
            'canonicalPath' => $cityPath,
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'WebPage',
                        '@id' => $cityPath . '#webpage',
                        'name' => $cityTitle,
                        'description' => $cityDescription,
                        'url' => $cityPath,
                        'inLanguage' => 'pl-PL',
                    ],
                    [
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => [
                            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Integracja Studencka', 'item' => '/'],
                            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Miasta', 'item' => '/miasta'],
                            ['@type' => 'ListItem', 'position' => 3, 'name' => $city, 'item' => $cityPath],
                        ],
                    ],
                    [
                        '@type' => 'FAQPage',
                        'mainEntity' => array_map(static fn (array $faq): array => [
                            '@type' => 'Question',
                            'name' => $faq['name'],
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => $faq['text'],
                            ],
                        ], $faqQuestions),
                    ],
                ],
            ],
            'city' => $city,
            'citySlug' => $slug,
            'events' => $events,
            'faqQuestions' => $faqQuestions,
            'allCities' => self::ACADEMIC_CITIES,
        ]);
    }

    public function newsArticle(array $params): string
    {
        $article = NewsArticle::findPublishedBySlug($params['slug']);
        if (!$article) {
            http_response_code(404);
            return View::render('public/404', ['title' => 'Nie znaleziono artykułu']);
        }

        return View::render('public/news-article', [
            'title' => $article['seo_title'] ?: $article['title'],
            'metaDescription' => $article['seo_description'] ?: $article['excerpt'],
            'metaImage' => $article['image_url'] ?: '/assets/images/hero-students-party.png',
            'metaType' => 'article',
            'canonicalPath' => '/news/' . $article['slug'],
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $article['title'],
                'description' => $article['seo_description'] ?: $article['excerpt'],
                'image' => $article['image_url'] ?: '/assets/images/hero-students-party.png',
                'datePublished' => $article['published_at'] ?: $article['created_at'],
                'dateModified' => $article['updated_at'] ?? $article['published_at'] ?? $article['created_at'],
                'author' => [
                    '@type' => 'Organization',
                    'name' => 'Integracja Studencka',
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Integracja Studencka',
                ],
                'inLanguage' => 'pl-PL',
            ],
            'article' => $article,
            'relatedNews' => NewsArticle::related($article['slug']),
        ]);
    }

    public function privacyPolicy(): string
    {
        return View::render('public/privacy', [
            'title' => 'Polityka prywatności - Integracja Studencka',
            'metaDescription' => 'Informacje o przetwarzaniu danych, zgodach marketingowych i możliwości rezygnacji z powiadomień.',
            'canonicalPath' => '/polityka-prywatnosci',
        ]);
    }

    public function cookiesPolicy(): string
    {
        return View::render('public/cookies', [
            'title' => 'Polityka cookies - Integracja Studencka',
            'metaDescription' => 'Informacje o plikach cookies i narzędziach analitycznych wykorzystywanych na stronie.',
            'canonicalPath' => '/polityka-cookies',
        ]);
    }

    public function terms(): string
    {
        return View::render('public/terms', [
            'title' => 'Regulamin serwisu - Integracja Studencka',
            'metaDescription' => 'Regulamin serwisu internetowego IntegracjaStudencka.pl.',
            'canonicalPath' => '/regulamin',
        ]);
    }

    public function consentLookup(): string
    {
        return View::render('public/consent-lookup', [
            'title' => 'Wycofaj zgody - Integracja Studencka',
            'metaDescription' => 'Wycofanie lub zmiana zgody na powiadomienia email i SMS.',
            'canonicalPath' => '/wycofaj-zgody',
            'status' => $_GET['status'] ?? '',
        ]);
    }

    public function consentLookupSubmit(): string
    {
        verify_csrf();
        $this->rejectSpam('/wycofaj-zgody');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
        $phone = trim((string) ($_POST['phone'] ?? ''));

        $contact = MarketingContact::findForConsentLookup($email, $phone);
        if (!$contact || empty($contact['unsubscribe_token'])) {
            redirect('/wycofaj-zgody?status=nie_znaleziono');
        }

        redirect('/rezygnacja/' . rawurlencode($contact['unsubscribe_token']));
    }

    public function unsubscribe(array $params): string
    {
        $contact = MarketingContact::findByUnsubscribeToken($params['token'] ?? '');
        return View::render('public/unsubscribe', [
            'title' => 'Zarządzanie zgodami - Integracja Studencka',
            'metaDescription' => 'Zarządzanie zgodą na powiadomienia email i SMS.',
            'canonicalPath' => '/rezygnacja',
            'contact' => $contact,
            'token' => $params['token'] ?? '',
            'status' => $_GET['status'] ?? '',
        ]);
    }

    public function unsubscribeUpdate(array $params): string
    {
        verify_csrf();
        $this->rejectSpam('/wycofaj-zgody');
        $token = $params['token'] ?? '';
        $ok = MarketingContact::updateConsentsByToken($token, !empty($_POST['email_consent']), !empty($_POST['sms_consent']));
        redirect('/rezygnacja/' . rawurlencode($token) . '?status=' . ($ok ? 'ok' : 'blad'));
    }

    public function event(array $params): string
    {
        $event = Event::findPublishedBySlug($params['slug']);
        if (!$event) {
            http_response_code(404);
            return View::render('public/404', ['title' => 'Nie znaleziono wydarzenia']);
        }
        EventAnalytics::trackView((int) $event['id']);
        $eventState = event_sales_state($event);

        $eventStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event['title'],
            'description' => $event['seo_description'] ?: $event['short_description'] ?: $event['description'],
            'image' => $event['hero_image'] ?: '/assets/images/hero-students-party.png',
            'startDate' => date('c', strtotime($event['starts_at'])),
            'endDate' => date('c', $eventState['ends_at']),
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => $eventState['finished'] ? 'https://schema.org/EventCompleted' : 'https://schema.org/EventScheduled',
            'location' => [
                '@type' => 'Place',
                'name' => $event['venue_name'] ?: $event['city'],
                'address' => trim(($event['venue_address'] ?: '') . ' ' . $event['city']),
            ],
            'organizer' => [
                '@type' => 'Organization',
                'name' => 'Integracja Studencka',
            ],
            'inLanguage' => 'pl-PL',
        ];
        if ($event['ticket_url']) {
            $eventStructuredData['offers'] = [
                '@type' => 'Offer',
                'url' => $event['ticket_url'],
                'availability' => $eventState['sold_out'] || $eventState['finished'] ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
            ];
        }

        $videos = EventVideo::forEvent((int) $event['id']);

        return View::render('public/event', [
            'title' => $event['seo_title'] ?: $event['title'],
            'metaDescription' => $event['seo_description'] ?: $event['short_description'],
            'metaImage' => $event['hero_image'] ?: '/assets/images/hero-students-party.png',
            'metaType' => 'article',
            'canonicalPath' => '/wydarzenia/' . $event['slug'],
            'structuredData' => $eventStructuredData,
            'event' => $event,
            'clubs' => EventClub::forEvent((int) $event['id']),
            'videos' => $videos ?: EventVideo::DEFAULT_VIDEOS,
            'partners' => Partner::forEvent((int) $event['id']),
            'relatedEvents' => Event::relatedByCity($event['city'], (int) $event['id']),
            'eventCityPath' => ($citySlug = $this->academicCitySlug($event['city'] ?? '')) ? '/miasta/' . $citySlug : '/miasta',
        ]);
    }

    public function trackEventClick(): string
    {
        $eventId = (int) ($_POST['event_id'] ?? 0);
        $action = trim((string) ($_POST['action'] ?? ''));
        $targetUrl = trim((string) ($_POST['target_url'] ?? ''));

        EventAnalytics::trackClick($eventId, $action, $targetUrl);
        header('Content-Type: application/json; charset=UTF-8');
        return json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    }

    public function marketingSignup(): string
    {
        verify_csrf();
        $this->rejectSpam('/?zapis=ok#powiadomienia');
        $emailConsent = !empty($_POST['email_consent']);
        $smsConsent = !empty($_POST['sms_consent']);
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $phone = trim($_POST['phone'] ?? '');

        $redirectTo = $_POST['redirect_to'] ?? '/';
        if (!is_string($redirectTo) || !str_starts_with($redirectTo, '/') || str_starts_with($redirectTo, '//')) {
            $redirectTo = '/';
        }

        if ((!$emailConsent && !$smsConsent) || ($emailConsent && !$email) || ($smsConsent && $phone === '')) {
            redirect($redirectTo . (str_contains($redirectTo, '?') ? '&' : '?') . 'zapis=blad#powiadomienia');
        }

        MarketingContact::create([
            'name' => trim($_POST['name'] ?? ''),
            'email' => $email ?: '',
            'phone' => $phone,
            'city' => trim($_POST['city'] ?? ''),
            'source' => 'powiadomienia_www',
            'tags' => 'powiadomienia,' . trim($_POST['city'] ?? ''),
            'sms_consent' => $smsConsent,
            'email_consent' => $emailConsent,
            'marketing_consent' => true,
            'consent_text' => 'Zgoda na otrzymywanie informacji marketingowych i powiadomien o wydarzeniach studenckich wybranymi kanalami: email i/lub SMS.',
        ]);

        redirect($redirectTo . (str_contains($redirectTo, '?') ? '&' : '?') . 'zapis=ok#powiadomienia');
    }

    public function ticketWaitlistSignup(): string
    {
        verify_csrf();
        $this->rejectSpam('/?bilety=ok');

        $eventId = (int) ($_POST['event_id'] ?? 0);
        $event = $eventId ? Event::find($eventId) : null;
        if (!$event || $event['status'] !== 'published') {
            redirect('/?bilety=blad');
        }

        $eventUrl = '/wydarzenia/' . $event['slug'];
        $eventState = event_sales_state($event);
        if ($eventState['can_buy']) {
            redirect($eventUrl . '?bilety=sa#bilety');
        }

        $name = trim($_POST['name'] ?? '');
        $emailConsent = !empty($_POST['email_consent']);
        $smsConsent = !empty($_POST['sms_consent']);
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $phone = trim($_POST['phone'] ?? '');

        if ($name === '' || (!$emailConsent && !$smsConsent) || ($emailConsent && !$email) || ($smsConsent && $phone === '')) {
            redirect($eventUrl . '?bilety=blad#bilety-wkrotce');
        }

        $city = trim($event['city'] ?? '');
        $contactId = MarketingContact::createOrMerge([
            'name' => $name,
            'email' => $email ?: '',
            'phone' => $phone,
            'city' => $city,
            'source' => 'powiadomienie_bilety',
            'tags' => 'powiadomienie-bilety,event-' . (int) $event['id'] . ',' . $city,
            'sms_consent' => $smsConsent,
            'email_consent' => $emailConsent,
            'marketing_consent' => true,
            'consent_text' => 'Zgoda na otrzymanie powiadomienia o dostepnosci biletow na wydarzenie oraz na komunikacje marketingowa wybranymi kanalami: email i/lub SMS.',
        ]);

        TicketWaitlist::createOrUpdate([
            'event_id' => (int) $event['id'],
            'marketing_contact_id' => $contactId,
            'name' => $name,
            'email' => $email ?: '',
            'phone' => $phone,
            'city' => $city,
            'email_consent' => $emailConsent,
            'sms_consent' => $smsConsent,
        ]);

        redirect($eventUrl . '?bilety=ok#bilety-wkrotce');
    }

    public function partnerContact(): string
    {
        verify_csrf();
        $this->rejectSpam('/?kontakt=ok#partnerzy');

        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $message = trim($_POST['message'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($name === '' || !$email || $message === '') {
            redirect('/?kontakt=blad#partnerzy');
        }

        $body = "Nowe zapytanie partnerskie z integracjastudencka.pl\n\n";
        $body .= "Imię i nazwisko: {$name}\n";
        $body .= "Firma/projekt: {$company}\n";
        $body .= "Email: {$email}\n";
        $body .= "Telefon: {$phone}\n\n";
        $body .= "Wiadomość:\n{$message}\n";

        Mailer::send(Mailer::contactRecipient(), 'Zapytanie partnerskie - Integracja Studencka', $body, [
            'reply_to' => $email,
        ]);

        redirect('/?kontakt=ok#partnerzy');
    }

    private function rejectSpam(string $redirectTo): void
    {
        foreach (['website', 'homepage', 'company_website'] as $field) {
            if (trim((string) ($_POST[$field] ?? '')) !== '') {
                redirect($redirectTo);
            }
        }
    }
}
