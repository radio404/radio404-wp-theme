<?php

namespace radio404\PostType;

use radio404\PostType\AbstractPostType;

class Artist extends AbstractPostType {

	const POST_TYPE = 'artist';

	public function __construct(){
		$this->init_post_type();
		add_filter( 'manage_'.self::POST_TYPE.'_posts_columns', [$this,'set_custom_edit_columns'] );
		add_action( 'manage_'.self::POST_TYPE.'_posts_custom_column', [$this,'custom_column'], 10, 2 );
	}


	public function getLabels() : array {
		return $labels = array(
			'name'                  => _x( 'Artistes', 'Post Type General Name', 'radio404' ),
			'singular_name'         => _x( 'Artiste', 'Post Type Singular Name', 'radio404' ),
			'menu_name'             => __( 'Artistes', 'radio404' ),
			'name_admin_bar'        => __( 'Artistes', 'radio404' ),
			'archives'              => __( 'Archives des artistes', 'radio404' ),
			'attributes'            => __( 'Attributs', 'radio404' ),
			'parent_item_colon'     => __( 'Artiste parent', 'radio404' ),
			'all_items'             => __( 'Tous les artistes', 'radio404' ),
			'add_new_item'          => __( 'Ajouter un nouvel artiste', 'radio404' ),
			'add_new'               => __( 'Ajouter', 'radio404' ),
			'new_item'              => __( 'Nouvel artiste', 'radio404' ),
			'edit_item'             => __( 'Éditer', 'radio404' ),
			'update_item'           => __( 'Mettre à jour', 'radio404' ),
			'view_item'             => __( 'Voir', 'radio404' ),
			'view_items'            => __( 'Voir les artistes', 'radio404' ),
			'search_items'          => __( 'Rechercher un artiste', 'radio404' ),
			'not_found'             => __( 'Non trouvé', 'radio404' ),
			'not_found_in_trash'    => __( 'Non trouvé dans la corbeille', 'radio404' ),
			'featured_image'        => __( 'Portrait', 'radio404' ),
			'set_featured_image'    => __( 'Ajouter un portrait', 'radio404' ),
			'remove_featured_image' => __( 'Supprimer le portrait', 'radio404' ),
			'use_featured_image'    => __( 'Utiliser comme portrait', 'radio404' ),
			'insert_into_item'      => __( 'Ajouter à l\'artiste', 'radio404' ),
			'uploaded_to_this_item' => __( 'Téléchargé', 'radio404' ),
			'items_list'            => __( 'Liste d\'artistes', 'radio404' ),
			'items_list_navigation' => __( 'Navigation de liste d\'artistes', 'radio404' ),
			'filter_items_list'     => __( 'Filtrer les artistes', 'radio404' ),
		);
	}

	private function getArgs():array {

		$labels = $this->getLabels();
		$args = array(
			'label'                 => __( 'Artiste', 'radio404' ),
			'description'           => __( 'Artistes, groupes, musiciens', 'radio404' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'author', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			'taxonomies'            => array( 'genre' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 7,
			'menu_icon'             => 'dashicons-id-alt',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'show_in_rest'          => true,
			'rest_base'             => 'artists',
		);
		return $args;
	}

	private function init_post_type() {

		$args = $this->getArgs();
		register_post_type( self::POST_TYPE, $args );

	}


	// Add the custom columns to the book post type:
	public function set_custom_edit_columns($columns) {
		unset( $columns['taxonomy-genre'] );
		unset( $columns['author'] );
		unset( $columns['date'] );

		return $columns;
	}

	// Add the data to the custom columns for the book post type:
	public function custom_column( $column, $post_id ) {
		switch ( $column ) {
		}
	}

	public static function get_artist_by_name( $name ) {

		$args = array(
			'post_status'    => 'any',
			'title'          => $name,
			'post_type'      => 'artist',
			'posts_per_page' => '1'
		);

		return self::get_post( $args );
	}
}
