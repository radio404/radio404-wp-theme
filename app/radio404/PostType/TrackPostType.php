<?php

namespace radio404\PostType;

use radio404\PostType\AbstractPostType;

class Track extends AbstractPostType {

	const POST_TYPE = 'track';

	public function __construct() {
		$this->init_post_type();
		add_action( 'restrict_manage_posts', [$this,'restrict_manage_posts']);
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', [$this,'manage_posts_columns'] );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column' , [$this,'manage_posts_custom_column'], 10, 2 );
		add_action( 'restrict_manage_posts', [$this,'restrict_manage_posts']);
		add_filter( 'parse_query', [$this,'parse_query'] );
	}

	protected function getLabels():array {
		return 	$labels = array(
			'name'                  => _x( 'Morceaux', 'Post Type General Name', 'radio404' ),
			'singular_name'         => _x( 'Morceau', 'Post Type Singular Name', 'radio404' ),
			'menu_name'             => __( 'Morceaux', 'radio404' ),
			'name_admin_bar'        => __( 'Morceaux', 'radio404' ),
			'archives'              => __( 'Archives des morceaux', 'radio404' ),
			'attributes'            => __( 'Attributs', 'radio404' ),
			'parent_item_colon'     => __( 'Morceau parent', 'radio404' ),
			'all_items'             => __( 'Tous les morceaux', 'radio404' ),
			'add_new_item'          => __( 'Ajouter un morceau', 'radio404' ),
			'add_new'               => __( 'Ajouter un nouveau', 'radio404' ),
			'new_item'              => __( 'Nouveau morceau', 'radio404' ),
			'edit_item'             => __( 'Éditer le morceau', 'radio404' ),
			'update_item'           => __( 'Mettre à jour le morceau', 'radio404' ),
			'view_item'             => __( 'Voir le morceau', 'radio404' ),
			'view_items'            => __( 'Voir les morceaux', 'radio404' ),
			'search_items'          => __( 'Rechercher un morceau', 'radio404' ),
			'not_found'             => __( 'Non trouvé', 'radio404' ),
			'not_found_in_trash'    => __( 'Non trouvé dans la corbeille', 'radio404' ),
			'featured_image'        => __( 'Pochette d\'album', 'radio404' ),
			'set_featured_image'    => __( 'Ajouter une pochette d\'album', 'radio404' ),
			'remove_featured_image' => __( 'Supprimer la pochette d\'album', 'radio404' ),
			'use_featured_image'    => __( 'Utiliser comme pochette d\'album', 'radio404' ),
			'insert_into_item'      => __( 'Ajouter au morceau', 'radio404' ),
			'uploaded_to_this_item' => __( 'Uploadé au morceau', 'radio404' ),
			'items_list'            => __( 'Liste de morceaux', 'radio404' ),
			'items_list_navigation' => __( 'Navigation de liste des morceaux', 'radio404' ),
			'filter_items_list'     => __( 'Filtrer la liste de morceaux', 'radio404' ),
		);
	}

	protected function getArgs():array {
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
			'menu_icon'             => 'dashicons-format-audio',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'show_in_rest'          => true,
			'rest_base'             => 'tracks',
		);
		return $args;
	}

	private function init_post_type(){
		register_post_type( self::POST_TYPE, $this->getArgs(), 0 );

	}

	// Add the custom columns to the book post type:
	protected function manage_posts_columns($columns) {

		$columns['author'] = __('Géré par','radio404');
		$columns['album_post_type'] = __( 'Type', 'radio404' );
		$columns['album'] = __( 'Album', 'radio404' );
		$columns['artist'] = __( 'Artiste', 'radio404' );

		return array_merge(array_slice($columns,0,1),[
			'cover' => __('Pochette', 'radio404')
		],array_slice($columns,1));
	}

	// Add the data to the custom columns for the book post type:
	protected function manage_posts_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'album_post_type':
				$column_value = get_post_meta( $post_id , $column , true );
				echo ucfirst($column_value);
				break;
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
			case 'album' :
				$column_post_id = get_post_meta( $post_id , $column , true );
				$album_title = get_the_title($column_post_id);
				$edit_link = get_edit_post_link($column_post_id);
				echo $column_post_id ? "<a href='$edit_link'>$album_title</a>" : "—";
				break;
		}
	}

	/*
	add authors menu filter to admin post list for custom post type
	*/
	function restrict_manage_posts($post_type) {

		global $wpdb;

		if($post_type !== self::POST_TYPE) return;

		wp_dropdown_users(array(
			'show_option_all'   => __('Tous les utilisateurs'),
			'show_option_none'  => false,
			'name'          => 'author',
			'selected'      => !empty($_GET['author']) ? $_GET['author'] : 0,
			'include_selected'  => false
		));

		/** Grab the results from the DB */
		$query = $wpdb->prepare('
        SELECT DISTINCT pm.meta_value FROM %1$s pm
        LEFT JOIN %2$s p ON p.ID = pm.post_id
        WHERE pm.meta_key = "%3$s" 
        AND p.post_status = "%4$s" 
        AND p.post_type = "%5$s"
        ORDER BY "%3$s"',
			$wpdb->postmeta,
			$wpdb->posts,
			'album_post_type', // Your meta key - change as required
			'publish',             // Post status - change as required
			$post_type
		);
		$results = $wpdb->get_col($query);

		/** Ensure there are options to show */
		if(empty($results))
			return;

		// get selected option if there is one selected
		if (isset( $_GET['album_post_type'] ) && $_GET['album_post_type'] != '') {
			$selectedAlbumPostType = $_GET['album_post_type'];
		} else {
			$selectedAlbumPostType = '';
		}

		/** Grab all of the options that should be shown */
		$options[] = sprintf('<option value="">%1$s</option>', __('Tous les types', 'radio404'));
		foreach($results as $result) :
			if ($result == $selectedAlbumPostType) {
				$selected = " selected";
			}else{
				$selected = '';
			}
			$options[] = sprintf('<option value="%1$s"'.$selected.'>%2$s</option>', esc_attr($result), $result);
		endforeach;

		/** Output the dropdown menu */
		echo '<select class="" id="album_post_type" name="album_post_type">';
		echo join("\n", $options);
		echo '</select>';

	}

	/**
	 * Add extra dropdowns to the List Tables
	 *
	 * @param required string $post_type    The Post Type that is being displayed
	 */

	function parse_query( $query )
	{
		global $pagenow;

		if($query->query['post_type'] !== self::POST_TYPE) return;

		if ( is_admin() && $pagenow=='edit.php' && isset($_GET['album_post_type']) && $_GET['album_post_type'] != '') {
			$query->query_vars['meta_key'] = 'album_post_type';
			if (isset($_GET['album_post_type']) && $_GET['album_post_type'] != ''){
				$query->query_vars['meta_value'] = $_GET['album_post_type'];
			}
		}
	}

	public static function get_track_by_id( $idtrack )
	{

		// grab page - polylang will take take or language selection ##
		$args = array(
			'post_status'       => 'any',
			'meta_query'        => array(
				array(
					'key'       => 'idtrack',
					'value'     => $idtrack
				)
			),
			'post_type'         => 'track',
			'posts_per_page'    => '1'
		);

		return self::get_post($args);

	}

	function get_cover_by_id( $idtrack ){

		$args = array(
			'post_status'       => 'any',
			'meta_query'        => array(
				array(
					'key'       => 'idtrack',
					'value'     => $idtrack
				)
			),
			'post_type'         => 'attachment',
			'posts_per_page'    => '1'
		);

		return self::get_post($args);

	}


}