<?php
defined('BASEPATH') or exit('No direct script access allowed');

$html = '
<style>
    body { font-family: sans-serif; font-size: 10px; }
    h1 { color: #333; }
    h2 { color: #555; font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
    .chart-image { max-width: 100%; height: auto; margin-top: 10px; margin-bottom: 20px; text-align: center; }
    .page-break { page-break-after: always; }
    .grid-container { width: 100%; }
    .grid-item { display: inline-block; width: 48%; vertical-align: top; margin: 1%; }
</style>
';

// Cabeçalho e Estatísticas
$html .= '<h1>' . $title . '</h1>';
$html .= '<p>' . _l('total_leads') . ': ' . ($stats['total_leads'] ?? 0) . '</p>';
$html .= '<p>' . _l('converted_leads') . ': ' . ($stats['converted_leads'] ?? 0) . '</p>';

// Gráficos em grade
$html .= '<div class="grid-container">';
$html .= '<div class="grid-item"><h2>' . _l('default_chart_leads_status') . '</h2><img src="' . ($charts['status_chart'] ?? '') . '" class="chart-image"></div>';
$html .= '<div class="grid-item"><h2>' . _l('default_chart_leads_source') . '</h2><img src="' . ($charts['source_chart'] ?? '') . '" class="chart-image"></div>';
$html .= '</div>';

$html .= '<div class="page-break"></div>'; // Quebra de página

// Gráficos maiores
$html .= '<h2>Funil de Leads</h2>';
$html .= '<img src="' . ($charts['funnel_chart'] ?? '') . '" class="chart-image">';

$html .= '<h2>' . _l('default_chart_leads_monthly') . '</h2>';
$html .= '<img src="' . ($charts['timeline_chart'] ?? '') . '" class="chart-image">';

$html .= '<div class="page-break"></div>'; // Quebra de página para a tabela

// Tabela de Dados Completa
$html .= '<h2>' . _l('showing_results') . '</h2>';
$html .= '
<table>
    <thead>
        <tr>
            <th>' . _l('search_lead_name') . '</th>
            <th>' . _l('search_email') . '</th>
            <th>' . _l('search_company') . '</th>
            <th>' . _l('search_status') . '</th>
            <th>' . _l('search_source') . '</th>
            <th>' . _l('search_assigned') . '</th>
        </tr>
    </thead>
    <tbody>';

foreach ($table_data as $row) {
    $html .= '
    <tr>
        <td>' . htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($row['company'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($row['source'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($row['assigned_to'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
    </tr>';
}

$html .= '</tbody></table>';

echo $html;