<?php
/**
 * Gerencia Meta Boxes para Upload de Arquivo e campos customizados.
 */

class TraceMark_Meta
{

	public function __construct()
	{
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_meta'));
		add_action('post_edit_form_tag', array($this, 'update_edit_form'));

		// Term meta para Taxonomia País
		add_action('pais_add_form_fields', array($this, 'add_pais_flag_field'), 10, 2);
		add_action('pais_edit_form_fields', array($this, 'edit_pais_flag_field'), 10, 2);
		add_action('created_pais', array($this, 'save_pais_flag_field'), 10, 2);
		add_action('edited_pais', array($this, 'save_pais_flag_field'), 10, 2);
		add_action('pais_term_edit_form_tag', array($this, 'update_edit_form'));
	}

	public function add_meta_boxes()
	{
		// Meta box para Boletim Semanal
		add_meta_box(
			'tracemark_boletim_details',
			'Detalhes do Boletim',
			array($this, 'render_boletim_meta_box'),
			'boletim_semanal',
			'normal',
			'high'
		);

		// Meta box para Relatório por País
		add_meta_box(
			'tracemark_relatorio_details',
			'Detalhes do Relatório',
			array($this, 'render_relatorio_meta_box'),
			'relatorio_pais',
			'normal',
			'high'
		);
	}

	public function update_edit_form()
	{
		echo ' enctype="multipart/form-data"';
	}

	/**
	 * Meta Box: Boletim Semanal
	 */
	public function render_boletim_meta_box($post)
	{
		wp_nonce_field('tracemark_save_meta', 'tracemark_meta_nonce');

		$file_path = get_post_meta($post->ID, '_tracemark_pdf_path', true);
		$filename = $file_path ? basename($file_path) : '';
		$custom_title = get_post_meta($post->ID, '_tracemark_custom_title', true);
		?>

		<p><strong>Título do Documento:</strong></p>
		<input type="text" name="tracemark_custom_title" value="<?php echo esc_attr($custom_title); ?>" style="width: 100%;"
			placeholder="Ex: Boletim Semanal - Semana 07/2026">

		<hr>

		<p><strong>Arquivo PDF:</strong></p>
		<?php if ($filename): ?>
			<p>Arquivo Atual: <code><?php echo esc_html($filename); ?></code></p>
		<?php endif; ?>
		<input type="file" name="tracemark_pdf_file" accept=".pdf">
		<p class="description">Faça upload do PDF do boletim. Os arquivos são armazenados de forma segura.</p>

		<?php
	}

	/**
	 * Meta Box: Relatório por País
	 */
	public function render_relatorio_meta_box($post)
	{
		wp_nonce_field('tracemark_save_meta', 'tracemark_meta_nonce');

		$file_path = get_post_meta($post->ID, '_tracemark_pdf_path', true);
		$filename = $file_path ? basename($file_path) : '';
		$custom_title = get_post_meta($post->ID, '_tracemark_custom_title', true);
		?>

		<p><strong>Título do Documento:</strong></p>
		<input type="text" name="tracemark_custom_title" value="<?php echo esc_attr($custom_title); ?>" style="width: 100%;"
			placeholder="Ex: Relatório Estratégico - Brasil">

		<hr>

		<p><strong>Arquivo PDF:</strong></p>
		<?php if ($filename): ?>
			<p>Arquivo Atual: <code><?php echo esc_html($filename); ?></code></p>
		<?php endif; ?>
		<input type="file" name="tracemark_pdf_file" accept=".pdf">
		<p class="description">Faça upload do PDF do relatório. Para substituir, basta enviar um novo arquivo.</p>

		<p class="description" style="color: #d63638; font-weight: bold;">
			Selecione o País na caixa lateral "Países" →
		</p>

		<?php
	}

	public function save_meta($post_id)
	{
		if (!isset($_POST['tracemark_meta_nonce']) || !wp_verify_nonce($_POST['tracemark_meta_nonce'], 'tracemark_save_meta')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$post_type = get_post_type($post_id);
		if (!in_array($post_type, array('boletim_semanal', 'relatorio_pais'))) {
			return;
		}

		// Salvar Título Customizado
		if (isset($_POST['tracemark_custom_title'])) {
			update_post_meta($post_id, '_tracemark_custom_title', sanitize_text_field($_POST['tracemark_custom_title']));
		}

		// Diretório Seguro
		$upload_dir = wp_upload_dir();
		$secure_dir = $upload_dir['basedir'] . '/tracemark-secure';

		if (!file_exists($secure_dir)) {
			mkdir($secure_dir, 0755, true);
			file_put_contents($secure_dir . '/.htaccess', 'Deny from all');
		}

		// Upload PDF
		if (!empty($_FILES['tracemark_pdf_file']['name'])) {
			$file = $_FILES['tracemark_pdf_file'];
			if ($file['error'] === UPLOAD_ERR_OK) {
				$file_type = wp_check_filetype($file['name']);
				if ('pdf' === $file_type['ext']) {
					$new_filename = 'doc_' . $post_id . '_' . time() . '.pdf';
					$destination = $secure_dir . '/' . $new_filename;
					if (move_uploaded_file($file['tmp_name'], $destination)) {
						// Deletar o arquivo antigo
						$old_file = get_post_meta($post_id, '_tracemark_pdf_path', true);
						if ($old_file && file_exists($old_file))
							unlink($old_file);
						update_post_meta($post_id, '_tracemark_pdf_path', $destination);
					}
				}
			}
		}
	}

	/**
	 * Campos Adicionais: Taxonomia País (Add)
	 */
	public function add_pais_flag_field($taxonomy)
	{
		?>
		<div class="form-field term-group">
			<label for="tracemark_flag_file">Bandeira do País (Upload)</label>
			<input type="file" name="tracemark_flag_file" id="tracemark_flag_file" accept="image/*">
			<p>Selecione uma imagem para a bandeira.</p>
		</div>

		<div class="form-field term-group">
			<label for="tracemark_flag_url">Ou URL/Dashicon da Bandeira</label>
			<input type="text" name="tracemark_flag_url" id="tracemark_flag_url" value="">
			<p>Se não fizer upload, pode inserir a URL de uma imagem ou nome de um Dashicon (ex: dashicons-flag).</p>
		</div>

		<div class="form-field term-group">
			<label for="tracemark_country_url">URL do País (Link Externo)</label>
			<input type="url" name="tracemark_country_url" id="tracemark_country_url" value="">
			<p>Link para onde o relatório do país deve redirecionar no frontend.</p>
		</div>
		<?php
	}

	/**
	 * Campos Adicionais: Taxonomia País (Edit)
	 */
	public function edit_pais_flag_field($term, $taxonomy)
	{
		$flag_url = get_term_meta($term->term_id, '_tracemark_flag_url', true);
		$country_url = get_term_meta($term->term_id, '_tracemark_country_url', true);
		?>
		<tr class="form-field term-group-wrap">
			<th scope="row"><label for="tracemark_flag_file">Bandeira do País (Upload)</label></th>
			<td>
				<?php if ($flag_url && filter_var($flag_url, FILTER_VALIDATE_URL)): ?>
					<div style="margin-bottom: 10px;">
						<img src="<?php echo esc_url($flag_url); ?>" alt="Bandeira atual"
							style="max-width: 100px; height: auto; border: 1px solid #ddd;">
					</div>
				<?php endif; ?>
				<input type="file" name="tracemark_flag_file" id="tracemark_flag_file" accept="image/*">
				<p class="description">Upload de um novo arquivo para substituir a bandeira atual.</p>
			</td>
		</tr>

		<tr class="form-field term-group-wrap">
			<th scope="row"><label for="tracemark_flag_url">Ou URL/Dashicon da Bandeira</label></th>
			<td>
				<input type="text" name="tracemark_flag_url" id="tracemark_flag_url" value="<?php echo esc_attr($flag_url); ?>">
				<p class="description">Pode inserir a URL de uma imagem ou o nome de um Dashicon (ex: dashicons-flag).</p>
			</td>
		</tr>

		<tr class="form-field term-group-wrap">
			<th scope="row"><label for="tracemark_country_url">URL do País (Link Externo)</label></th>
			<td>
				<input type="url" name="tracemark_country_url" id="tracemark_country_url"
					value="<?php echo esc_attr($country_url); ?>" style="width: 100%;">
				<p class="description">Link para onde o relatório do país deve redirecionar no frontend.</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Salvar Term Meta: Taxonomia País
	 */
	public function save_pais_flag_field($term_id, $tt_id)
	{
		if (isset($_POST['tracemark_flag_url'])) {
			update_term_meta($term_id, '_tracemark_flag_url', sanitize_text_field($_POST['tracemark_flag_url']));
		}

		if (isset($_POST['tracemark_country_url'])) {
			update_term_meta($term_id, '_tracemark_country_url', esc_url_raw($_POST['tracemark_country_url']));
		}

		// Upload de arquivo de bandeira
		if (!empty($_FILES['tracemark_flag_file']['name'])) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			$uploadedfile = $_FILES['tracemark_flag_file'];
			$upload_overrides = array('test_form' => false);
			$movefile = wp_handle_upload($uploadedfile, $upload_overrides);

			if ($movefile && !isset($movefile['error'])) {
				update_term_meta($term_id, '_tracemark_flag_url', $movefile['url']);
			}
		}
	}
}
