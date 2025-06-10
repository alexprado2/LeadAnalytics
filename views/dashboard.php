<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content lead-analytics-container">
        
        <div class="lead-analytics-header">
            <h1><?php echo _l('lead_analytics_dashboard'); ?></h1>
            <p><?php echo _l('lead_analytics_description'); ?></p>
        </div>

        <div class="analytics-stats-grid">
            <div class="analytics-stat-card"><p><?php echo _l('total_leads'); ?></p><h3 id="total-leads">0</h3></div>
            <div class="analytics-stat-card"><p><?php echo _l('new_leads'); ?> (30d)</p><h3 id="new-leads">0</h3></div>
            <div class="analytics-stat-card"><p><?php echo _l('converted_leads'); ?></p><h3 id="converted-leads">0</h3></div>
            <div class="analytics-stat-card"><p><?php echo _l('conversion_rate'); ?></p><h3 id="conversion-rate">0%</h3></div>
        </div>

        <div class="analytics-filters">
            <h4><i class="fa fa-filter"></i> <?php echo _l('btn_apply_filters'); ?></h4>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="status"><?php echo _l('search_status'); ?></label>
                    <select name="status" id="status" class="form-control selectpicker analytics-filter" data-live-search="true"><option value=""><?php echo _l('all'); ?></option><?php foreach($lead_statuses as $s){ echo "<option value='{$s['id']}'>{$s['name']}</option>"; } ?></select>
                </div>
                <div class="filter-group">
                    <label for="source"><?php echo _l('search_source'); ?></label>
                    <select name="source" id="source" class="form-control selectpicker analytics-filter" data-live-search="true"><option value=""><?php echo _l('all'); ?></option><?php foreach($lead_sources as $s){ echo "<option value='{$s['id']}'>{$s['name']}</option>"; } ?></select>
                </div>
                <div class="filter-group">
                    <label for="assigned"><?php echo _l('search_assigned'); ?></label>
                    <select name="assigned" id="assigned" class="form-control selectpicker analytics-filter" data-live-search="true"><option value=""><?php echo _l('all'); ?></option><?php foreach($staff_members as $s){ echo "<option value='{$s['staffid']}'>{$s['full_name']}</option>"; } ?></select>
                </div>
                <!-- <div class="filter-group">
                    <label for="company"><?php echo _l('search_company'); ?></label>
                    <input type="text" name="company" id="company" class="form-control analytics-filter">
                </div> -->
                <div class="filter-group">
                    <label for="date_from"><?php echo _l('date_range_custom'); ?> (Início)</label>
                    <input type="date" name="date_from" id="date_from" class="form-control analytics-filter">
                </div>
                 <div class="filter-group">
                    <label for="date_to"><?php echo _l('date_range_custom'); ?> (Fim)</label>
                    <input type="date" name="date_to" id="date_to" class="form-control analytics-filter">
                </div>
            </div>
            <div class="filter-actions">
                <button id="clear-filters" class="btn-clear"><i class="fa fa-refresh"></i> <?php echo _l('btn_clear'); ?></button>
                <button id="apply-filters" class="btn-filter"><i class="fa fa-check"></i> <?php echo _l('btn_apply_filters'); ?></button>
            </div>
        </div>
        
        <div class="analytics-export">
            <div class="export-buttons">
                <button id="export-pdf" class="btn-export"><i class="fa fa-file-pdf-o"></i> <?php echo _l('export_pdf'); ?></button>
                <button id="export-excel" class="btn-export"><i class="fa fa-file-excel-o"></i> <?php echo _l('export_excel'); ?></button>
                <button id="export-csv" class="btn-export"><i class="fa fa-file-text-o"></i> <?php echo _l('export_csv'); ?></button>
            </div>
        </div>

        <div class="analytics-charts">
            <div class="chart-container">
                <div class="chart-header"><h4 class="chart-title"><?php echo _l('default_chart_leads_status'); ?></h4></div>
                <div id="leads_by_status"></div>
            </div>
            <div class="chart-container" style="grid-column: 1 / -1;">
                <div class="chart-header"><h4 class="chart-title">Funil de Leads</h4></div>
                <div id="leads_funnel_chart"></div>
            </div>
            <div class="chart-container">
                <div class="chart-header"><h4 class="chart-title"><?php echo _l('default_chart_leads_source'); ?></h4></div>
                <div id="leads_by_source"></div>
            </div>
            <div class="chart-container" style="grid-column: 1 / -1;">
                <div class="chart-header"><h4 class="chart-title"><?php echo _l('default_chart_leads_monthly'); ?></h4></div>
                <div id="leads_timeline"></div>
            </div>
        </div>

        <div class="analytics-table-container">
            <h4><?php echo _l('showing_results'); ?></h4>
            <div class="table-responsive">
                <table class="table analytics-table">
                    <thead><tr><th><?php echo _l('search_lead_name'); ?></th><th><?php echo _l('search_email'); ?></th><th><?php echo _l('search_company'); ?></th><th><?php echo _l('search_status'); ?></th><th><?php echo _l('search_source'); ?></th><th><?php echo _l('search_assigned'); ?></th><th><?php echo _l('search_date_created'); ?></th></tr></thead>
                    <tbody id="analytics-table-body"></tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<?php init_tail(); ?>
<script>
    // Function to check permission in JS
    function has_permission(feature, capability, user_id) {
        // This is a simplified check. For real security, rely on server-side checks.
        // Here we are checking if the user has a general permission for 'export' on 'leads'.
        return <?php echo has_permission('leads', '', 'export') ? 'true' : 'false'; ?>;
    }
</script>