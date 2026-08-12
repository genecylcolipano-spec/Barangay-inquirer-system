// Dashboard frontend logic: charts, AJAX cancel, and flash handling

async function loadChartJs() {
    if (window.Chart) return window.Chart;
    return new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        s.onload = () => resolve(window.Chart);
        s.onerror = reject;
        document.head.appendChild(s);
    });
}

function showFlash(message, type = 'success', timeout = 5000) {
    const container = document.querySelector('main.col-md-9.p-4');
    if (!container) return;

    const wrapper = document.createElement('div');
    wrapper.className = `alert alert-${type} alert-dismissible fade show`;
    wrapper.role = 'alert';
    wrapper.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    container.insertBefore(wrapper, container.firstChild);

    if (timeout) {
        setTimeout(() => {
            try { wrapper.classList.remove('show'); wrapper.classList.add('hide'); wrapper.remove(); } catch(e){}
        }, timeout);
    }
}

function autoDismissExistingAlerts(timeout = 5000) {
    document.querySelectorAll('.alert').forEach(el => {
        setTimeout(() => { try { el.remove(); } catch(e){} }, timeout);
    });
}

function initAjaxCancel(token, charts) {
    document.querySelectorAll('form[data-ajax="cancel"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!confirm('Cancel this request?')) return;
            const action = form.getAttribute('action');

            fetch(action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'ok') {
                    const tr = form.closest('tr');
                    if (tr) {
                        const statusTd = tr.querySelector('td:nth-child(4)');
                        if (statusTd) statusTd.innerHTML = '<span class="badge bg-secondary">Cancelled</span>';
                        const actionTd = tr.querySelector('td:nth-child(6)');
                        if (actionTd) actionTd.innerHTML = '';
                    }

                    showFlash(data.message || 'Request cancelled.', 'success');

                    // Update charts if available
                    if (charts && charts.pie) {
                        const ds = charts.pie.data.datasets[0].data;
                        // Assuming order [pending, approved, rejected, cancelled]
                        if (ds[0] > 0) ds[0] = ds[0] - 1;
                        ds[3] = (ds[3] || 0) + 1;
                        charts.pie.update();
                    }
                } else {
                    showFlash(data.message || 'Unable to cancel.', 'danger');
                }
            })
            .catch(()=>{
                showFlash('Network error.', 'danger');
            });
        });
    });
}

async function initChartsAndBehaviors(data) {
    await loadChartJs();

    const charts = { pie: null, spark: null };

    const statusCtxEl = document.getElementById('requestStatusChart');
    if (statusCtxEl) {
        const ctx = statusCtxEl.getContext('2d');
        charts.pie = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Pending','Approved','Rejected','Cancelled'],
                datasets: [{
                    data: [data.pending ?? 0, data.approved ?? 0, data.rejected ?? 0, data.cancelled ?? 0],
                    backgroundColor: ['#f6c23e','#1cc88a','#e74a3b','#6c757d'],
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } }
        });
    }

    const sparkCtxEl = document.getElementById('requestsSparkline');
    if (sparkCtxEl) {
        const ctx = sparkCtxEl.getContext('2d');
        const weekly = data.weekly ?? [];
        charts.spark = new Chart(ctx, {
            type: 'line',
            data: { labels: weekly.map((_,i)=>i+1), datasets: [{ data: weekly, borderColor: '#4e73df', backgroundColor: 'rgba(78,115,223,0.05)', fill: true, tension: 0.3, pointRadius: 0 }] },
            options: { responsive: true, scales: { x: { display: false }, y: { display: false } }, plugins: { legend: { display: false } } }
        });
    }

    return charts;
}

export async function initDashboard() {
    // Wait for DOM
    if (document.readyState === 'loading') await new Promise(r => document.addEventListener('DOMContentLoaded', r));

    const data = window.DASHBOARD_DATA || {};
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

    autoDismissExistingAlerts(5000);

    const charts = await initChartsAndBehaviors(data);

    initAjaxCancel(token, charts);
}

// When module is loaded, attempt to init (if data already present)
if (document.readyState !== 'loading') {
    // Defer to next tick to allow window.DASHBOARD_DATA to be set by blade
    setTimeout(() => { if (window.DASHBOARD_DATA) initDashboard(); }, 0);
} else {
    document.addEventListener('DOMContentLoaded', () => { setTimeout(() => { if (window.DASHBOARD_DATA) initDashboard(); }, 0); });
}
