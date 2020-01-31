<?php

namespace radio404\PostType;

use radio404\PostType\AbstractPostType;

class Podcast extends AbstractPostType {

	const POST_TYPE = 'podcast';

	public function __construct(){
		$this->init_post_type();
		add_filter( 'manage_'.self::POST_TYPE.'_posts_columns', [self::class,'set_custom_edit_columns'] );
		add_action( 'manage_'.self::POST_TYPE.'_posts_custom_column', 'custom_column', 10, 2 );
	}

	public function getLabels() : array {
		return 	$labels = array(
			'name'                  => _x( 'Podcasts', 'Post Type General Name', 'radio404' ),
			'singular_name'         => _x( 'Podcast', 'Post Type Singular Name', 'radio404' ),
			'menu_name'             => __( 'Podcasts', 'radio404' ),
			'name_admin_bar'        => __( 'Podcasts', 'radio404' ),
			'archives'              => __( 'Archives des podcasts', 'radio404' ),
			'attributes'            => __( 'Attributs', 'radio404' ),
			'parent_item_colon'     => __( 'Podcast parent', 'radio404' ),
			'all_items'             => __( 'Tous les podcasts', 'radio404' ),
			'add_new_item'          => __( 'Ajouter un podcast', 'radio404' ),
			'add_new'               => __( 'Ajouter un nouveau', 'radio404' ),
			'new_item'              => __( 'Nouveau podcast', 'radio404' ),
			'edit_item'             => __( 'Éditer le podcast', 'radio404' ),
			'update_item'           => __( 'Mettre à jour le podcast', 'radio404' ),
			'view_item'             => __( 'Voir le podcast', 'radio404' ),
			'view_items'            => __( 'Voir les podcasts', 'radio404' ),
			'search_items'          => __( 'Rechercher un podcast', 'radio404' ),
			'not_found'             => __( 'Non trouvé', 'radio404' ),
			'not_found_in_trash'    => __( 'Non trouvé dans la corbeille', 'radio404' ),
			'featured_image'        => __( 'Logo', 'radio404' ),
			'set_featured_image'    => __( 'Ajouter un logo', 'radio404' ),
			'remove_featured_image' => __( 'Supprimer le logo', 'radio404' ),
			'use_featured_image'    => __( 'Utiliser commer logo', 'radio404' ),
			'insert_into_item'      => __( 'Ajouter au podcast', 'radio404' ),
			'uploaded_to_this_item' => __( 'Uploadé au podcast', 'radio404' ),
			'items_list'            => __( 'Liste de podcasts', 'radio404' ),
			'items_list_navigation' => __( 'Navigation de liste des podcasts', 'radio404' ),
			'filter_items_list'     => __( 'Filtrer la liste de podcasts', 'radio404' ),
		);
	}

	private function getArgs():array {

		$labels = $this->getLabels();
		$args = array(
			'label'                 => __( 'Podcast', 'radio404' ),
			'description'           => __( 'Podcasts', 'radio404' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'author', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			'taxonomies'            => array( 'post_tag' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-rss',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'show_in_rest'          => true,
			'rest_base'             => 'podcasts',
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
	public function set_custom_edit_podcast_columns($columns) {
		$columns['artist'] = __( 'Artiste', 'radio404' );
		return array_merge(array_slice($columns,0,1),[
			'cover' => __('Pochette', 'radio404')
		],array_slice($columns,1));}


	/**
	 * @param $column
	 * @param $post_id
	 * Add the data to the custom columns for the book post type:
	 */
	public function custom_podcast_column( $column, $post_id ) {
		switch ( $column ) {
			case 'cover':
				the_post_thumbnail('thumbnail',['class'=>'admin-list-cover']);
				break;
			case 'artist' :
				$artists = [];
				foreach(get_post_meta( $post_id , $column, true ) as $artist_id){
					$artist_edit_link = get_edit_post_link($artist_id);
					$artist_name = get_the_title($artist_id);
					$artists[] = "<a href='$artist_edit_link'>$artist_name</a>";
				}
				echo implode(', ',$artists);
				break;
		}
	}

	/**
	 * @param $title
	 *
	 * @return \WP_Post|null
	 */
	public static function get_podcast_by_title($title){
		$args = array(
			'post_status'       => 'any',
			'title'        => $title,
			'post_type'         => 'podcast',
			'posts_per_page'    => '1'
		);
		return self::get_post($args);
	}


}

