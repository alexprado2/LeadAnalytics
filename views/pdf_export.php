<?php
defined('BASEPATH') or exit('No direct script access allowed');

$html = '
<style>
    body { font-family: sans-serif; }
    h1 { color: #333; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>
';

$html .= '<h1>' . $title . '</h1>';
$html .= '<p>' . _l('total_leads') . ': ' . $stats['total_leads'] . '</p>';
$html .= '<p>' . _l('converted_leads') . ': ' . $stats['converted_leads'] . '</p>';
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
        <td>' . $row['name'] . '</td>
        <td>' . $row['email'] . '</td>
        <td>' . $row['company'] . '</td>
        <td>' . $row['status'] . '</td>
        <td>' . $row['source'] . '</td>
        <td>' . $row['assigned_to'] . '</td>
    </tr>';
}

$html .= '</tbody></table>';

echo $html;