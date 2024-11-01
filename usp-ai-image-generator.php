<?php
/**
* Plugin Name: USP AI
* Plugin URI: https://usp.ai/
* Description: Awesome generated ROYALTY FREE AI images for your posts and blogs.
* Version: 1.0
* Author: uspai
* Author URI: https://usp.ai/wp-plugin
**/

define('USP_AI_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('USP_AI_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('USP_AI_API_SITE_URL','https://app.usp.ai');

add_action( 'wp_enqueue_scripts', 'usp_ai_load_css_js' );
add_action( 'admin_enqueue_scripts', 'usp_ai_load_css_js' );

function usp_ai_load_css_js() {
  $ver_num = mt_rand();
  wp_enqueue_style('usp-ai-css', USP_AI_PLUGIN_URL .'css/style.css', array(), $ver_num,'all');
  wp_enqueue_script('usp-ai-script', USP_AI_PLUGIN_URL .'js/custom.js', array(), $ver_num,'all');
  wp_localize_script( 'usp-ai-script', 'ajax_var', array( 'ajaxurl' => admin_url('admin-ajax.php') ));
}

require_once(USP_AI_PLUGIN_PATH.'wp-ajax/wp-usp-image-genarator-ajax.php');

add_action( 'admin_menu', 'usp_ai_generate_image_api_setting' );
function usp_ai_generate_image_api_setting(){
	add_menu_page('Usp AI', 'Usp AI Setting', 'manage_options', 'usp_ai_settings', 'usp_ai_api_settings_form');
	add_submenu_page( 'upload.php', 'Generate AI Images', 'Generate AI Images', 'manage_categories', 'generate-usp-ai-images', 'usp_ai_generate_image_page_html');
}

add_action('media_buttons', 'usp_ai_add_generate_media_button');
function usp_ai_add_generate_media_button(){
	echo '<a href="#" id="wp-insert-usp-media" class="button">Generate AI Images</a>';
}

function usp_ai_generate_block_init() {
    register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'usp_ai_generate_block_init' );

function usp_ai_api_settings_form(){
	?>
<div class="wp-usp-form">
	<form method="post" style="margin: 5%;">
		<h5><?php esc_html_e( 'USP AI API Setting' ); ?> </h5>
		<div class="wp-usp-setting">
			<div class="loader loader-design wp-usp-setting-loader" id="wp-usp-image-loader"></div>
			<div class="wp-usp-setting-email" style="margin: 1%;">
				<label for="email"><?php esc_html_e( 'Email' ); ?></label>
				<input type="text" name="email" id="wp-usp-setting-email-field" value="<?php echo esc_attr(get_option('wp_test_plug_email'))?>">
			</div>
			<div class="wp-usp-setting-key" style="margin: 1%;">
				<label for="key"><?php esc_html_e( 'Key' ); ?></label>
				<input type="text" name="key" id="wp-usp-setting-key-field" value="<?php echo esc_attr(get_option('wp_test_plug_key'))?>">
			</div>
			<div class="wp-usp-setting-button" style="margin: 1%;">
				<button type="submit" id="wp-usp-setting-submit" name="submit"> <?php esc_html_e( 'Submit' ); ?></button>
			</div>
			<span class="wp-usp-api-error"></span>
			<span class="wp-usp-no-account"><?php esc_html_e( 'Don\'t have an account?' ); ?>  <a href="<?php esc_html_e(USP_AI_API_SITE_URL);?>" target="_blank"><?php esc_html_e( 'click here' ); ?> </span>
		</div>
	</form>
</div>
	<?php
}
function usp_ai_generate_image_page_html(){
	?>
	<div class="wp-usp-image">
		<div class="wp-usp-image-heading">
			<span class="wp-usp-image-heading-logo"><img src="<?php echo USP_AI_PLUGIN_URL."assets/logo.png"; ?>">
			</span>
		</div>
		<div class="wp-usp-image-content">
			<form method="post" action="">
				<div class="wp-usp-fields">
					<div class="wp-usp-image-textarea">
						<label><?php esc_html_e( 'What would you like to create?' ); ?></label>
						<textarea name="description" id="wp-usp-description-generate-image"></textarea>
					</div>
					<div class="wp-usp-image-select model">
            <div class="dropdown-model">
              <label> Model </label>
               	<div class="wp-usp-ai-image dropdown-content">
                  <div class="loader loader-design" id="wp-usp-image-model-loader"></div>
               	</div>
            </div>
          </div>

					<div class="wp-usp-image-select">
						<label><?php esc_html_e( 'Choose image ratio' ); ?></label>
						<select id="wp-usp-ratio-generate-image" name="ratio" id="image-ratio" class="wp-usp-ratio-generate-image">
							<option value="square"><?php esc_html_e( 'Square (1:1)' ); ?></option>
							<option value="portrait"><?php esc_html_e( 'Portrait (9:16)' ); ?></option>
							<option value="landscape"><?php esc_html_e( 'Landscape (16:9)'); ?></option>
						</select>
					</div>
					<div class="wp-usp-image-select wp-usp-image-select-alt-text">
            <label><?php esc_html_e( 'Enter alternative text' ); ?></label>
            <input class="usp-ai-input" name="alt" type="text" id="usp-ai-alt-text">
          </div>
					<span class="wp-usp-api-error"></span>
					<div class="wp-usp-image-btn">
						<button class="wp-usp-submit" id="wp-usp-submit-generate-image" type="submit" name="submit"><?php esc_html_e( 'Generate AI Images' ); ?></button>
					</div>
					<div class="credit">
							<span class="credit-name">
							<?php esc_html_e( 'Credit:' ); ?>
							</span>
								<span class="credit-no">
								6
							</span>
						<span class="credit-image">
							<img src="<?php echo USP_AI_PLUGIN_URL."assets/crown-icon.png"; ?>">
						</span>
					</div>
					<span class="wp-usp-no-account-generate"><?php esc_html_e( 'Don\'t have an account?' ); ?> <a href="<?php echo esc_url(USP_AI_API_SITE_URL);?>" target="_blank"><?php echo esc_html( 'click here' ); ?></a></span>
				</div>
			
				<div class="overlay">
					<div class="wp-usp-no-credit">
						<div class="modal" tabindex="-1" role="dialog"> 
							<div class="modal-dialog" role="document"> 
								<div class="modal-content"> 
									<div class="modal-header"> 
										<h5 class="modal-title"><?php esc_html_e( 'Get lifetime deal' ); ?></h5> 
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"> 
											<span aria-hidden="true">&times;</span> 
										</button> 
									</div>
									<div class="modal-body"> 
										<p><?php esc_html_e( 'Get USP.ai lifetime deal with 1500 monthly credits.' ); ?></p>
										<a id="wp-usp-ratio-get-it-link" href="https://usp.ai/" target="_blank"><button type="button" class="btn btn-primary"><?php esc_html_e( 'Get it now' ); ?></button></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="wp-usp-image-box">
					<div class="loader loader-design" id="wp-usp-image-loader"></div>
					<div class="wp-usp-image-img message-box" id="wp-usp-generated-image">
						<span class="wp-usp-message"><?php esc_html_e( 'Input an image description to begin generating images.' ); ?></span>
					</div>
					<div class="wp-usp-msg">
					</div>
					<div class="wp-usp-dwn-btn">
					<button class="wp-usp-clear" type="submit" id="wp-usp-generate-use-image" img-data="<?php echo USP_AI_PLUGIN_URL."assets/default-image-icon-missing-picture-page-vector-40546530.jpg"?>"><?php esc_html_e( 'Use' ); ?> 
					</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php
}