<?php
/**
 * Media handler
 *
 * @package Top_Ten
 */

/**
 * Add custom image size of thumbnail. Filters `init`.
 *
 * @since 2.0.0
 */
function tptn_add_image_sizes() {
	$thumb_size = tptn_get_option( 'thumb_size' );

	if ( ! in_array( $thumb_size, get_intermediate_image_sizes() ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		$thumb_size = 'tptn_thumbnail';
	}

	// Add image sizes if 'tptn_thumbnail' is selected or the selected thumbnail size is no longer valid.
	if ( 'tptn_thumbnail' === $thumb_size && tptn_get_option( 'thumb_create_sizes' ) ) {
		$width  = tptn_get_option( 'thumb_width', 150 );
		$height = tptn_get_option( 'thumb_height', 150 );
		$crop   = tptn_get_option( 'thumb_crop', true );

		add_image_size( 'tptn_thumbnail', $width, $height, $crop );
	}
}
add_action( 'init', 'tptn_add_image_sizes' );


/**
 * Function to get the post thumbnail.
 *
 * @since   1.8
 * @param   array $args   Query string of options related to thumbnails.
 * @return  string  Image tag
 */
function tptn_get_the_post_thumbnail( $args = array() ) {

	$defaults = array(
		'postid'             => '',
		'thumb_height'       => '150',            // Max height of thumbnails.
		'thumb_width'        => '150',         // Max width of thumbnails.
		'thumb_meta'         => 'post-image',       // Meta field that is used to store the location of default thumbnail image.
		'thumb_html'         => 'html',     // HTML / CSS for width and height attributes.
		'thumb_default'      => '',  // Default thumbnail image.
		'thumb_default_show' => true,   // Show default thumb if none found (if false, don't show thumb at all).
		'scan_images'        => false,         // Scan post for images.
		'class'              => 'tptn_thumb',            // Class of the thumbnail.
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	// Issue notice for deprecated arguments.
	if ( isset( $args['thumb_timthumb'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.1', esc_html__( 'thumb_timthumb argument has been deprecated', 'top-10' ) );
	}

	if ( isset( $args['thumb_timthumb_q'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.1', esc_html__( 'thumb_timthumb_q argument has been deprecated', 'top-10' ) );
	}

	if ( isset( $args['filter'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.1', esc_html__( 'filter argument has been deprecated', 'top-10' ) );
	}

	if ( is_int( $args['postid'] ) ) {
		$result = get_post( $args['postid'] );
	} else {
		$result = $args['postid'];
	}

	$post_title = esc_attr( $result->post_title );

	/**
	 * Filters the title and alt message for thumbnails.
	 *
	 * @since   2.3.0
	 *
	 * @param   string  $post_title     Post tile used as thumbnail alt and title
	 * @param   object  $result         Post Object
	 */
	$post_title = apply_filters( 'tptn_thumb_title', $post_title, $result );

	$output    = '';
	$postimage = '';
	$pick      = '';

	// Let's start fetching the thumbnail. First place to look is in the post meta defined in the Settings page.
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, $args['thumb_meta'], true );
		$pick      = 'meta';
		if ( $postimage ) {
			$postimage_id = tptn_get_attachment_id_from_url( $postimage );

			if ( false != wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$postthumb = wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) );
				$postimage = $postthumb[0];
			}
			$pick .= 'correct';
		}
	}

	// If there is no thumbnail found, check the post thumbnail.
	if ( ! $postimage ) {
		if ( false != get_post_thumbnail_id( $result->ID ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$postthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $result->ID ), array( $args['thumb_width'], $args['thumb_height'] ) );

			if ( false !== $postthumb ) {
				$postimage = $postthumb[0];
				$pick      = 'featured';
			}
		}
	}

	// If there is no thumbnail found, fetch the first image in the post, if enabled.
	if ( ! $postimage && $args['scan_images'] ) {
		preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $result->post_content, $matches );
		if ( isset( $matches[1][0] ) && $matches[1][0] ) {          // any image there?
			$postimage = $matches[1][0]; // we need the first one only!
		}
		$pick = 'first';
		if ( $postimage ) {
			$postimage_id = tptn_get_attachment_id_from_url( $postimage );

			if ( false != wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$postthumb = wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) );
				$postimage = $postthumb[0];
				$pick     .= 'correct';
			}
		}
	}

	// If there is no thumbnail found, fetch the first child image.
	if ( ! $postimage ) {
		$postimage = tptn_get_first_image( $result->ID, $args['thumb_width'], $args['thumb_height'] );  // Get the first image.
		$pick      = 'firstchild';
	}

	// If no other thumbnail set, try to get the custom video thumbnail set by the Video Thumbnails plugin.
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, '_video_thumbnail', true );
		$pick      = 'video_thumb';
	}

	// If no thumb found and settings permit, use default thumb.
	if ( ! $postimage && $args['thumb_default_show'] ) {
		$postimage = $args['thumb_default'];
		$pick      = 'default_thumb';
	}

	// Hopefully, we've found a thumbnail by now. If so, run it through the custom filter, check for SSL and create the image tag.
	if ( $postimage ) {

		/**
		 * Filters the thumbnail image URL.
		 *
		 * Use this filter to modify the thumbnail URL that is automatically created
		 * Before v2.1 this was used for cropping the post image using timthumb
		 *
		 * @since   2.1.0
		 *
		 * @param   string  $postimage      URL of the thumbnail image
		 * @param   int     $thumb_width    Thumbnail width
		 * @param   int     $thumb_height   Thumbnail height
		 * @param   object  $result         Post Object
		 */
		$postimage = apply_filters( 'tptn_thumb_url', $postimage, $args['thumb_width'], $args['thumb_height'], $result );

		/* Backward compatibility */
		$thumb_timthumb   = false;
		$thumb_timthumb_q = 75;

		/**
		 * Filters the thumbnail image URL.
		 *
		 * @since 1.8.10
		 * @deprecated  2.1.0   Use tptn_thumb_url instead.
		 *
		 * @param   string  $postimage      URL of the thumbnail image
		 * @param   int     $thumb_width    Thumbnail width
		 * @param   int     $thumb_height   Thumbnail height
		 * @param   boolean $thumb_timthumb Enable timthumb?
		 * @param   int     $thumb_timthumb_q   Quality of timthumb thumbnail.
		 * @param   object  $result         Post Object
		 */
		$postimage = apply_filters( 'tptn_postimage', $postimage, $args['thumb_width'], $args['thumb_height'], $thumb_timthumb, $thumb_timthumb_q, $result );

		if ( is_ssl() ) {
			$postimage = preg_replace( '~http://~', 'https://', $postimage );
		}

		$class = $args['class'] . ' tptn_' . $pick;

		/**
		 * Filters the thumbnail classes and allows a filter function to add any more classes if needed.
		 *
		 * @since 2.2.0
		 *
		 * @param string $class Thumbnail Class
		 */
		$attr['class'] = apply_filters( 'tptn_thumb_class', $class );

		/**
		 * Filters the thumbnail alt.
		 *
		 * @since 2.6.0
		 *
		 * @param string $post_title Thumbnail alt attribute
		 */
		$attr['alt'] = apply_filters( 'tptn_thumb_alt', $post_title );

		/**
		 * Filters the thumbnail title.
		 *
		 * @since 2.6.0
		 *
		 * @param string $post_title Thumbnail title attribute
		 */
		$attr['title'] = apply_filters( 'tptn_thumb_title', $post_title );

		$attr['thumb_html']   = $args['thumb_html'];
		$attr['thumb_width']  = $args['thumb_width'];
		$attr['thumb_height'] = $args['thumb_height'];

		$output .= tptn_get_image_html( $postimage, $attr );

	}

	/**
	 * Filters post thumbnail created for Top 10.
	 *
	 * @since   1.9.10.1
	 *
	 * @param   array   $output Formatted output
	 * @param   array   $args   Argument list
	 * @param   string  $postimage Thumbnail URL
	 */
	return apply_filters( 'tptn_get_the_post_thumbnail', $output, $args, $postimage );
}

/**
 * Get an HTML img element
 *
 * @since 2.6.0
 *
 * @param string $attachment_url Image URL.
 * @param array  $attr Attributes for the image markup.
 * @return string HTML img element or empty string on failure.
 */
function tptn_get_image_html( $attachment_url, $attr = array() ) {

	// If there is no url, return.
	if ( '' == $attachment_url ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		return;
	}

	$default_attr = array(
		'src'          => $attachment_url,
		'thumb_html'   => tptn_get_option( 'thumb_html', 'html' ),
		'thumb_width'  => tptn_get_option( 'thumb_width', 150 ),
		'thumb_height' => tptn_get_option( 'thumb_height', 150 ),
	);

	$attr = wp_parse_args( $attr, $default_attr );

	$hwstring = tptn_get_image_hwstring( $attr );

	// Generate 'srcset' and 'sizes' if not already present.
	if ( empty( $attr['srcset'] ) ) {
		$attachment_id = tptn_get_attachment_id_from_url( $attachment_url );
		$image_meta    = wp_get_attachment_metadata( $attachment_id );

		if ( is_array( $image_meta ) ) {
			$size_array = array( absint( $attr['thumb_width'] ), absint( $attr['thumb_height'] ) );
			$srcset     = wp_calculate_image_srcset( $size_array, $attachment_url, $image_meta, $attachment_id );
			$sizes      = wp_calculate_image_sizes( $size_array, $attachment_url, $image_meta, $attachment_id );

			if ( $srcset && ( $sizes || ! empty( $attr['sizes'] ) ) ) {
				$attr['srcset'] = $srcset;

				if ( empty( $attr['sizes'] ) ) {
					$attr['sizes'] = $sizes;
				}
			}
		}
	}

	// Unset attributes we don't want to display.
	unset( $attr['thumb_html'] );
	unset( $attr['thumb_width'] );
	unset( $attr['thumb_height'] );

	/**
	 * Filters the list of attachment image attributes.
	 *
	 * @since 2.6.0
	 *
	 * @param array  $attr Attributes for the image markup.
	 * @param string $attachment_url Image URL.
	 */
	$attr = apply_filters( 'tptn_get_image_attributes', $attr, $attachment_url );
	$attr = array_map( 'esc_attr', $attr );

	$html = '<img ' . $hwstring;
	foreach ( $attr as $name => $value ) {
		$html .= " $name=" . '"' . $value . '"';
	}
	$html .= ' />';

	return apply_filters( 'tptn_get_image_html', $html );
}


/**
 * Retrieve width and height attributes using given width and height values.
 *
 * @since 2.6.0
 *
 * @param array $args Argument array.
 *
 * @return string Height-width string.
 */
function tptn_get_image_hwstring( $args = array() ) {

	$default_args = array(
		'thumb_html'   => tptn_get_option( 'thumb_html', 'html' ),
		'thumb_width'  => tptn_get_option( 'thumb_width', 150 ),
		'thumb_height' => tptn_get_option( 'thumb_height', 150 ),
	);

	$args = wp_parse_args( $args, $default_args );

	if ( 'css' === $args['thumb_html'] ) {
		$thumb_html = ' style="max-width:' . $args['thumb_width'] . 'px;max-height:' . $args['thumb_height'] . 'px;" ';
	} elseif ( 'html' === $args['thumb_html'] ) {
		$thumb_html = ' width="' . $args['thumb_width'] . '" height="' . $args['thumb_height'] . '" ';
	} else {
		$thumb_html = '';
	}

	/**
	 * Filters the thumbnail HTML and allows a filter function to add any more HTML if needed.
	 *
	 * @since   2.2.0
	 *
	 * @param string $thumb_html Thumbnail HTML.
	 * @param array  $args       Argument array.
	 */
	return apply_filters( 'tptn_thumb_html', $thumb_html, $args );
}


/**
 * Get the first child image in the post.
 *
 * @since   1.9.8
 * @param   mixed $postid Post ID.
 * @param   int   $thumb_width Thumb width.
 * @param   int   $thumb_height Thumb height.
 * @return  string  Location of thumbnail
 */
function tptn_get_first_image( $postid, $thumb_width, $thumb_height ) {
	$args = array(
		'numberposts'    => 1,
		'order'          => 'ASC',
		'post_mime_type' => 'image',
		'post_parent'    => $postid,
		'post_status'    => null,
		'post_type'      => 'attachment',
	);

	$attachments = get_children( $args );

	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$image_attributes = wp_get_attachment_image_src( $attachment->ID, array( $thumb_width, $thumb_height ) ) ? wp_get_attachment_image_src( $attachment->ID, array( $thumb_width, $thumb_height ) ) : wp_get_attachment_image_src( $attachment->ID, 'full' );

			/**
			 * Filters first child attachment from the post.
			 *
			 * @since 1.9.10.1
			 *
			 * @param array $image_attributes[0] URL of the image
			 * @param int   $postid              Post ID
			 * @param int   $thumb_width         Thumb width
			 * @param int   $thumb_height        Thumb height
			 */
			return apply_filters( 'tptn_get_first_image', $image_attributes[0], $postid, $thumb_width, $thumb_height );
		}
	} else {
		return false;
	}
}


/**
 * Function to get the attachment ID from the attachment URL.
 *
 * @since 2.1
 *
 * @param   string $attachment_url Attachment URL.
 * @return  int     Attachment ID
 */
function tptn_get_attachment_id_from_url( $attachment_url = '' ) {

	global $wpdb;
	$attachment_id = false;

	// If there is no url, return.
	if ( '' == $attachment_url ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		return;
	}

	// Get the upload directory paths.
	$upload_dir_paths = wp_upload_dir();

	// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image.
	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

		// If this is the URL of an auto-generated thumbnail, get the URL of the original image.
		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

		// Remove the upload path base directory from the attachment URL.
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

		// Finally, run a custom database query to get the attachment ID from the modified attachment URL.
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = %s AND wposts.post_type = 'attachment'", $attachment_url ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	}

	/**
	 * Filter the attachment ID from the attachment URL.
	 *
	 * @since 2.1
	 *
	 * @param   int     Attachment ID
	 * @param   string  $attachment_url Attachment URL
	 */
	return apply_filters( 'tptn_get_attachment_id_from_url', $attachment_id, $attachment_url );
}


/**
 * Function to get the correct height and width of the thumbnail.
 *
 * @since   2.2.0
 *
 * @param  array $args Array of arguments.
 * @return array Width and height
 */
function tptn_get_thumb_size( $args ) {

	// Get thumbnail size.
	$tptn_thumb_size = tptn_get_all_image_sizes( $args['thumb_size'] );

	if ( isset( $tptn_thumb_size['width'] ) ) {
		$thumb_width  = $tptn_thumb_size['width'];
		$thumb_height = $tptn_thumb_size['height'];
	}

	if ( empty( $thumb_width ) || ( $args['is_widget'] && $thumb_width != $args['thumb_width'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		$thumb_width        = $args['thumb_width'];
		$args['thumb_html'] = 'css';
	}

	if ( empty( $thumb_height ) || ( $args['is_widget'] && $thumb_height != $args['thumb_height'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		$thumb_height       = $args['thumb_height'];
		$args['thumb_html'] = 'css';
	}

	$thumb_size = array( $thumb_width, $thumb_height );

	/**
	 * Filter array of thumbnail size.
	 *
	 * @since   2.2.0
	 *
	 * @param   array   $thumb_size Array with width and height of thumbnail
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_get_thumb_size', $thumb_size, $args );

}


/**
 * Get all image sizes.
 *
 * @since   2.0.0
 * @param   string $size   Get specific image size.
 * @return  array   Image size names along with width, height and crop setting
 */
function tptn_get_all_image_sizes( $size = '' ) {
	global $_wp_additional_image_sizes;

	/* Get the intermediate image sizes and add the full size to the array. */
	$intermediate_image_sizes = get_intermediate_image_sizes();

	foreach ( $intermediate_image_sizes as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict

			$sizes[ $_size ]['name']   = $_size;
			$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );

			if ( ( 0 == $sizes[ $_size ]['width'] ) && ( 0 == $sizes[ $_size ]['height'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				unset( $sizes[ $_size ] );
			}
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

			$sizes[ $_size ] = array(
				'name'   => $_size,
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => (bool) $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	/* Get only 1 size if found */
	if ( $size ) {
		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}
	}

	/**
	 * Filters array of image sizes.
	 *
	 * @since   2.0.0
	 *
	 * @param   array   $sizes  Image sizes
	 */
	return apply_filters( 'tptn_get_all_image_sizes', $sizes );
}


