<?php
/**
 * Registra os Custom Post Types e Taxonomias.
 */

class TraceMark_CPT
{

    public function __construct()
    {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
    }

    public function register_post_types()
    {
        // ─── POST TYPE 1: Boletim Semanal ───
        $boletim_labels = array(
            'name' => 'Boletins Semanais',
            'singular_name' => 'Boletim Semanal',
            'menu_name' => 'Boletins Semanais',
            'name_admin_bar' => 'Boletim Semanal',
            'all_items' => 'Todos os Boletins',
            'add_new_item' => 'Adicionar Novo Boletim',
            'add_new' => 'Adicionar Novo',
            'new_item' => 'Novo Boletim',
            'edit_item' => 'Editar Boletim',
            'update_item' => 'Atualizar Boletim',
            'view_item' => 'Ver Boletim',
            'search_items' => 'Pesquisar Boletins',
            'not_found' => 'Nenhum boletim encontrado',
            'not_found_in_trash' => 'Nenhum boletim no lixo',
        );
        register_post_type('boletim_semanal', array(
            'label' => 'Boletim Semanal',
            'description' => 'Boletins semanais do Comitê Internacional',
            'labels' => $boletim_labels,
            'supports' => array('title'),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-media-text',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'capability_type' => 'post',
        ));

        // ─── POST TYPE 2: Relatório por País ───
        $relatorio_labels = array(
            'name' => 'Relatórios por País',
            'singular_name' => 'Relatório por País',
            'menu_name' => 'Relatórios por País',
            'name_admin_bar' => 'Relatório por País',
            'all_items' => 'Todos os Relatórios',
            'add_new_item' => 'Adicionar Novo Relatório',
            'add_new' => 'Adicionar Novo',
            'new_item' => 'Novo Relatório',
            'edit_item' => 'Editar Relatório',
            'update_item' => 'Atualizar Relatório',
            'view_item' => 'Ver Relatório',
            'search_items' => 'Pesquisar Relatórios',
            'not_found' => 'Nenhum relatório encontrado',
            'not_found_in_trash' => 'Nenhum relatório no lixo',
        );
        register_post_type('relatorio_pais', array(
            'label' => 'Relatório por País',
            'description' => 'Relatórios por país com PDF substituível',
            'labels' => $relatorio_labels,
            'supports' => array('title'),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 6,
            'menu_icon' => 'dashicons-admin-site-alt3',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'capability_type' => 'post',
        ));
    }

    public function register_taxonomies()
    {
        $pais_labels = array(
            'name' => 'Países',
            'singular_name' => 'País',
            'search_items' => 'Pesquisar Países',
            'all_items' => 'Todos os Países',
            'edit_item' => 'Editar País',
            'update_item' => 'Atualizar País',
            'add_new_item' => 'Adicionar Novo País',
            'new_item_name' => 'Nome do Novo País',
            'menu_name' => 'Países',
        );
        register_taxonomy('pais', array('relatorio_pais'), array(
            'hierarchical' => true,
            'labels' => $pais_labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'pais'),
            'show_in_rest' => false,
        ));
    }
}
