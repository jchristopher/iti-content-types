<?php

// dependencies
require_once( dirname( __FILE__ ) . '/taxonomy-single-term/class.taxonomy-single-term.php' );

	/**
	 * Utility class to assist with creation of custom content types by way
	 * of minimal arguments. Specifically labels are mostly auto-generated
	 * and there are opinionated defaults to the other core arguments.
	 *
	 * Example Usage:
	 *
	 * $content_type_factory = new ITI_Custom_Content();
	 *
	 * // register taxonomy (minimal example)
	 * $content_type_factory->register_taxonomy( array(
	 *      'name'      => 'service_cat',
	 *      'post_type' => 'service',
	 * ) );
	 *
	 * // register post type (minimal example)
	 * $content_type_factory->register_post_type( array(
	 *      'name' => 'service',
	 * ) );
	 */

class ITI_Custom_Content {

	/**
	 * Custom Post Type and Taxonomy labels need to be set up in specific ways,
	 * this function tries to automate that based on the provided parameters
	 *
	 * @param $params
	 *
	 * @return array
	 */
	function sanitize_cpt_labels( $params ) {
		$flag = false;

		$params['name']     = sanitize_title( $params['name'] );
		$params['label']    = ( empty( $params['label'] ) ? ucfirst( $params['name'] ) : $params['label'] );
		$params['singular'] = ( empty( $params['singular'] ) ? substr( ucfirst( $params['name'] ), 0, strlen( $params['name'] ) ) : $params['singular'] );

		if ( ! empty( $params['plural'] ) ) {
			$flag = true;
		}

		$params['plural'] = ( empty( $params['plural'] ) ? ucfirst( $params['name'] ) : $params['plural'] );

		if ( ! $flag ) {
			$params['plural'] = ( strtolower( substr( $params['plural'], strlen( $params['plural'] ) - 1, strlen( $params['plural'] ) ) ) != 's' ? $params['plural'] . 's' : $params['plural'] );
		}

		$params['slug'] = ( empty( $params['slug'] ) ? sanitize_title( $params['name'] ) : $params['slug'] );
		$params['menu'] = ( empty( $params['menu'] ) ? $params['plural'] : $params['menu'] );

		$params = array_map( 'sanitize_text_field', $params );

		return $params;
	}


	/**
	 * Registers a custom taxonomy with WordPress
	 *
	 * @param array $params
	 */
	function register_taxonomy( $params = array() ) {
		$defaults = array(
			'post_type'                     => 'post',
			'name'                          => 'iti_tax',
			'menu_name'                     => 'iti_tax',
			'hierarchical'                  => true,
			'public'                        => true,
			'show_ui'                       => true,
			'show_in_nav_menus'             => true,
			'query_var'                     => true,

			// Taxonomy Single Term enhancements
			'single_term'                   => false,
			'single_term_field'             => 'select',
			'single_term_allow_new'         => true,
			'single_term_field_priority'    => 'low',
		);

		$params = array_merge( $defaults, $this->sanitize_cpt_labels( $params ) );

		$labels = array(
			'name'                       => $params['plural'],
			'menu_name'                  => isset( $params['menu_name'] ) ? $params['menu_name'] : $params['plural'],
			'singular_name'              => $params['singular'],
			'search_items'               => 'Search ' . $params['plural'],
			'popular_items'              => 'Popular ' .$params['plural'],
			'all_items'                  => 'All ' . $params['plural'],
			'parent_item'                => 'Parent ' . $params['singular'],
			'edit_item'                  => 'Edit ' . $params['singular'],
			'update_item'                => 'Update ' . $params['singular'],
			'add_new_item'               => 'Add New ' . $params['singular'],
			'new_item_name'              => 'New ' . $params['singular'],
			'separate_items_with_commas' => 'Separate ' . $params['plural'] . ' with commas',
			'add_or_remove_items'        => 'Add or remove ' . $params['plural'],
			'choose_from_most_used'      => 'Choose from most used ' . $params['plural'],
		);

		$args = array(
			'label'             => $params['label'],
			'labels'            => $labels,
			'public'            => $params['public'],
			'hierarchical'      => $params['hierarchical'],
			'show_ui'           => $params['show_ui'],
			'show_in_nav_menus' => $params['show_in_nav_menus'],
			'args'              => array( 'orderby' => 'term_order' ),
			'rewrite'           => array( 'slug' => $params['slug'], 'with_front' => false ),
			'query_var'         => $params['query_var'],
		);

		register_taxonomy( $params['name'], $params['post_type'], $args );

		// enhance if this is a single term taxonomy
		$taxonomy_meta_box = new Taxonomy_Single_Term( $params['name'], $params['post_type'], $params['single_term_field'] );
		if ( $params['single_term_allow_new'] ) {
			$taxonomy_meta_box->set( 'allow_new_terms', true );
		}
		$taxonomy_meta_box->set( 'priority', $params['single_term_field_priority'] );
	}


	/**
	 * Registers a Custom Post Type with Wordpress
	 *
	 * @param array $params
	 */
	function register_post_type( $params = array() ) {

		if ( ! isset( $params['slug'] ) ) {
			$params['slug'] = 'iti_cpt';
		}

		$defaults = array(
			'name'          	=> 'iti_cpt',
			'supports'      	=> array( 'title', 'editor', 'revisions' ),
			'has_archive'   	=> true,
			'public'        	=> true,
			'show_ui'       	=> true,
			'show_in_menu'  	=> true,
			'hierarchical'  	=> false,
			'menu_position' 	=> null,
			'menu_icon'         => null,
			'rewrite'           => array( 'slug' => $params['slug'], 'with_front' => false ),
		);

		$params = array_merge( $defaults, $this->sanitize_cpt_labels( $params ) );

		register_post_type( $params['name'],
			array(
				'labels'       => array(
					'name'               => $params['label'],
					'singular_name'      => $params['singular'],
					'add_new'            => 'Add New ' . $params['singular'],
					'add_new_item'       => 'Add New ' . $params['singular'],
					'edit_item'          => 'Edit ' . $params['singular'],
					'new_item'           => 'New ' . $params['singular'],
					'view_item'          => 'View ' . $params['singular'],
					'search_items'       => 'Search ' . $params['plural'],
					'not_found'          => 'No ' . $params['plural'] . ' found',
					'not_found_in_trash' => 'No ' . $params['plural'] . ' found in Trash',
					'parent_item_colon'  => 'Separate ' . $params['plural'] . ' with commas',
					'menu_name'          => $params['menu'],
				),
				'public'       		=> $params['public'],
				'show_ui'      		=> $params['show_ui'],
				'show_in_menu' 		=> $params['show_in_menu'],
				'supports'     		=> $params['supports'],
				'hierarchical' 		=> $params['hierarchical'],
				'rewrite'      		=> $params['rewrite'],
				'has_archive'  		=> $params['has_archive'],
				'menu_icon'  		=> $params['menu_icon'],
				'menu_position'     => $params['menu_position'],
			)
		);

	}

}