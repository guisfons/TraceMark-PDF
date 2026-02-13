<?php
/**
 * Plugin Name: TraceMark PDF
 * Plugin URI:  https://github.com/guisfons/TraceMark-PDF
 * Description: Distribuição segura de PDFs com marca d'água dinâmica para representantes da empresa.
 * Version:     1.0.0
 * Author:      Guilherme Silva Fonseca
 * Author URI:  https://github.com/guisfons
 * Text Domain: tracemark-pdf
 */

if (!defined('ABSPATH')) {
    exit; // Sair se acessado diretamente.
}

// Definir Constantes
define('TRACEMARK_PDF_PATH', plugin_dir_path(__FILE__));
define('TRACEMARK_PDF_URL', plugin_dir_url(__FILE__));
define('TRACEMARK_PDF_VERSION', '1.0.0');

// Autoload de dependências
if (file_exists(TRACEMARK_PDF_PATH . 'vendor/autoload.php')) {
    require_once TRACEMARK_PDF_PATH . 'vendor/autoload.php';
}

// Incluir arquivos críticos
require_once TRACEMARK_PDF_PATH . 'includes/class-tracemark-cpt.php';
require_once TRACEMARK_PDF_PATH . 'includes/class-tracemark-meta.php';
require_once TRACEMARK_PDF_PATH . 'includes/class-tracemark-protection.php';
require_once TRACEMARK_PDF_PATH . 'includes/class-tracemark-watermark.php';
require_once TRACEMARK_PDF_PATH . 'includes/class-tracemark-user.php';

// Inicializar o plugin
function tracemark_pdf_init()
{
    new TraceMark_CPT();
    new TraceMark_Meta();
    new TraceMark_Protection();
    new TraceMark_User();
}
add_action('plugins_loaded', 'tracemark_pdf_init');

// Hook de Ativação
register_activation_hook(__FILE__, 'tracemark_pdf_activate');
function tracemark_pdf_activate()
{
    // Garantir que o CPT esteja registrado antes de limpar as regras
    if (!class_exists('TraceMark_CPT')) {
        require_once TRACEMARK_PDF_PATH . 'includes/class-tracemark-cpt.php';
    }
    $cpt = new TraceMark_CPT();
    $cpt->register_post_type();
    flush_rewrite_rules();
}

// Hook de Desativação
register_deactivation_hook(__FILE__, 'tracemark_pdf_deactivate');
function tracemark_pdf_deactivate()
{
    flush_rewrite_rules();
}
