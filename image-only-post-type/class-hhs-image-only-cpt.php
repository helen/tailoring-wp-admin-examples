<?php
/**
 * This example shows what can be done to the add/edit and list table
 * screens for a post type that only supports a featured image.
 * Here we'll use a slider of images as a practical use case.
 *
 * For more details, see the blog post at:
 * http://10up.com/blog/custom-tailoring-the-wordpress-admin-experience/
 *
 * Edit screen, before: http://slides.helenhousandi.com/wcsf2013/images/slide-edit-before.png
 * Edit screen, after: http://slides.helenhousandi.com/wcsf2013/images/slide-edit-after.png
 *
 * List table, before: http://slides.helenhousandi.com/wcsf2013/images/slide-list-table-before.png
 * List table, after: http://slides.helenhousandi.com/wcsf2013/images/slide-list-table-after.png
 */
class HHS_Image_Only_CPT {
	/**
	 * Set up various hook callbacks
	 */
	public function __construct() {
		add_action( 'init',                              array( $this, 'register_post_type' ) );
		add_filter( 'admin_post_thumbnail_html',         array( $this, 'admin_post_thumbnail_html' ), 10, 2 );
		add_filter( 'media_view_strings',                array( $this, 'media_view_strings' ), 10, 2 );
		add_filter( 'manage_edit-slide_columns',         array( $this, 'manage_edit_columns' ) );
		add_action( 'manage_slide_posts_custom_column',  array( $this, 'custom_edit_columns' ), 10, 2 );
	}

	/**
	 * Register our custom post type of slide.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
		    'name'               => _x( 'Slide', 'post type general name', 'textdomain' ),
		    'singular_name'      => _x( 'Slide', 'post type singular name',  'textdomain' ),
		    'add_new'            => _x( 'Add Slide', 'slide', 'textdomain' ),
		    'add_new_item'       => __( 'Add New Slide', 'textdomain' ),
		    'edit_item'          => __( 'Edit Slide', 'textdomain' ),
		    'new_item'           => __( 'New Slide', 'textdomain' ),
		    'view_item'          => __( 'View Slides', 'textdomain' ),
		    'search_items'       => __( 'Search Slides', 'textdomain' ),
		    'not_found'          => __( 'No Slides found', 'textdomain' ),
		    'not_found_in_trash' => __( 'No Slides found in Trash', 'textdomain' ),
		    'parent_item_colon'  => '',
		);

		register_post_type( 'slide', array (
			'label'                => __( 'Slides', 'textdomain' ),
			'labels'               => $labels,
			'show_in_menu'         => 'themes.php', // we don't need a whole top level menu item
			'show_in_nav_menus'    => false,
			'exclude_from_search'  => true,
			'hierarchical'         => true, // for ease of ordering
			'supports'             => array( 'thumbnail' ),
			'register_meta_box_cb' => array( $this, 'meta_boxes' ),
		) );
	}

	/**
	 * Move the post thumbnail meta box
	 *
	 * Removes the post thumbnail meta box from its default
	 * side position and move it to the main column, relabeling
	 * it appropriately in the process.
	 *
	 * @return void
	 */
	public function meta_boxes() {
		remove_meta_box( 'postimagediv', 'slide', 'side' );

		add_meta_box( 'postimagediv', __( 'Slide Image' ), 'post_thumbnail_meta_box', 'slide', 'normal', 'high' );
	}

	/**
	 * Customize the 'Set|Remove featured image' text
	 *
	 * Replace some pieces inside the post thumbnail meta
	 * box to make it more specific to the usage.
	 *
	 * @param string $output  Inner HTML of the post thumbnail meta box.
	 * @param int    $post_id ID of the currently edited post.
	 *
	 * @return string The altered string(s).
	 */
	public function admin_post_thumbnail_html( $output, $post_id ) {
		$post_type = get_post_type( $post_id );

		// beware of translations, as this is a straight string replace
		if ( ! empty ( $post_type ) && 'slide' === $post_type ) {
			$output = str_replace( 'Set featured image', 'Select / Upload a slide image', $output );
			$output = str_replace( 'Remove featured image', 'Remove slide image', $output );
		}

		return $output;
	}

	/**
	 * Modify media modal text to 'Set slide image'
	 *
	 * Set some of the strings in the 3.5+ media modal to be
	 * specific to the usage of selecting a slide image.
	 *
	 * @param array  $strings Array of strings for the media modal.
	 * @param object $post    Post object.
	 *
	 * @return array The array of strings.
	 */
	public function media_view_strings( $strings, $post ) {
		if ( 'slide' === get_post_type( $post ) ) {
			$strings['setFeaturedImageTitle'] = __( 'Set slide image', 'textdomain' );
			$strings['setFeaturedImage']      = __( 'Set slide image', 'textdomain' );
		}

		return $strings;
	}

	/**
	 * Set the columns to be only what's necessary
	 *
	 * @param array $columns Columns for the list table.
	 *
	 * @return array The columns array.
	 */
	public function manage_edit_columns( $columns ) {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'thumbnail' => __( 'Slide', 'textdomain' ),
		);

		return $columns;
	}

	/**
	 * Post-specific display for any custom columns in the list table
	 *
	 * @param string $column  Key of the column.
	 * @param int    $post_id ID of the post in question.
	 *
	 * @return void
	 */
	public function custom_edit_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'thumbnail' :
				if ( has_post_thumbnail( $post_id ) )
					the_post_thumbnail( $post_id );
				else
					echo __( 'No image', 'textdomain' );

				// add row_action links for Edit and Trash because there's no title column
				$post_type = get_post_type( $post_id );
				$post_type_object = get_post_type_object( $post_type );
				$post_status = get_post_status( $post_id );
				$actions = array();

				if ( current_user_can( $post_type_object->cap->edit_post, $post_id ) && 'trash' !== $post_status ) {
					$actions['edit'] = '<a href="' . get_edit_post_link( $post_id, true ) . '">' . __( 'Edit' ) . '</a>';
				}
				if ( current_user_can( $post_type_object->cap->delete_post, $post_id ) ) {
					if ( 'trash' === $post_status )
						$actions['untrash'] = '<a href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post_id ) ), 'untrash-' . $post_type . '_' . $post_id ) . '">' . __( 'Restore' ) . '</a>';
					elseif ( EMPTY_TRASH_DAYS )
						$actions['trash'] = '<a class="submitdelete" href="' . get_delete_post_link( $post_id ) . '">' . __( 'Trash' ) . '</a>';
					if ( 'trash' === $post_status || ! EMPTY_TRASH_DAYS )
						$actions['delete'] = '<a class="submitdelete" href="' . get_delete_post_link( $post_id, '', true ) . '">' . __( 'Delete Permanently' ) . '</a>';
				}

				$action_count = count( $actions );
				$i = 0;
				// change class to row-actions-visible to make them always visible rather than on hover
				$out = '<div class="row-actions">';
				foreach ( $actions as $action => $link ) {
					$i++;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					$out .= "<span class='$action'>$link$sep</span>";
				}
				$out .= '</div>';

				echo $out;
				break;
		}
	}
}

$hhs_image_only_cpt = new HHS_Image_Only_CPT;