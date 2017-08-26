<?php
	/*
	  Plugin Name: Replace Broken Images
	  Version: 1.0
	  Plugin URI: http://www.mangbinhdinh.vn
	  Description: Alternate image with a default image if source image is not found on posts and pages.
	  Author: Huynh Mai Anh Kiet
	  Author URI: mailto:huynhmaianhkiet@gmail.com
	 */
	function HMAK_FixImageBrokenLink_settings_page()
	{	
		if ( isset( $_POST['submit_image_selector'] ) && isset( $_POST['image_attachment_id'] ) ) :
			check_admin_referer( 'default_image_for_bronken_link_nonce');
			update_option( 'default_image_for_bronken_link', absint( $_POST['image_attachment_id'] ) );
		endif;
		wp_enqueue_media();
		?>
			<style>
				h3.hndle2{
					cursor: pointer;
					border-bottom: 1px solid #eeeeee;
				}			
			</style>
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div class="inner-sidebar">
					<div id="side-sortables" class="meta-box-sortabless ui-sortable">
						<div class="postbox ">
							<h3 class="hndle2"><span>About This Plugin</span></h3>
							<div class="inside">
								<p>This plugin will help to replace broken images in post by a default image.</p>
								<p>
									Version: 1.0<br>
									Release date: 11/03/2017
								</p>
								
							</div>
						</div>
						<div class="postbox ">
								<h3 class="hndle2"><span>About Me</span></h3>
								<div class="inside">
									<p></p>
									<p>My name is Kiet. I’m come from Vietnam. I’m a web developer and I working for a small team in Vietnam. You can contact with me to:</p>
									<ul>
										<li>Email: <a href="mailto:huynhmaianhkiet@gmail.com">HuynhMaiAnhKiet@Gmail.Com</a></li>
										<li>Facebook: <a href="//www.facebook.com/huynhmaianhkiet" target="_blank">HuynhMaiAnhKiet</a></li>
										<li>Twitter: <a href="//twitter.com/huynhmaianhkiet" target="_blank">HuynhMaiAnhKiet</a></li>
										<li>Instagram: <a href="//www.instagram.com/huynhmaianhkiet/" target="_blank">HuynhMaiAnhKiet</a></li>
										<li>Website: <a href="http://www.mangbinhdinh.vn" target="_blank">wWw.MangBinhDinh.Vn</a></li>
									</ul>
									<p></p>
								</div>
						</div>
					</div>
				</div>
				<div class="has-sidebar sm-padded">
					<div id="post-body-content" class="has-sidebar-content">
						<div class="meta-box-sortabless">
							<div class="postbox">
								<h3 class="hndle2">Settings</h3>
								<div class="inside">
									<?php
										if(current_user_can('administrator')){
									?>
									<p>Please select alternate image</p>
									<form method='post'>
										 <?php wp_nonce_field('default_image_for_bronken_link_nonce') ?>
										<div class='image-preview-wrapper'>
											<img id='image-preview' src='<?php if(!get_option('default_image_for_bronken_link')){ echo plugins_url( 'images/default.jpg', __FILE__ ); }else{ echo wp_get_attachment_url( get_option( 'default_image_for_bronken_link' ) ); } ?>' height='100'>
										</div>
										<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image' ); ?>" />
										<input type='hidden' name='image_attachment_id' id='image_attachment_id' value='<?php echo get_option( 'default_image_for_bronken_link' ); ?>'>
										<input type="submit" name="submit_image_selector" value="Save" class="button-primary">
									</form>
									<?php
										}else{
											echo "<p style='text-align:center;'>You don't have permission to access</p>";
										}
									?>
								</div>
							</div>
							
							<div class="postbox">
								<div class="inside">
									<p style="text-align:center;">Copyright &copy; <?php echo date("Y"); ?> by <a href="http://www.mangbinhdinh.vn" target="_blank">wWw.MangBinhDinh.Vn</a>. All rights reserved.<br>Developed and Designed by <a href="mailto:huynhmaianhkiet@gmail.com">Huynh Mai Anh Kiet</a>.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
	}
	
	function add_FixImageBrokenLink_menu_item()
	{
		add_menu_page("Replace Broken Images Panel", "Replace Broken Images", "manage_options", "hmak-replace-broken-images-panel", "HMAK_FixImageBrokenLink_settings_page", null, 99);
	}
	add_action("admin_menu", "add_FixImageBrokenLink_menu_item");
	if($_GET['page']=='hmak-replace-broken-images-panel'){
		add_action( 'admin_footer', 'hmak_media_selector_print_scripts' );
	}
	function hmak_media_selector_print_scripts() {
		$hmak_saved_attachment_post_id = get_option( 'default_image_for_bronken_link', 0 );
		?><script type='text/javascript'>
			jQuery( document ).ready( function( $ ) {
				var file_frame;
				var wp_media_post_id = wp.media.model.settings.post.id;
				var set_to_post_id = <?php echo $hmak_saved_attachment_post_id; ?>;
				jQuery('#upload_image_button').on('click', function( event ){
					event.preventDefault();
					if ( file_frame ) {
						file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						file_frame.open();
						return;
					} else {
						wp.media.model.settings.post.id = set_to_post_id;
					}
					file_frame = wp.media.frames.file_frame = wp.media({
						title: 'Select a image to upload',
						button: {
							text: 'Use this image',
						},
						multiple: false
					});
					file_frame.on( 'select', function() {
						attachment = file_frame.state().get('selection').first().toJSON();
						$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
						$( '#image_attachment_id' ).val( attachment.id );
						wp.media.model.settings.post.id = wp_media_post_id;
					});
					file_frame.open();
				});
				jQuery( 'a.add_media' ).on( 'click', function() {
					wp.media.model.settings.post.id = wp_media_post_id;
				});
			});
		</script><?php
	}
	function HMAK_FixImageBrokenLink_main($content) {
		if(!get_option('default_image_for_bronken_link')){
			$code = "<img onerror=\"this.src='".plugins_url( 'images/default.jpg', __FILE__ )."'\"";
		}else{
			$code = "<img onerror=\"this.src='".wp_get_attachment_url( get_option( 'default_image_for_bronken_link' ) )."'\"";
		}
		$content = str_ireplace("<img", $code, $content);
		return $content;
	}	
	add_filter("the_content", 'HMAK_FixImageBrokenLink_main', 10);
?>