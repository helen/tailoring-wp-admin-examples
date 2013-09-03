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
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages',             array( $this, 'slide_messages' ) );
		add_filter( 'admin_post_thumbnail_html', array( $this, 'admin_post_thumbnail_html' ), 10, 2 );
		add_filter( 'media_view_strings', array( $this, 'media_view_strings' ), 10, 2 );
		add_filter( 'manage_edit-slide_columns', array( $this, 'manage_edit_columns' ) );
		add_action( 'manage_slide_posts_custom_column',  array( $this, 'custom_edit_columns' ), 10, 2 );
	}

	/**
	 * Register our custom post type of slide.
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
		    'name' => _x( 'Slide', 'post type general name' ),
		    'singular_name' => _x( 'Slide', 'post type singular name' ),
		    'add_new' => _x( 'Add Slide', 'slide' ),
		    'add_new_item' => __( 'Add New Slide' ),
		    'edit_item' => __( 'Edit Slide' ),
		    'new_item' => __( 'New Slide' ),
		    'view_item' => __( 'View Slides' ),
		    'search_items' => __( 'Search Slides' ),
		    'not_found' =>  __( 'No Slides found' ),
		    'not_found_in_trash' => __( 'No Slides found in Trash' ),
		    'parent_item_colon' => '',
		);

		register_post_type( 'slide', array (
			'label' => __( 'Slides' ),
			'labels' => $labels,
			'show_in_menu' => 'themes.php', // we don't need a whole top level menu item
			'show_in_nav_menus' => false,
			'exclude_from_search' => true,
			'hierarchical' => true, // for ease of ordering
			'supports' => array( 'thumbnail' ),
			'register_meta_box_cb' => array( $this, 'meta_boxes' ),
		) );
	}

	/**
	 * Adjust messages from 'post'-specific to 'slide'-specific
	 *
	 * @param array $messages The messages strings array.
	 *
	 * @global WP_Post $post    The post object.
	 * @global int     $post_ID The post id.
	 *
	 * @return array The messages strings array.
	 */
	public function slide_messages( $messages = array() ) {
		global $post, $post_ID;

		$messages['slide'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Slide updated. <a href="%s">View slide</a>.', 'textdomain' ), esc_url( get_permalink( $post_ID ) ) ),
			2  => __( 'Custom field updated.', 'textdomain' ),
			3  => __( 'Custom field deleted.', 'textdomain' ),
			4  => __( 'Slide updated.', 'textdomain' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Slide restored to revision from %s', 'textdomain' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Slide published. <a href="%s">View slide</a>', 'textdomain' ), esc_url( get_permalink( $post_ID ) ) ),
			7  => __( 'Slide saved.', 'textdomain' ),
			8  => sprintf( __( 'Slide submitted. <a target="_blank" href="%s">Preview slide</a>', 'textdomain' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9  => sprintf( __( 'Slide scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview slide</a>', 'textdomain' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'textdomain' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Slide draft updated. <a target="_blank" href="%s">Preview slide</a>', 'textdomain' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

	/**
	 * Remove the post thumbnail meta box from its default side position
	 * and move it to the main column, relabeling it appropriately
	 * in the process.
	 * @return void
	 */
	public function meta_boxes() {
		remove_meta_box( 'postimagediv', 'slide', 'side' );

		add_meta_box( 'postimagediv', __( 'Slide Image' ), 'post_thumbnail_meta_box', 'slide', 'normal', 'high' );
	}

	/**
	 * Replace some pieces inside the post thumbnail meta box
	 * to make it more specific to the usage.
	 * @param string $output  Inner HTML of the post thumbnail meta box.
	 * @param int $post_id ID of the currently edited post.
	 * @return string
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
	 * Set some of the strings in the 3.5+ media modal to be
	 * specific to the usage of selecting a slide image.
	 * @param array $strings Array of strings for the media modal.
	 * @param object $post   Post object.
	 * @return array
	 */
	public function media_view_strings( $strings, $post ) {
		if ( 'slide' === get_post_type( $post ) ) {
			$strings['setFeaturedImageTitle'] = 'Set slide image';
			$strings['setFeaturedImage'] = 'Set slide image';
		}

		return $strings;
	}

	/**
	 * Set the columns to be only what's necessary.
	 * @param array $columns Columns for the list table.
	 * @return array
	 */
	public function manage_edit_columns( $columns ) {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'thumbnail' => 'Slide',
		);

		return $columns;
	}

	/**
	 * Post-specific display for any custom columns in the list table.
	 * @param string $column Key of the column.
	 * @param int $post_id ID of the post in question.
	 * @return void
	 */
	public function custom_edit_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'thumbnail' :
				if ( has_post_thumbnail( $post_id ) )
					the_post_thumbnail( $post_id );
				else
					echo 'No image';

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
				$out = '<div class="row-actions">'; // change class to row-actions-visible to make them always visible rather than on hover
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