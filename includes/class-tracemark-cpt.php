<?php
/**
 * Registra o Custom Post Type 'restricted_pdf'.
 */

class TraceMark_CPT
{

    public function __construct()
    {
        add_action('init', array($this, 'register_post_type'));
    }

    public function register_post_type()
    {
        $labels = array(
            'name' => _x('PDFs Restritos', 'Nome Geral do Post Type', 'tracemark-pdf'),
            'singular_name' => _x('PDF Restrito', 'Nome Singular do Post Type', 'tracemark-pdf'),
            'menu_name' => __('PDFs Restritos', 'tracemark-pdf'),
            'name_admin_bar' => __('PDF Restrito', 'tracemark-pdf'),
            'archives' => __('Arquivos do Item', 'tracemark-pdf'),
            'attributes' => __('Atributos do Item', 'tracemark-pdf'),
            'parent_item_colon' => __('Item Pai:', 'tracemark-pdf'),
            'all_items' => __('Todos os PDFs', 'tracemark-pdf'),
            'add_new_item' => __('Adicionar Novo PDF Restrito', 'tracemark-pdf'),
            'add_new' => __('Adicionar Novo', 'tracemark-pdf'),
            'new_item' => __('Novo Item', 'tracemark-pdf'),
            'edit_item' => __('Editar Item', 'tracemark-pdf'),
            'update_item' => __('Atualizar Item', 'tracemark-pdf'),
            'view_item' => __('Ver Item', 'tracemark-pdf'),
            'view_items' => __('Ver Itens', 'tracemark-pdf'),
            'search_items' => __('Pesquisar Item', 'tracemark-pdf'),
            'not_found' => __('Não encontrado', 'tracemark-pdf'),
            'not_found_in_trash' => __('Não encontrado no Lixo', 'tracemark-pdf'),
            'featured_image' => __('Imagem Destacada', 'tracemark-pdf'),
            'set_featured_image' => __('Definir imagem destacada', 'tracemark-pdf'),
            'remove_featured_image' => __('Remover imagem destacada', 'tracemark-pdf'),
            'use_featured_image' => __('Usar como imagem destacada', 'tracemark-pdf'),
            'insert_into_item' => __('Inserir no item', 'tracemark-pdf'),
            'uploaded_to_this_item' => __('Enviado para este item', 'tracemark-pdf'),
            'items_list' => __('Lista de itens', 'tracemark-pdf'),
            'items_list_navigation' => __('Navegação da lista de itens', 'tracemark-pdf'),
            'filter_items_list' => __('Filtrar lista de itens', 'tracemark-pdf'),
        );
        $args = array(
            'label' => __('PDF Restrito', 'tracemark-pdf'),
            'description' => __('PDFs restritos a usuários específicos', 'tracemark-pdf'),
            'labels' => $labels,
            'supports' => array('title'),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-media-document',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'capability_type' => 'post',
        );
        register_post_type('restricted_pdf', $args);
    }
}
