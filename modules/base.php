<?php
/**
 * @author: suifengtec coolwp.com
 * @date:   2016-01-11 16:15:56
 * @last Modified by:   suifengtec coolwp.com
 * @last Modified time: 2016-01-28 09:46:54
 */

class WP_Dropify_Base {

	public $img_name = '';
	public $ext = '';

	public function __construct() {

	}

	/**
	 * Upload to uploads directory.
	 * @param  array  $posted [description]
	 * @return [type]         [description]
	 */
	public static function upload2($posted=array()){


/*
array(
'data'=>''

);

 */
	        if (empty($posted['data'])) {
	        	$arr = array('success' => false,'msg' => 'Parameter is missing!');
				return $arr;
	        }

/*return $posted['data'];*/

	       	$img  = $posted['data'];


	/*========================*/

			$upload_dir       = wp_upload_dir();

			$upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

	/*		$path = empty($posted['base_path']) ? $posted['base_path']: __DIR__ . DIRECTORY_SEPARATOR. 'imgs'. DIRECTORY_SEPARATOR;
			*/
			//$upload_path = empty($posted['base_path']) ? $posted['base_path']: $upload_path;

			$ext = self::get2($img , false);
/*	return $ext;*/

			$img = str_replace('data:image/'.$ext.';base64,', '', $img);
		/*	$img = str_replace(' ', '+', $img);*/


			$hashed_filename  = self::get_name().'.'.$ext;


			$decoded          = base64_decode($img) ;

/*			$f = finfo_open();
			$mime_type = finfo_buffer($f, $decoded, FILEINFO_MIME_TYPE);
*/

			// @new
			$image_upload     = file_put_contents( $upload_path . $hashed_filename, $decoded );



			//HANDLE UPLOADED FILE
			if( !function_exists( 'wp_handle_sideload' ) ) {

			  require_once( ABSPATH . 'wp-admin/includes/file.php' );

			}
			// Without that I'm getting a debug error!?
			if( !function_exists( 'wp_get_current_user' ) ) {

			  require_once( ABSPATH . 'wp-includes/pluggable.php' );

			}

			// @new
			$file             = array();
			$file['error']    = '';
			$file['tmp_name'] = $upload_path . $hashed_filename;
			$file['name']     = $hashed_filename;
			$file['type']     = 'image/'.$ext;
			$file['size']     = filesize( $upload_path . $hashed_filename );

			// upload file to server
			// @new use $file instead of $image_upload
			$file_return      = wp_handle_sideload( $file, array( 'test_form' => false ) );

		/**/

			$filename = $file_return['file'];
			/*
			array(6) {
			    ["path"]=>
			    string(51) "L:\Ampps\www\dropify.com/wp-content/uploads/2016/09"
			    ["url"]=>
			    string(45) "http://dropify.com/wp-content/uploads/2016/09"
			    ["subdir"]=>
			    string(8) "/2016/09"
			    ["basedir"]=>
			    string(43) "L:\Ampps\www\dropify.com/wp-content/uploads"
			    ["baseurl"]=>
			    string(37) "http://dropify.com/wp-content/uploads"
			    ["error"]=>
			    bool(false)
			  }

			*/
			$file_url = $upload_dir['url'] . '/' . basename($filename);

			$attachment = array(
				'post_mime_type' => $file_return['type'],
				'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
				'post_content' => '',
				'post_status' => 'inherit',
				'post_author' => !empty($posted['user_id'])?$posted['user_id']:1,
				'guid' => $file_url
			);
			$attach_id = wp_insert_attachment( $attachment, $filename, 289 );
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			$r = array(
				'success' => true,
				'attach_id' => $attach_id,
				'url' => $file_url,
				);
			return $r;

		/*========================*/

	}

        /**
         * 上传图片到指定目录
         * @return [type] [description]
         */
        public static function upload($posted=array()) {

/*
array(
'data'=>'',
'base_path'=>'',
'base_url'=>'',
)
 */
            if (empty($posted['data'])) {
            	$arr = array('success' => false,'msg' => 'Parameter is missing!');
				return $arr;
            }
          	$path = empty($posted['base_path']) ? $posted['base_path']: __DIR__ . '/imgs/';

          	$base64_string = $posted['data'];


          	$is_writable = wp_is_writable( $path );
          	if(!$is_writable ){
            	$arr = array('success' => false,'msg' => 'Path is not writable!');
				return $arr;
          	}
          
            $file_full_name = self::save($base64_string, $path );
            if ($file_full_name) {
            	 $img_url = empty($posted['base_url']) ? $posted['base_url'] : plugins_url('imgs/' . $file_full_name, __FILE__);

                $arr = array('success'=>true,'msg'=>'Uploaded!','url' => $img_url, 'fileName' => $file_full_name);
                return  $arr;
            } else {

                $r = array(
                    'success' => false,
                    'msg' => 'Something is wrong!',
                );

                return  $arr;
            }


        }


	/**
	 * [save description]
	 * @param  [type] $base64_string [description]
	 * @param  [type] $dir           [description]
	 * @param  string $base_url      [description]
	 * @return [type]                [description]
	 */
	public 	static  function save($base64_string, $dir) {

		$include_dot = true;

		$name = self::get_name();

		$ext = self::get($base64_string, $include_dot);

		$full_name = $name . $ext;

		$output_file = $dir . $full_name;

		$ifp = fopen($output_file, "wb");

		$data = explode(',', $base64_string);

		fwrite($ifp, base64_decode($data[1]));
		fclose($ifp);

		if (!$full_name) {
			return false;
		}
		return $full_name;
		/*return $output_file;*/

	}

	public 	static  function get_name() {

		$time = microtime();
		$r = str_replace(array(' ', '.'), '-', $time);

		return $r;
	}

	/**
	 * Get image ext by base64 string.
	 * @param  [type]  $str         [description]
	 * @param  boolean $include_dot [description]
	 * @return [type]               [description]
	 */
	public static function get2($str, $include_dot=false){

		$ext = substr($str, 5, strpos($str, ';')-5);
		$ext = str_replace('image/', '', $ext);
		if($include_dot){
			return '.'.$ext;
		}
		return $ext;

	}

	public 	static  function get($base64_string, $include_dot=false) {

		/*$image_type*/
		$b = getimagesize($base64_string);
		$image_type = $b[2];

		define("INVALID_IMAGETYPE", '.gif');

		$extension = INVALID_IMAGETYPE; /// Default return value for invalid input

		$image_type_identifiers = array( ### These values correspond to the IMAGETYPE constants
		array(IMAGETYPE_GIF => 'gif', "mime_type" => 'image/gif'), ###  1 = GIF
		array(IMAGETYPE_JPEG => 'jpg', "mime_type" => 'image/jpeg'), ###  2 = JPG
		array(IMAGETYPE_PNG => 'png', "mime_type" => 'image/png'), ###  3 = PNG
		array(IMAGETYPE_SWF => 'swf', "mime_type" => 'application/x-shockwave-flash'), ###  4 = SWF  // A. Duplicated MIME type
		array(IMAGETYPE_PSD => 'psd', "mime_type" => 'image/psd'), ###  5 = PSD
		array(IMAGETYPE_BMP => 'bmp', "mime_type" => 'image/bmp'), ###  6 = BMP
		array(IMAGETYPE_TIFF_II => 'tiff', "mime_type" => 'image/tiff'), ###  7 = TIFF (intel byte order)
		array(IMAGETYPE_TIFF_MM => 'tiff', "mime_type" => 'image/tiff'), ###  8 = TIFF (motorola byte order)
		array(IMAGETYPE_JPC => 'jpc', "mime_type" => 'application/octet-stream'), ###  9 = JPC  // B. Duplicated MIME type
		array(IMAGETYPE_JP2 => 'jp2', "mime_type" => 'image/jp2'), ### 10 = JP2
		array(IMAGETYPE_JPX => 'jpf', "mime_type" => 'application/octet-stream'), ### 11 = JPX  // B. Duplicated MIME type
		array(IMAGETYPE_JB2 => 'jb2', "mime_type" => 'application/octet-stream'), ### 12 = JB2  // B. Duplicated MIME type
		array(IMAGETYPE_SWC => 'swc', "mime_type" => 'application/x-shockwave-flash'), ### 13 = SWC  // A. Duplicated MIME type
		array(IMAGETYPE_IFF => 'aiff', "mime_type" => 'image/iff'), ### 14 = IFF
		array(IMAGETYPE_WBMP => 'wbmp', "mime_type" => 'image/vnd.wap.wbmp'), ### 15 = WBMP
		array(IMAGETYPE_XBM => 'xbm', "mime_type" => 'image/xbm'), ### 16 = XBM
	);

		if ((is_int($image_type)) AND (IMAGETYPE_GIF <= $image_type) AND (IMAGETYPE_XBM >= $image_type)) {
			$extension = $image_type_identifiers[$image_type - 1]; // -1 because $image_type_identifiers array starts at [0]
			$extension = $extension[$image_type];
		} elseif (is_string($image_type) AND (($image_type != 'application/x-shockwave-flash') OR ($image_type != 'application/octet-stream'))) {

			$extension = self::match_mime_type_to_extension($image_type, $image_type_identifiers);
		} else {
			$extension = INVALID_IMAGETYPE;
		}

		if (is_bool($include_dot)) {

			if ((false != $include_dot) AND (INVALID_IMAGETYPE != $extension)) {
				$extension = '.' . $extension;
			}
		} else {
			$extension = INVALID_IMAGETYPE;
		}

		return $extension;

	}

	public 	static  function match_mime_type_to_extension($image_type, $image_type_identifiers) {
		// Return from loop on a match
		foreach ($image_type_identifiers as $_key_outer_loop => $_val_outer_loop) {
			foreach ($_val_outer_loop as $_key => $_val) {
				if (is_int($_key)) {
					// Keep record of extension for mime check
					$extension = $_val;
				}
				if ($_key == 'mime_type') {
					if ($_val === $image_type) { // Found match no need to continue looping
						return $extension; ### Return
					}
				}
			}
		}
		// Compared all values without match
		return $extension = INVALID_IMAGETYPE;
	}

}/*//CLASS*/