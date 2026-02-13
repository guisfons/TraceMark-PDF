<?php
/**
 * Gerencia Meta Boxes para Upload de Arquivo.
 */

class TraceMark_Meta
{

	public function __construct()
	{
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_meta'));
		add_action('post_edit_form_tag', array($this, 'update_edit_form'));
	}

	public function add_meta_boxes()
	{
		add_meta_box(
			'tracemark_pdf_details',
			__('Detalhes do PDF', 'tracemark-pdf'),
			array($this, 'render_meta_box'),
			'restricted_pdf',
			'normal',
			'high'
		);
	}

	public function update_edit_form()
	{
		echo ' enctype="multipart/form-data"';
	}

	public function render_meta_box($post)
	{
		wp_nonce_field('tracemark_save_meta', 'tracemark_meta_nonce');

		// Arquivo PDF
		$file_path = get_post_meta($post->ID, '_tracemark_pdf_path', true);
		$filename = $file_path ? basename($file_path) : '';
		?>

		<!-- SEÇÃO DE ARQUIVO -->
		<p><strong><?php _e('Arquivo PDF:', 'tracemark-pdf'); ?></strong></p>
		<?php if ($filename): ?>
			<p>
				<?php _e('Arquivo Atual:', 'tracemark-pdf'); ?> <code><?php echo esc_html($filename); ?></code>
			</p>
		<?php endif; ?>
		<input type="file" name="tracemark_pdf_file" accept=".pdf">
		<p class="description">
			<?php _e('Faça upload de um novo PDF para substituir o atual. Os arquivos são armazenados de forma segura.', 'tracemark-pdf'); ?>
		</p>
		<p class="description" style="color: #d63638; font-weight: bold;">
			<?php _e('Acesso: Todos os usuários com o cargo "Contributor" podem acessar este documento.', 'tracemark-pdf'); ?>
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
						$old_file = get_post_meta($post_id, '_tracemark_pdf_path', true);
						if ($old_file && file_exists($old_file))
							unlink($old_file);
						update_post_meta($post_id, '_tracemark_pdf_path', $destination);
					}
				}
			}
		}
	}
}
