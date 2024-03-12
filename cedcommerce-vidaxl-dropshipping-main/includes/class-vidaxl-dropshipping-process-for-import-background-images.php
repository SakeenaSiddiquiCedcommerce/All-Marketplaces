<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Vidaxl_dropshipping_process_for_import_background_images extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'Vidaxl_dropshipping_background_process';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		$product_id  		= isset( $item['product_id'] ) ? $item['product_id'] : '';
		$image_url_array 	= isset( $item['images_url'] ) ? $item['images_url'] : array();
		// error_log("product id=".$product_id);
		// error_log("Image url array=".$image_url_array);
		try {
			$image_ids = array();
			foreach ( $image_url_array as $key1 => $value1 ) {
				$image_url  = $value1;	// Define the image URL here
				$image_name = basename($image_url);
				$upload_dir       = wp_upload_dir(); // Set upload folder
				// $image_data       = file_get_contents($image_url); // Get image data
				$image_url = str_replace('https', 'http', $image_url);
				$connection = curl_init();
				curl_setopt($connection, CURLOPT_URL, $image_url);
				curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
				$image_data = curl_exec($connection);	
				curl_close($connection);
				$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
				$filename         = basename( $unique_file_name ); // Create image file name
				if( wp_mkdir_p( $upload_dir['path'] ) ) {
					$file = $upload_dir['path'] . '/' . $filename;
				} else {
					$file = $upload_dir['basedir'] . '/' . $filename;
				}
				// Create the image  file on the server
				file_put_contents( $file, $image_data );

				// Check image file type
				$wp_filetype = wp_check_filetype( $filename, null );

				// Set attachment data
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => sanitize_file_name( $filename ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);
		
				// Create the attachment
				$attach_id = wp_insert_attachment( $attachment, $file, $product_id );
		
				// Include image.php
				require_once(ABSPATH . 'wp-admin/includes/image.php');

				// Define attachment metadata
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

				// Assign metadata to attachment
				wp_update_attachment_metadata( $attach_id, $attach_data );

				if ( 0 == $key1 ) {
					set_post_thumbnail( $product_id, $attach_id );
					error_log( 'Thumbnail id for feature image": ' . $attach_id );
				} else {
					$image_ids[] = $attach_id;

				}

			}	
			update_post_meta( $product_id, '_product_image_gallery', implode( ',', $image_ids ) );
			//error_log( 'Thumbnail id for gallery images": ' . $image_ids );
		} catch ( Exception $e ) {
			error_log( "Vidaxl Error log" . $e->getMessage() );
			return false;
		} 
		return false;
	}

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_downloading() {
		return $this->is_process_running();
	}

	protected function complete() {
		if ( ! $this->is_process_running() && $this->is_queue_empty() ) {
			set_transient( 'Vidaxl_dropshipping_process_for_import_background_images', time() );
		}
		// Show notice to user or perform some other arbitrary task...
		parent::complete();
	}


	public function delete_all_batches() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		}

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE {$column} LIKE %s", $key ) ); // @codingStandardsIgnoreLine.

		return $this;
	}

	/**
	 * Kill process.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public function kill_process() {
		if ( ! $this->is_queue_empty() ) {
			$this->delete_all_batches();
			wp_clear_scheduled_hook( $this->cron_hook_identifier );
		}
	}

	/**
	 * Is queue empty
	 *
	 * @return bool
	 */
	public function is_queue_empty() {
		return parent::is_queue_empty();
	}
}