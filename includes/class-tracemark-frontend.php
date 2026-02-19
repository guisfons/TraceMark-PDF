<?php
/**
 * Shortcodes para listagem dos documentos no frontend.
 *
 * [boletins_semanais] — Lista com histórico de boletins
 * [relatorios_pais]   — Cards agrupados por país
 */

class TraceMark_Frontend
{

    public function __construct()
    {
        add_shortcode('boletins_semanais', array($this, 'render_boletins'));
        add_shortcode('relatorios_pais', array($this, 'render_relatorios'));
    }

    /**
     * Verifica se o usuário tem acesso (contributor ou admin).
     */
    private function user_has_access()
    {
        if (!is_user_logged_in())
            return false;

        $user_id = get_current_user_id();
        if (user_can($user_id, 'manage_options'))
            return true;

        $user = get_userdata($user_id);
        return ($user && in_array('contributor', (array) $user->roles));
    }

    /**
     * Shortcode [boletins_semanais]
     * Tabela com histórico: Data | Título | Ação
     */
    public function render_boletins($atts)
    {
        if (!$this->user_has_access()) {
            return '<p style="text-align:center; color:#999;">Você precisa estar logado como representante para acessar esta área.</p>';
        }

        $boletins = get_posts(array(
            'post_type' => 'boletim_semanal',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
        ));

        if (empty($boletins)) {
            return '<p style="text-align:center; color:#999;">Nenhum boletim disponível.</p>';
        }

        $output = '<div class="tracemark-docs-wrapper">';
        $output .= '<style>
			.tracemark-docs-wrapper {
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			}
			.tm-bulletin-list {
				list-style: none;
				padding: 0;
				margin: 0;
			}
			.tm-bulletin-row {
				display: flex;
				align-items: center;
				padding: 15px 0;
				border-bottom: 1px dotted #ccc;
				gap: 20px;
			}
			.tm-bulletin-row:last-child {
				border-bottom: none;
			}
			.tm-icon-col {
				flex-shrink: 0;
				display: flex;
				align-items: center;
			}
			.tm-icon-col svg {
				width: 35px;
				height: 45px;
				fill: #81b441;
			}
			.tm-content-col {
				flex-grow: 1;
				display: flex;
				align-items: center;
				gap: 25px;
			}
			.tm-date {
				color: #81b441;
				font-size: 15px;
				white-space: nowrap;
				min-width: 140px;
			}
			.tm-title {
				font-weight: 700;
				font-size: 16px;
				color: #3e5266;
				line-height: 1.4;
			}
			.tm-action-col {
				flex-shrink: 0;
			}
			.tm-download-btn {
				background: #81b441;
				color: #fff !important;
				text-decoration: none;
				padding: 8px 18px;
				border-radius: 4px;
				font-size: 13px;
				font-weight: 700;
				display: flex;
				align-items: center;
				gap: 8px;
				text-transform: uppercase;
				transition: background 0.2s;
			}
			.tm-download-btn:hover {
				background: #6a9435;
			}
			.tm-download-btn svg {
				width: 16px;
				height: 16px;
				fill: currentColor;
			}
			@media (max-width: 768px) {
				.tm-bulletin-row {
					flex-wrap: wrap;
					gap: 15px;
				}
				.tm-content-col {
					flex-direction: column;
					align-items: flex-start;
					gap: 5px;
					width: calc(100% - 60px);
				}
				.tm-date {
					min-width: auto;
				}
				.tm-action-col {
					width: 100%;
					display: flex;
					justify-content: flex-end;
				}
			}
		</style>';

        $output .= '<div class="tm-bulletin-list">';

        foreach ($boletins as $boletim) {
            $custom_title = get_post_meta($boletim->ID, '_tracemark_custom_title', true);
            $title = !empty($custom_title) ? $custom_title : $boletim->post_title;

            // Format date: "novembro 17, 2025"
            $date_str = get_the_date('F j, Y', $boletim->ID);
            // Ensure month is lowercase as in the image
            $date_parts = explode(' ', $date_str);
            if (count($date_parts) >= 1) {
                $date_parts[0] = mb_strtolower($date_parts[0]);
                $date = implode(' ', $date_parts);
            } else {
                $date = $date_str;
            }

            $link = get_permalink($boletim->ID);

            $output .= '<div class="tm-bulletin-row">';

            // Icon Column
            $output .= '<div class="tm-icon-col">';
            $output .= '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M14,2H6C4.89,2 4,2.89 4,4V20C4,21.11 4.89,22 6,22H18C19.11,22 20,21.11 20,20V8L14,2M18,20H6V4H13V9H18V20M16,11V13H8V11H16M16,15V17H8V15H16M14,7V4.5L16.5,7H14Z"/></svg>';
            $output .= '</div>';

            // Content Column
            $output .= '<div class="tm-content-col">';
            $output .= '<div class="tm-date">' . esc_html($date) . '</div>';
            $output .= '<div class="tm-title">' . esc_html($title) . '</div>';
            $output .= '</div>';

            // Action Column
            $output .= '<div class="tm-action-col">';
            $output .= '<a class="tm-download-btn" href="' . esc_url($link) . '">';
            $output .= '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5,20H19V18H5M19,9H15V3H9V9H5L12,16L19,9Z"/></svg>';
            $output .= 'Download';
            $output .= '</a>';
            $output .= '</div>';

            $output .= '</div>';
        }

        $output .= '</div>'; // list
        $output .= '</div>'; // wrapper

        return $output;
    }

    /**
     * Shortcode [relatorios_pais]
     * Cards agrupados por país (1 relatório por país)
     */
    public function render_relatorios($atts)
    {
        if (!$this->user_has_access()) {
            return '<p style="text-align:center; color:#999;">Você precisa estar logado como representante para acessar esta área.</p>';
        }

        $paises = get_terms(array(
            'taxonomy' => 'pais',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ));

        if (empty($paises) || is_wp_error($paises)) {
            return '<p style="text-align:center; color:#999;">Nenhum relatório disponível.</p>';
        }

        $output = '<div class="tracemark-docs-wrapper">';
        $output .= '<style>
			.tracemark-docs-wrapper {
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			}
			.tracemark-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
				gap: 25px;
				margin-top: 10px;
			}
			.tracemark-card {
				border: 1px solid #e0e0e0;
				border-radius: 12px;
				overflow: hidden;
				background: #fff;
				transition: transform 0.2s, box-shadow 0.2s;
				display: flex;
				flex-direction: column;
				box-shadow: 0 2px 5px rgba(0,0,0,0.05);
			}
			.tracemark-card:hover {
				transform: translateY(-4px);
				box-shadow: 0 8px 20px rgba(0,0,0,0.1);
			}
			.tracemark-card-header {
				background: #1d2327;
				color: #fff !important;
				padding: 16px 20px;
				font-size: 16px;
				font-weight: 700;
				display: flex;
				align-items: center;
				gap: 12px;
				text-decoration: none;
			}
			a.tracemark-card-header:hover {
				background: #2c3338;
			}
			.tracemark-card-header .tm-icon {
				width: 28px;
				height: 28px;
				display: flex;
				align-items: center;
				justify-content: center;
				overflow: hidden;
				border-radius: 4px;
				background: rgba(255,255,255,0.1);
			}
			.tracemark-card-header .tm-icon img {
				max-width: 100%;
				max-height: 100%;
				object-fit: cover;
			}
			.tracemark-card-header .tm-icon span.dashicons {
				font-size: 20px;
			}
			.tracemark-card-body {
				padding: 20px;
				flex-grow: 1;
				display: flex;
				flex-direction: column;
			}
			.tracemark-card-body .tm-doc-title {
				font-size: 15px;
				font-weight: 700;
				color: #3e5266;
				margin-bottom: 8px;
				line-height: 1.4;
			}
			.tracemark-card-body .tm-doc-date {
				font-size: 12px;
				color: #888;
				margin-bottom: 20px;
			}
			.tracemark-card-body .tm-btn {
				margin-top: auto;
				display: block;
				padding: 10px 15px;
				background: #81b441;
				color: #fff !important;
				text-decoration: none;
				border-radius: 4px;
				font-size: 13px;
				font-weight: 700;
				text-align: center;
				transition: background 0.2s;
				text-transform: uppercase;
			}
			.tracemark-card-body .tm-btn:hover {
				background: #6a9435;
			}
		</style>';

        $output .= '<div class="tracemark-grid">';

        foreach ($paises as $pais) {
            // Buscar o relatório mais recente deste país (apenas 1)
            $relatorios = get_posts(array(
                'post_type' => 'relatorio_pais',
                'posts_per_page' => 1,
                'post_status' => 'publish',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'pais',
                        'field' => 'term_id',
                        'terms' => $pais->term_id,
                    ),
                ),
            ));

            if (empty($relatorios))
                continue;

            $relatorio = $relatorios[0];
            $custom_title = get_post_meta($relatorio->ID, '_tracemark_custom_title', true);
            $title = !empty($custom_title) ? $custom_title : $relatorio->post_title;
            $link = get_permalink($relatorio->ID);
            $updated = get_the_modified_date('d/m/Y', $relatorio->ID);

            // Ícone/Bandeira Customizada
            $flag_url = get_term_meta($pais->term_id, '_tracemark_flag_url', true);
            $country_url = get_term_meta($pais->term_id, '_tracemark_country_url', true);
            $icon_html = '';

            if ($flag_url) {
                if (filter_var($flag_url, FILTER_VALIDATE_URL)) {
                    $icon_html = '<img src="' . esc_url($flag_url) . '" alt="' . esc_attr($pais->name) . '">';
                } else {
                    $icon_html = '<span class="dashicons ' . esc_attr($flag_url) . '"></span>';
                }
            } else {
                $icon_html = '<span class="dashicons dashicons-flag"></span>';
            }

            $output .= '<div class="tracemark-card">';

            // Header (pode ser link se houver URL do país)
            $header_tag = $country_url ? 'a' : 'div';
            $header_attr = $country_url ? ' href="' . esc_url($country_url) . '"' : '';

            $output .= '<' . $header_tag . ' class="tracemark-card-header"' . $header_attr . '>';
            $output .= '<div class="tm-icon">' . $icon_html . '</div>';
            $output .= esc_html($pais->name);
            $output .= '</' . $header_tag . '>';

            $output .= '<div class="tracemark-card-body">';
            $output .= '<div class="tm-doc-title">' . esc_html($title) . '</div>';
            $output .= '<div class="tm-doc-date">Atualizado em: ' . esc_html($updated) . '</div>';
            $output .= '<a class="tm-btn" href="' . esc_url($link) . '">Ver Relatório</a>';
            $output .= '</div>';
            $output .= '</div>';
        }

        $output .= '</div>'; // grid
        $output .= '</div>'; // wrapper

        return $output;
    }
}
