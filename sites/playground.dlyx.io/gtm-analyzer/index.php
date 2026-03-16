<?php
require_once 'Parser.php';

$error = null;
$parser = null;
$successMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parser = new Parser();
    try {
        if (!empty($_POST['gtm_id'])) {
            $gtmId = trim($_POST['gtm_id']);
            $parser->parseFromId($gtmId);
            $successMsg = "Container " . htmlspecialchars($gtmId) . " erfolgreich geladen!";
        } elseif (!empty($_POST['gtm_js'])) {
            $parser->parseFromJs($_POST['gtm_js']);
            $successMsg = "Eingefügter JavaScript Code erfolgreich geparst!";
        } elseif (isset($_FILES['gtm_file']) && $_FILES['gtm_file']['error'] === UPLOAD_ERR_OK) {
            $parser->parseFromFile($_FILES['gtm_file']['tmp_name']);
            $successMsg = "Export-Datei erfolgreich eingelesen!";
        } else {
            throw new Exception("Bitte geben Sie eine GTM-ID an, fügen Sie JavaScript Code ein oder laden Sie eine Datei hoch.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        $parser = null;
    }
}

$tags = $parser ? $parser->getTags() : [];
$triggers = $parser ? $parser->getTriggers() : [];
$variables = $parser ? $parser->getVariables() : [];
$templates = $parser ? $parser->getCustomTemplates() : [];
$insights  = $parser ? $parser->getInsights() : ['ga4_dimensions' => [], 'measurement_ids' => [], 'transport_urls' => [], 'consent_overview' => []];


?>
<!DOCTYPE html>
<html lang="de" class="bg-slate-50 text-slate-800 antialiased">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GTM Analyzer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    <?php $hasResults = ($parser && !$error); ?>
    <!-- Header -->
    <header class="bg-white shadow-[0_1px_3px_0_rgba(0,0,0,0.05)] border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-[72px]">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 text-white p-2.5 rounded-xl shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-bold text-xl tracking-tight text-slate-900 leading-tight">GTM Analyzer</h1>
                        <p class="text-xs text-slate-500 font-medium">Analytics Infrastructure Tool</p>
                    </div>
                </div>
                
                <!-- Dynamic Header Right Side (Visible on Results) -->
                <?php if ($hasResults): ?>
                <div id="header-results-info" class="flex items-center gap-4 sm:gap-6 animate-in fade-in duration-300">
                    <div class="hidden sm:flex flex-col items-end">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Erfolgreich geladen</span>
                        </div>
                        <div class="text-[13px] font-semibold text-slate-700">
                            <?= htmlspecialchars(!empty($_POST['gtm_id']) ? $_POST['gtm_id'] : 'Aus JS / Datei importiert') ?>
                        </div>
                    </div>
                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                    <button type="button" onclick="document.getElementById('input-section').style.display='block'; document.getElementById('header-results-info').style.display='none'; window.scrollTo({top: 0, behavior: 'smooth'});"
                        class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-1.5 px-3 sm:px-4 rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 text-xs sm:text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        <span class="hidden sm:inline">Quelle ändern</span>
                        <span class="sm:hidden">Ändern</span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        <!-- Input Area Section -->
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8" id="input-section" <?= $hasResults ? 'style="display: none;"' : '' ?>>
            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold flex items-center gap-2 text-slate-800">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Datenquelle auswählen
                    </h2>
                    <?php if ($hasResults): ?>
                        <button type="button" onclick="document.getElementById('input-section').style.display='none'; document.getElementById('header-results-info').style.display='flex';" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    <?php endif; ?>
                </div>

                <form action="" method="post" enctype="multipart/form-data"
                    class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">

                    <!-- GTM ID Input -->
                    <div
                        class="bg-slate-50/50 p-6 rounded-xl border border-dashed border-slate-300 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all flex flex-col">
                        <label for="gtm_id" class="block text-sm font-semibold text-slate-700 mb-3">Google Tag Manager
                            ID</label>
                        <div class="flex flex-col gap-3 flex-grow">
                            <input type="text" name="gtm_id" id="gtm_id" placeholder="z.B. GTM-XXXXXXX" value="<?= htmlspecialchars($_POST['gtm_id'] ?? '') ?>"
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 border text-sm transition-shadow">
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 text-sm mt-auto">
                                Abrufen
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-3 font-medium">Live-Container über gtm.js laden (öffentliche
                            Version).</p>
                    </div>

                    <!-- JS Code Input -->
                    <div
                        class="bg-slate-50/50 p-6 rounded-xl border border-dashed border-slate-300 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all flex flex-col">
                        <label for="gtm_js" class="block text-sm font-semibold text-slate-700 mb-3">JavaScript
                            Code</label>
                        <div class="flex flex-col gap-3 flex-grow">
                            <textarea name="gtm_js" id="gtm_js" placeholder="Raw GTM.js Code hier einfügen..."
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2 border text-sm transition-shadow resize-none flex-grow min-h-[44px]"><?= htmlspecialchars($_POST['gtm_js'] ?? '') ?></textarea>
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 text-sm mt-auto">
                                Analysieren
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-3 font-medium">Direktes Parsen des Quellcodes.</p>
                    </div>

                    <!-- File Upload Input (disabled) -->
                    <div
                        class="bg-slate-50/50 p-6 rounded-xl border border-dashed border-slate-200 flex flex-col opacity-50 cursor-not-allowed relative">
                        <label class="block text-sm font-semibold text-slate-400 mb-3">Export JSON
                            hochladen</label>
                        <div class="flex flex-col gap-3 flex-grow">
                            <input type="file" disabled
                                class="block w-full text-sm text-slate-400 file:mr-0 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-400 cursor-not-allowed mb-2">
                            <button type="button" disabled
                                class="w-full bg-slate-400 text-white font-medium py-2.5 px-6 rounded-lg shadow-sm text-sm mt-auto cursor-not-allowed">
                                Analysieren
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-3 font-medium">Demnächst verfügbar.</p>
                    </div>
                </form>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-t border-red-100 p-4 px-8 flex items-start gap-3 text-red-700">
                    <svg class="h-5 w-5 mt-0.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm font-medium">
                        <?= htmlspecialchars($error) ?>
                    </span>
                </div>
            <?php elseif ($successMsg): ?>
                <div class="bg-emerald-50 border-t border-emerald-100 p-4 px-8 flex items-start gap-3 text-emerald-800">
                    <svg class="h-5 w-5 mt-0.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-sm font-medium">
                        <?= htmlspecialchars($successMsg) ?>
                    </span>
                </div>
            <?php endif; ?>
        </section>

        <!-- Results Area -->
        <?php if ($parser && !$error): ?>

            <div id="gtm-app"></div>
            <script>
                const gtmData = <?= json_encode([
                    'tags'      => $tags,
                    'triggers'  => $triggers,
                    'variables' => $variables,
                    'templates' => $templates,
                    'insights'  => $insights,
                ]) ?>;
                const containerInfo = {
                    id: <?= json_encode($_POST['gtm_id'] ?? 'Export / JS Snippet') ?>,
                    version: 'N/A'
                };
            </script>
            <script src="https://unpkg.com/lucide@latest"></script>
            <script src="app.js"></script>
            <script>
                window.app = new GTMApp(gtmData, containerInfo);
            </script>
        <?php else: ?>
            <!-- Placeholder state -->
            <div
                class="flex flex-col items-center justify-center p-16 bg-white rounded-2xl border border-slate-200 border-dashed text-slate-400 shadow-sm">
                <div class="bg-slate-50 p-4 rounded-full mb-4">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
                <p class="text-xl font-semibold text-slate-700 mb-2">Bereit zur Analyse</p>
                <p class="text-sm font-medium text-slate-500 max-w-sm text-center">Geben Sie eine Container-ID ein oder
                    laden Sie eine Datei hoch, um die Google Tag Manager Konfiguration zu visualisieren.</p>
            </div>
        <?php endif; ?>

    </main>

    <footer class="bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm font-medium text-slate-400">
            &copy;
            <?= date('Y') ?> GTM Analyzer. Erstellt für Analytics-Audits.
        </div>
    </footer>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(2px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>

</html>