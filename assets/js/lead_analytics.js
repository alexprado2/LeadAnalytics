/**
 * Lead Analytics JavaScript
 */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded.');
        return;
    }

    class LeadAnalytics {
        constructor() {
            this.filters = {};
            this.charts = {};
            this.init();
        }

        init() {
            this.initCharts();
            this.bindEvents();
            this.loadData();
        }

        bindEvents() {
            document.getElementById('apply-filters')?.addEventListener('click', () => this.applyFilters());
            document.getElementById('clear-filters')?.addEventListener('click', () => this.clearFilters());
            document.getElementById('export-pdf')?.addEventListener('click', () => this.export('pdf'));
            document.getElementById('export-excel')?.addEventListener('click', () => this.export('excel'));
            document.getElementById('export-csv')?.addEventListener('click', () => this.export('csv'));
        }
        
        initCharts() {
            this.charts.leads_by_status = this.createChart('leads_by_status', 'doughnut', 'Leads por Status');
            this.charts.leads_by_source = this.createChart('leads_by_source', 'bar', 'Leads por Fonte');
            this.charts.leads_timeline = this.createChart('leads_timeline', 'line', 'Timeline de Leads');
        }

        createChart(canvasId, type, label) {
            const ctx = document.getElementById(canvasId)?.getContext('2d');
            if (!ctx) return null;
            return new Chart(ctx, {
                type: type,
                data: { labels: [], datasets: [{ label, data: [] }] },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { display: type !== 'bar' } }
                }
            });
        }
        
        collectFilters() {
            this.filters = {};
            document.querySelectorAll('.analytics-filter').forEach(el => {
                if (el.value) this.filters[el.name] = el.value;
            });
        }

        applyFilters() {
            this.collectFilters();
            this.loadData();
        }

        clearFilters() {
            document.querySelectorAll('.analytics-filter').forEach(el => {
                if (el.tagName === 'SELECT') {
                    $(el).selectpicker('val', '');
                } else {
                    el.value = '';
                }
            });
            this.filters = {};
            this.loadData();
        }

        loadData() {
            fetch(`${admin_url}lead_analytics/get_analytics_data`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.filters)
            })
            .then(res => res.json())
            .then(data => {
                this.updateStats(data.stats);
                this.updateAllCharts(data.charts);
                this.updateTable(data.table_data);
            })
            .catch(err => console.error('Error loading data:', err));
        }

        updateStats(stats) {
            if (!stats) return;
            document.getElementById('total-leads').textContent = stats.total_leads || 0;
            document.getElementById('new-leads').textContent = stats.new_leads || 0;
            document.getElementById('converted-leads').textContent = stats.converted_leads || 0;
            document.getElementById('conversion-rate').textContent = `${stats.conversion_rate || 0}%`;
        }

        updateAllCharts(chartData) {
            if (!chartData) return;
            this.updateChart(this.charts.leads_by_status, chartData.leads_by_status, ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E7E9ED']);
            this.updateChart(this.charts.leads_by_source, chartData.leads_by_source, '#667eea');
            this.updateChart(this.charts.leads_timeline, chartData.leads_timeline, '#764ba2');
        }

        updateChart(chart, data, backgroundColor) {
            if (!chart || !data) return;
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.data;
            chart.data.datasets[0].backgroundColor = backgroundColor;
            chart.update();
        }

        updateTable(tableData) {
            const tbody = document.getElementById('analytics-table-body');
            if (!tbody || !tableData) return;
            tbody.innerHTML = tableData.map(row => `
                <tr>
                    <td>${row.name || ''}</td>
                    <td>${row.email || ''}</td>
                    <td>${row.company || ''}</td>
                    <td>${row.status || ''}</td>
                    <td>${row.source || ''}</td>
                    <td>${row.assigned_to || ''}</td>
                    <td>${row.dateadded ? new Date(row.dateadded).toLocaleDateString() : ''}</td>
                </tr>
            `).join('');
        }
        
        export(format) {
             if (!has_permission('leads', 'export')) {
                alert('Você não tem permissão para exportar.');
                return;
            }
            const params = new URLSearchParams(this.filters).toString();
            window.open(`${admin_url}lead_analytics/export_${format}?${params}`, '_blank');
        }
    }
    new LeadAnalytics();
});
