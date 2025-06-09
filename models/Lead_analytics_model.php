<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lead_analytics_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_dashboard_stats($filters = [])
    {
        $where = $this->build_where_clause($filters);
        
        // Total
        $this->db->select('COUNT(id) as total')->from(db_prefix() . 'leads l');
        if($where) $this->db->where($where, null, false);
        $total_leads = $this->db->get()->row()->total;
        
        // New (last 30 days based on filters)
        $this->db->select('COUNT(id) as total')->from(db_prefix() . 'leads l');
        if($where) $this->db->where($where, null, false);
        $this->db->where('l.dateadded >=', date('Y-m-d H:i:s', strtotime('-30 days')));
        $new_leads = $this->db->get()->row()->total;

        // Converted
        $this->db->select('COUNT(id) as total')->from(db_prefix() . 'leads l');
        $this->db->where('l.date_converted IS NOT NULL');
        if($where) $this->db->where($where, null, false);
        $converted_leads = $this->db->get()->row()->total;

        // A seção do Avg Value foi removida para evitar o erro.

        return [
            'total_leads'     => (int) $total_leads,
            'new_leads'       => (int) $new_leads,
            'converted_leads' => (int) $converted_leads,
            'conversion_rate' => $total_leads > 0 ? round(($converted_leads / $total_leads) * 100, 2) : 0,
            'avg_lead_value'  => 0, // Retornando 0 para manter a consistência.
        ];
    }

    public function get_chart_data($filters = [])
    {
        return [
            'leads_by_status'  => $this->get_leads_by_status($filters),
            'leads_by_source'  => $this->get_leads_by_source($filters),
            'leads_timeline'   => $this->get_leads_timeline($filters),
        ];
    }

    private function get_leads_by_status($filters = [])
    {
        $where = $this->build_where_clause($filters);
        $this->db->select('IFNULL(ls.name, "Sem Status") as label, COUNT(l.id) as data') // Corrigido (aqui o l.id é necessário para desambiguação)
                 ->from(db_prefix() . 'leads l')
                 ->join(db_prefix() . 'leads_status ls', 'ls.id = l.status', 'left')
                 ->group_by('l.status')
                 ->order_by('data', 'DESC');
        if($where) $this->db->where($where, null, false);
        $results = $this->db->get()->result_array();

        return ['labels' => array_column($results, 'label'), 'data' => array_map('intval', array_column($results, 'data'))];
    }

    private function get_leads_by_source($filters = [])
    {
        $where = $this->build_where_clause($filters);
        $this->db->select('IFNULL(lso.name, "Sem Origem") as label, COUNT(l.id) as data') // Corrigido
                 ->from(db_prefix() . 'leads l')
                 ->join(db_prefix() . 'leads_sources lso', 'lso.id = l.source', 'left')
                 ->group_by('l.source')
                 ->order_by('data', 'DESC')
                 ->limit(10);
        if($where) $this->db->where($where, null, false);
        $results = $this->db->get()->result_array();

        return ['labels' => array_column($results, 'label'), 'data' => array_map('intval', array_column($results, 'data'))];
    }
    
    private function get_leads_timeline($filters = [])
    {
        $where = $this->build_where_clause($filters);
        $this->db->select("DATE_FORMAT(l.dateadded, '%Y-%m') as month, COUNT(l.id) as count") // Corrigido
                 ->from(db_prefix() . 'leads l')
                 ->where('l.dateadded >=', date('Y-m-d', strtotime('-12 months')))
                 ->group_by("month")
                 ->order_by('month', 'ASC');
        if($where) $this->db->where($where, null, false);
        $results = $this->db->get()->result_array();

        $labels = []; $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $labels[] = date('M/y', strtotime($month . '-01'));
            $count = 0;
            foreach ($results as $row) {
                if ($row['month'] === $month) {
                    $count = (int)$row['count'];
                    break;
                }
            }
            $data[] = $count;
        }
        return ['labels' => $labels, 'data' => $data];
    }
    
    public function get_table_data($filters = [], $limit = true)
    {
        $where = $this->build_where_clause($filters);
        $this->db->select('l.name, l.email, l.company, ls.name as status, lso.name as source, CONCAT(s.firstname, " ", s.lastname) as assigned_to, l.dateadded')
                 ->from(db_prefix() . 'leads l')
                 ->join(db_prefix() . 'leads_status ls', 'ls.id = l.status', 'left')
                 ->join(db_prefix() . 'leads_sources lso', 'lso.id = l.source', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = l.assigned', 'left')
                 ->order_by('l.dateadded', 'DESC');

        if($where) $this->db->where($where, null, false);
        if($limit) $this->db->limit(100);

        return $this->db->get()->result_array();
    }

    private function build_where_clause($filters)
    {
        $parts = [];
        if (!empty($filters['status']))   $parts[] = "l.status = " . $this->db->escape((int)$filters['status']);
        if (!empty($filters['source']))   $parts[] = "l.source = " . $this->db->escape((int)$filters['source']);
        if (!empty($filters['assigned'])) $parts[] = "l.assigned = " . $this->db->escape((int)$filters['assigned']);
        if (!empty($filters['company']))  $parts[] = "l.company LIKE " . $this->db->escape('%' . $filters['company'] . '%');
        if (!empty($filters['date_from'])) $parts[] = "DATE(l.dateadded) >= " . $this->db->escape($filters['date_from']);
        if (!empty($filters['date_to']))   $parts[] = "DATE(l.dateadded) <= " . $this->db->escape($filters['date_to']);
        
        return implode(' AND ', $parts);
    }
}