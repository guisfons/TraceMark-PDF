<?php
/**
 * Gerencia campos personalizados no Perfil do Usuário (Logo da Empresa).
 */

class TraceMark_User
{

    public function __construct()
    {
        add_action('show_user_profile', array($this, 'render_user_profile_fields'));
        add_action('edit_user_profile', array($this, 'render_user_profile_fields'));
        add_action('personal_options_update', array($this, 'save_user_profile_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_profile_fields'));

        // Necessário para permitir upload de arquivos no formulário de usuário
        add_action('user_edit_form_tag', array($this, 'update_user_form_enctype'));
    }

    public function update_user_form_enctype()
    {
        echo ' enctype="multipart/form-data"';
    }

    public function render_user_profile_fields($user)
    {
        $logo_path = get_user_meta($user->ID, '_tracemark_user_logo', true);
        $logo_name = $logo_path ? basename($logo_path) : '';
        $company_name = get_user_meta($user->ID, '_tracemark_company_name', true);
        ?>
        <h3><?php _e('TraceMark PDF - Configurações da Empresa', 'tracemark-pdf'); ?></h3>

        <table class="form-table">
            <tr>
                <th><label for="tracemark_user_logo"><?php _e('Logo da Empresa', 'tracemark-pdf'); ?></label></th>
                <td>
                    <?php if ($logo_name): ?>
                        <p>
                            <?php _e('Logo Atual:', 'tracemark-pdf'); ?> <code><?php echo esc_html($logo_name); ?></code>
                            <br>
                            <label><input type="checkbox" name="tracemark_delete_logo" value="1">
                                <?php _e('Excluir logo atual', 'tracemark-pdf'); ?></label>
                        </p>
                    <?php endif; ?>
                    <input type="file" name="tracemark_user_logo" id="tracemark_user_logo" accept="image/png, image/jpeg">
                    <p class="description">
                        <?php _e('Faça upload do logo da empresa (PNG ou JPG). Este logo será usado como papel timbrado (fundo) nos PDFs que você baixar.', 'tracemark-pdf'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="tracemark_company_name"><?php _e('Nome da Empresa', 'tracemark-pdf'); ?></label></th>
                <td>
                    <input type="text" name="tracemark_company_name" id="tracemark_company_name"
                        value="<?php echo esc_attr($company_name); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('O nome da empresa que aparecerá na marca d\'água do PDF.', 'tracemark-pdf'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_user_profile_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        // Salvar Nome da Empresa
        if (isset($_POST['tracemark_company_name'])) {
            update_user_meta($user_id, '_tracemark_company_name', sanitize_text_field($_POST['tracemark_company_name']));
        }

        // Diretório Seguro
        $upload_dir = wp_upload_dir();
        $secure_dir = $upload_dir['basedir'] . '/tracemark-secure';

        if (!file_exists($secure_dir)) {
            mkdir($secure_dir, 0755, true);
            file_put_contents($secure_dir . '/.htaccess', 'Deny from all');
        }

        // Excluir Logo
        if (isset($_POST['tracemark_delete_logo']) && '1' === $_POST['tracemark_delete_logo']) {
            $old_logo = get_user_meta($user_id, '_tracemark_user_logo', true);
            if ($old_logo && file_exists($old_logo)) {
                unlink($old_logo);
            }
            delete_user_meta($user_id, '_tracemark_user_logo');
        }

        // Upload Logo
        if (!empty($_FILES['tracemark_user_logo']['name'])) {
            $file = $_FILES['tracemark_user_logo'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $file_type = wp_check_filetype($file['name']);
                if (in_array($file_type['ext'], array('png', 'jpg', 'jpeg'))) {
                    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_type['ext'];
                    $destination = $secure_dir . '/' . $new_filename;
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $old_file = get_user_meta($user_id, '_tracemark_user_logo', true);
                        if ($old_file && file_exists($old_file))
                            unlink($old_file);
                        update_user_meta($user_id, '_tracemark_user_logo', $destination);
                    }
                }
            }
        }
    }
}
