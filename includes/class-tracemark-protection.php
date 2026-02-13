<?php
/**
 * Lida com Controle de Acesso e Exibição do Arquivo.
 */

class TraceMark_Protection
{

    public function __construct()
    {
        add_action('template_redirect', array($this, 'control_access'));
        add_filter('the_content', array($this, 'append_download_button'));
    }

    public function control_access()
    {
        if (!is_singular('restricted_pdf')) {
            return;
        }

        // 1. Verificar Login
        if (!is_user_logged_in()) {
            auth_redirect();
        }

        $user_id = get_current_user_id();
        $post_id = get_the_ID();

        // 2. Verificar Permissões
        if (!$this->user_has_access($user_id, $post_id)) {
            wp_die(__('Você não tem permissão para visualizar este documento.', 'tracemark-pdf'), __('Acesso Negado', 'tracemark-pdf'), array('response' => 403));
        }

        // 3. Lidar com Pedido de Download
        if (isset($_GET['tm_download']) && '1' === $_GET['tm_download']) {
            $this->serve_pdf($post_id, $user_id);
        }
    }

    private function user_has_access($user_id, $post_id)
    {
        // Admin sempre tem acesso
        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        // Verificar se o usuário tem a role 'contributor'
        $user = get_userdata($user_id);
        if ($user && in_array('contributor', (array) $user->roles)) {
            return true;
        }

        return false;
    }

    private function serve_pdf($post_id, $user_id)
    {
        $file_path = get_post_meta($post_id, '_tracemark_pdf_path', true);

        if (!$file_path || !file_exists($file_path)) {
            wp_die(__('Arquivo não encontrado.', 'tracemark-pdf'));
        }

        // Gerar Marca d'água
        $watermarker = new TraceMark_Watermark();
        $pdf_content = $watermarker->generate($file_path, $user_id, $post_id);

        if (!$pdf_content) {
            wp_die(__('Erro ao gerar PDF.', 'tracemark-pdf'));
        }

        $filename = basename($file_path);
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdf_content;
        exit;
    }

    public function append_download_button($content)
    {
        if (!is_singular('restricted_pdf') || !is_user_logged_in()) {
            return $content;
        }

        $user_id = get_current_user_id();
        $post_id = get_the_ID();

        if (!$this->user_has_access($user_id, $post_id)) {
            return $content;
        }

        // Buscar dados
        $user = get_userdata($user_id);
        $logo_path = get_user_meta($user_id, '_tracemark_user_logo', true);
        $company_name = get_user_meta($user_id, '_tracemark_company_name', true);

        $output = '<div class="tracemark-container" style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: #f9f9f9; text-align: center;">';

        if ($logo_path && file_exists($logo_path)) {
            $type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $data = file_get_contents($logo_path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $output .= '<img src="' . $base64 . '" alt="Logo da Empresa" style="max-width: 200px; margin-bottom: 20px;">';
        }

        if ($company_name) {
            $output .= '<h4>' . esc_html($company_name) . '</h4>';
        }

        $output .= '<h3>' . __('Documento Restrito', 'tracemark-pdf') . '</h3>';
        $output .= '<p><strong>' . __('Representante:', 'tracemark-pdf') . '</strong> ' . esc_html($user->display_name) . '</p>';
        $output .= '<p><strong>' . __('Email:', 'tracemark-pdf') . '</strong> ' . esc_html($user->user_email) . '</p>';

        $url = add_query_arg('tm_download', '1', get_permalink());

        $output .= '<a href="' . esc_url($url) . '" class="button button-primary button-large" style="display:inline-block; padding:10px 20px; background:#0073aa; color:#fff; text-decoration:none; border-radius:4px; margin-top:20px;">';
        $output .= __('Baixar / Visualizar Documento', 'tracemark-pdf');
        $output .= '</a>';
        $output .= '</div>';

        return $content . $output;
    }
}
