<?php
class Parser
{
    private $data = null;
    private $isExport = false;

    private $functionMap = [
        // System
        '__e' => ['name' => 'Core Event (gtm.js)', 'category' => 'System', 'desc' => 'Base event fired when the GTM container loads.'],
        '__paused' => ['name' => 'Paused Tag', 'category' => 'System', 'desc' => 'This tag is disabled in GTM and will not be executed.'],
        // Tags
        '__ua' => ['name' => 'Universal Analytics', 'category' => 'Tag', 'desc' => 'Classic Google Analytics (UA) tag type.'],
        '__ga4_event' => ['name' => 'GA4 Event', 'category' => 'Tag', 'desc' => 'Native Google Analytics 4 event tag.'],
        '__html' => ['name' => 'Custom HTML', 'category' => 'Tag', 'desc' => 'Custom JavaScript or HTML snippet.'],
        '__cl' => ['name' => 'Conversion Linker', 'category' => 'Tag', 'desc' => 'Stores the Google Click Identifier (GCLID) in first-party cookies for accurate conversion tracking.'],
        '__gclidw' => ['name' => 'GCLID Cookie Writer', 'category' => 'Tag', 'desc' => 'Writes the GCLID into a first-party cookie on the domain.'],
        '__googtag' => ['name' => 'Google Tag', 'category' => 'Tag', 'desc' => 'The unified Google Tag (gtag.js) forwarding data to Google Ads and GA4.'],
        '__gaawe' => ['name' => 'GA4 Enhanced Measurement', 'category' => 'Tag', 'desc' => 'Google Analytics Enhanced Measurement event – e.g. automatically tracked scroll, click or video events.'],
        '__baut' => ['name' => 'Build Attribution Update Tag', 'category' => 'System', 'desc' => 'Internal GTM tag for updating attribution data in the container.'],
        '__awct' => ['name' => 'Google Ads Conversion Tracking', 'category' => 'Tag', 'desc' => 'Sends conversion data to Google Ads (formerly AdWords Conversion Tracking).'],
        '__pntr' => ['name' => 'Google Ads Remarketing', 'category' => 'Tag', 'desc' => 'Google Ads Remarketing Tag – sets a cookie for remarketing audiences in Google Ads.'],
        // Variables
        '__gas' => ['name' => 'Google Analytics Settings', 'category' => 'Variable', 'desc' => 'Contains configurations such as Tracking ID, Cookie Domain and Fields to Set.'],
        '__v' => ['name' => 'Data Layer Variable', 'category' => 'Variable', 'desc' => 'Reads values directly from the JavaScript window.dataLayer object.'],
        '__c' => ['name' => 'Constant', 'category' => 'Variable', 'desc' => 'A fixed, static value.'],
        '__u' => ['name' => 'URL', 'category' => 'Variable', 'desc' => 'Extracts parts of the current URL (hostname, path, query parameters).'],
        '__f' => ['name' => 'HTTP Referrer', 'category' => 'Variable', 'desc' => 'The URL of the previous page (document referrer).'],
        '__cid' => ['name' => 'Client ID', 'category' => 'Variable', 'desc' => 'The unique identifier of the user for Google Analytics.'],
        '__j' => ['name' => 'JavaScript Variable', 'category' => 'Variable', 'desc' => 'Reads the value of a global JavaScript variable from the window object.'],
        '__jsm' => ['name' => 'Custom JavaScript', 'category' => 'Variable', 'desc' => 'Executes a custom JavaScript function and returns its return value.'],
        '__k' => ['name' => 'First-Party Cookie', 'category' => 'Variable', 'desc' => 'Reads the value of a first-party cookie from the browser.'],
        '__smm' => ['name' => 'Lookup Table', 'category' => 'Variable', 'desc' => 'Returns a defined output value depending on the input value (simple value table).'],
        '__dbg' => ['name' => 'Debug Mode', 'category' => 'Variable', 'desc' => 'Returns true when GTM is running in preview/debug mode.'],
        '__gtes' => ['name' => 'Event Settings Variable', 'category' => 'Variable', 'desc' => 'Configuration variable for event parameters passed to Google Analytics 4 or Google Ads.'],
        '__awec' => ['name' => 'Enhanced Conversions', 'category' => 'Variable', 'desc' => 'Contains user data (e.g. email, phone) for Enhanced Conversions in Google Ads.'],
        // Triggers
        '__lcl' => ['name' => 'Link Click Listener', 'category' => 'Trigger', 'desc' => 'Monitors clicks on hyperlinks.'],
        '__evl' => ['name' => 'Element Visibility Listener', 'category' => 'Trigger', 'desc' => 'Fires when a specific element becomes visible in the viewport.'],
        '__fsl' => ['name' => 'Form Submission Listener', 'category' => 'Trigger', 'desc' => 'Monitors HTML form submissions.'],
        '__jel' => ['name' => 'JavaScript Error Listener', 'category' => 'Trigger', 'desc' => 'Captures JavaScript errors on the page.'],
        '__tl' => ['name' => 'Timer', 'category' => 'Trigger', 'desc' => 'Fires at defined time intervals.'],
        '__ytl' => ['name' => 'YouTube Video Trigger', 'category' => 'Trigger', 'desc' => 'Captures interactions with embedded YouTube videos.'],
        '__sp' => ['name' => 'Scroll Depth Listener', 'category' => 'Trigger', 'desc' => 'Fires when users reach a defined scroll depth (% or px).'],
        '__sdl' => ['name' => 'Scroll Depth Listener', 'category' => 'Trigger', 'desc' => 'Fires when users reach a defined scroll depth (% or px).'],
        '__tg' => ['name' => 'Trigger Group', 'category' => 'Trigger', 'desc' => 'Combines multiple triggers with AND logic – all must fire before the tag executes.'],
        '__rem' => ['name' => 'Regex Table', 'category' => 'Variable', 'desc' => 'Returns a defined output value based on a regex match of the input value.'],
        '__aev' => ['name' => 'Auto-Event Variable', 'category' => 'Variable', 'desc' => 'Reads properties of the element that triggered an auto-event (click, form, scroll).'],
        '__ctv' => ['name' => 'Container Version Number', 'category' => 'Variable', 'desc' => 'Returns the current GTM container version number.'],
        '__d' => ['name' => 'DOM Element', 'category' => 'Variable', 'desc' => 'Reads the text content or attribute value of a DOM element via CSS selector or element ID.'],
        '__hid' => ['name' => 'History ID', 'category' => 'Variable', 'desc' => 'Returns a unique counter that increments with each History Change event (SPA navigation).'],
        '__r' => ['name' => 'Random Number', 'category' => 'Variable', 'desc' => 'Generates a random integer on each call (e.g. for cache-busting or sampling).'],
        '__remm' => ['name' => 'Regex Table', 'category' => 'Variable', 'desc' => 'Like Regex Table, but allows multiple simultaneous matches.'],
        '__flc' => ['name' => 'Floodlight Counter', 'category' => 'Tag', 'desc' => 'Google Campaign Manager 360 Floodlight Counter Tag.'],
        '__hl' => ['name' => 'HTML Listener', 'category' => 'System', 'desc' => 'Internal GTM listener tag that monitors DOM events for auto-event triggers.'],
    ];

    private function getMappedInfo($func)
    {
        if (isset($this->functionMap[$func])) {
            return $this->functionMap[$func];
        }
        // Normalize without leading underscores and try again
        $nameWithoutUnderscores = ltrim($func, '_');
        if (isset($this->functionMap['__' . $nameWithoutUnderscores])) {
            return $this->functionMap['__' . $nameWithoutUnderscores];
        }
        // Custom Template Tags: __cvt_XXXXX
        if (strpos($func, '__cvt_') === 0) {
            $templateId = substr($func, 6); // strip '__cvt_'
            return ['name' => 'Custom Template Tag', 'category' => 'Tag', 'desc' => 'Ein benutzerdefiniertes Community-Template Tag (Template-ID: ' . $templateId . ').'];
        }
        return ['name' => $func, 'category' => 'Unbekannt', 'desc' => 'Unbekannte Funktions-Kennung im GTM-Container.'];
    }

    private function extractDomains($str)
    {
        if (empty($str))
            return [];

        $multiLevelTlds = 'co\.uk|org\.uk|me\.uk|ac\.uk|com\.au|net\.au|org\.au|co\.nz|net\.nz|org\.nz|co\.jp|or\.jp|ne\.jp|co\.kr|or\.kr|com\.br|net\.br|org\.br|co\.in|net\.in|org\.in|co\.za|com\.mx|co\.id|or\.id|com\.tr|org\.tr|com\.pl|net\.pl|co\.il|com\.ar|com\.sg|org\.sg|com\.hk|co\.th';
        $knownTlds = 'com|de|org|net|io|co|uk|at|ch|fr|nl|be|it|es|pl|cz|sk|hu|ro|se|no|dk|fi|eu|info|biz|app|dev|cloud|agency|digital|online|shop|store';
        $blocklist = [
            'www.w3.org',
            'schema.org',
            'localhost',
            'googletagmanager.com',
            'google-analytics.com',
            'google.com',
            'googleapis.com',
            'gstatic.com',
            'example.com'
        ];

        $hosts = [];

        // Strategy 1: Extract hostnames from any https:// or // URL
        // Unescape JSON-encoded slashes first (\/  => /)
        $clean = str_replace('\/', '/', $str);
        if (preg_match_all('#https?://([a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,})#i', $clean, $m)) {
            foreach ($m[1] as $h)
                $hosts[] = strtolower($h);
        }
        if (preg_match_all('#(?<![a-zA-Z0-9:])//([a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,})#i', $clean, $m)) {
            foreach ($m[1] as $h)
                $hosts[] = strtolower($h);
        }

        // Strategy 2a: Detect bare hostnames with multi-level TLDs first (e.g. "bitterliebe.co.uk")
        if (preg_match_all('/\b([a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9][a-zA-Z0-9\-]+)*\.(?:' . $multiLevelTlds . '))\b/i', $str, $m)) {
            foreach ($m[1] as $h)
                $hosts[] = strtolower($h);
        }

        // Strategy 2b: Detect bare hostnames with single-level TLDs (e.g. "foo.de", "booking.example.com")
        if (preg_match_all('/\b([a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9][a-zA-Z0-9\-]+)*\.(?:' . $knownTlds . '))\b/i', $str, $m)) {
            foreach ($m[1] as $h)
                $hosts[] = strtolower($h);
        }

        $hosts = array_values(array_unique(array_filter($hosts, function ($h) use ($blocklist) {
            return !in_array($h, $blocklist)
                && strpos($h, '.') !== false
                && !preg_match('/^\d+\.\d+\.\d+\.\d+$/', $h)
                && strlen($h) > 4;
        })));

        return $hosts;
    }

    private function hasMacroRef($arr, $mIdx, $mName)
    {
        if (!is_array($arr)) {
            if ($this->isExport && is_string($arr) && strpos($arr, '{{' . $mName . '}}') !== false)
                return true;
            return false;
        }
        if (!$this->isExport && isset($arr[0]) && $arr[0] === 'macro' && isset($arr[1]) && $arr[1] == $mIdx)
            return true;
        foreach ($arr as $v) {
            if ($this->hasMacroRef($v, $mIdx, $mName))
                return true;
        }
        return false;
    }

    public function parseFromId($gtmId)
    {
        $url = "https://www.googletagmanager.com/gtm.js?id=" . urlencode($gtmId);
        $context = stream_context_create([
            "http" => [
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n"
            ]
        ]);

        $js = @file_get_contents($url, false, $context);
        if (!$js) {
            throw new Exception("Fehler beim Laden des GTM Containers aus ID: " . htmlspecialchars($gtmId) . " - Bitte prüfen Sie die ID.");
        }

        $this->parseFromJs($js, $gtmId);
    }

    public function parseFromJs($js, $gtmId = null)
    {
        $parsed = false;
        $candidates = [];

        // Find "var data ="
        $pos = strpos($js, 'var data =');
        if ($pos !== false) {
            $candidates[] = $pos;
        }

        // Find "bootstrap"
        if ($gtmId) {
            $pos = strpos($js, '["' . $gtmId . '"].bootstrap');
            if ($pos !== false)
                $candidates[] = $pos;
        }
        $pos = strpos($js, '].bootstrap');
        if ($pos !== false) {
            $candidates[] = $pos;
        }

        foreach ($candidates as $offset) {
            $jsonStr = $this->extractJson($js, $offset);
            if ($jsonStr) {
                $this->data = json_decode($jsonStr, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $parsed = true;
                    break;
                }
            }
        }

        if (!$parsed) {
            throw new Exception("Die JSON-Struktur konnte im GTM Script nicht gefunden oder nicht geparst werden. Möglicherweise ist der Container leer oder im neuen Format.");
        }
        $this->isExport = false;
    }

    private function extractJson($string, $offset)
    {
        $startBrace = strpos($string, '{', $offset);
        $startBracket = strpos($string, '[', $offset);

        if ($startBrace === false && $startBracket === false)
            return null;

        if ($startBrace === false) {
            $start = $startBracket;
            $openChar = '[';
            $closeChar = ']';
        } elseif ($startBracket === false) {
            $start = $startBrace;
            $openChar = '{';
            $closeChar = '}';
        } else {
            $start = min($startBrace, $startBracket);
            $openChar = ($start === $startBrace) ? '{' : '[';
            $closeChar = ($start === $startBrace) ? '}' : ']';
        }

        $depth = 0;
        $inString = false;
        $escape = false;

        $len = strlen($string);
        // Limit scanning to avoid huge loops on malformed code
        $maxScan = min($start + 2000000, $len);

        for ($i = $start; $i < $maxScan; $i++) {
            $char = $string[$i];

            if ($escape) {
                $escape = false;
                continue;
            }
            if ($char === '\\') {
                $escape = true;
                continue;
            }
            if ($char === '"') {
                $inString = !$inString;
                continue;
            }
            if (!$inString) {
                if ($char === $openChar) {
                    $depth++;
                } elseif ($char === $closeChar) {
                    $depth--;
                    if ($depth === 0) {
                        return substr($string, $start, $i - $start + 1);
                    }
                }
            }
        }
        return null;
    }

    public function parseFromFile($filePath)
    {
        $json = @file_get_contents($filePath);
        if (!$json) {
            throw new Exception("Fehler beim Lesen der hochgeladenen Datei.");
        }
        $this->data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Ungültiges JSON-Format in der Datei. Bitte stellen Sie sicher, dass es ein gültiger GTM-Export ist.");
        }
        $this->isExport = true;
    }

    private function getNested($array, $keys, $default = [])
    {
        $curr = $array;
        foreach ($keys as $key) {
            if (is_array($curr) && isset($curr[$key])) {
                $curr = $curr[$key];
            } else {
                return $default;
            }
        }
        return $curr;
    }

    public function getTags()
    {
        if (!$this->data)
            return [];
        $tags = [];
        $typeCounters = [];

        $tagList = $this->isExport
            ? $this->getNested($this->data, ['containerVersion', 'tag'])
            : $this->getNested($this->data, ['resource', 'tags']);

        if (!empty($tagList)) {
            foreach ($tagList as $idx => $t) {
                $func = $this->isExport ? ($t['type'] ?? 'Unbekannt') : ($t['function'] ?? 'Unbekannt');
                $mapped = $this->getMappedInfo($func);

                // Skip GTM-internal listener tags (they serve as trigger infrastructure, not user tags)
                if (in_array($mapped['category'], ['Trigger', 'System'])) {
                    continue;
                }
                $html = '';
                if ($this->isExport) {
                    $params = $t['parameter'] ?? [];
                    foreach ($params as $p) {
                        if (($p['key'] ?? '') === 'html') {
                            $html = $p['value'] ?? '';
                            break;
                        }
                    }
                } else {
                    $vtpHtml = $t['vtp_html'] ?? '';
                    $html = is_string($vtpHtml) ? $vtpHtml : json_encode($vtpHtml);
                }

                // Increment per-type counter
                $typeCounters[$func] = ($typeCounters[$func] ?? 0) + 1;
                $typeNum = $typeCounters[$func];

                // Smart fallback name based on tag type
                $tagEventName = ''; // GA4 event name, if any
                if ($func === '__html' || $func === 'html') {
                    $displayName = 'HTML ' . $typeNum;
                } elseif ($mapped['category'] === 'Unbekannte Funktion' || strpos($mapped['name'], '(Unbekannte Funktion)') !== false) {
                    $displayName = $func . ' ' . $typeNum; // show raw key + counter
                } else {
                    $displayName = $mapped['name'] . ' ' . $typeNum;
                }
                $detectedProvider = $mapped['name'];
                $description = $mapped['desc'];

                if ($func === '__ua' || $func === 'ua') {
                    $trackType = $t['vtp_trackType'] ?? '';
                    if ($this->isExport) {
                        foreach ((array) ($t['parameter'] ?? []) as $p) {
                            if (($p['key'] ?? '') === 'trackType')
                                $trackType = $p['value'] ?? '';
                        }
                    }
                    if ($trackType === 'TRACK_PAGEVIEW') {
                        $displayName = 'UA Pageview';
                    } elseif ($trackType === 'TRACK_EVENT') {
                        $displayName = 'UA Event';
                    }
                } elseif ($func === '__ga4_event' || $func === 'ga4a') {
                    $eventName = $t['vtp_eventName'] ?? '';
                    if ($this->isExport) {
                        foreach ((array) ($t['parameter'] ?? []) as $p) {
                            if (($p['key'] ?? '') === 'eventName')
                                $eventName = $p['value'] ?? '';
                        }
                    }
                    if (is_string($eventName) && trim($eventName) !== '') {
                        $tagEventName = trim($eventName);
                        $displayName = 'GA4 Event: ' . $tagEventName;
                    }
                } elseif ($func === '__gaawe') {
                    // GA4 Enhanced Measurement — extract the event name
                    $eventName = $t['vtp_eventName'] ?? '';
                    if ($this->isExport) {
                        foreach ((array) ($t['parameter'] ?? []) as $p) {
                            if (($p['key'] ?? '') === 'eventName')
                                $eventName = $p['value'] ?? '';
                        }
                    }
                    if ($eventName && is_string($eventName) && trim($eventName) !== '') {
                        $tagEventName = trim($eventName);
                        $displayName = 'GA4 ' . $tagEventName . ' ' . $typeNum;
                    }
                } elseif ($func === '__html' || $func === 'html') {
                    $customService = '';
                    if (strpos($html, 'fbevents.js') !== false || strpos($html, 'fbq(') !== false) {
                        $customService = 'Facebook Pixel';
                    } elseif (strpos($html, 'googletraveladservices.com') !== false) {
                        $customService = 'Google Travel Ads';
                    } elseif (strpos($html, 'hotelchamp.com') !== false) {
                        $customService = 'HotelChamp Widget';
                    } elseif (strpos($html, 'thehotelsnetwork.com') !== false) {
                        $customService = 'Hotels Network Loader';
                    } elseif (strpos($html, 'd-edgeconnect.media') !== false) {
                        $customService = 'D-Edge Tracker';
                    } elseif (strpos($html, 'mews.com') !== false) {
                        $customService = 'Mews Booking Integration';
                    } elseif (strpos($html, 'hotjar.com') !== false) {
                        $customService = 'Hotjar Tracking';
                    }

                    if ($customService && strpos($displayName, 'HTML ') === 0) {
                        $displayName = $customService . ' (HTML ' . $typeNum . ')';
                    }
                }

                if (!empty($t['name'])) {
                    $displayName = $t['name'];
                }

                $technicalSummary = [];
                $jsonStr = json_encode($t);
                if (preg_match_all('/(UA-\d+-\d+|G-[A-Z0-9]+|AW-\d+)/', $jsonStr, $matches)) {
                    $technicalSummary[] = "Account ID(s): " . implode(', ', array_unique($matches[1]));
                }

                $hosts = $this->extractDomains($html);

                $triggers = [];
                $blockingTriggers = [];
                if ($this->isExport) {
                    if (!empty($t['firingTriggerId'])) {
                        foreach ((array) $t['firingTriggerId'] as $tid) {
                            $triggers[] = $this->getTriggerNameById($tid);
                        }
                    }
                    if (!empty($t['blockingTriggerId'])) {
                        foreach ((array) $t['blockingTriggerId'] as $tid) {
                            $blockingTriggers[] = $this->getTriggerNameById($tid);
                        }
                    }
                } else {
                    $rules = $this->getNested($this->data, ['resource', 'rules'], []);
                    foreach ($rules as $rIdx => $r) {
                        foreach ($r as $action) {
                            if (!is_array($action)) continue;
                            $actionType = $action[0] ?? '';
                            if ($actionType === 'add' && in_array($idx, array_slice($action, 1))) {
                                $name = $this->getTriggerNameByIdFromRules($rIdx);
                                if ($name !== null) {
                                    $triggers[] = 'trigger_' . $rIdx;
                                }
                            } elseif ($actionType === 'block' && in_array($idx, array_slice($action, 1))) {
                                $name = $this->getTriggerNameByIdFromRules($rIdx);
                                if ($name !== null) {
                                    $blockingTriggers[] = 'trigger_' . $rIdx;
                                }
                            }
                        }
                    }
                }

                // Extract consent requirements (Built-in Consent Check)
                $consentRequired = [];
                $rawConsent = $t['consent'] ?? null;
                if ($this->isExport) {
                    // Export format: consentSettings.consentStatus.consentType / generalConsentType
                    $cs = $t['consentSettings'] ?? null;
                    if (is_array($cs)) {
                        foreach (($cs['consentStatus'] ?? []) as $entry) {
                            $ct = $entry['consentType'] ?? null;
                            if (is_string($ct) && $ct !== '') $consentRequired[] = $ct;
                        }
                    }
                } elseif (is_array($rawConsent)) {
                    // Runtime format: ["list", "ad_storage", "analytics_storage"]
                    $entries = $rawConsent;
                    if (($entries[0] ?? '') === 'list') $entries = array_slice($entries, 1);
                    foreach ($entries as $ct) {
                        if (is_string($ct) && $ct !== '') $consentRequired[] = $ct;
                    }
                }

                // Detect Consent Initialization tags (default consent state setters)
                $consentDefaults = [];
                $consentTypes = ['ad_storage', 'analytics_storage', 'ad_user_data', 'ad_personalization',
                                 'functionality_storage', 'personalization_storage', 'security_storage'];
                foreach ($consentTypes as $ct) {
                    $key = $this->isExport ? $ct : 'vtp_' . $ct;
                    $val = $this->isExport ? null : ($t[$key] ?? null);
                    if ($this->isExport) {
                        foreach ((array)($t['parameter'] ?? []) as $p) {
                            if (($p['key'] ?? '') === $ct) $val = $p['value'] ?? null;
                        }
                    }
                    if (is_string($val) && $val !== '') {
                        $consentDefaults[$ct] = $val;
                    }
                }

                $tags[] = [
                    'id' => 'tag_' . $idx,
                    'display_name' => $displayName,
                    'detected_provider' => $detectedProvider,
                    'technical_summary' => empty($technicalSummary) ? '-' : implode(' | ', $technicalSummary),
                    'external_scripts' => $hosts,
                    'domains' => $this->extractDomains($jsonStr),
                    'triggers' => $triggers,
                    'raw_tech' => $technicalSummary,
                    'html_content' => $html,
                    'description' => $description,
                    'event_name' => $tagEventName,
                    'consent_required' => $consentRequired,
                    'consent_defaults' => $consentDefaults,
                    'blocking_triggers' => $blockingTriggers,
                ];
            }
        }
        return $tags;
    }

    public function getTriggers()
    {
        if (!$this->data)
            return [];
        $triggers = [];

        if ($this->isExport) {
            $triggerList = $this->getNested($this->data, ['containerVersion', 'trigger']);
            if (!empty($triggerList)) {
                foreach ($triggerList as $idx => $tr) {
                    $filters = [];
                    if (isset($tr['filter']) && is_array($tr['filter'])) {
                        foreach ($tr['filter'] as $f) {
                            $filters[] = $f['type'] ?? 'Filter';
                        }
                    }
                    $func = $tr['type'] ?? 'Unbekannt';
                    $mapped = $this->getMappedInfo($func);

                    $triggers[] = [
                        'id' => 'trigger_export_' . $idx,
                        'display_name' => $tr['name'] ?? 'Trigger ' . $idx,
                        'detected_provider' => $mapped['name'],
                        'technical_summary' => empty($filters) ? 'Alle Events' : 'Filter: ' . implode(', ', $filters),
                        'conditions' => $filters,
                        'domains' => $this->extractDomains(json_encode($tr)),
                        'description' => $mapped['desc']
                    ];
                }
            }
        } else {
            $rules = $this->getNested($this->data, ['resource', 'rules'], []);
            $predicates = $this->getNested($this->data, ['resource', 'predicates'], []);
            $typeCounters = [];

            foreach ($rules as $idx => $r) {
                $displayName = 'Regel ' . $idx;
                $detectedProvider = 'Regel Trigger';
                $conditions = [];
                $isLinkClick = false;
                $isElementVis = false;
                $isPageview = false;

                // Known GTM internal event → display name mapping
                $knownEvents = [
                    'gtm.js' => ['name' => 'All Pages', 'provider' => 'Page View', 'isPageview' => true],
                    'gtm.dom' => ['name' => 'DOM Ready', 'provider' => 'Page View', 'isPageview' => true],
                    'gtm.load' => ['name' => 'Window Loaded', 'provider' => 'Page View', 'isPageview' => true],
                    'gtm.click' => ['name' => 'Click - All Elements', 'provider' => 'Click - All Elements', 'isLinkClick' => true],
                    'gtm.linkClick' => ['name' => 'Click - Just Links', 'provider' => 'Click - Just Links', 'isLinkClick' => true],
                    'gtm.elementVisibility' => ['name' => 'Element Visibility', 'provider' => 'Element Visibility', 'isElementVis' => true],
                    'gtm.triggerGroup' => ['name' => 'Trigger Group', 'provider' => 'Trigger Group'],
                    'gtm.scrollDepth' => ['name' => 'Scroll Depth', 'provider' => 'Scroll Depth'],
                    'gtm.formSubmit' => ['name' => 'Form Submission', 'provider' => 'Form Submission'],
                    'gtm.historyChange' => ['name' => 'History Change', 'provider' => 'History Change'],
                    'gtm.timer' => ['name' => 'Timer', 'provider' => 'Timer'],
                ];

                foreach ($r as $cond) {
                    if (is_array($cond) && $cond[0] === 'if') {
                        foreach (array_slice($cond, 1) as $pId) {
                            $pred = $predicates[$pId] ?? null;
                            if (!$pred)
                                continue;
                            $pfunc = $pred['function'] ?? '';
                            $arg1 = $pred['arg1'] ?? '';
                            $arg0 = $pred['arg0'] ?? '';

                            // Only process comparison predicates
                            if (!in_array($pfunc, ['_eq', '_re', '_cn', '_sw', '_ne']))
                                continue;

                            $arg1Str = is_string($arg1) ? $arg1 : '';

                            // 1) Direct match on known GTM event names in arg1
                            if (isset($knownEvents[$arg1Str])) {
                                $ev = $knownEvents[$arg1Str];
                                $displayName = $ev['name'];
                                $detectedProvider = $ev['provider'] ?? $detectedProvider;
                                if (!empty($ev['isLinkClick']))
                                    $isLinkClick = true;
                                if (!empty($ev['isElementVis']))
                                    $isElementVis = true;
                                if (!empty($ev['isPageview']))
                                    $isPageview = true;
                                break 2; // name found, stop scanning predicates
                            }

                            // 2) One side is a macro reference (= a GTM variable) and
                            //    the other is a plain string → likely equals-event-name check
                            $isArg0Macro = is_array($arg0) && ($arg0[0] ?? '') === 'macro';
                            $isArg1Macro = is_array($arg1) && ($arg1[0] ?? '') === 'macro';

                            if ($isArg0Macro && $arg1Str !== '') {
                                // The variable's value is being compared to a string literal
                                // Most likely: Event == "some_custom_event"
                                if ($displayName === 'Regel ' . $idx) { // don't overwrite a better name
                                    $displayName = 'Event: ' . $arg1Str;
                                }
                                $conditions[] = 'Event=' . $arg1Str;
                            } elseif ($isArg1Macro && is_string($arg0) && $arg0 !== '') {
                                if ($displayName === 'Regel ' . $idx) {
                                    $displayName = 'Event: ' . $arg0;
                                }
                                $conditions[] = 'Event=' . $arg0;
                            } elseif ($arg1Str !== '') {
                                $conditions[] = $arg1Str;
                            }
                        }
                    }
                }

                if ($isLinkClick)
                    $detectedProvider = 'Link Click Trigger';
                elseif ($isElementVis)
                    $detectedProvider = 'Element Visibility Trigger';
                elseif ($isPageview)
                    $detectedProvider = 'Pageview Trigger';

                // Number the display name per type (e.g. "Pageview 1", "Pageview 2")
                $typeCounters[$displayName] = ($typeCounters[$displayName] ?? 0) + 1;
                $displayName .= ' ' . $typeCounters[$displayName];

                $triggers[] = [
                    'id' => 'trigger_' . $idx,
                    'display_name' => $displayName,
                    'detected_provider' => $detectedProvider,
                    'technical_summary' => empty($conditions) ? '-' : 'Bedingungen: ' . implode(', ', $conditions),
                    'conditions' => $conditions,
                    'domains' => $this->extractDomains(json_encode($r)),
                    'description' => 'Trigger-Regel basierend auf Event- und Variablen-Bedingungen.',
                    'firing_tags' => [],
                ];
            }

            // Invert tag→rule mapping to populate firing_tags per trigger
            // Only include user-visible tags (skip system/listener internal tags)
            $tagList = $this->getNested($this->data, ['resource', 'tags'], []);
            foreach ($tagList as $tIdx => $tagData) {
                $tagFunc = $tagData['function'] ?? '';
                $tagMapped = $this->getMappedInfo($tagFunc);
                // Skip GTM-internal listener/system tags
                if (in_array($tagMapped['category'], ['System', 'Trigger'])) {
                    continue;
                }
                foreach ($rules as $rIdx => $rule) {
                    foreach ($rule as $action) {
                        if (is_array($action) && $action[0] === 'add' && in_array($tIdx, array_slice($action, 1))) {
                            if (isset($triggers[$rIdx])) {
                                $tagId = 'tag_' . $tIdx; // store ID, not vtp_name — frontend resolves display_name
                                if (!in_array($tagId, $triggers[$rIdx]['firing_tags'])) {
                                    $triggers[$rIdx]['firing_tags'][] = $tagId;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $triggers;
    }

    public function getVariables()
    {
        if (!$this->data)
            return [];
        $variables = [];

        $varItems = $this->isExport
            ? $this->getNested($this->data, ['containerVersion', 'variable'], [])
            : $this->getNested($this->data, ['resource', 'macros'], []);

        $typeCounters = [];

        foreach ($varItems as $idx => $v) {
            $func = $this->isExport ? ($v['type'] ?? 'Unbekannt') : ($v['function'] ?? 'Unbekannt');

            $mapped = $this->getMappedInfo($func);

            // Per-type counter for the fallback name (like tags: "Lookup Table 1", "Custom JavaScript 2")
            $typeCounters[$func] = ($typeCounters[$func] ?? 0) + 1;
            $typeNum = $typeCounters[$func];
            $typeName = $mapped['name'];

            // Short type label used when there's no explicit name
            // Strip trailing "Variable" word to keep it concise (e.g. "Data Layer Variable 3" → "Data Layer 3")
            $shortType = preg_replace('/\s*Variable\s*$/i', '', $typeName);
            $fallbackName = $shortType . ' ' . $typeNum;

            $name = $this->isExport
                ? ($v['name'] ?? $fallbackName)
                : ($v['vtp_name'] ?? $fallbackName);

            $detectedProvider = $mapped['name'];
            $summary = [];
            $value = '';
            $lookupRows = [];
            $inputLabel = '';
            $gasDetails = [];
            $jsCode = '';

            // Normalize parameters regardless of source format
            $p = [];
            if ($this->isExport && isset($v['parameter'])) {
                foreach ($v['parameter'] as $param) {
                    $p[$param['key']] = ($param['type'] === 'LIST') ? ($param['list'] ?? []) : ($param['value'] ?? '');
                }
            } else {
                foreach ($v as $k => $val) {
                    if (strpos($k, 'vtp_') === 0) {
                        $p[substr($k, 4)] = $val;
                    } else {
                        $p[$k] = $val;
                    }
                }
            }

            // Universal parsing based on normalized parameters $p
            if ($func === '__c' || $func === 'c') {
                $detectedProvider = 'Konstante';
                $val = $p['value'] ?? '';
                $value = is_string($val) ? $val : json_encode($val);
                $summary[] = "Wert: " . $value;
            } elseif ($func === '__v' || $func === 'v') {
                $detectedProvider = 'Data Layer Variable';
                $val = $p['name'] ?? '';
                $nameValue = is_string($val) ? $val : json_encode($val);
                $summary[] = "Key: " . $nameValue;
                if (strpos($nameValue, 'gtm.') === 0) {
                    $detectedProvider = 'Integrierte GTM Variable (Auto-generiert)';
                }
                $value = $nameValue;
            } elseif ($func === '__gas') {
                $gasDetails = [];
                if (isset($p['trackingId'])) {
                    $tid = $p['trackingId'];
                    $value = is_string($tid) ? $tid : json_encode($tid);
                    $summary[] = "Tracking ID: " . $value;
                    $gasDetails['tracking_id'] = $value;
                }
                if (isset($p['cookieDomain'])) {
                    $dom = $p['cookieDomain'];
                    $domStr = is_string($dom) ? $dom : json_encode($dom);
                    $summary[] = "Cookie Domain: " . $domStr;
                    $gasDetails['cookie_domain'] = $domStr;
                }
                if (isset($p['transportUrl']) && is_string($p['transportUrl']) && trim($p['transportUrl']) !== '') {
                    $gasDetails['transport_url'] = trim($p['transportUrl']);
                }

                // Fields to Set
                $fieldsToSet = [];
                $rawFields = $p['fieldsToSet'] ?? [];
                if (is_array($rawFields)) {
                    if ($this->isExport) {
                        foreach ($rawFields as $entry) {
                            if (!isset($entry['type']) || $entry['type'] !== 'MAP') continue;
                            $fn = ''; $fv = '';
                            foreach (($entry['map'] ?? []) as $kv) {
                                if (($kv['key'] ?? '') === 'fieldName') $fn = $kv['value'] ?? '';
                                if (($kv['key'] ?? '') === 'value') $fv = $kv['value'] ?? '';
                            }
                            if ($fn !== '') $fieldsToSet[] = ['field' => $fn, 'value' => $fv];
                        }
                    } else {
                        $entries = $rawFields;
                        if (($entries[0] ?? '') === 'list') $entries = array_slice($entries, 1);
                        foreach ($entries as $entry) {
                            if (!is_array($entry) || ($entry[0] ?? '') !== 'map') continue;
                            $fn = null; $fv = null;
                            for ($i = 1; $i < count($entry) - 1; $i += 2) {
                                if ($entry[$i] === 'fieldName') $fn = $entry[$i + 1] ?? null;
                                if ($entry[$i] === 'value') $fv = $entry[$i + 1] ?? null;
                            }
                            if ($fn !== null) {
                                $fvStr = is_array($fv) ? (($fv[0] ?? '') === 'macro' ? '{{Macro ' . ($fv[1] ?? '?') . '}}' : json_encode($fv)) : (string)($fv ?? '');
                                $fieldsToSet[] = ['field' => $fn, 'value' => $fvStr];
                            }
                        }
                    }
                }
                if (!empty($fieldsToSet)) $gasDetails['fields_to_set'] = $fieldsToSet;

                // Custom Dimensions
                $gasDimensions = [];
                $rawDims = $p['dimension'] ?? [];
                if (is_array($rawDims)) {
                    if ($this->isExport) {
                        foreach ($rawDims as $entry) {
                            if (!isset($entry['type']) || $entry['type'] !== 'MAP') continue;
                            $di = ''; $dv = '';
                            foreach (($entry['map'] ?? []) as $kv) {
                                if (($kv['key'] ?? '') === 'index') $di = $kv['value'] ?? '';
                                if (($kv['key'] ?? '') === 'dimension') $dv = $kv['value'] ?? '';
                            }
                            if ($di !== '') $gasDimensions[] = ['index' => $di, 'value' => $dv];
                        }
                    } else {
                        $entries = $rawDims;
                        if (($entries[0] ?? '') === 'list') $entries = array_slice($entries, 1);
                        foreach ($entries as $entry) {
                            if (!is_array($entry) || ($entry[0] ?? '') !== 'map') continue;
                            $di = null; $dv = null;
                            for ($i = 1; $i < count($entry) - 1; $i += 2) {
                                if ($entry[$i] === 'index') $di = $entry[$i + 1] ?? null;
                                if ($entry[$i] === 'dimension') $dv = $entry[$i + 1] ?? null;
                            }
                            if ($di !== null) {
                                $dvStr = is_array($dv) ? (($dv[0] ?? '') === 'macro' ? '{{Macro ' . ($dv[1] ?? '?') . '}}' : json_encode($dv)) : (string)($dv ?? '');
                                $gasDimensions[] = ['index' => (string)$di, 'value' => $dvStr];
                            }
                        }
                    }
                }
                if (!empty($gasDimensions)) $gasDetails['dimensions'] = $gasDimensions;

                // Custom Metrics
                $gasMetrics = [];
                $rawMetrics = $p['metric'] ?? [];
                if (is_array($rawMetrics)) {
                    $entries = $rawMetrics;
                    if (($entries[0] ?? '') === 'list') $entries = array_slice($entries, 1);
                    foreach ($entries as $entry) {
                        if (!is_array($entry) || ($entry[0] ?? '') !== 'map') continue;
                        $mi = null; $mv = null;
                        for ($i = 1; $i < count($entry) - 1; $i += 2) {
                            if ($entry[$i] === 'index') $mi = $entry[$i + 1] ?? null;
                            if ($entry[$i] === 'metric') $mv = $entry[$i + 1] ?? null;
                        }
                        if ($mi !== null) {
                            $mvStr = is_array($mv) ? (($mv[0] ?? '') === 'macro' ? '{{Macro ' . ($mv[1] ?? '?') . '}}' : json_encode($mv)) : (string)($mv ?? '');
                            $gasMetrics[] = ['index' => (string)$mi, 'value' => $mvStr];
                        }
                    }
                }
                if (!empty($gasMetrics)) $gasDetails['metrics'] = $gasMetrics;

                // Boolean flags
                $gasFlags = [];
                $flagKeys = [
                    'doubleClick' => 'Enable Display Advertising Features',
                    'useHashAutoLink' => 'Use # in Auto-Link',
                    'decorateFormsAutoLink' => 'Decorate Forms (Auto-Link)',
                    'enableLinkId' => 'Enable Enhanced Link Attribution',
                    'enableEcommerce' => 'Enable Ecommerce',
                    'enableRecaptchaOption' => 'Enable reCAPTCHA',
                    'enableUaRlsa' => 'Enable RLSA',
                    'setTrackerName' => 'Set Tracker Name',
                    'useDebugVersion' => 'Use Debug Version',
                ];
                foreach ($flagKeys as $fk => $label) {
                    if (isset($p[$fk])) {
                        $gasFlags[] = ['label' => $label, 'enabled' => !empty($p[$fk])];
                    }
                }
                if (!empty($gasFlags)) $gasDetails['flags'] = $gasFlags;

            } elseif ($func === '__u' || $func === '__f') {
                if (isset($p['component'])) {
                    $comp = $p['component'];
                    $value = is_string($comp) ? $comp : json_encode($comp);
                    $summary[] = "Komponente: " . $value;
                    if ($func === '__u' && trim($value) === 'URL') {
                        $value = 'Full URL';
                    }
                }
            } elseif ($func === '__cid') {
                $summary[] = "Typ: Client ID";
                $value = "Stellt die Google Analytics Client ID bereit";
            } elseif ($func === '__smm' || $func === '__rem') {
                // Lookup / Regex Table
                if (isset($p['input'])) {
                    $inp = $p['input'];
                    if (is_array($inp) && ($inp[0] ?? '') === 'macro') {
                        $inputLabel = '{{Macro ' . ($inp[1] ?? '?') . '}}';
                    } elseif (is_array($inp) && ($inp['type'] ?? '') === 'TEMPLATE') {
                        $inputLabel = $inp['value'] ?? '?';
                    } else {
                        $inputLabel = is_string($inp) ? $inp : json_encode($inp);
                    }
                }

                $mapData = $p['map'] ?? [];
                if (!empty($mapData) && is_array($mapData)) {
                    // Export JSON format (list of maps)
                    if ($this->isExport) {
                        foreach ($mapData as $entry) {
                            if (!isset($entry['type']) || $entry['type'] !== 'MAP')
                                continue;
                            $k = '';
                            $v_val = '';
                            foreach (($entry['map'] ?? []) as $kv) {
                                if ($kv['key'] === 'key')
                                    $k = $kv['value'] ?? '';
                                if ($kv['key'] === 'value')
                                    $v_val = $kv['value'] ?? '';
                            }
                            if ($k !== '')
                                $lookupRows[] = ['key' => $k, 'value' => $v_val];
                        }
                    } else {
                        // Runtime format ["list", ["map", "key", K, "value", V], ...]
                        $mapArr = $mapData;
                        if (($mapArr[0] ?? '') === 'list')
                            array_shift($mapArr);
                        foreach ($mapArr as $entry) {
                            if (!is_array($entry))
                                continue;
                            $k = null;
                            $v_val = null;
                            for ($i = 1; $i < count($entry) - 1; $i += 2) {
                                if ($entry[$i] === 'key')
                                    $k = is_array($entry[$i + 1]) ? json_encode($entry[$i + 1]) : (string) $entry[$i + 1];
                                if ($entry[$i] === 'value')
                                    $v_val = is_array($entry[$i + 1]) ? json_encode($entry[$i + 1]) : (string) $entry[$i + 1];
                            }
                            if ($k !== null)
                                $lookupRows[] = ['key' => $k, 'value' => $v_val ?? ''];
                        }
                    }
                }

                if ($inputLabel)
                    $summary[] = 'Eingabe: ' . $inputLabel;
                if (!empty($lookupRows)) {
                    $shortRows = array_map(fn($r) => $r['key'] . ' → ' . $r['value'], array_slice($lookupRows, 0, 3));
                    $value = implode(', ', $shortRows) . (count($lookupRows) > 3 ? ' …' : '');
                }
            } elseif ($func === '__jsm') {
                $js = $p['javascript'] ?? '';
                $jsStr = is_string($js) ? $js : json_encode($js);
                $jsCode = $jsStr;
                $preview = trim(preg_replace('/\s+/', ' ', strip_tags($jsStr)));
                $value = mb_strlen($preview) > 80 ? mb_substr($preview, 0, 80) . '…' : $preview;
                $summary[] = 'JS: ' . $value;
            } elseif ($func === '__j') {
                $jName = $p['name'] ?? '';
                $value = 'window.' . (is_string($jName) ? $jName : json_encode($jName));
                $summary[] = 'Variable: ' . $value;
            } elseif ($func === '__k') {
                $cName = $p['name'] ?? '';
                $value = is_string($cName) ? $cName : json_encode($cName);
                $summary[] = 'Cookie: ' . $value;
            }

            $varEntry = [
                'display_name' => $name,
                'detected_provider' => $detectedProvider,
                'technical_summary' => empty($summary) ? '-' : implode(' | ', $summary),
                'value' => $value,
                'description' => $mapped['desc'],
                'lookup_input' => $inputLabel,
                'lookup_rows' => $lookupRows,
                'domains' => $this->extractDomains(json_encode($v)),
            ];
            if (!empty($gasDetails)) $varEntry['ga_settings'] = $gasDetails;
            if (!empty($jsCode)) $varEntry['js_code'] = $jsCode;
            $variables[] = $varEntry;
        }

        $tags = clone (object) $this;
        $allTags = $tags->getTags();
        $triggers = clone (object) $this;
        $allTriggers = $triggers->getTriggers();

        // Build rawIdx → tag map: getTags() skips system/trigger tags via 'continue',
        // so $allTags is densely re-indexed and $allTags[$rawIdx] would be wrong.
        // We use the embedded 'id' field (e.g. 'tag_5') to recover the original raw index.
        $allTagsByRawIdx = [];
        foreach ($allTags as $tagObj) {
            if (preg_match('/^tag_(\d+)$/', $tagObj['id'] ?? '', $m)) {
                $allTagsByRawIdx[(int) $m[1]] = $tagObj;
            }
        }

        $tagsData = $this->isExport ? $this->getNested($this->data, ['containerVersion', 'tag'], []) : $this->getNested($this->data, ['resource', 'tags'], []);
        $triggersData = $this->isExport ? $this->getNested($this->data, ['containerVersion', 'trigger'], []) : $this->getNested($this->data, ['resource', 'rules'], []);
        $macrosData = $this->isExport ? $this->getNested($this->data, ['containerVersion', 'variable'], []) : $this->getNested($this->data, ['resource', 'macros'], []);
        $predicates = $this->getNested($this->data, ['resource', 'predicates'], []);

        foreach ($variables as $mIdx => &$varItem) {
            $mName = $varItem['display_name'];
            $usedInTags = [];
            $usedInTriggers = [];
            $usedInVars = [];

            $tagParamUsage = []; // tagId => 'parameter label'
            foreach ($tagsData as $tIdx => $tItem) {
                if ($this->hasMacroRef($tItem, $mIdx, $mName)) {
                    $tagId = $allTagsByRawIdx[$tIdx]['id'] ?? null;
                    if ($tagId === null)
                        continue; // system/trigger tag — skip
                    $usedInTags[] = $tagId;
                    $context = $this->findMacroUsageContext($tItem, $mIdx, $mName);
                    if ($context !== '') {
                        $tagParamUsage[$tagId] = $context;
                    }
                }
            }

            foreach ($triggersData as $trIdx => $trItem) {
                if ($this->isExport) {
                    if ($this->hasMacroRef($trItem, $mIdx, $mName)) {
                        $usedInTriggers[] = $allTriggers[$trIdx]['id'] ?? ('trigger_export_' . $trIdx);
                    }
                } else {
                    $ruleUsesMacro = false;
                    foreach ($trItem as $action) {
                        if (is_array($action) && in_array($action[0], ['if', 'unless'])) {
                            foreach (array_slice($action, 1) as $pIdx) {
                                $pred = $predicates[$pIdx] ?? null;
                                if ($this->hasMacroRef($pred, $mIdx, $mName)) {
                                    $ruleUsesMacro = true;
                                    break 2;
                                }
                            }
                        }
                    }
                    if ($ruleUsesMacro) {
                        // Store the trigger's unique id — not just display_name which may be duplicate
                        $usedInTriggers[] = $allTriggers[$trIdx]['id'] ?? ('trigger_' . $trIdx);
                    }
                }
            }

            foreach ($macrosData as $oIdx => $oItem) {
                if ($oIdx === $mIdx)
                    continue;
                if ($this->hasMacroRef($oItem, $mIdx, $mName)) {
                    $usedInVars[] = $variables[$oIdx]['display_name'] ?? ('Variable ' . $oIdx);
                }
            }

            $varItem['used_in_tags'] = $usedInTags;
            $varItem['used_in_triggers'] = $usedInTriggers;
            $varItem['used_in_variables'] = $usedInVars;
            $varItem['tag_param_usage'] = $tagParamUsage;
            $varItem['usage_count'] = count($usedInTags) + count($usedInTriggers) + count($usedInVars);

            // Resolve {{Macro N}} references in ga_settings to variable display names
            if (!empty($varItem['ga_settings'])) {
                $resolveMacroRefs = function ($str) use ($variables) {
                    return preg_replace_callback('/\{\{Macro (\d+)\}\}/', function ($m) use ($variables) {
                        $idx = (int)$m[1];
                        return '{{' . ($variables[$idx]['display_name'] ?? 'Macro ' . $idx) . '}}';
                    }, $str);
                };
                foreach (['fields_to_set', 'dimensions', 'metrics'] as $listKey) {
                    if (isset($varItem['ga_settings'][$listKey])) {
                        foreach ($varItem['ga_settings'][$listKey] as &$entry) {
                            if (isset($entry['value']))
                                $entry['value'] = $resolveMacroRefs($entry['value']);
                        }
                        unset($entry);
                    }
                }
            }
        }

        return $variables;
    }

    public function getCustomTemplates()
    {
        if (!$this->data)
            return [];
        $templates = [];

        $templateList = $this->isExport
            ? $this->getNested($this->data, ['containerVersion', 'customTemplate'])
            : [];

        if (!empty($templateList)) {
            foreach ($templateList as $idx => $ct) {
                $templates[] = [
                    'display_name' => $ct['name'] ?? 'Template ' . $idx,
                    'detected_provider' => 'Custom Template',
                    'technical_summary' => isset($ct['templateData']) ? 'Vorhanden' : 'Unbekannt'
                ];
            }
        }
        return $templates;
    }

    public function getInsights()
    {
        if (!$this->data)
            return ['ga4_dimensions' => [], 'measurement_ids' => [], 'transport_urls' => [], 'consent_overview' => []];

        $dimensionMap   = []; // paramName => [variable display_names]
        $measurementIds = [];
        $transportUrls  = [];

        $json = json_encode($this->data);

        // Measurement IDs via regex
        if (preg_match_all('/G-[A-Z0-9]{6,12}/', $json, $m)) {
            $measurementIds = array_values(array_unique($m[0]));
            sort($measurementIds);
        }

        // GA4 system params to exclude from dimensions
        $systemParams = [
            'server_container_url', 'user_data', 'user_data_enabled',
            'user_properties', 'send_page_view', 'send_to', 'ecommerce',
            'debug_mode', 'allow_google_signals', 'allow_ad_personalization_signals',
        ];

        $variables = $this->getVariables();

        // Helper: extract parameter name/value pairs from a settings table array
        $extractSettingsParams = function ($tableData) {
            $params = [];
            if (!is_array($tableData)) return $params;
            $entries = $tableData;
            if (($entries[0] ?? '') === 'list') $entries = array_slice($entries, 1);
            foreach ($entries as $entry) {
                if (!is_array($entry) || ($entry[0] ?? '') !== 'map') continue;
                $pName = null; $pValue = null;
                for ($i = 1; $i < count($entry) - 1; $i += 2) {
                    $k = $entry[$i];
                    if (in_array($k, ['name', 'n', 'parameter', 'key']))                    $pName  = $entry[$i+1] ?? null;
                    if (in_array($k, ['value', 'v', 'parameterValue', 'settingValue']))     $pValue = $entry[$i+1] ?? null;
                }
                if (is_string($pName) && trim($pName) !== '')
                    $params[] = ['name' => trim($pName), 'value' => $pValue];
            }
            return $params;
        };

        // Helper: extract transport URLs from a raw macro (e.g. __smm lookup table, __c constant)
        $extractUrlsFromMacro = function ($macro) {
            $urls = [];
            if (is_string($macro['vtp_defaultValue'] ?? null) && trim($macro['vtp_defaultValue']) !== '')
                $urls[] = trim($macro['vtp_defaultValue']);
            $mapData = $macro['vtp_map'] ?? [];
            if (is_array($mapData)) {
                $rows = $mapData;
                if (($rows[0] ?? '') === 'list') $rows = array_slice($rows, 1);
                foreach ($rows as $entry) {
                    if (!is_array($entry) || ($entry[0] ?? '') !== 'map') continue;
                    for ($i = 1; $i < count($entry) - 1; $i += 2) {
                        if ($entry[$i] === 'value' && is_string($entry[$i+1] ?? null) && trim($entry[$i+1]) !== '')
                            $urls[] = trim($entry[$i+1]);
                    }
                }
            }
            if (is_string($macro['vtp_value'] ?? null) && trim($macro['vtp_value']) !== '')
                $urls[] = trim($macro['vtp_value']);
            return $urls;
        };

        // Helper: resolve a macro reference to the variable's display_name
        $resolveMacroName = function ($value) use ($variables) {
            if (is_array($value) && ($value[0] ?? '') === 'macro') {
                $idx = (int)($value[1] ?? -1);
                return $variables[$idx]['display_name'] ?? null;
            }
            return null;
        };

        // Helper: add a dimension with optional variable reference
        $addDimension = function ($paramName, $value) use (&$dimensionMap, $systemParams, $resolveMacroName) {
            if (in_array($paramName, $systemParams)) return;
            if (!isset($dimensionMap[$paramName])) $dimensionMap[$paramName] = [];
            $varName = $resolveMacroName($value);
            if ($varName !== null && !in_array($varName, $dimensionMap[$paramName]))
                $dimensionMap[$paramName][] = $varName;
        };

        if (!$this->isExport) {
            $rawTags = $this->getNested($this->data, ['resource', 'tags'], []);
            $rawMacros = $this->getNested($this->data, ['resource', 'macros'], []);

            // Pre-extract params from __gtes/__gtcs macros (shared settings variables)
            $settingsMacroParams = [];
            foreach ($rawMacros as $mIdx => $macro) {
                $func = $macro['function'] ?? '';
                if ($func === '__gtes')
                    $settingsMacroParams[$mIdx] = $extractSettingsParams($macro['vtp_eventSettingsTable'] ?? null);
                elseif ($func === '__gtcs')
                    $settingsMacroParams[$mIdx] = $extractSettingsParams($macro['vtp_configSettingsTable'] ?? null);
            }

            // Scan all GA4 tags for event parameters
            foreach ($rawTags as $tag) {
                $func = $tag['function'] ?? '';
                if (!in_array($func, ['__googtag', '__gaawe', '__ga4_event'])) continue;

                // Inline eventSettingsTable
                foreach ($extractSettingsParams($tag['vtp_eventSettingsTable'] ?? null) as $param)
                    $addDimension($param['name'], $param['value']);

                // Inline configSettingsTable
                foreach ($extractSettingsParams($tag['vtp_configSettingsTable'] ?? null) as $param)
                    $addDimension($param['name'], $param['value']);

                // Referenced eventSettingsVariable -> __gtes macro
                $esv = $tag['vtp_eventSettingsVariable'] ?? null;
                if (is_array($esv) && ($esv[0] ?? '') === 'macro') {
                    foreach ($settingsMacroParams[(int)($esv[1] ?? -1)] ?? [] as $param)
                        $addDimension($param['name'], $param['value']);
                }

                // Referenced configSettingsVariable -> __gtcs macro
                $csv = $tag['vtp_configSettingsVariable'] ?? null;
                if (is_array($csv) && ($csv[0] ?? '') === 'macro') {
                    foreach ($settingsMacroParams[(int)($csv[1] ?? -1)] ?? [] as $param)
                        $addDimension($param['name'], $param['value']);
                }
            }

            // Extract transport URLs from settings macros
            foreach ($settingsMacroParams as $mIdx => $params) {
                foreach ($params as $param) {
                    if ($param['name'] !== 'server_container_url') continue;
                    if (is_string($param['value']) && trim($param['value']) !== '') {
                        $transportUrls[] = trim($param['value']);
                    } elseif (is_array($param['value']) && ($param['value'][0] ?? '') === 'macro') {
                        $refMacro = $rawMacros[(int)($param['value'][1] ?? -1)] ?? null;
                        if ($refMacro)
                            $transportUrls = array_merge($transportUrls, $extractUrlsFromMacro($refMacro));
                    }
                }
            }

            // Direct vtp_serverContainer / vtp_transportUrl on tags and macros
            foreach (array_merge($rawTags, $rawMacros) as $item) {
                foreach (['vtp_serverContainer', 'vtp_transportUrl'] as $p) {
                    if (is_string($item[$p] ?? null) && trim($item[$p]) !== '')
                        $transportUrls[] = trim($item[$p]);
                }
            }
        } else {
            // Export JSON format
            $exportTags = $this->getNested($this->data, ['containerVersion', 'tag'], []);
            $exportVars = $this->getNested($this->data, ['containerVersion', 'variable'], []);

            // Build __gtes/__gtcs settings by variable name
            $exportSettingsByName = [];
            foreach ($exportVars as $ev) {
                $evType = $ev['type'] ?? '';
                if ($evType !== '__gtes' && $evType !== '__gtcs') continue;
                $tableKey = $evType === '__gtes' ? 'eventSettingsTable' : 'configSettingsTable';
                $params = [];
                foreach ((array)($ev['parameter'] ?? []) as $p) {
                    if (($p['key'] ?? '') !== $tableKey || ($p['type'] ?? '') !== 'LIST') continue;
                    foreach (($p['list'] ?? []) as $listItem) {
                        if (($listItem['type'] ?? '') !== 'MAP') continue;
                        $pName = null; $pValue = null;
                        foreach (($listItem['map'] ?? []) as $kv) {
                            if (($kv['key'] ?? '') === 'parameter') $pName = $kv['value'] ?? null;
                            if (($kv['key'] ?? '') === 'parameterValue') $pValue = $kv['value'] ?? null;
                        }
                        if ($pName !== null) $params[] = ['name' => $pName, 'value' => $pValue];
                    }
                }
                $exportSettingsByName[$ev['name']] = $params;
            }

            foreach ($exportTags as $tag) {
                $tagType = $tag['type'] ?? '';
                if (!in_array($tagType, ['googtag', 'gaawe', 'ga4_event'])) continue;

                foreach ((array)($tag['parameter'] ?? []) as $p) {
                    $pKey = $p['key'] ?? '';

                    // Inline event/config settings table
                    if (in_array($pKey, ['eventSettingsTable', 'configSettingsTable']) && ($p['type'] ?? '') === 'LIST') {
                        foreach (($p['list'] ?? []) as $listItem) {
                            if (($listItem['type'] ?? '') !== 'MAP') continue;
                            $pName = null; $pValue = null;
                            foreach (($listItem['map'] ?? []) as $kv) {
                                if (($kv['key'] ?? '') === 'parameter') $pName = $kv['value'] ?? null;
                                if (($kv['key'] ?? '') === 'parameterValue') $pValue = $kv['value'] ?? null;
                            }
                            if ($pName !== null && !in_array($pName, $systemParams)) {
                                if (!isset($dimensionMap[$pName])) $dimensionMap[$pName] = [];
                                if (is_string($pValue) && preg_match('/^\{\{(.+)\}\}$/', $pValue, $vm)) {
                                    if (!in_array($vm[1], $dimensionMap[$pName]))
                                        $dimensionMap[$pName][] = $vm[1];
                                }
                            }
                        }
                    }

                    // Referenced settings variable
                    if (in_array($pKey, ['eventSettingsVariable', 'configSettingsVariable'])) {
                        $refName = null;
                        $val = $p['value'] ?? '';
                        if (is_string($val) && preg_match('/^\{\{(.+)\}\}$/', $val, $vm)) $refName = $vm[1];
                        if ($refName !== null && isset($exportSettingsByName[$refName])) {
                            foreach ($exportSettingsByName[$refName] as $param) {
                                if (in_array($param['name'], $systemParams)) continue;
                                if (!isset($dimensionMap[$param['name']])) $dimensionMap[$param['name']] = [];
                                if (is_string($param['value']) && preg_match('/^\{\{(.+)\}\}$/', $param['value'], $vm2)) {
                                    if (!in_array($vm2[1], $dimensionMap[$param['name']]))
                                        $dimensionMap[$param['name']][] = $vm2[1];
                                }
                            }
                        }
                    }

                    // Direct serverContainer
                    if ($pKey === 'serverContainer' && is_string($p['value'] ?? null))
                        $transportUrls[] = $p['value'];
                }
            }
        }

        // Build final dimensions list with variable references
        $dimensions = [];
        ksort($dimensionMap);
        foreach ($dimensionMap as $paramName => $varNames) {
            $dimensions[] = [
                'name' => $paramName,
                'variables' => $varNames,
            ];
        }

        $transportUrls = array_values(array_unique($transportUrls));
        sort($transportUrls);

        // Build consent overview from tags
        $allTags = $this->getTags();
        $consentOverview = [
            'consent_types' => [],   // type => ['required_count' => N, 'tags' => [...]]
            'default_states' => [],  // type => state (from consent initialization tags)
            'init_tag' => null,      // name of the consent init tag, if any
        ];
        $consentTypeMap = [];
        foreach ($allTags as $tag) {
            foreach ($tag['consent_required'] ?? [] as $ct) {
                if (!isset($consentTypeMap[$ct])) $consentTypeMap[$ct] = [];
                $consentTypeMap[$ct][] = $tag['display_name'];
            }
            if (!empty($tag['consent_defaults'])) {
                $consentOverview['default_states'] = $tag['consent_defaults'];
                $consentOverview['init_tag'] = $tag['display_name'];
            }
        }
        ksort($consentTypeMap);
        foreach ($consentTypeMap as $ct => $tagNames) {
            $consentOverview['consent_types'][$ct] = [
                'required_count' => count($tagNames),
                'tags' => $tagNames,
            ];
        }

        return [
            'ga4_dimensions'   => $dimensions,
            'measurement_ids'  => $measurementIds,
            'transport_urls'   => $transportUrls,
            'consent_overview' => $consentOverview,
        ];
    }
    private function findMacroUsageContext($item, $mIdx, $mName)
    {
        if (!$this->isExport) {
            foreach ($item as $key => $val) {
                if (!is_string($key))
                    continue;
                $baseKey = strpos($key, 'vtp_') === 0 ? substr($key, 4) : $key;

                // Direct macro ref: vtp_measurementId => ['macro', 3]
                if (is_array($val) && ($val[0] ?? '') === 'macro' && (string) ($val[1] ?? '') === (string) $mIdx) {
                    return $this->friendlyParamName($baseKey);
                }

                // List-of-maps: handles both ["list", [map...], ...] and plain [[map...], ...]
                if (is_array($val) && count($val) > 0) {
                    // Determine where the map entries start
                    $firstEl = $val[0] ?? null;
                    if ($firstEl === 'list') {
                        $mapEntries = array_slice($val, 1);  // skip "list" header
                    } elseif (is_array($firstEl) && ($firstEl[0] ?? '') === 'map') {
                        $mapEntries = $val;                   // plain array of maps
                    } else {
                        $mapEntries = [];
                    }

                    foreach ($mapEntries as $entry) {
                        if (!is_array($entry) || ($entry[0] ?? '') !== 'map')
                            continue;
                        $mapName = null;
                        $mapValue = null;
                        // Pairs start at index 1: [map, key1, val1, key2, val2, ...]
                        for ($i = 1; $i < count($entry) - 1; $i += 2) {
                            $k = $entry[$i];
                            // Accept 'name'/'n' for the parameter name key
                            if ($k === 'name' || $k === 'n')
                                $mapName = $entry[$i + 1] ?? null;
                            // Accept 'value'/'v' for the parameter value key
                            if ($k === 'value' || $k === 'v')
                                $mapValue = $entry[$i + 1] ?? null;
                        }
                        if (
                            is_array($mapValue) && ($mapValue[0] ?? '') === 'macro'
                            && (string) ($mapValue[1] ?? '') === (string) $mIdx
                        ) {
                            $friendlyBase = $this->friendlyParamName($baseKey);
                            $mapNameStr = is_string($mapName) ? $mapName
                                : (is_array($mapName) ? '{{macro}}' : '?');
                            return $friendlyBase . ': ' . $mapNameStr;
                        }
                    }
                }

                // 3. Generic recursive fallback: macro is somewhere deep in this vtp_ key.
                if (is_array($val) && $this->hasMacroRef($val, $mIdx, $mName)) {
                    $paramName = $this->extractParamNameNearMacro($val, (string) $mIdx);
                    $friendly = $this->friendlyParamName($baseKey);
                    return $paramName !== null ? $friendly . ': ' . $paramName : $friendly;
                }
            }
        } else {
            // Export format: scan parameter[] array
            foreach ((array) ($item['parameter'] ?? []) as $p) {
                $paramKey = $p['key'] ?? '';
                $paramVal = $p['value'] ?? '';
                if (is_string($paramVal) && strpos($paramVal, '{{' . $mName . '}}') !== false) {
                    return $this->friendlyParamName($paramKey);
                }
            }
        }
        return '';
    }

    /**
     * Walks $arr recursively. When it finds a ['map',...] entry that contains ['macro',$mIdx],
     * it returns the value of the sibling 'name'/'n' key as the parameter name.
     */
    private function extractParamNameNearMacro($arr, $mIdx)
    {
        if (!is_array($arr))
            return null;

        // If this node is itself a map entry, check it directly
        if (($arr[0] ?? '') === 'map') {
            $name = null;
            $found = false;
            for ($i = 1; $i < count($arr) - 1; $i += 2) {
                $k = $arr[$i];
                $v = $arr[$i + 1] ?? null;
                if ($k === 'name' || $k === 'n' || $k === 'parameter' || $k === 'key') {
                    $name = is_string($v) ? $v : null;
                }
                if (is_array($v) && ($v[0] ?? '') === 'macro' && (string) ($v[1] ?? '') === $mIdx) {
                    $found = true;
                }
            }
            if ($found)
                return $name; // may be null if no name sibling
        }

        // Recurse into children
        foreach ($arr as $child) {
            if (!is_array($child))
                continue;
            $result = $this->extractParamNameNearMacro($child, $mIdx);
            if ($result !== null)
                return $result;
        }

        return null;
    }

    private function friendlyParamName($key)
    {
        $map = [
            'eventSettingsTable' => 'Event Parameter',
            'measurementId' => 'Measurement ID',
            'eventName' => 'Event Name',
            'sendTo' => 'Send To',
            'trackingId' => 'Tracking ID',
            'conversionId' => 'Conversion ID',
            'conversionLabel' => 'Conversion Label',
            'conversionValue' => 'Value',
            'eventParameters' => 'Event Parameter',
            'userProperties' => 'User Property',
            'serverContainer' => 'Server Container URL',
            'category' => 'Event Category',
            'action' => 'Event Action',
            'label' => 'Event Label',
            'value' => 'Value',
        ];
        if (isset($map[$key]))
            return $map[$key];
        // CamelCase to words
        return ucfirst(trim(preg_replace('/([A-Z])/', ' $1', $key)));
    }

    private function getTriggerNameById($id)
    {
        $triggerList = $this->getNested($this->data, ['containerVersion', 'trigger'], []);
        foreach ($triggerList as $tr) {
            if (isset($tr['triggerId']) && $tr['triggerId'] == $id) {
                return $tr['name'] ?? $id;
            }
        }
        return $id;
    }

    private function getTriggerNameByIdFromRules($ruleIdx)
    {
        $rules = $this->getNested($this->data, ['resource', 'rules'], []);
        $predicates = $this->getNested($this->data, ['resource', 'predicates'], []);
        $r = $rules[$ruleIdx] ?? [];

        $knownEvents = [
            'gtm.js' => 'All Pages',
            'gtm.dom' => 'DOM Ready',
            'gtm.load' => 'Window Loaded',
            'gtm.click' => 'Click - All Elements',
            'gtm.linkClick' => 'Click - Just Links',
            'gtm.elementVisibility' => 'Element Visibility',
            'gtm.triggerGroup' => 'Trigger Group',
            'gtm.scrollDepth' => 'Scroll Depth',
            'gtm.formSubmit' => 'Form Submission',
            'gtm.historyChange' => 'History Change',
            'gtm.timer' => 'Timer',
        ];

        foreach ($r as $cond) {
            if (!is_array($cond) || $cond[0] !== 'if')
                continue;
            foreach (array_slice($cond, 1) as $pId) {
                $pred = $predicates[$pId] ?? null;
                if (!$pred)
                    continue;
                $pfunc = $pred['function'] ?? '';
                $arg1 = $pred['arg1'] ?? '';
                $arg0 = $pred['arg0'] ?? '';
                if (!in_array($pfunc, ['_eq', '_re', '_cn', '_sw', '_ne']))
                    continue;

                $arg1Str = is_string($arg1) ? $arg1 : '';

                // Direct match on known GTM event name
                if (isset($knownEvents[$arg1Str])) {
                    return $knownEvents[$arg1Str];
                }
                // Variable compared to a string literal → custom event
                $isArg0Macro = is_array($arg0) && ($arg0[0] ?? '') === 'macro';
                if ($isArg0Macro && $arg1Str !== '') {
                    return 'Event: ' . $arg1Str;
                }
            }
        }
        return null; // caller will use trigger id or skip
    }
}
?>
