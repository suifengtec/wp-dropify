<?php
/**
 * @Author: suifengtec
 * @Date:   2016-09-26 11:53:27
 * @Last Modified by:   suifengtec
 * @Last Modified time: 2016-09-27 14:57:37
 */
/**
 * Plugin Name: WP Dropfiy
 * Plugin URI: http://coolwp.com/wp-dropify.html
 * Description: Description.
 * Author: suifengtec
 * Author URI: https://coolwp.com
 * Version: 0.9.0
 * Text Domain: wp_dropify
 * Domain Path: /languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ){
	exit;	
}

if ( ! class_exists( 'WP_Dropify' ) ) :

if( !session_id() ) { session_start(); }

final class WP_Dropify {

	private static $instance;

	public static $is_debug = true;

	public function __wakeup() {}
	public function __clone() {}
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Dropify ) ) {
			self::$instance = new WP_Dropify;
			self::$instance->setup_constants();
			//add_action( 'plugins_loaded', array( self::, 'load_textdomain' ) );
			self::$instance->includes();
			self::$instance->hooks();
		}

		return self::$instance;

	}

	public function hooks(){


		add_action('wp_enqueue_scripts',array(__CLASS__, 'enqueue'),11);

		add_action('init', array($this,'init'));
	}

	public function init(){

		add_shortcode('wpdropify', array( $this,  'shortcode_wpdropify') );
	}

	/*
	[wpdropify]
	 */
	public function shortcode_wpdropify($atts=array(),$content=null){

        extract( shortcode_atts( array(
        	'use_popup' =>'true',
        	'label'=>'', 
        	'class'=>'',
        	'is_inline'=>'true',
        	'height'=>200, 
        	'default_file'=>'',
        	'default_min_width'=>'100',
        	'default_min_height'=>'100',
        	'default_max_width'=>'800',
        	'default_max_height'=>'600',
        	'default_max_file_size'=>'3M',
        	'allowed_format'=>'png jpg jpeg',
        	), $atts ) );

        ob_start();

        $id =  $id?' id="'.$id.'"':'';
        $class = ' class="wp-dropify'.($class||'').'"';

       	$is_inline = ('is_inline'===$is_inline)?'inline-block':'block';

       	$display_mode = ' style="display:'.$is_inline.'"';

       	$height = is_numeric($height)?$height:200;
       	$default_file = $default_file?'data-default-file="'.$default_file.'"':'';
       	$default_max_file_size = $default_max_file_size?' data-max-file-size="'.$default_max_file_size.'"':'';


/*
data-show-errors="true"
data-errors-position="outside"
data-allowed-formats="portrait square"
data-allowed-file-extensions="pdf png psd"
data-max-file-size-preview="3M" 
.$default_max_file_size
 <?php echo $default_file;?> data-show-loader="true"
   data-height="<?php echo $height; ?>"
 */

if('true'===$use_popup){
?>

<style>
#wp-dropify-popup,
#wp-dropify-preview-popup
{
    background-color: #fff;
    border-radius: 0;
    box-shadow: 0 0 25px 5px #999;
    color: #111;
    display: none;
    min-width: 450px;
    padding: 25px;
    background-color: #fff;
    border-radius: 0;
    box-shadow: 0 0 25px 5px #999;
    color: #111;
    display: none;
    min-width: 450px;
    padding: 25px;

}

.button.b-close>span {
    font-size: 84%;
}
.button.b-close ,.button.b-close:hover{
    border-radius: 0;
    box-shadow: none;
    font: bold 131% sans-serif;
    padding: 0 6px 2px;
    position: absolute;
    right: 0;
    top: 0;
    background-color: #5AB0BD;
    color:#fff;
    cursor:pointer;
}



::-moz-selection {
    background-color: #2b91af;
    color: #fff;
    text-shadow: none;
}

::selection {
    background-color: #2b91af;
    color: #fff;
    text-shadow: none;
}

button:hover, button:focus, .button:hover, .button:focus, input[type="button"]:hover, input[type="button"]:focus, input[type="reset"]:hover, input[type="reset"]:focus, input[type="submit"]:hover, input[type="submit"]:focus {

    /*border: 0;
    border-radius: 2px;
    color: #fff;
    font-size: 12px;
    /*font-weight: 700;
    padding: 10px 30px 11px;
    text-transform: uppercase;
    vertical-align: bottom;

    -webkit-appearance: button;
    cursor: pointer;*/

    background-color: #5AB0BD;
    color: #fff;


    /*text-align: center;
    text-decoration: none;
    border-bottom:none!important;    */
}
#wp-dropify-uploaded-img{
	display:block;
	max-width:800px;
}
#wp-drpoify-close-priview{
	display:block;
	width:100%;
	text-align:center;
	margin-top:6px;
}
</style>

			<button id="wp-dropify-upload-trigger">上传文件</button>
            <div  id="wp-dropify-popup">
            	<span class="button b-close"><span>X</span></span>
	     		<div <?php echo $id.$class.$display; ?>>
					<input  type="file" class="dropify-event" data-nonce="<?php echo wp_create_nonce('wp-dropify-upload-nonce'); ?>">
					
	            </div>
	            <input type="hidden" id="wp-dropify-img-url" name="wp-dropify-img-url">
	            <div id="wp-dropify-tip-content"></div>
	            <a id="wp-dropify-priview-trigger" href="javascript:void(0);" style="display:none">点此查看</a>
            </div>

			<div id="wp-dropify-preview-popup">
				<span class="button b-close"><span>X</span></span>
				<img  id="wp-dropify-uploaded-img" src="">
				<button class="button" id="wp-drpoify-close-priview">关闭</button>
			</div>
<?php
}else{


        ?>
			
			<div <?php echo $id.$class.$display; ?>>
				<input  type="file" class="dropify-event">
			</div>

        <?php
}
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
	}

	public static function is_debug(){

		return self::$is_debug;
	}


	public static function enqueue(){

		$con = self::is_debug()?'':'.min';

		wp_enqueue_style('jquery-dropify-css', WP_DRIPIFY_PLUGIN_URL.'assets/vendor/dropify/dist/css/dropify'.$con.'.css' );
		wp_enqueue_style('wp-dropify-custom-css', WP_DRIPIFY_PLUGIN_URL.'assets/css/custom.css' );

		wp_enqueue_script('jquery' );
		wp_enqueue_script('jquery-easing', WP_DRIPIFY_PLUGIN_URL.'assets/vendor/easing/jquery.easing.min.js', array('jquery'));
		wp_enqueue_script('jquery-bpopup-js', WP_DRIPIFY_PLUGIN_URL.'assets/vendor/bpopup/jquery.bpopup'.$con.'.js', array('jquery'), '0.11.0', true );
		wp_enqueue_script('jquery-dropify-js', WP_DRIPIFY_PLUGIN_URL.'assets/vendor/dropify/dist/js/dropify'.$con.'.js', array('jquery','jquery-bpopup-js'), '0.2.1', true );
		wp_enqueue_script('wp-dropify-custom-js', WP_DRIPIFY_PLUGIN_URL.'assets/js/custom'.$con.'.js' , array('jquery','jquery-bpopup-js','jquery-dropify-js'), WP_DRIPIFY_VERSION, true );
		
		$post_id = 0;
        if(is_single()){
        	global $post;
        	$post_id = $post->ID;
        }
        $l10n = array(
            'url' => admin_url('admin-ajax.php'),
            'post_id' => $post_id,
            'user_id' => get_current_user_id(),
            'token1' => wp_create_nonce('wpDropify-token1'),
            'token2' => wp_create_nonce('wpDropify-token2'),
            'token3' => wp_create_nonce('wpDropify-token3'),
        );
        wp_localize_script('wp-dropify-custom-js', 'wpDropifyI10n', $l10n);

	}


	private function includes() {
	
		require_once WP_DRIPIFY_PLUGIN_DIR . 'modules/base.php';
		require_once WP_DRIPIFY_PLUGIN_DIR . 'modules/scripts.php';
		require_once WP_DRIPIFY_PLUGIN_DIR . 'modules/ajax.php';
		require_once WP_DRIPIFY_PLUGIN_DIR . 'modules/hooks.php';
		new WP_Dropify_AJAX;

	}

	public function load_textdomain() {

		$wp_dropify_lang_dir  = dirname( plugin_basename( WP_DRIPIFY_PLUGIN_FILE ) ) . '/languages/';
		$wp_dropify_lang_dir  = apply_filters( 'wp_dropify_languages_directory', $wp_dropify_lang_dir );

		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'wp_dropify' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'wp_dropify', $locale );
		$mofile_global1 = WP_LANG_DIR . '/cwp/wp_dropify-' . $locale . '.mo';
		$mofile_global2 = WP_LANG_DIR . '/cwp/cwp-' . $locale . '.mo';
		$mofile_global3 = WP_LANG_DIR . '/plugins/wp_dropify/' . $mofile;

		if ( file_exists( $mofile_global1 ) ) {

			load_textdomain( 'wp_dropify', $mofile_global1 );

		} elseif ( file_exists(  ) ) {

			load_textdomain( 'wp_dropify', $mofile_global2 );

		} elseif ( file_exists(  ) ) {

			load_textdomain( 'wp_dropify', $mofile_global3 );

		} else {

			load_plugin_textdomain( 'wp_dropify', false, $wp_dropify_lang_dir );
		}


	}

	private function setup_constants() {

		if ( ! defined( 'WP_DRIPIFY_VERSION' ) ) {
			define( 'WP_DRIPIFY_VERSION', '0.9.0' );
		}
		if ( ! defined( 'WP_DRIPIFY_PLUGIN_DIR' ) ) {
			define( 'WP_DRIPIFY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}
		if ( ! defined( 'WP_DRIPIFY_PLUGIN_URL' ) ) {
			define( 'WP_DRIPIFY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
		if ( ! defined( 'WP_DRIPIFY_PLUGIN_FILE' ) ) {
			define( 'WP_DRIPIFY_PLUGIN_FILE', __FILE__ );
		}


	}

}

global $wp_dropify;
$wp_dropify = WP_Dropify::instance();

endif;


add_action('admin_init','fdsgreghrehre');
function fdsgreghrehre(){

	if(defined('DOING_AJAX')&&DOING_AJAX){
		return ;
	}

	$data = array(
		'',
		'base_path'=>'',
		'base_url'=>'',
		);
	
 	/*$r = WP_Dropify_Base::upload2($data);
 	var_dump($r );
*/

}