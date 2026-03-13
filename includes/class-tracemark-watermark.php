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

    /**
     * Suporte para Rotação (usado para Marca d'água diagonal)
     */
    protected $angle = 0;

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}

class TraceMark_Watermark
{

    public function generate($source_file, $user_id, $post_id)
    {
        if (!file_exists($source_file)) {
            return false;
        }

        // Normalizar o PDF para versão 1.4 (FPDI compatibility)
        // Se a normalização falhar ou não for suportada, cai de volta para o arquivo original
        $normalized_file = $this->normalize_pdf($source_file);
        $work_file = $normalized_file ? $normalized_file : $source_file;

        $user = get_userdata($user_id);
        if (!$user) {
            if ($normalized_file && file_exists($normalized_file)) {
                @unlink($normalized_file);
            }
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
        try {
            $date_brasil = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        } catch (\Throwable $e) {
            $date_brasil = new DateTime();
        }
        $footer_text = sprintf("Cópia Rastreada - %s (%s) - %s", $email, $company, $date_brasil->format('d/m/Y H:i'));

        // Obter Logo (Do Perfil do Usuário)
        $logo_path = get_user_meta($user_id, '_tracemark_user_logo', true);

        // Inicializar FPDI Customizado
        $pdf = new TraceMark_FPDI();
        $pdf->SetAutoPageBreak(false); // Importante para evitar páginas extras ao desenhar no rodapé

        try {
            $page_count = $pdf->setSourceFile($work_file);
        } catch (\Throwable $e) {
            // Qualquer erro lançado pelo FPDI ou pelo PHP (ex: memory limit ou pdf corrompido)
            if ($normalized_file && file_exists($normalized_file)) {
                @unlink($normalized_file);
            }
            return false;
        }

        for ($page_no = 1; $page_no <= $page_count; $page_no++) {
            try {
                $template_id = $pdf->importPage($page_no);
                $size = $pdf->getTemplateSize($template_id);

                $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));

                // 1. Inserir o conteúdo original primeiro
                $pdf->useTemplate($template_id);

                // 2. Inserir Logo Centralizado com Opacidade (15%) - Reduzi de 30% para não atrapalhar
                if ($logo_path && file_exists($logo_path)) {
                    $max_dim = 100;
                    $info = @getimagesize($logo_path);
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

                // 3. Marca d'água de Fundo (Diagonal - Empresa + Email)
                $pdf->SetFont('Helvetica', 'B', 40);
                $pdf->SetTextColor(200, 200, 200);
                $pdf->SetAlpha(0.3);

                $watermark_text = sprintf("%s\n%s", strtoupper($company), strtolower($email));
                $watermark_text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $watermark_text);

                // Centralizar e Rotacionar
                $pdf->Rotate(45, $size['width'] / 2, $size['height'] / 2);

                // Usar MultiCell para permitir quebra de linha se for muito longo
                $pdf->SetXY(($size['width'] / 2) - 100, ($size['height'] / 2) - 20);
                $pdf->MultiCell(200, 15, $watermark_text, 0, 'C');

                $pdf->Rotate(0); // Resetar rotação
                $pdf->SetAlpha(1.0);

                // 4. Marca d'água de rastreabilidade (Rodapé de Todas as Páginas)
                $pdf->SetFont('Helvetica', 'I', 8);
                $pdf->SetTextColor(120, 120, 120);
                $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $footer_text);

                // Centralizar o rodapé a 10mm do fundo
                $pdf->SetXY(0, $size['height'] - 10);
                $pdf->Cell($size['width'], 10, $text, 0, 0, 'C');
            } catch (\Throwable $e) {
                // Se der erro ao processar uma página especificamente
                continue;
            }
        }

        try {
            $result = $pdf->Output('S');
        } catch (\Throwable $e) {
            $result = false;
        }

        // Limpar arquivo temporário se existir
        if ($normalized_file && file_exists($normalized_file)) {
            @unlink($normalized_file);
        }

        return $result;
    }

    /**
     * Usa Ghostscript para converter o PDF para a versão 1.4,
     * que é compatível com a versão gratuita do FPDI.
     */
    private function normalize_pdf($source_file)
    {
        if (!function_exists('exec') || !function_exists('escapeshellcmd') || !function_exists('escapeshellarg')) {
            return false;
        }

        $gs_path = '/usr/bin/gs';
        if (!is_executable($gs_path)) {
            return false;
        }

        // Verifica diretório temporário para ser mais seguro
        $tmp_dir = sys_get_temp_dir();
        if (!is_dir($tmp_dir) || !is_writable($tmp_dir)) {
            return false;
        }

        // Suprime warnings caso ocorra open_basedir restriction, etc.
        $base_file = @tempnam($tmp_dir, 'tm_pdf_');
        if (!$base_file) {
            return false;
        }

        $output_file = $base_file . '.pdf';

        if (!@rename($base_file, $output_file)) {
            @unlink($base_file);
            return false;
        }

        // Comando do Ghostscript para PDF 1.4
        $command = sprintf(
            '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>&1',
            escapeshellcmd($gs_path),
            escapeshellarg($output_file),
            escapeshellarg($source_file)
        );

        @exec($command, $output, $return_var);

        if ($return_var === 0 && file_exists($output_file) && filesize($output_file) > 0) {
            return $output_file;
        }

        if (file_exists($output_file)) {
            @unlink($output_file);
        }

        return false;
    }
}
