<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');

$user = $_GET['user'] ?? ($user ?? 'leetcode');
$user = htmlspecialchars($user, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>LeetCode Stats — <?= $user ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        inter: ['Inter', 'ui-sans-serif', 'system-ui']
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#4f46e5',
                            soft: '#eef2ff'
                        },
                        easy: '#10b981',
                        medium: '#f59e0b',
                        hard: '#ef4444'
                    },
                    boxShadow: {
                        subtle: '0 1px 2px 0 rgb(0 0 0 / 0.06), 0 1px 3px 0 rgb(0 0 0 / 0.10)',
                    }
                }
            }
        }
    </script>

    <!-- Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <!-- Chart.js + html2canvas + Icons -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        :root {
            --card-bg: #ffffff;
            --muted: #6b7280;
            --border: #e5e7eb;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow, 0 1px 2px rgba(0, 0, 0, .06))
        }

        .kpi {
            display: flex;
            gap: .5rem;
            align-items: baseline
        }

        .kpi .value {
            font-weight: 700;
            font-size: 1.5rem;
            line-height: 1
        }

        .kpi .label {
            color: var(--muted);
            font-size: .825rem
        }

        .progress {
            height: 8px;
            background: #f3f4f6;
            border-radius: 999px;
            overflow: hidden
        }

        .progress>span {
            display: block;
            height: 100%
        }

        .shareable {
            width: 720px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px
        }

        .table thead th {
            background: #f9fafb;
            font-weight: 600
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen">
    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header -->
        <header class="mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-lg bg-brand.soft flex items-center justify-center">
                        <i class="fa-solid fa-code text-brand"></i>
                    </div>
                    <h1 class="text-xl font-semibold">LeetCode Stats</h1>
                </div>

                <form id="user-form" class="w-full md:w-auto">
                    <div class="relative flex items-center gap-2">
                        <input id="username-input" type="text"
                            class="w-full md:w-80 rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 text-sm outline-none focus-visible:ring-2 focus-visible:ring-brand"
                            placeholder="Usuario de LeetCode" value="<?= $user ?>" />
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand px-3 py-2 text-white text-sm font-medium hover:bg-indigo-600 transition-colors">
                            <i class="fa-solid fa-magnifying-glass"></i><span>Buscar</span>
                        </button>
                    </div>
                </form>
            </div>
        </header>

        <!-- Grid: Main + Sidebar -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- MAIN -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <!-- User card -->
                <div class="card p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <img id="avatar" class="h-16 w-16 rounded-full border border-gray-200 object-cover"
                            alt="Avatar" />
                        <div class="flex-1">
                            <h2 id="name" class="text-lg font-semibold">—</h2>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-globe"></i> <span
                                        id="country-text">—</span></span>
                                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-trophy"></i> <span
                                        id="ranking">Ranking: —</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI strip -->
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="card p-4 shadow-subtle">
                            <div class="kpi"><span class="value" id="kpi-total">0</span><span
                                    class="label">resueltos</span></div>
                        </div>
                        <div class="card p-4 shadow-subtle">
                            <div class="kpi"><span class="value" id="kpi-acc">0%</span><span class="label">aceptación
                                    global</span></div>
                        </div>
                        <div class="card p-4 shadow-subtle">
                            <div class="kpi"><span class="value" id="kpi-user">@<?= $user ?></span><span
                                    class="label">usuario</span></div>
                        </div>
                    </div>
                </div>

                <!-- Stats + Chart -->
                <div class="card p-5">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        <!-- Left: difficulty cards -->
                        <div class="space-y-4">
                            <!-- Easy -->
                            <div class="p-4 rounded-xl border border-gray-200">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="text-sm text-gray-500 font-medium">Easy</div>
                                        <div class="mt-1 text-2xl font-bold text-gray-900" id="easy">0</div>
                                    </div>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-emerald-50 text-emerald-700">Verde</span>
                                </div>
                                <div class="mt-3 progress"><span id="easy-progress" class="bg-easy"
                                        style="width:0%"></span></div>
                                <div class="mt-2 text-xs text-gray-500">Aceptación: <span id="easy-acc">0%</span> •
                                    Envíos: <span id="easy-sub">0</span></div>
                            </div>

                            <!-- Medium -->
                            <div class="p-4 rounded-xl border border-gray-200">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="text-sm text-gray-500 font-medium">Medium</div>
                                        <div class="mt-1 text-2xl font-bold text-gray-900" id="medium">0</div>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-50 text-amber-800">Ámbar</span>
                                </div>
                                <div class="mt-3 progress"><span id="medium-progress" class="bg-medium"
                                        style="width:0%"></span></div>
                                <div class="mt-2 text-xs text-gray-500">Aceptación: <span id="medium-acc">0%</span> •
                                    Envíos: <span id="medium-sub">0</span></div>
                            </div>

                            <!-- Hard -->
                            <div class="p-4 rounded-xl border border-gray-200">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="text-sm text-gray-500 font-medium">Hard</div>
                                        <div class="mt-1 text-2xl font-bold text-gray-900" id="hard">0</div>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full bg-rose-50 text-rose-700">Rojo</span>
                                </div>
                                <div class="mt-3 progress"><span id="hard-progress" class="bg-hard"
                                        style="width:0%"></span></div>
                                <div class="mt-2 text-xs text-gray-500">Aceptación: <span id="hard-acc">0%</span> •
                                    Envíos: <span id="hard-sub">0</span></div>
                            </div>
                        </div>

                        <!-- Right: donut + table -->
                        <div class="space-y-4">
                            <div class="p-4 rounded-xl border border-gray-200">
                                <canvas id="chart" height="220"></canvas>
                            </div>

                            <div class="p-4 rounded-xl border border-gray-200 overflow-x-auto">
                                <table class="w-full table">
                                    <thead>
                                        <tr class="text-left text-sm text-gray-600">
                                            <th class="py-2 pr-3">Dificultad</th>
                                            <th class="py-2 pr-3">Resueltos</th>
                                            <th class="py-2 pr-3">Envíos</th>
                                            <th class="py-2">Aceptación</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm text-gray-800">
                                        <tr>
                                            <td class="py-2 pr-3">Easy</td>
                                            <td class="py-2 pr-3" id="t-easy-s">0</td>
                                            <td class="py-2 pr-3" id="t-easy-sub">0</td>
                                            <td class="py-2" id="t-easy-acc">0%</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="py-2 pr-3">Medium</td>
                                            <td class="py-2 pr-3" id="t-medium-s">0</td>
                                            <td class="py-2 pr-3" id="t-medium-sub">0</td>
                                            <td class="py-2" id="t-medium-acc">0%</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 pr-3">Hard</td>
                                            <td class="py-2 pr-3" id="t-hard-s">0</td>
                                            <td class="py-2 pr-3" id="t-hard-sub">0</td>
                                            <td class="py-2" id="t-hard-acc">0%</td>
                                        </tr>
                                        <tr class="border-t border-gray-200 font-semibold">
                                            <td class="py-2 pr-3">Total</td>
                                            <td class="py-2 pr-3" id="t-total-s">0</td>
                                            <td class="py-2 pr-3" id="t-total-sub">0</td>
                                            <td class="py-2" id="t-total-acc">0%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Share actions -->
                    <div class="mt-5 flex flex-wrap items-center gap-3 justify-between">
                        <div class="flex items-center gap-2">
                            <button
                                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50"
                                onclick="shareToTwitter(event)">
                                <i class="fa-brands fa-x-twitter"></i> <span class="ml-2">Twitter</span>
                            </button>
                            <button
                                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50"
                                onclick="shareToLinkedIn(event)">
                                <i class="fa-brands fa-linkedin"></i> <span class="ml-2">LinkedIn</span>
                            </button>
                            <button
                                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50"
                                onclick="shareToWhatsApp(event)">
                                <i class="fa-brands fa-whatsapp"></i> <span class="ml-2">WhatsApp</span>
                            </button>
                            <button
                                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50"
                                onclick="shareToTelegram(event)">
                                <i class="fa-brands fa-telegram"></i> <span class="ml-2">Telegram</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <button id="download-btn"
                                class="rounded-lg bg-brand px-3 py-2 text-sm text-white hover:bg-indigo-600"
                                onclick="downloadCard()">
                                <i class="fa-solid fa-download mr-2"></i>Descargar tarjeta
                            </button>
                            <span id="share-msg" class="text-sm text-gray-600"></span>
                        </div>
                    </div>
                </div>

                <!-- Loading / Error -->
                <div id="loading" class="card p-6 flex items-center justify-center">
                    <div class="flex items-center gap-3 text-gray-600">
                        <span class="h-2 w-2 rounded-full bg-brand animate-ping"></span>
                        <span>Cargando datos…</span>
                    </div>
                </div>

                <div id="error" class="card p-6 hidden">
                    <div class="text-center">
                        <div class="text-rose-600 text-2xl mb-2"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <p id="error-msg" class="text-gray-700 mb-4">Ha ocurrido un error.</p>
                        <button class="rounded-lg bg-gray-900 text-white px-4 py-2 text-sm"
                            onclick="location.reload()">Reintentar</button>
                    </div>
                </div>
            </div>

            <!-- SIDEBAR: tarjeta resumen (la que pedías al lado) -->
            <aside class="lg:col-span-1">
                <div class="card p-5 sticky top-6">
                    <h3 class="text-sm font-semibold text-gray-600 mb-4">Resumen rápido</h3>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-lg border border-gray-200 p-3 text-center">
                            <div class="text-xs text-gray-500">Easy</div>
                            <div class="mt-1 text-xl font-bold text-emerald-600" id="s-easy">0</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3 text-center">
                            <div class="text-xs text-gray-500">Medium</div>
                            <div class="mt-1 text-xl font-bold text-amber-600" id="s-medium">0</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3 text-center">
                            <div class="text-xs text-gray-500">Hard</div>
                            <div class="mt-1 text-xl font-bold text-rose-600" id="s-hard">0</div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-brand.soft border border-indigo-100 p-4 text-center">
                            <div class="text-xs text-gray-600">Total Resueltos</div>
                            <div class="mt-1 text-2xl font-bold text-indigo-700" id="s-total">0</div>
                        </div>
                        <div class="rounded-lg bg-violet-50 border border-violet-100 p-4 text-center">
                            <div class="text-xs text-gray-600">Tasa de Aceptación</div>
                            <div class="mt-1 text-2xl font-bold text-violet-700" id="s-acc">0%</div>
                        </div>
                    </div>

                    <hr class="my-4 border-gray-200" />

                    <!-- Tarjeta mini para compartir (preview simple) -->
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-lg bg-brand.soft flex items-center justify-center">
                                <i class="fa-solid fa-chart-pie text-brand"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium">Tarjeta compartible</div>
                                <div class="text-xs text-gray-500">PNG listo para redes</div>
                            </div>
                        </div>
                        <button
                            class="mt-3 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-50"
                            onclick="downloadCard()">
                            <i class="fa-solid fa-image mr-2"></i>Generar imagen
                        </button>
                    </div>
                </div>
            </aside>
        </section>
    </main>

    <!-- Tarjeta para compartir (oculta, pero clara) -->
    <div id="shareable-card" class="shareable hidden mx-auto">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-lg bg-brand.soft flex items-center justify-center">
                <i class="fa-solid fa-code text-brand"></i>
            </div>
            <div>
                <div class="text-lg font-semibold">LeetCode Stats</div>
                <div class="text-xs text-gray-500">wherepanda.xyz/leetcode.php</div>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <img id="card-avatar" class="h-14 w-14 rounded-full border border-gray-200 object-cover" alt="Avatar">
            <div>
                <div id="card-name" class="font-semibold text-gray-900">—</div>
                <div class="text-sm text-gray-600 flex items-center gap-2">
                    <span><i class="fa-solid fa-globe"></i> <span id="card-country-text">—</span></span>
                    <span>•</span>
                    <span><i class="fa-solid fa-trophy"></i> <span id="card-ranking">Ranking: —</span></span>
                </div>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-3 gap-3">
            <div class="rounded-lg border border-gray-200 p-3 text-center">
                <div class="text-xs text-gray-500">Easy</div>
                <div class="mt-1 text-xl font-bold text-emerald-600" id="card-easy">0</div>
            </div>
            <div class="rounded-lg border border-gray-200 p-3 text-center">
                <div class="text-xs text-gray-500">Medium</div>
                <div class="mt-1 text-xl font-bold text-amber-600" id="card-medium">0</div>
            </div>
            <div class="rounded-lg border border-gray-200 p-3 text-center">
                <div class="text-xs text-gray-500">Hard</div>
                <div class="mt-1 text-xl font-bold text-rose-600" id="card-hard">0</div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-lg bg-brand.soft border border-indigo-100 p-4 text-center">
                <div class="text-xs text-gray-600">Total Resueltos</div>
                <div class="mt-1 text-2xl font-bold text-indigo-700" id="card-total">0</div>
            </div>
            <div class="rounded-lg bg-violet-50 border border-violet-100 p-4 text-center">
                <div class="text-xs text-gray-600">Tasa de Aceptación</div>
                <div class="mt-1 text-2xl font-bold text-violet-700" id="card-acceptance">0%</div>
            </div>
        </div>

        <div class="mt-5 border border-gray-200 rounded-lg p-3">
            <canvas id="card-chart" height="140"></canvas>
        </div>
    </div>

    <script>
        // DOM
        const loadingEl = document.getElementById('loading');
        const errorEl = document.getElementById('error');
        const errorMsgEl = document.getElementById('error-msg');
        const contentBlocks = [ /* main parts already visible via cards */ ];

        const usernameInput = document.getElementById('username-input');
        const userForm = document.getElementById('user-form');

        // URL param
        const params = new URLSearchParams(window.location.search);
        const userParam = params.get('user') || "<?= $user ?>";

        // Bind form
        userForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const username = usernameInput.value.trim();
            if (!username) return;
            const newUrl = `${window.location.pathname}?user=${encodeURIComponent(username)}`;
            window.history.pushState({}, '', newUrl);
            loadUserData(username);
        });

        document.addEventListener('DOMContentLoaded', () => loadUserData(userParam));

        // Helpers
        const pct = (a, b) => b > 0 ? Math.round((a / b) * 100) : 0;

        let donutChart, cardDonut;

        function loadUserData(username) {
            // show loading
            loadingEl.classList.remove('hidden');
            errorEl.classList.add('hidden');

            fetch(`leetcode_api.php?user=${encodeURIComponent(username)}`)
                .then(r => r.json())
                .then(d => {
                    if (d.error) throw new Error(d.error);

                    // hide loading
                    loadingEl.classList.add('hidden');

                    // USER
                    document.getElementById('avatar').src = d.avatar ||
                        'https://leetcode.com/static/images/LeetCode_logo_rvs.png';
                    document.getElementById('name').textContent = d.name || d.username;
                    document.getElementById('country-text').textContent = d.country || 'No especificado';
                    document.getElementById('ranking').textContent = d.ranking ? `Ranking: #${d.ranking}` :
                        'Ranking: -';

                    // STATS
                    const eS = d.stats.easy.solved || 0;
                    const mS = d.stats.medium.solved || 0;
                    const hS = d.stats.hard.solved || 0;

                    const eSub = d.stats.easy.submissions || 0;
                    const mSub = d.stats.medium.submissions || 0;
                    const hSub = d.stats.hard.submissions || 0;

                    const totalSolved = eS + mS + hS;
                    const totalSub = eSub + mSub + hSub;
                    const accGlobal = pct(totalSolved, totalSub);

                    // Update KPIs
                    document.getElementById('kpi-total').textContent = totalSolved;
                    document.getElementById('kpi-acc').textContent = accGlobal + '%';

                    // Difficulty cards
                    document.getElementById('easy').textContent = eS;
                    document.getElementById('medium').textContent = mS;
                    document.getElementById('hard').textContent = hS;

                    document.getElementById('easy-sub').textContent = eSub;
                    document.getElementById('medium-sub').textContent = mSub;
                    document.getElementById('hard-sub').textContent = hSub;

                    const eAcc = pct(eS, eSub),
                        mAcc = pct(mS, mSub),
                        hAcc = pct(hS, hSub);
                    document.getElementById('easy-acc').textContent = eAcc + '%';
                    document.getElementById('medium-acc').textContent = mAcc + '%';
                    document.getElementById('hard-acc').textContent = hAcc + '%';

                    // Progress (proporción dentro del total)
                    const t = totalSolved || 1;
                    document.getElementById('easy-progress').style.width = (eS / t * 100).toFixed(2) + '%';
                    document.getElementById('medium-progress').style.width = (mS / t * 100).toFixed(2) + '%';
                    document.getElementById('hard-progress').style.width = (hS / t * 100).toFixed(2) + '%';

                    // Table
                    document.getElementById('t-easy-s').textContent = eS;
                    document.getElementById('t-medium-s').textContent = mS;
                    document.getElementById('t-hard-s').textContent = hS;

                    document.getElementById('t-easy-sub').textContent = eSub;
                    document.getElementById('t-medium-sub').textContent = mSub;
                    document.getElementById('t-hard-sub').textContent = hSub;

                    document.getElementById('t-easy-acc').textContent = eAcc + '%';
                    document.getElementById('t-medium-acc').textContent = mAcc + '%';
                    document.getElementById('t-hard-acc').textContent = hAcc + '%';

                    document.getElementById('t-total-s').textContent = totalSolved;
                    document.getElementById('t-total-sub').textContent = totalSub;
                    document.getElementById('t-total-acc').textContent = accGlobal + '%';

                    // Sidebar summary
                    document.getElementById('s-easy').textContent = eS;
                    document.getElementById('s-medium').textContent = mS;
                    document.getElementById('s-hard').textContent = hS;
                    document.getElementById('s-total').textContent = totalSolved;
                    document.getElementById('s-acc').textContent = accGlobal + '%';

                    // Donut main
                    const ctx = document.getElementById('chart');
                    if (donutChart) donutChart.destroy();
                    donutChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Easy', 'Medium', 'Hard'],
                            datasets: [{
                                data: [eS, mS, hS],
                                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                borderWidth: 0,
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 14
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(3,7,18,0.85)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff'
                                }
                            },
                            cutout: '62%'
                        }
                    });

                    // Shareable card data
                    prepareShareableCard(d, totalSolved, accGlobal);
                })
                .catch(err => {
                    loadingEl.classList.add('hidden');
                    errorEl.classList.remove('hidden');
                    errorMsgEl.textContent = 'Error: ' + err.message;
                });
        }

        function prepareShareableCard(data, totalSolved, acc) {
            // Fill basic
            document.getElementById('card-avatar').src = data.avatar ||
                'https://leetcode.com/static/images/LeetCode_logo_rvs.png';
            document.getElementById('card-name').textContent = data.name || data.username;
            document.getElementById('card-country-text').textContent = data.country || 'No especificado';
            document.getElementById('card-ranking').textContent = data.ranking ? `Ranking: #${data.ranking}` :
                'Ranking: -';

            const eS = data.stats.easy.solved || 0;
            const mS = data.stats.medium.solved || 0;
            const hS = data.stats.hard.solved || 0;

            document.getElementById('card-easy').textContent = eS;
            document.getElementById('card-medium').textContent = mS;
            document.getElementById('card-hard').textContent = hS;
            document.getElementById('card-total').textContent = totalSolved;
            document.getElementById('card-acceptance').textContent = acc + '%';

            const cctx = document.getElementById('card-chart').getContext('2d');
            if (cardDonut) cardDonut.destroy();
            cardDonut = new Chart(cctx, {
                type: 'doughnut',
                data: {
                    labels: ['Easy', 'Medium', 'Hard'],
                    datasets: [{
                        data: [eS, mS, hS],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    cutout: '62%'
                }
            });
        }

        // Share funcs
        function shareToTwitter(e) {
            e.preventDefault();
            const url = encodeURIComponent(location.href);
            const text = encodeURIComponent('Mira mis estadísticas de LeetCode 🚀');
            window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
        }

        function shareToLinkedIn(e) {
            e.preventDefault();
            const url = encodeURIComponent(location.href);
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank');
        }

        function shareToWhatsApp(e) {
            e.preventDefault();
            const url = encodeURIComponent(location.href);
            const text = encodeURIComponent('Mira mis estadísticas de LeetCode 🚀');
            window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
        }

        function shareToTelegram(e) {
            e.preventDefault();
            const url = encodeURIComponent(location.href);
            const text = encodeURIComponent('Mira mis estadísticas de LeetCode 🚀');
            window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
        }

        // Download shareable
        function downloadCard() {
            const messageEl = document.getElementById('share-msg');
            const cardEl = document.getElementById('shareable-card');
            messageEl.textContent = 'Generando imagen…';
            cardEl.classList.remove('hidden');

            html2canvas(cardEl, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff'
                })
                .then(canvas => {
                    const image = canvas.toDataURL('image/png');
                    const link = document.createElement('a');
                    const u = document.getElementById('username-input').value || 'user';
                    link.download = `leetcode-stats-${u}.png`;
                    link.href = image;
                    link.click();
                    messageEl.textContent = 'Imagen lista ✅';
                    setTimeout(() => messageEl.textContent = '', 2500);
                    cardEl.classList.add('hidden');
                })
                .catch(err => {
                    console.error(err);
                    messageEl.textContent = 'Error al generar la imagen';
                    cardEl.classList.add('hidden');
                });
        }
    </script>
</body>

</html>