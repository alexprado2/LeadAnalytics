<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(__DIR__ . '/../vendor/autoload.php');

use Mpdf\Mpdf;

class Pdf extends Mpdf
{
    public function __construct($config = [])
    {
        // Configuração padrão se nenhuma for passada
        if (empty($config)) {
            $config = [
                'mode'              => 'utf-8',
                'format'            => 'A4',
                'default_font_size' => 10,
                'default_font'      => 'dejavusans',
                'margin_left'       => 15,
                'margin_right'      => 15,
                'margin_top'        => 16,
                'margin_bottom'     => 16,
                'margin_header'     => 9,
                'margin_footer'     => 9,
                'tempDir'           => FCPATH . 'temp',
            ];
        }
        
        parent::__construct($config);
    }
}