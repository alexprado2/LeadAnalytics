<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lead_analytics extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!has_permission('leads', '', 'view')) {
            access_denied('leads');
        }
        $this->load->model('lead_analytics_model');
    }

    public function index()
    {
        $data['title']         = _l('lead_analytics_dashboard');
        $data['lead_statuses'] = $this->leads_model->get_status();
        $data['lead_sources']  = $this->leads_model->get_source();
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('lead_analytics/dashboard', $data);
    }

    public function get_analytics_data()
    {
        $filters = $this->input->post(); 
        $filters = $this->sanitize_filters($filters);

        $data = [
            'stats'      => $this->lead_analytics_model->get_dashboard_stats($filters),
            'charts'     => $this->lead_analytics_model->get_chart_data($filters),
            'table_data' => $this->lead_analytics_model->get_table_data($filters, true) // Apply limit
        ];

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function export_pdf()
    {
        if (!has_permission('leads', '', 'export')) {
            access_denied('leads');
        }

        $post_data = $this->input->post(null, false);

        $filters = $post_data;
        unset($filters['status_chart'], $filters['funnel_chart'], $filters['source_chart'], $filters['timeline_chart']);
        
        $filters = $this->sanitize_filters($filters);

        $data['title']       = _l('lead_analytics') . ' ' . _l('reports');
        $data['stats']       = $this->lead_analytics_model->get_dashboard_stats($filters);
        $data['table_data']  = $this->lead_analytics_model->get_table_data($filters, false);

        $data['charts'] = [
            'status_chart'   => $post_data['status_chart'] ?? '',
            'funnel_chart'   => $post_data['funnel_chart'] ?? '',
            'source_chart'   => $post_data['source_chart'] ?? '',
            'timeline_chart' => $post_data['timeline_chart'] ?? '',
        ];
        
        $this->load->library('lead_analytics/pdf');
        $view = $this->load->view('lead_analytics/pdf_export', $data, true);
        $this->pdf->WriteHTML($view);
        $this->pdf->Output(_l('lead_analytics') . '_report.pdf', 'D');
    }

    public function export_excel()
    {
        if (!has_permission('leads', '', 'export')) {
            access_denied('leads');
        }

        $filters = $this->input->get(null, true);
        $filters = $this->sanitize_filters($filters);
        $data = $this->lead_analytics_model->get_table_data($filters, false);

        $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);
        $sheet = $this->excel->getActiveSheet();
        $sheet->setTitle(_l('lead_analytics'));

        $headers = [_l('search_lead_name'), _l('search_email'), _l('search_company'), _l('search_status'), _l('search_source'), _l('search_assigned'), _l('search_date_created')];
        $sheet->fromArray($headers, null, 'A1');

        $rows = [];
        foreach ($data as $lead) {
            $rows[] = [
                $lead['name'], $lead['email'], $lead['company'], $lead['status'],
                $lead['source'], $lead['assigned_to'], $lead['dateadded']
            ];
        }
        $sheet->fromArray($rows, null, 'A2');

        $filename = _l('lead_analytics') . '_export.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $writer->save('php://output');
    }

    public function export_csv()
    {
        if (!has_permission('leads', '', 'export')) {
            access_denied('leads');
        }

        $filters = $this->input->get(null, true);
        $filters = $this->sanitize_filters($filters);
        $data = $this->lead_analytics_model->get_table_data($filters, false);

        $filename = _l('lead_analytics') . '_export.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [_l('search_lead_name'), _l('search_email'), _l('search_company'), _l('search_status'), _l('search_source'), _l('search_assigned'), _l('search_date_created')]);

        foreach ($data as $lead) {
            fputcsv($output, [$lead['name'], $lead['email'], $lead['company'], $lead['status'], $lead['source'], $lead['assigned_to'], $lead['dateadded']]);
        }
        fclose($output);
    }
    
     private function sanitize_filters($filters)
    {
        $sanitized = [];
        // A chave 'limit' foi adicionada aos filtros permitidos
        $allowed_filters = ['status', 'source', 'assigned', 'company', 'date_from', 'date_to', 'limit'];

        foreach ($allowed_filters as $filter) {
            if (isset($filters[$filter]) && $filters[$filter] !== '') {
                $sanitized[$filter] = $this->security->xss_clean($filters[$filter]);
            }
        }
        return $sanitized;
    }
}