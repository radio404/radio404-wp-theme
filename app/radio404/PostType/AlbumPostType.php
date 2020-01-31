<?php

namespace radio404\PostType;

use radio404\PostType\AbstractPostType;

class Album extends AbstractPostType {

	const POST_TYPE = 'album';

	public function __construct(){
		$this->init_post_type();
		add_filter( 'manage_'.self::POST_TYPE.'_posts_columns', [self::class,'set_custom_edit_columns'] );
		add_action( 'manage_'.self::POST_TYPE.'_posts_custom_column', 'custom_column', 10, 2 );
	}

	private function getLabels() : array {
		return $labels = array(
			'name'                  => _x( 'Albums', 'Post Type General Name', 'radio404' ),
			'singular_name'         => _x( 'Album', 'Post Type Singular Name', 'radio404' ),
			'menu_name'             => __( 'Albums', 'radio404' ),
			'name_admin_bar'        => __( 'Albums', 'radio404' ),
			'archives'              => __( 'Archives des albums', 'radio404' ),
			'attributes'            => __( 'Attributs', 'radio404' ),
			'parent_item_colon'     => __( 'Album parent', 'radio404' ),
			'all_items'             => __( 'Tous les albums', 'radio404' ),
			'add_new_item'          => __( 'Ajouter un nouvel album', 'radio404' ),
			'add_new'               => __( 'Ajouter', 'radio404' ),
			'new_item'              => __( 'Nouvel album', 'radio404' ),
			'edit_item'             => __( 'Éditer', 'radio404' ),
			'update_item'           => __( 'Mettre à jour', 'radio404' ),
			'view_item'             => __( 'Voir', 'radio404' ),
			'view_items'            => __( 'Voir les albums', 'radio404' ),
			'search_items'          => __( 'Rechercher un album', 'radio404' ),
			'not_found'             => __( 'Non trouvé', 'radio404' ),
			'not_found_in_trash'    => __( 'Non trouvé dans la corbeille', 'radio404' ),
			'featured_image'        => __( 'Pochette d’album', 'radio404' ),
			'set_featured_image'    => __( 'Ajouter une pochette d’album', 'radio404' ),
			'remove_featured_image' => __( 'Supprimer la pochette d’album', 'radio404' ),
			'use_featured_image'    => __( 'Utiliser comme pochette d’album', 'radio404' ),
			'insert_into_item'      => __( 'Ajouter à l\'album', 'radio404' ),
			'uploaded_to_this_item' => __( 'Téléchargé', 'radio404' ),
			'items_list'            => __( 'Liste d\'albums', 'radio404' ),
			'items_list_navigation' => __( 'Navigation de liste d\'albums', 'radio404' ),
			'filter_items_list'     => __( 'Filtrer les albums', 'radio404' ),
		);
	}

	private function getArgs():array {

		$labels = $this->getLabels();
		$args   = array(
			'label'               => __( 'Album', 'radio404' ),
			'description'         => __( 'Albums', 'radio404' ),
			'labels'              => $labels,
			'supports'            => array(
				'title',
				'editor',
				'author',
				'excerpt',
				'thumbnail',
				'revisions',
				'custom-fields'
			),
			'taxonomies'          => array( 'genre' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-album',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
			'show_in_rest'        => true,
			'rest_base'           => 'albums',
		);

		return $args;
	}

	private function init_post_type() {

		$args = $this->getArgs();
		register_post_type( self::POST_TYPE, $args );

	}


	/**
	 * @param $columns
	 *
	 * @return array
	 * Add the custom columns to the book post type:
	 */
	public static function set_custom_edit_columns( $columns ) {

		$columns['author']       = __( 'Géré par', 'radio404' );
		$columns['artist']       = __( 'Artiste', 'radio404' );
		$columns['release_year'] = __( 'Année', 'radio404' );

		return array_merge( array_slice( $columns, 0, 1 ), [
			'cover' => __( 'Pochette', 'radio404' )
		], array_slice( $columns, 1 ) );
	}


	/**
	 * @param $column
	 * @param $post_id
	 * Add the data to the custom columns for the book post type:
	 */
	public static function custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'cover':
				the_post_thumbnail( 'thumbnail', [ 'class' => 'admin-list-cover' ] );
				break;
			case 'artist' :
				foreach ( get_post_meta( $post_id, $column, true ) as $artist_id ) {
					$artist_edit_link = get_edit_post_link( $artist_id );
					$artist_name      = get_the_title( $artist_id );
					echo "<a href='$artist_edit_link'>$artist_name</a> ";
				}
				break;
			case 'release_year' :
				$meta_value = get_post_meta( $post_id, $column, true );
				echo $meta_value;
				break;
		}
	}

	public static function get_cover_by_album( $album = '', $artist = '', $cover = '' ){
		if(!$album) return false;

		$args = array(
			'post_status'       => 'any',
			'meta_query'        => array(
				[
					'key'       => 'cover',
					'value'     => "$cover",
					'compare' => 'LIKE',
				],
				'relation'      => 'OR',
				[
					[
						'key'       => 'album',
						'value'     => "$album",
						'compare' => 'LIKE',
					],
					'relation'      => 'AND',
					[
						'key'       => 'artist',
						'value'     => "$artist",
						'compare' => 'LIKE',
					],
				]
			),
			'post_type'         => 'attachment',
			'posts_per_page'    => '1'
		);

		return self::get_post($args);
	}

	public static function get_album_by_title_and_artist( $title, $artist){
		$args = array(
			'post_status'       => 'any',
			'title'        => $title,
			'meta_query'        => array(
				array(
					'key'       => 'artist_literal',
					'value'     => "$artist"
				)
			),
			'post_type'         => 'album',
			'posts_per_page'    => '1'
		);
		return self::get_post($args);

	}



}