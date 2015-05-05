<?php 
/*
Plugin Name: Media Load Related Posts
Description: Adds a metabox to 'Edit Media' page that lists posts containing the attachment or using the attachment as a featured image.
Version: 1.0
Author: Alicia Duffy
Author URI: http://www.aliciaduffy.com
*/

/**
 * Supporting AJAX script to load posts containing attachement
*/
function media_register_ajax_load_related() {
	$current_screen = get_current_screen();
	
	if ( !is_null($current_screen) && $current_screen->base == 'post' && $current_screen->post_type == 'attachment' ) {

		$url = plugin_dir_url( __FILE__ );
		global $post;
		// register scripts
		wp_register_script( 'media-load-related', plugins_url('js/media-load-related.js', __FILE__), array( 'jquery' ) );
		wp_localize_script( 'media-load-related', 'media_load_related', array( 
			'ajaxurl'    => admin_url( 'admin-ajax.php'),
			'pluginurl'  => $url,
			'postid'     => $post->ID
		) );
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'media-load-related' );
	}
}
add_action( 'admin_enqueue_scripts', 'media_register_ajax_load_related' );

/**
 * Create metabox to list posts containing attachement, is initially empty
 */
function media_list_related_posts_meta() {
	add_meta_box( 
		'list-attachment-posts-meta', 
		'Related Posts', 
		'media_list_attachment_posts_callback',
		'attachment',
		'side',
		'low'
    );
}
add_action( 'admin_init', 'media_list_related_posts_meta' );

/* Meta box initially contains post ID to pass to ajax function */
function media_list_attachment_posts_callback() {
	//	empty
}

/**
 * AJAX function to list all posts referencing/containing the attachment	 
 */	
function media_load_related_posts() {
	global $post;
	$postID = (isset($_GET['postid'])) ? $_GET['postid'] : 0;
	
	// get attachment metadata
	$metadata = wp_get_attachment_metadata($postID);
	$filename = $metadata[file];
	
	// remove the last 4 characters from file (extension and . - '.jpg' or '.gif')
	$filename = substr($filename, 0, -4);

	// query for all posts containing that file (or resized variations)
	$search_args = array(
		'post_status' => array( 'pending', 'draft', 'future', 'publish' ),
        'numberposts' => 500,
        's'           => $filename
    );
	    
	if ( $filename != '' ) {
		$posts_containing_image = get_posts($search_args);
    }
    // Posts that contain the image in the body content ?>
    <h4 style="margin-bottom: 0;">Posts Containing Image:</h4>
	<?php if ($posts_containing_image) { ?>
	    <ul style="margin-top: 0;">
	    <?php foreach ( $posts_containing_image as $post ) : setup_postdata( $post ); ?>
			<li>
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</li>
		<?php endforeach; ?>
	    </ul>
    <?php } else { ?>
		<p style="margin-top: 0;">None found.</p>	    	
    <?php }
    
    // Posts that use this image as featured image	
	$values_serialized = array($postID);
	$featured_args = array(
	    'post_type'  => $all_post_types,
	    'meta_query' => array (
			array(
				'key'     => '_thumbnail_id',
				'value'   => $values_serialized,
				'compare' => 'IN'
			)
		),
	   'numberposts' => 500
    );
	$posts_featured_image = get_posts($featured_args); ?>
	
	<h4 style="margin-bottom: 0;">Posts Featuring Image:</h4>				
	<?php if ($posts_featured_image) { ?>
		<ul style="margin-top: 0;">
		<?php foreach ( $posts_featured_image as $post ) : setup_postdata( $post ); ?>
			<li>
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</li>
		<?php endforeach; ?>
	    </ul>
	    <?php
	} else { ?>
		<p style="margin-top: 0;">None found.</p>	    	
    <?php }
    
	die();
}
add_action( "wp_ajax_media_load_related_posts", "media_load_related_posts" );
add_action( "wp_ajax_nopriv_media_load_related_posts", "media_load_related_posts" );