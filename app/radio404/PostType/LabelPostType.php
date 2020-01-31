<?php

namespace radio404\PostType;

use radio404\PostType\AbstractPostType;

class Label extends AbstractPostType {

	const POST_TYPE = 'label';

	public function __construct(){
		$this->init_post_type();
	}


	private function getLabels() : array {
		return 	$labels = array(
			'name'                  => _x( 'Labels', 'Post Type General Name', 'radio404' ),
			'singular_name'         => _x( 'Label', 'Post Type Singular Name', 'radio404' ),
			'menu_name'             => __( 'Labels', 'radio404' ),
			'name_admin_bar'        => __( 'Labels', 'radio404' ),
			'archives'              => __( 'Archives des labels', 'radio404' ),
			'attributes'            => __( 'Attributs', 'radio404' ),
			'parent_item_colon'     => __( 'Label parent', 'radio404' ),
			'all_items'             => __( 'Tous les labels', 'radio404' ),
			'add_new_item'          => __( 'Ajouter un label', 'radio404' ),
			'add_new'               => __( 'Ajouter un nouveau', 'radio404' ),
			'new_item'              => __( 'Nouveau label', 'radio404' ),
			'edit_item'             => __( 'Éditer le label', 'radio404' ),
			'update_item'           => __( 'Mettre à jour le label', 'radio404' ),
			'view_item'             => __( 'Voir le label', 'radio404' ),
			'view_items'            => __( 'Voir les labels', 'radio404' ),
			'search_items'          => __( 'Rechercher un label', 'radio404' ),
			'not_found'             => __( 'Non trouvé', 'radio404' ),
			'not_found_in_trash'    => __( 'Non trouvé dans la corbeille', 'radio404' ),
			'featured_image'        => __( 'Logo', 'radio404' ),
			'set_featured_image'    => __( 'Ajouter un logo', 'radio404' ),
			'remove_featured_image' => __( 'Supprimer le logo', 'radio404' ),
			'use_featured_image'    => __( 'Utiliser commer logo', 'radio404' ),
			'insert_into_item'      => __( 'Ajouter au label', 'radio404' ),
			'uploaded_to_this_item' => __( 'Uploadé au label', 'radio404' ),
			'items_list'            => __( 'Liste de labels', 'radio404' ),
			'items_list_navigation' => __( 'Navigation de liste des labels', 'radio404' ),
			'filter_items_list'     => __( 'Filtrer la liste de labels', 'radio404' ),
		);
	}

	private function getArgs():array {

		$labels = $this->getLabels();
		$args = array(
			'label'                 => __( 'Label', 'radio404' ),
			'description'           => __( 'Labels', 'radio404' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'custom-fields' ),
			'taxonomies'            => array( 'genre' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-media-audio',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'show_in_rest'          => true,
			'rest_base'             => 'labels',
		);
		return $args;
	}

	private function init_post_type() {

		$args = $this->getArgs();
		register_post_type( self::POST_TYPE, $args );

	}

}