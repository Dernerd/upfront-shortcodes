<?php

/**
 *  Resizes an image and returns an array containing the resized URL, width, height and file type. Uses native WordPress functionality.
 *
 *  @author Matthew Ruddy (http://easinglider.com)
 *  @return array   An array containing the resized image URL, width, height and file type.
 */
function su_image_resize( $url, $width = null, $height = null, $crop = true, $retina = false ) {
	global $wp_version;

	//######################################################################
	//  First implementation
	//######################################################################
	if ( isset( $wp_version ) && version_compare( $wp_version, '3.5' ) >= 0 ) {
		global $wpdb;
		if ( empty( $url ) ) {
			return new WP_Error( 'no_image_url', 'Es wurde keine Bild-URL eingegeben.', $url );
		}
		// Get default size from database
		$width  = ( $width ) ? $width : get_option( 'thumbnail_size_w' );
		$height = ( $height ) ? $height : get_option( 'thumbnail_size_h' );
		// Allow for different retina sizes
		$retina = $retina ? ( $retina === true ? 2 : $retina ) : 1;
		// Get the image file path
		$file_path = parse_url( $url );
		$file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];
		// Check for Multisite
		if ( is_multisite() ) {
			global $blog_id;
			$blog_details = get_blog_details( $blog_id );
			$file_path    = str_replace( $blog_details->path . 'files/', '/wp-content/blogs.dir/' . $blog_id . '/files/', $file_path );
		}
		// Destination width and height variables
		$dest_width  = intval( $width ) * intval( $retina );
		$dest_height = intval( $height ) * intval( $retina );
		// File name suffix (appended to original file name)
		$suffix = "{$dest_width}x{$dest_height}";
		// Some additional info about the image
		$info = pathinfo( $file_path );
		$dir  = $info['dirname'];
		$ext  = $info['extension'];
		$name = wp_basename( $file_path, ".$ext" );
		// Suffix applied to filename
		$suffix = "{$dest_width}x{$dest_height}";
		// Get the destination file name
		$dest_file_name = "{$dir}/{$name}-{$suffix}.{$ext}";
		if ( ! file_exists( $dest_file_name ) ) {
			$query          = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid='%s'", $url );
			$get_attachment = $wpdb->get_results( $query );
			if ( ! $get_attachment ) {
				return array(
					'url'    => $url,
					'width'  => $width,
					'height' => $height,
				);
			}
			// Load WordPress Image Editor
			$editor = wp_get_image_editor( $file_path );
			if ( is_wp_error( $editor ) ) {
				return array(
					'url'    => $url,
					'width'  => $width,
					'height' => $height,
				);
			}
			// Get the original image size
			$size        = $editor->get_size();
			$orig_width  = $size['width'];
			$orig_height = $size['height'];
			$src_x       = $src_y = 0;
			$src_w       = $orig_width;
			$src_h       = $orig_height;
			if ( $crop ) {

				$cmp_x = $orig_width / $dest_width;
				$cmp_y = $orig_height / $dest_height;

				// Calculate x or y coordinate, and width or height of source
				if ( $cmp_x > $cmp_y ) {
					$src_w = round( $orig_width / $cmp_x * $cmp_y );
					$src_x = round( ( $orig_width - ( $orig_width / $cmp_x * $cmp_y ) ) / 2 );
				} elseif ( $cmp_y > $cmp_x ) {
						$src_h = round( $orig_height / $cmp_y * $cmp_x );
						$src_y = round( ( $orig_height - ( $orig_height / $cmp_y * $cmp_x ) ) / 2 );
				}
			}

			// Time to crop the image!
			$editor->crop( $src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height );
			// Now let's save the image
			$saved = $editor->save( $dest_file_name );
			// Get resized image information
			$resized_url    = str_replace( basename( $url ), basename( $saved['path'] ), $url );
			$resized_width  = $saved['width'];
			$resized_height = $saved['height'];
			$resized_type   = $saved['mime-type'];
			// Add the resized dimensions to original image metadata (so we can delete our resized images when the original image is delete from the Media Library)
			$metadata = wp_get_attachment_metadata( $get_attachment[0]->ID );
			if ( isset( $metadata['image_meta'] ) ) {
				$metadata['image_meta']['resized_images'][] = $resized_width . 'x' . $resized_height;
				wp_update_attachment_metadata( $get_attachment[0]->ID, $metadata );
			}
			// Create the image array
			$image_array = array(
				'url'    => $resized_url,
				'width'  => $resized_width,
				'height' => $resized_height,
				'type'   => $resized_type,
			);
		} else {
			$image_array = array(
				'url'    => str_replace( basename( $url ), basename( $dest_file_name ), $url ),
				'width'  => $dest_width,
				'height' => $dest_height,
				'type'   => $ext,
			);
		}
		// Return image array
		return $image_array;
	}

	//######################################################################
	//  Second implementation
	//######################################################################
	else {
		global $wpdb;

		if ( empty( $url ) ) {
			return new WP_Error( 'no_image_url', 'Es wurde keine Bild-URL eingegeben.', $url );
		}

		// Bail if GD Library doesn't exist
		if ( ! extension_loaded( 'gd' ) || ! function_exists( 'gd_info' ) ) {
			return array(
				'url'    => $url,
				'width'  => $width,
				'height' => $height,
			);
		}

		// Get default size from database
		$width  = ( $width ) ? $width : get_option( 'thumbnail_size_w' );
		$height = ( $height ) ? $height : get_option( 'thumbnail_size_h' );

		// Allow for different retina sizes
		$retina = $retina ? ( $retina === true ? 2 : $retina ) : 1;

		// Destination width and height variables
		$dest_width  = $width * $retina;
		$dest_height = $height * $retina;

		// Get image file path
		$file_path = parse_url( $url );
		$file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];

		// Check for Multisite
		if ( is_multisite() ) {
			global $blog_id;
			$blog_details = get_blog_details( $blog_id );
			$file_path    = str_replace( $blog_details->path . 'files/', '/wp-content/blogs.dir/' . $blog_id . '/files/', $file_path );
		}

		// Some additional info about the image
		$info = pathinfo( $file_path );
		$dir  = $info['dirname'];
		$ext  = $info['extension'];
		$name = wp_basename( $file_path, ".$ext" );

		// Suffix applied to filename
		$suffix = "{$dest_width}x{$dest_height}";

		// Get the destination file name
		$dest_file_name = "{$dir}/{$name}-{$suffix}.{$ext}";

		// No need to resize & create a new image if it already exists!
		if ( ! file_exists( $dest_file_name ) ) {

			/*
				 *  Bail if this image isn't in the Media Library either.
				 *  We only want to resize Media Library images, so we can be sure they get deleted correctly when appropriate.
				 */
			$query          = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid='%s'", $url );
			$get_attachment = $wpdb->get_results( $query );
			if ( ! $get_attachment ) {
				return array(
					'url'    => $url,
					'width'  => $width,
					'height' => $height,
				);
			}

			$image = wp_load_image( $file_path );
			if ( ! is_resource( $image ) ) {
				return new WP_Error( 'error_loading_image_as_resource', $image, $file_path );
			}

			// Get the current image dimensions and type
			$size = @getimagesize( $file_path );
			if ( ! $size ) {
				return new WP_Error( 'file_path_getimagesize_failed', 'Fehler beim Abrufen von $file_path-Informationen mit getimagesize.' );
			}
			list( $orig_width, $orig_height, $orig_type ) = $size;

			// Create new image
			$new_image = wp_imagecreatetruecolor( $dest_width, $dest_height );

			// Do some proportional cropping if enabled
			if ( $crop ) {

				$src_x = $src_y = 0;
				$src_w = $orig_width;
				$src_h = $orig_height;

				$cmp_x = $orig_width / $dest_width;
				$cmp_y = $orig_height / $dest_height;

				// Calculate x or y coordinate, and width or height of source
				if ( $cmp_x > $cmp_y ) {
					$src_w = round( $orig_width / $cmp_x * $cmp_y );
					$src_x = round( ( $orig_width - ( $orig_width / $cmp_x * $cmp_y ) ) / 2 );
				} elseif ( $cmp_y > $cmp_x ) {
						$src_h = round( $orig_height / $cmp_y * $cmp_x );
						$src_y = round( ( $orig_height - ( $orig_height / $cmp_y * $cmp_x ) ) / 2 );
				}

				// Create the resampled image
				imagecopyresampled( $new_image, $image, 0, 0, $src_x, $src_y, $dest_width, $dest_height, $src_w, $src_h );
			} else {
				imagecopyresampled( $new_image, $image, 0, 0, 0, 0, $dest_width, $dest_height, $orig_width, $orig_height );
			}

			// Convert from full colors to index colors, like original PNG.
			if ( IMAGETYPE_PNG == $orig_type && function_exists( 'imageistruecolor' ) && ! imageistruecolor( $image ) ) {
				imagetruecolortopalette( $new_image, false, imagecolorstotal( $image ) );
			}

			// Remove the original image from memory (no longer needed)
			imagedestroy( $image );

			// Check the image is the correct file type
			if ( IMAGETYPE_GIF == $orig_type ) {
				if ( ! imagegif( $new_image, $dest_file_name ) ) {
					return new WP_Error( 'resize_path_invalid', 'Größe ändern Pfad ungültig (GIF)' );
				}
			} elseif ( IMAGETYPE_PNG == $orig_type ) {
				if ( ! imagepng( $new_image, $dest_file_name ) ) {
					return new WP_Error( 'resize_path_invalid', 'Größe ändern Pfad ungültig (PNG).' );
				}
			} else {

				// All other formats are converted to jpg
				if ( 'jpg' != $ext && 'jpeg' != $ext ) {
					$dest_file_name = "{$dir}/{$name}-{$suffix}.jpg";
				}
				if ( ! imagejpeg( $new_image, $dest_file_name, apply_filters( 'resize_jpeg_quality', 90 ) ) ) {
					return new WP_Error( 'resize_path_invalid', 'Größe ändern Pfad ungültig (JPG).' );
				}
			}

			// Remove new image from memory (no longer needed as well)
			imagedestroy( $new_image );

			// Set correct file permissions
			$stat  = stat( dirname( $dest_file_name ) );
			$perms = $stat['mode'] & 0000666;
			@chmod( $dest_file_name, $perms );

			// Get some information about the resized image
			$new_size = @getimagesize( $dest_file_name );
			if ( ! $new_size ) {
				return new WP_Error( 'resize_path_getimagesize_failed', 'Fehler beim Abrufen der Informationen zu $dest_file_name (Bild mit geänderter Größe) über @getimagesize', $dest_file_name );
			}
			list( $resized_width, $resized_height, $resized_type ) = $new_size;

			// Get the new image URL
			$resized_url = str_replace( basename( $url ), basename( $dest_file_name ), $url );

			// Add the resized dimensions to original image metadata (so we can delete our resized images when the original image is delete from the Media Library)
			$metadata = wp_get_attachment_metadata( $get_attachment[0]->ID );
			if ( isset( $metadata['image_meta'] ) ) {
				$metadata['image_meta']['resized_images'][] = $resized_width . 'x' . $resized_height;
				wp_update_attachment_metadata( $get_attachment[0]->ID, $metadata );
			}

			// Return array with resized image information
			$image_array = array(
				'url'    => $resized_url,
				'width'  => $resized_width,
				'height' => $resized_height,
				'type'   => $resized_type,
			);
		} else {
			$image_array = array(
				'url'    => str_replace( basename( $url ), basename( $dest_file_name ), $url ),
				'width'  => $dest_width,
				'height' => $dest_height,
				'type'   => $ext,
			);
		}

		return $image_array;
	}
}

/**
 *  Deletes the resized images when the original image is deleted from the WordPress Media Library.
 *
 *  @author Matthew Ruddy
 */
function su_delete_resized_images( $post_id ) {

	// Get attachment image metadata
	$metadata = wp_get_attachment_metadata( $post_id );
	if ( ! $metadata ) {
		return;
	}

	// Do some bailing if we cannot continue
	if ( ! isset( $metadata['file'] ) || ! isset( $metadata['image_meta']['resized_images'] ) ) {
		return;
	}
	$pathinfo       = pathinfo( $metadata['file'] );
	$resized_images = $metadata['image_meta']['resized_images'];

	// Get WordPress uploads directory (and bail if it doesn't exist)
	$wp_upload_dir = wp_upload_dir();
	$upload_dir    = $wp_upload_dir['basedir'];
	if ( ! is_dir( $upload_dir ) ) {
		return;
	}

	// Delete the resized images
	foreach ( $resized_images as $dims ) {

		// Get the resized images filename
		$file = $upload_dir . '/' . $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . $dims . '.' . $pathinfo['extension'];

		// Delete the resized image
		@unlink( $file );
	}
}

add_action( 'delete_attachment', 'su_delete_resized_images' );
