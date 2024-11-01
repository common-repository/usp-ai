<?php
add_action('wp_ajax_usp_ai_get_model', 'usp_ai_get_model_callback_function');
add_action('wp_ajax_nopriv_usp_ai_get_model', 'usp_ai_get_model_callback_function');

function usp_ai_get_model_callback_function(){
	$result['status'] = 404;
	$apiUrlModels = sanitize_url($_POST['apiUrlModels']);
	$resultModels = uspai_api_curl_call_function($apiUrlModels,);
	if($resultModels != 'false'){
		$result['status'] = 200;
		$resultModels = json_decode($resultModels);
		$result['data'] = $resultModels;
	}
	echo wp_json_encode($result,JSON_UNESCAPED_SLASHES);
	wp_die();
}

add_action('wp_ajax_usp_ai_get_image', 'usp_ai_get_image_callback_function');
add_action('wp_ajax_nopriv_usp_ai_get_image', 'usp_ai_get_image_callback_function');

function usp_ai_get_image_callback_function(){
	$postFields['username'] = get_option('wp_test_plug_email');
	$postFields['apiKey'] = get_option('wp_test_plug_key');
	$result['status'] = 404;
	$apiUrlCredit = sanitize_url($_POST['apiUrlCredit']);
	$resultCredit = uspai_api_curl_call_function($apiUrlCredit,$postFields,'post');
	if($resultCredit != 'false'){
		$resultCredit = json_decode($resultCredit);
		$result['credit'] = $resultCredit->creditsLeft;
		if($result['credit']){
			if(isset($_POST['msg'])){
				$data = sanitize_text_field($_POST['msg']);
				$postFields['prompt'] = $data;
			}if(isset($_POST['ratio'])){
				$data = sanitize_text_field($_POST['ratio']);
				$postFields['ratio'] = $data;
			}if(isset($_POST['model_id'])){
				$data = sanitize_text_field($_POST['model_id']);
				$postFields['model_id'] = $data;
			}
			$apiUrlPrompt = sanitize_url($_POST['apiUrl']);
			
			$resultPrompt = uspai_api_curl_call_function($apiUrlPrompt,$postFields,'post');
			if($resultPrompt!='false'){
				$result['status'] = 200;
				$resultImg = json_decode($resultPrompt);
				$result['imgUrl'] = $resultImg->output[0];
			}
		}
	}
	echo wp_json_encode($result,JSON_UNESCAPED_SLASHES);
	wp_die();
}

add_action('wp_ajax_usp_ai_set_setting', 'usp_ai_set_setting_callback_function');
add_action('wp_ajax_nopriv_usp_ai_set_setting', 'usp_ai_set_setting_callback_function');

function usp_ai_set_setting_callback_function(){
	$postFields['username'] = get_option('wp_test_plug_email');
	$postFields['apiKey'] = get_option('wp_test_plug_key');
	$apiUrl = '';
	$response['status'] = 500;
	if(isset($_POST['email']) && isset($_POST['key']) && isset($_POST['apiUrl'])){
		if($_POST['email'] != '' && $_POST['key'] != ''){
			$postFields['username'] = sanitize_email($_POST['email']);
			$postFields['apiKey'] = sanitize_text_field($_POST['key']);
			$apiUrl = sanitize_url($_POST['apiUrl']);
			$result = uspai_api_curl_call_function($apiUrl,$postFields,'post');
			if(!empty($result)){
				update_option('wp_test_plug_key',$postFields['apiKey']);
     			update_option('wp_test_plug_email',$postFields['username']);
     			$response['status'] = 400;
			}else{
				$response['status'] = 404;
			}
		}else{
			$response['status'] = 300;
		}
	}
	echo wp_json_encode($response);
	wp_die();
}

add_action('wp_ajax_usp_ai_upload_image', 'usp_ai_upload_image_callback_function');
add_action('wp_ajax_nopriv_usp_ai_upload_image', 'usp_ai_upload_image_callback_function');

function usp_ai_upload_image_callback_function(){
	$resultData['status'] = 404;
	$altText = isset($_POST['alt'])?sanitize_text_field($_POST['alt']):'';
	if(isset($_POST['src'])){
		if($_POST['src']!=''){
			$attachment_id = usp_ai_upload_file_by_url(sanitize_url($_POST['src']),$altText);
			$resultData['status'] = 200;
			$resultData['attachment_id'] = $attachment_id;
			$resultData['attachment_url'] = wp_get_attachment_url($attachment_id);
		}
	}
	echo wp_json_encode($resultData);
	wp_die();
}

function uspai_api_curl_call_function($apiUrl,$data='',$method='get'){
	$endpoint = $apiUrl;

	$body = wp_json_encode( $data );

	$options = [
		'body'        => $body,
		'headers'     => [
			'Content-Type' => 'application/json',
		],
		'timeout'     => 60,
		'redirection' => 5,
		'blocking'    => true,
		'httpversion' => '1.0',
		'sslverify'   => false,
		'data_format' => 'body',
	];

	if($method=='get'){
		$request = wp_remote_get( $endpoint );
	}
	if($method=='post'){
		$request = wp_remote_post( $endpoint, $options );
	}

	$response = wp_remote_retrieve_body( $request );

	return $response;
}

/**
 * Upload image from URL
 *
 */
function usp_ai_upload_file_by_url( $image_url, $altText = '' ) {

	// it allows us to use download_url() and wp_handle_sideload() functions
	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	// download to temp dir
	$temp_file = download_url( $image_url );

	if( is_wp_error( $temp_file ) ) {
		return false;
	}

	// move the temp file into the uploads directory
	$file = array(
		'name'     => basename( $image_url ),
		'type'     => mime_content_type( $temp_file ),
		'tmp_name' => $temp_file,
		'size'     => filesize( $temp_file ),
	);

	$sideload = wp_handle_sideload(
		$file,
		array(
			'test_form'   => false // no needs to check 'action' parameter
		)
	);

	if( ! empty( $sideload[ 'error' ] ) ) {
		// you may return error message if you want
		return false;
	}

	// it is time to add our uploaded image into WordPress media library
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => $sideload[ 'url' ],
			'post_mime_type' => $sideload[ 'type' ],
			'post_title'     => basename( $sideload[ 'file' ] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$sideload[ 'file' ]
	);

	if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return false;
	}

	// update medatata, regenerate image sizes
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
	);

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $altText ) );

	return $attachment_id;

}
