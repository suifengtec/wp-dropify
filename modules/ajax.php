<?php
/**
 * @Author: suifengtec
 * @Date:   2016-09-26 11:54:54
 * @Last Modified by:   suifengtec
 * @Last Modified time: 2016-09-27 15:21:34
 */

if(!class_exists('WP_Dropify_Base')){
	require_once('base.php');
}
class WP_Dropify_AJAX extends WP_Dropify_Base{

	public function __construct(){

		add_action('wp_ajax_wp_dropify_upload_handler',array($this,'wp_dropify_upload_handler'));
	}

	public function wp_dropify_upload_handler(){

		$posted = stripslashes_deep( $_POST );

		/*wp_send_json_success( $posted );*/
		
		if(
			empty($posted['nonce'])
			||empty($posted['data'])
			||empty($posted['user_id'])
			){

			$data = array(
				'success'=> false,
				'msg'=> '缺少必要的参数!',
				);

			wp_send_json_error( $data );
		}

		if(!wp_verify_nonce( $posted['nonce'], 'wp-dropify-upload-nonce' )){
			$data = array(
				'success'=> false,
				'msg'=> '安全检查失败!',
				);

			wp_send_json_error( $data );
		}


/*$posted['success']=FALSE;
wp_send_json_success( $posted['data'] );*/

		$data = parent::upload2($posted);

/*wp_send_json_success( $data );*/

		if(!$data['success']){
			wp_send_json_error( $data );
		}else{
			$data = array(
				'success'=> true,
				'msg'=> $data['url'],
				);

			wp_send_json_success( $data );
		}

	}

}