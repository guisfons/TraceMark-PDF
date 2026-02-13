<?php
/**
 * Gera Marca d'água em PDFs usando FPDF/FPDI.
 */

use setasign\Fpdi\Fpdi;

/**
 * Extensão do FPDI para suportar transparência (Alpha).
 */
class TraceMark_FPDI extends Fpdi
{
    protected $extgstates = array();

    function SetAlpha($alpha, $bm = 'Normal')
    {
        $gs = $this->AddExtGState(array('ca' => $alpha, 'CA' => $alpha, 'BM' => '/' . $bm));
        $this->SetExtGState($gs);
    }

    function AddExtGState($parms)
    {
        $n = count($this->extgstates) + 1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }

    function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }

    function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++) {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_put('<</Type /ExtGState');
            foreach ($this->extgstates[$i]['parms'] as $k => $v)
                $this->_put('/' . $k . ' ' . $v);
            $this->_put('>>');
            $this->_put('endobj');
        }
    }

    function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_put('/ExtGState <<');
        foreach ($this->extgstates as $k => $v)
            $this->_put('/GS' . $k . ' ' . $v['n'] . ' 0 R');
        $this->_put('>>');
    }

    function _putresources()
    {
        $this->_putextgstates();
        parent::_putresources();
    }
}

class TraceMark_Watermark
{

    public function generate($source_file, $user_id, $post_id)
    {
        if (!file_exists($source_file)) {
            return false;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Obter Detalhes do Usuário
        $email = $user->user_email;

        // Tentar obter Nome da Empresa do Perfil
        $company = get_user_meta($user_id, '_tracemark_company_name', true);
        if (!$company)
            $company = get_user_meta($user_id, 'company_name', true);
        if (!$company)
            $company = get_user_meta($user_id, 'billing_company', true);
        if (!$company)
            $company = get_user_meta($user_id, 'company', true);
        if (!$company)
            $company = 'Empresa Desconhecida';

        // Configurar Data/Hora Brasil
        $date_brasil = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        $footer_text = sprintf("Cópia Rastreada - %s (%s) - %s", $email, $company, $date_brasil->format('d/m/Y H:i'));

        // Obter Logo (Do Perfil do Usuário)
        $logo_path = get_user_meta($user_id, '_tracemark_user_logo', true);

        // Inicializar FPDI Customizado
        $pdf = new TraceMark_FPDI();
        $pdf->SetAutoPageBreak(false); // Importante para evitar páginas extras ao desenhar no rodapé

        try {
            $page_count = $pdf->setSourceFile($source_file);
        } catch (Exception $e) {
            return false;
        }

        for ($page_no = 1; $page_no <= $page_count; $page_no++) {
            $template_id = $pdf->importPage($page_no);
            $size = $pdf->getTemplateSize($template_id);

            $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));

            // 1. Inserir o conteúdo original primeiro
            $pdf->useTemplate($template_id);

            // 2. Inserir Logo Centralizado com Opacidade (30%)
            if ($logo_path && file_exists($logo_path)) {
                $max_dim = 80;
                $info = getimagesize($logo_path);
                if ($info) {
                    $w_px = $info[0];
                    $h_px = $info[1];
                    $ratio = $w_px / $h_px;
                    if ($ratio > 1) {
                        $w = $max_dim;
                        $h = $max_dim / $ratio;
                    } else {
                        $h = $max_dim;
                        $w = $max_dim * $ratio;
                    }
                } else {
                    $w = $max_dim;
                    $h = 0;
                }

                $x = ($size['width'] - $w) / 2;
                $y = ($size['height'] - $h) / 2;

                $pdf->SetAlpha(0.3);
                $pdf->Image($logo_path, $x, $y, $w, $h);
                $pdf->SetAlpha(1.0);
            }

            // 3. Marca d'água de rastreabilidade (Rodapé de Todas as Páginas)
            $pdf->SetFont('Helvetica', 'I', 8);
            $pdf->SetTextColor(120, 120, 120);
            $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $footer_text);

            // Centralizar o rodapé a 10mm do fundo
            $pdf->SetXY(0, $size['height'] - 10);
            $pdf->Cell($size['width'], 10, $text, 0, 0, 'C');
        }

        return $pdf->Output('S');
    }
}
