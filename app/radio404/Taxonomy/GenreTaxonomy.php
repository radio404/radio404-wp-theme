<?php

namespace radio404\Taxonomy;

use radio404\Taxonomy\AbstractTaxonomy;

class Genre extends AbstractTaxonomy {

	public const TAXONOMY = 'genre';
	public const POST_TYPES = ['artist', 'label', 'album', 'track'];

	public function __construct() {
		register_taxonomy( self::TAXONOMY, self::POST_TYPES, $this->getArgs() );
	}

	protected function getLabels():array {
		return $labels = array(
			'name'                       => _x( 'Genres', 'Taxonomy General Name', 'radio404' ),
			'singular_name'              => _x( 'Genre', 'Taxonomy Singular Name', 'radio404' ),
			'menu_name'                  => __( 'Genres', 'radio404' ),
			'all_items'                  => __( 'Tous les genres', 'radio404' ),
			'parent_item'                => __( 'Genre parent', 'radio404' ),
			'parent_item_colon'          => __( 'Genre parent :', 'radio404' ),
			'new_item_name'              => __( 'Nouveau genre', 'radio404' ),
			'add_new_item'               => __( 'Ajouter un genre', 'radio404' ),
			'edit_item'                  => __( 'Éditer le genre', 'radio404' ),
			'update_item'                => __( 'Mettre à jour le genre', 'radio404' ),
			'view_item'                  => __( 'Voir le genre', 'radio404' ),
			'separate_items_with_commas' => __( 'Séparés par des virgules', 'radio404' ),
			'add_or_remove_items'        => __( 'Ajouter ou supprimer des genres', 'radio404' ),
			'choose_from_most_used'      => __( 'Choisir à partir des plus utilisés', 'radio404' ),
			'popular_items'              => __( 'Genres populaires', 'radio404' ),
			'search_items'               => __( 'Rechercher des genres', 'radio404' ),
			'not_found'                  => __( 'Non trouvé', 'radio404' ),
			'no_terms'                   => __( 'Pas de genre', 'radio404' ),
			'items_list'                 => __( 'Liste de genres', 'radio404' ),
			'items_list_navigation'      => __( 'Navigation de liste de genre', 'radio404' ),
		);
	}

	protected function getArgs():array {
		$labels = $this->getLabels();
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'update_count_callback'      => 'update_count_genre_callback',
			'show_in_rest'               => true,
		);
		return $args;
	}
}