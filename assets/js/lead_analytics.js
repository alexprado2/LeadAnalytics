/**
 * Lead Analytics JavaScript com ApexCharts
 */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts is not loaded.');
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
           document.getElementById('apply-limit')?.addEventListener('click', () => this.loadTableDataOnly());
        }

        initCharts() {
            // Opções padrão para todos os gráficos para manter a consistência
            const defaultOptions = {
                chart: {
                    height: 350,
                    toolbar: { show: true },
                    fontFamily: 'inherit',
                },
                dataLabels: { enabled: true },
                legend: { show: true, position: 'bottom' }
            };

            // Gráfico de Status (Rosca/Doughnut)
            const statusOptions = {
                ...defaultOptions,
                chart: { ...defaultOptions.chart, type: 'donut' },
                series: [],
                labels: [],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: { width: '100%' },
                        legend: { position: 'bottom' }
                    }
                }]
            };
            this.charts.leads_by_status = new ApexCharts(document.querySelector("#leads_by_status"), statusOptions);
            this.charts.leads_by_status.render();

            // Gráfico de Funil
            const funnelOptions = {
        series: [{ name: 'Leads', data: [] }],
        chart: {
            type: 'bar', // Mudamos o tipo para 'bar'
            height: 350,
            fontFamily: 'inherit'
        },
        plotOptions: {
            bar: {
                horizontal: true,
                isFunnel: true, // Usamos a opção especial isFunnel
                borderRadius: 0,
                barHeight: '80%',
            },
        },
        dataLabels: {
            enabled: true,
            formatter: function(val, opt) {
                // Os rótulos virão do eixo X (xaxis) neste caso
                return opt.w.globals.labels[opt.dataPointIndex] + ':  ' + val
            },
            dropShadow: {
                enabled: true,
            },
        },
        // Para o gráfico de barras, os rótulos ficam em xaxis.categories
        xaxis: {
            categories: [],
        },
        legend: {
            show: false,
        }
    };
    this.charts.leads_funnel_chart = new ApexCharts(document.querySelector("#leads_funnel_chart"), funnelOptions);
    this.charts.leads_funnel_chart.render();

            // Gráfico de Fonte (Barras)
            const sourceOptions = {
                ...defaultOptions,
                chart: { ...defaultOptions.chart, type: 'bar' },
                series: [{ name: 'Total de Leads', data: [] }],
                xaxis: { categories: [] },
                plotOptions: { bar: { horizontal: true, distributed: true } },
                legend: { show: false }
            };
            this.charts.leads_by_source = new ApexCharts(document.querySelector("#leads_by_source"), sourceOptions);
            this.charts.leads_by_source.render();

            // Gráfico de Timeline (Linha)
            const timelineOptions = {
                ...defaultOptions,
                chart: { ...defaultOptions.chart, type: 'line', zoom: { enabled: false } },
                series: [{ name: 'Novos Leads', data: [] }],
                xaxis: { type: 'category', categories: [] },
                stroke: { curve: 'smooth' }
            };
            this.charts.leads_timeline = new ApexCharts(document.querySelector("#leads_timeline"), timelineOptions);
            this.charts.leads_timeline.render();
        }
        
        collectFilters() {
            this.filters = {};
            // Usando jQuery para ser mais compatível com a biblioteca selectpicker
            $('.analytics-filter').each((index, el) => {
                // Usar $(el).val() é mais confiável para ler o valor de um selectpicker
                const value = $(el).val(); 
                if (value && value !== '') {
                    this.filters[el.name] = Array.isArray(value) ? value.join(',') : value;
                }
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
        
        loadTableDataOnly() {
                this.collectFilters(); // Coleta todos os filtros atuais, incluindo o novo limite

                const postData = new FormData();
                for (const key in this.filters) {
                    postData.append(key, this.filters[key]);
                }

                if (typeof csrfData !== 'undefined') {
                    postData.append(csrfData.token_name, csrfData.hash);
                }

                fetch(`${admin_url}lead_analytics/get_analytics_data`, {
                    method: 'POST',
                    body: postData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text) });
                    }
                    return res.json();
                })
                .then(data => {
                    // A diferença chave está aqui: apenas a tabela é atualizada
                    this.updateTable(data.table_data);
                })
                .catch(err => {
                    console.error('Error loading table data:', err);
                    document.getElementById('analytics-table-body').innerHTML = `<tr><td colspan="7" style="color: red; text-align: center;">Falha ao carregar os dados da tabela.</td></tr>`;
                });
            }

        loadData() {
            const postData = new FormData();
            for (const key in this.filters) {
                postData.append(key, this.filters[key]);
            }

            if (typeof csrfData !== 'undefined') {
                postData.append(csrfData.token_name, csrfData.hash);
            }

            fetch(`${admin_url}lead_analytics/get_analytics_data`, {
                method: 'POST',
                body: postData
            })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => { throw new Error(text) });
                }
                return res.json();
            })
            .then(data => {
                this.updateStats(data.stats);
                this.updateAllCharts(data.charts);
                this.updateTable(data.table_data);
            })
            .catch(err => {
                console.error('Error loading data:', err);
                document.getElementById('analytics-table-body').innerHTML = `<tr><td colspan="7" style="color: red; text-align: center;">Falha ao carregar os dados. Verifique o console do navegador (F12) para detalhes técnicos.</td></tr>`;
            });
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
        
            // A lógica para os dados de status agora é mais elaborada
            if (chartData.leads_by_status) {
                const statusData = chartData.leads_by_status;
        
                // 1. Extrai os dados e rótulos REAIS do resultado
                const realLabels = statusData.map(item => item.label);
                const realData = statusData.map(item => parseInt(item.data, 10));
        
                // 2. Atualiza o gráfico de Rosca (Doughnut) com os dados REAIS
                if (this.charts.leads_by_status) {
                    this.charts.leads_by_status.updateOptions({
                        series: realData,
                        labels: realLabels
                    });
                }
        
                // 3. Lógica para o Funil com formato fixo
                if (this.charts.leads_funnel_chart) {
                    // 3a. Gera dados "FALSOS" para criar a forma de pirâmide perfeita
                    // Ex: [100, 85, 70, 55, 40]
                    const fakeShapeData = [];
                    const totalSteps = realLabels.length > 0 ? realLabels.length : 1;
                    for (let i = 0; i < totalSteps; i++) {
                        // Cria uma sequência descendente para a forma do funil
                        const value = 100 - (i * (80 / totalSteps));
                        fakeShapeData.push(value);
                    }
        
                    // 3b. Atualiza o gráfico de funil
                    this.charts.leads_funnel_chart.updateOptions({
                        // Usa os dados FALSOS para desenhar a forma
                        series: [{
                            data: fakeShapeData
                        }],
                        // Usa os rótulos REAIS
                        xaxis: {
                            categories: realLabels
                        },
                        // Usa o formatador para mostrar os dados REAIS nos rótulos
                        dataLabels: {
                            formatter: function(val, opt) {
                                const seriesIndex = opt.seriesIndex;
                                const dataPointIndex = opt.dataPointIndex;
                                const originalValue = realData[dataPointIndex]; // Pega o valor real
        
                                // Retorna o rótulo com o valor real
                                return realLabels[dataPointIndex] + ':  ' + originalValue;
                            }
                        }
                    });
                }
            }

    // A lógica para os outros gráficos permanece a mesma
    if (this.charts.leads_by_source && chartData.leads_by_source) {
        this.charts.leads_by_source.updateOptions({
            series: [{ data: chartData.leads_by_source.data }],
            xaxis: { categories: chartData.leads_by_source.labels }
        });
    }
    
    if (this.charts.leads_timeline && chartData.leads_timeline) {
        this.charts.leads_timeline.updateOptions({
            series: [{ data: chartData.leads_timeline.data }],
            xaxis: { categories: chartData.leads_timeline.labels }
        });
    }
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