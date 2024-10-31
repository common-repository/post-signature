<?php
/*
Plugin Name: Post Signature
Plugin URI: http://sillybean.net/code/wordpress/post-signature/
Description: Appends the author's display name to posts and/or pages. Useful for multi-author blogs that are crossposted elsewhere, such as LiveJournal or Facebook.
Version: 1.01
Author: Stephanie Leary
Author URI: http://sillybean.net/

Changelog
= 1.01 =
* Fixed i18n (January 30, 2010)
= 1.0 = 
* First release (January 17, 2010)

Copyright 2010  Stephanie Leary  (email : steph@sillybean.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_filter('the_content','post_signature');

function post_signature($content) {
	global $post;
	$options = get_option('post_signature_options');
	
	$author_id = $post->post_author;
	$author = get_userdata($author_id);
	$sig = '<p class="post-signature">&mdash; '.$author->display_name.'</p>';
	
	if (($post->post_type == 'page') && $options['pages'])
		$content .= $sig;
	if (($post->post_type == 'post') && $options['posts'])
		$content .= $sig;
	
	return $content;
}

// Hook for adding admin menus
add_action('admin_menu', 'post_signature_add_pages');

function post_signature_add_pages() {
    // Add a new submenu under Options:
	add_options_page(__('Post Signature'), __('Post Signature'), 8, 'post-signature', 'post_signature_settings_page');
	
	// set defaults
	$options = array(
	'posts' => 1,
	'pages' => 0,
	'hide_here' => 0,
		);
	add_option('post_signature_options', $options, '', 'yes');
	
	//call register settings function
	add_action( 'admin_init', 'register_post_signature_settings' );
}

function register_post_signature_settings() {
	//register our settings
	register_setting( 'post_signature_options', 'post_signature_options' );
}

function post_signature_settings_page() {
	if ( current_user_can('manage_options') ) {  
	?>
	<div class="wrap">
		<h2><?php _e('Post Signature', 'post-signature'); ?></h2>
		<form method="post" id="post-signature" action="options.php">
			<?php settings_fields('post_signature_options');
			$options = get_option('post_signature_options'); ?>
			<table class="form-table">
		        <tr valign="top">
			        <th scope="row"><?php _e('Display', 'post-signature'); ?></th>
			        <td>
						<p><label><input type="checkbox" name="post_signature_options[posts]" value="1" <?php checked(1, $options['posts']) ?> /> 
						<?php _e("Add the author's signature to posts.", 'post-signature'); ?></label></p>
						<p><label><input type="checkbox" name="post_signature_options[pages]" value="1" <?php checked(1, $options['pages']) ?> /> 
						<?php _e("Add the author's signature to pages.", 'post-signature'); ?></label></p>
					</td>
		        </tr>
					
				<tr valign="top">
			        <th scope="row">Hide on this site</th>
			        <td><p><label><input type="checkbox" name="post_signature_options[hide_here]" value="1" <?php checked(1, $options['hide_here']) ?> /> 
						<?php _e('Add CSS to this site to hide the signature. The signature will be shown in feeds and on any sites where your 
						posts are crossposted (such as LiveJournal or Facebook).'); ?></label></p></td>
		        </tr>
		    </table>
				
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'post-signature') ?>" />
			</p>

		</form>
	</div>
	<?php 
	} 
}

// Add CSS 
function post_signature_add_styles() {
	$options = get_option('post_signature_options'); 
	if ($options['hide_here']) 
		add_action('wp_head', 'post_signature_print_styles');
}
function post_signature_print_styles() {
	echo '<style type="text/css">';
	echo '.post-signature { display: none; }';
	echo '</style>';
}
add_action('init', 'post_signature_add_styles');

// when deactivated, return roles to normal
register_deactivation_hook( __FILE__, 'post_signature_remove_options' );

function post_signature_remove_options() {
	delete_option('post_signature_options');
}

// i18n
$plugin_dir = basename(dirname(__FILE__)). '/languages';
load_plugin_textdomain( 'post-signature', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
?>