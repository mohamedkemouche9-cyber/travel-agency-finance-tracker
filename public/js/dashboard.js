/* ============================================================================
 * dashboard.js - draws the dashboard charts and runs the what-if simulator.
 * ----------------------------------------------------------------------------
 * Data comes as JSON from the MVC API layer:
 *     GET  api/cashflow    -> monthly income & expense series
 *     GET  api/categories  -> expense split by category (current month)
 *     GET  api/forecast    -> linear-regression projection (next 3 months)
 * Charts are rendered with Chart.js (bundled locally in js/vendor/).
 * ========================================================================== */
(function () {
    'use strict';

    if (typeof Chart === 'undefined') return;          // vendor file missing

    var BASE = (window.APP && window.APP.base) || '';
    var CUR  = (window.APP && window.APP.currency) || '£';

    /* ------------------------------ helpers ------------------------------ */

    function api(path) {
        return fetch(BASE + '/' + path, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        }).then(function (r) {
            if (!r.ok) throw new Error('API error ' + r.status);
            return r.json();
        });
    }

    /** "2026-07" -> "Jul 26" */
    function monthLabel(ym) {
        var parts  = ym.split('-');
        var names  = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return names[parseInt(parts[1], 10) - 1] + ' ' + parts[0].slice(2);
    }

    function fmtMoney(v) {
        var sign = v < 0 ? '-' : '';
        return sign + CUR + Math.abs(v).toLocaleString(undefined, {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = '#64748b';

    var moneyTicks = {
        callback: function (value) { return CUR + Number(value).toLocaleString(); }
    };
    var moneyTooltip = {
        callbacks: {
            label: function (ctx) {
                return ctx.dataset.label + ': ' + fmtMoney(ctx.parsed.y !== undefined ? ctx.parsed.y : ctx.parsed);
            }
        }
    };

    /* --------------------------- 1. cash flow ----------------------------- */

    var cashflowEl = document.getElementById('cashflowChart');
    if (cashflowEl) {
        api('api/cashflow').then(function (d) {
            new Chart(cashflowEl, {
                type: 'bar',
                data: {
                    labels: d.months.map(monthLabel),
                    datasets: [
                        {
                            label: 'Income',
                            data: d.income,
                            backgroundColor: 'rgba(5, 150, 105, .75)',
                            borderRadius: 6,
                            maxBarThickness: 26
                        },
                        {
                            label: 'Expenses',
                            data: d.expense,
                            backgroundColor: 'rgba(225, 29, 72, .7)',
                            borderRadius: 6,
                            maxBarThickness: 26
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { tooltip: moneyTooltip, legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, ticks: moneyTicks, grid: { color: '#eef2f7' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }).catch(function () { /* chart quietly skipped if API unavailable */ });
    }

    /* ----------------------- 2. category doughnut ------------------------- */

    var categoryEl = document.getElementById('categoryChart');
    if (categoryEl) {
        api('api/categories').then(function (d) {
            if (!d.labels.length) {
                categoryEl.parentElement.hidden = true;
                var empty = document.getElementById('categoryEmpty');
                if (empty) empty.hidden = false;
                return;
            }
            new Chart(categoryEl, {
                type: 'doughnut',
                data: {
                    labels: d.labels,
                    datasets: [{
                        data: d.totals,
                        backgroundColor: d.colors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                    var pct   = total ? Math.round(ctx.parsed / total * 100) : 0;
                                    return ' ' + ctx.label + ': ' + fmtMoney(ctx.parsed) + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }).catch(function () {});
    }

    /* ------------------- 3. forecast + what-if simulator ------------------ */

    var forecastEl = document.getElementById('forecastChart');
    if (!forecastEl) return;

    var fc            = null;   // data from the API
    var forecastChart = null;

    /** Recompute the projected series with the current what-if inputs. */
    function whatIfSeries() {
        var dIncome  = parseFloat(document.getElementById('wiIncome').value)  || 0;
        var dExpense = parseFloat(document.getElementById('wiExpense').value) || 0;
        var oneOff   = parseFloat(document.getElementById('wiOneOff').value)  || 0;

        var adjNet = fc.forecast.net.map(function (n, i) {
            return n + dIncome - dExpense - (i === 0 ? oneOff : 0);
        });

        var running = fc.balance.current;
        var balances = adjNet.map(function (n) {
            running += n;
            return Math.round(running * 100) / 100;
        });

        return { adjNet: adjNet, balances: balances, changed: dIncome || dExpense || oneOff };
    }

    function renderWhatIfText(w) {
        var out  = document.getElementById('whatifOut');
        if (!out) return;
        var last = w.balances[w.balances.length - 1];
        var base = fc.balance.projected[fc.balance.projected.length - 1];
        var diff = Math.round((last - base) * 100) / 100;

        var html = 'Projected balance in 3 months: <strong>' + fmtMoney(last) + '</strong>';
        if (w.changed) {
            html += ' (' + (diff >= 0 ? '+' : '') + fmtMoney(diff) + ' vs. the current trend)';
        } else {
            html += ' - adjust the fields above to simulate a financial decision.';
        }
        out.innerHTML = html;
    }

    function drawForecast() {
        var histLabels = fc.history.months.map(monthLabel);
        var futLabels  = fc.forecast.months.map(monthLabel);
        var labels     = histLabels.concat(futLabels);
        var H          = fc.history.months.length;
        var w          = whatIfSeries();

        // History series padded with nulls in the future zone, and vice versa.
        var histIncome  = fc.history.income.concat(new Array(futLabels.length).fill(null));
        var histExpense = fc.history.expense.concat(new Array(futLabels.length).fill(null));

        // Bridge: the projection lines start from the last historical point.
        var futIncome  = new Array(Math.max(0, H - 1)).fill(null)
            .concat([fc.history.income[H - 1]]).concat(fc.forecast.income);
        var dExp   = parseFloat(document.getElementById('wiExpense').value) || 0;
        var oneOff = parseFloat(document.getElementById('wiOneOff').value)  || 0;
        var futExpense = new Array(Math.max(0, H - 1)).fill(null)
            .concat([fc.history.expense[H - 1]]).concat(
                fc.forecast.expense.map(function (e, i) {
                    return Math.max(0, e + dExp + (i === 0 ? oneOff : 0));
                }));

        var ds = [
            {
                label: 'Income',
                data: histIncome,
                borderColor: '#059669',
                backgroundColor: 'rgba(5,150,105,.08)',
                tension: .35, fill: false, pointRadius: 3
            },
            {
                label: 'Expenses',
                data: histExpense,
                borderColor: '#e11d48',
                backgroundColor: 'rgba(225,29,72,.06)',
                tension: .35, fill: false, pointRadius: 3
            },
            {
                label: 'Income (forecast)',
                data: futIncome,
                borderColor: '#059669',
                borderDash: [6, 5],
                tension: .35, pointRadius: 4, pointStyle: 'rectRot', fill: false
            },
            {
                label: 'Expenses (forecast)',
                data: futExpense,
                borderColor: '#e11d48',
                borderDash: [6, 5],
                tension: .35, pointRadius: 4, pointStyle: 'rectRot', fill: false
            }
        ];

        if (forecastChart) {
            forecastChart.data.labels = labels;
            forecastChart.data.datasets.forEach(function (d, i) { d.data = ds[i].data; });
            forecastChart.update();
        } else {
            forecastChart = new Chart(forecastEl, {
                type: 'line',
                data: { labels: labels, datasets: ds },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { tooltip: moneyTooltip, legend: { position: 'bottom' } },
                    scales: {
                        y: { beginAtZero: true, ticks: moneyTicks, grid: { color: '#eef2f7' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        renderWhatIfText(w);
    }

    api('api/forecast').then(function (d) {
        fc = d;

        // Model-fit badge (R² of the net-balance trend, 0..1)
        var badge = document.getElementById('forecastConfidence');
        if (badge) {
            badge.textContent = 'model fit: R² = ' + (d.model.r2 || 0).toFixed(2);
        }

        drawForecast();

        ['wiIncome', 'wiExpense', 'wiOneOff'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', drawForecast);
        });

        var reset = document.getElementById('whatifReset');
        if (reset) {
            reset.addEventListener('click', function () {
                ['wiIncome', 'wiExpense', 'wiOneOff'].forEach(function (id) {
                    document.getElementById(id).value = 0;
                });
                drawForecast();
            });
        }
    }).catch(function () {});
})();
