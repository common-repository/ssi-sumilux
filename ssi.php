<?php
/*
Plugin Name: Social Sign-In
Plugin URI: http://ssi.sumilux.com
Description: Plugin for Social Sign-In from   <a href="http://ssi.sumilux.com/ssi/">Sumilux</a>
Version: 0.8.4
Author: Serhiy Martynenko
Author URI: serhiy.martynenko@gmail.com
*/

//constants
define('SSI_HOME_DIR', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
define('SSI_DIR', realpath(dirname(__FILE__)) . '/');
define('SSI_TEMPLATES_DIR', SSI_DIR . 'templates/');
define("SSI_PLUGIN_URL",WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)));
define("SSI_VERSION", "0.8.4");


define('DEBUG', get_option("ssi_debug_mode"));

if (DEBUG){
    //define('APPKEY', get_option('ssi_debug_app_key'));
        define('APPSECRET', get_option('ssi_debug_app_secret'));
        define('WIDGETNAME', get_option('ssi_debug_widget_name'));
} else {
   // define('APPKEY', get_option('ssi_app_key'));
        define('APPSECRET', get_option('ssi_app_secret'));
        define('WIDGETNAME', get_option('ssi_widget_name'));
}

define('ENDPOINT', "https://social-sign-in.com/smx");
define('ENDPOINT_DEBUG', "http://demo.sumilux.com/smx");


require_once SSI_DIR . 'SsiUser.php';


// work class
require_once SSI_DIR . 'ssi/vendor/ssi-client-php/Services_Sumilux_SSI.php';

// WP include
if (file_exists(SSI_HOME_DIR . 'wp-load.php')) {
    // WP 2.6
    require_once(SSI_HOME_DIR . 'wp-load.php');
} else {
    // Before 2.6
    require_once(SSI_HOME_DIR . 'wp-config.php');
}
require_once(SSI_HOME_DIR . 'wp-includes/registration.php');
require_once(SSI_HOME_DIR . 'wp-includes/pluggable.php');



function my_plugin_init() {
    if (!session_id()){
        session_start();
	}
    if($_REQUEST['stat'] == "ng"){
        add_action( 'login_head', 'wp_shake_js', 12 );
        SsiUser::$errors = $_REQUEST['errMsg'];
    }elseif (isset($_POST['ssi_token'])) {
        ssi_work_with_token();
    }
}


function ssi_plugin_action_links( $links, $file ) {
    if ( $file == plugin_basename( dirname(__FILE__).'/ssi.php' ) ) {
        $links[] = '<a href="options-general.php?page=ssi-identifier">'.__('Settings').'</a>';
    }
    return $links;
}

// plugin initialization
add_action('init','my_plugin_init');
add_filter( 'plugin_action_links', 'ssi_plugin_action_links', 10, 2 );
add_action('admin_menu', 'my_plugin_menu');

if ((APPSECRET == "") || (WIDGETNAME=="")){
    add_action('admin_notices', 'ssi_admin_warnings');
} else {
    add_action('login_head', 'ssi_ui_login_form');
}




function ssi_work_with_token(){

    $token = $_POST['ssi_token'];
    $ssi = new Services_Sumilux_SSI(WIDGETNAME, APPSECRET);

    $ssi->setToken($token);
    $jo = $ssi->getUserProfile('current');

    global $wpdb;
    $WpUser = wp_get_current_user();


    if ($WpUser->ID){
        add_action( 'login_head', 'wp_shake_js', 12 );
        SsiUser::$errors = "You are already logged in!";
        return false;
    }

    $returnURL = "http://".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    if (stripos($_SERVER['REQUEST_URI'], "wp-login")){
        $redirectURL = site_url();
    } else{
        $redirectURL = $returnURL;
    }


    $already_registered = get_users(array('meta_key' => 'ssi_uid', 'meta_value' => $ssi->getUID()));

    if (empty($already_registered)){

       email_exists($jo->primaryEmail);
        if (!empty($jo->primaryEmail)){
            $profile_email = $jo->primaryEmail;
        }
        if ((email_exists($jo->primaryEmail)) || (empty($jo->primaryEmail)) ){
            $profile_email = $jo->user->userID . '@' . parse_url(get_option('siteurl'), PHP_URL_HOST);
        }
        $wpuid = SsiUser::create($profile_email, $jo, $ssi->getUID());
    } else {
        $wpuid = $already_registered[0]->ID;

    }


    if ($wpuid) {
      wp_set_auth_cookie($wpuid, true, false);
        wp_set_current_user($wpuid);
        wp_safe_redirect($redirectURL);
    }

}

function my_plugin_menu()
{
    add_options_page('SSI Options', 'SSI Plugin', 'manage_options', 'ssi-identifier', 'ssi_options');
}

function ssi_admin_warnings() {
    echo "<div id='ssi-warning' class='updated fade'><p>You should set Widget name and App secret of <strong> \"SSI plugin\"</strong> <a href='/wp-admin/options-general.php?page=ssi-identifier'>here</a></p></div>";
    return true;
}


function ssi_options()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    $plugname = "Ssi plugin for WordPress";
    $shortname = "ssi";
    $plugoptions = array(
        array("name" => "SSI main options",
            "type" => "title"),
        array("name" => "Widget Name",
            "desc" => "This is the name of the widget, as shown after you have it created from our site",
            "id" => $shortname . "_widget_name",
            "std" => "",
            "type" => "text"),
        array("name" =>  "Widget Secret",
            "desc" => "This is the secret key of the widget, also as shown on our web site",
            "id" => $shortname . "_app_secret",
            "std" => "",
            "type" => "text"),

     );


    $tpl_data = array(
        'plugname' => $plugname,
        'shortname' => $shortname,
        'plugo[tions' => $plugoptions,
    );

    if (empty($_POST) || (isset($_REQUEST["appName"]) && isset($_REQUEST["appSecret"]))) {
        options($plugoptions, $plugname, $shortname);
     } else {
        save_options($plugoptions, $plugname, $shortname);
    }

    //echo ssi_show_template('html_options_form.tpl', $tpl_data);
}

function ssi_ui_login_form($type_show)
{
    $WpUser = wp_get_current_user();

    $return_to = get_option('siteurl');


    $returnURL = "http://".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $authURL = getAuthURL($returnURL);

    $site_url = site_url() . '/wp-login.php';

    if (DEBUG){
        Services_Sumilux_SSI::setEndpoint("http://idmedemo.sumilux.com/smx/rpcService/xmlRpcService");
    }


    $html_block = Services_Sumilux_SSI::generateCode(
        WIDGETNAME, // widget name
        APPSECRET, // app secret
        'medium-icon',
        array('linkText'=>'Sign-in', 'returnURL'=>$returnURL)
    );

    $html_block = str_replace('Sign-in', 'Sign In with Your Social Identity', $html_block);


    $javacript_for_login = "<script type=\"text/javascript\">
        window.onload=function(){
        var respond_div = document.getElementById('loginform');

        if (!respond_div) {
        respond_div = document.getElementById(\"respond\");
        }

        var newP = document.getElementById('ssilogin');

        if (respond_div) {
        respond_div.parentNode.insertBefore(newP,respond_div);
        }
    }
    </script>";
    if ($type_show!=""){
        $javacript_for_login = "";
    }
    // prepare data for template
    $tpl_data = array(
        'javascript_for_login' => $javacript_for_login,
        'url' => $authURL,
        'html_block' => $html_block['html-body-code'],
        'html_header_block' => $html_block['html-head-code'],
    );

    if ($type_show==""){
        include(SSI_TEMPLATES_DIR."login.php");
    } else {
        echo ssi_show_template('html_login_form.tpl', $tpl_data);
    }
}


function ssi_show_template($template_name, $parameteres = null)
{
    if (is_array($parameteres)) {
        /* Change keys of array to %key% format*/
        $params_for_display = array();
        foreach ($parameteres as $k => $v) {
            $params_for_display["%$k%"] = $v;
        }
        return strtr(file_get_contents(SSI_TEMPLATES_DIR . $template_name), $params_for_display);
    }

    return file_get_contents(SSI_TEMPLATES_DIR . $template_name);
}


function getAuthURL($exitURL)
{
    $exitURL = "http://" . $exitURL;
    $sig = md5($exitURL . APPSECRET); // signature
    $authURL = ENDPOINT . "/owa?exitURL=" . urlencode($exitURL) . "&sig=$sig&WIDGETNAME=" . WIDGETNAME;
    return $authURL;
}


function setToken($token)
{
    $sid = session_id();
    if (empty($sid)) {
        throw new Exception("Active PHP session needed to save session token");
    }
    $_SESSION[self::TOKEN_NAME_IN_SESSION] = $token;
}


function save_options($plugoptions, $plugname, $shortname)
{


    if (isset($_POST['save'])) {

        foreach ($plugoptions as $value) {
            update_option($value['id'], $_REQUEST[$value['id']]);
        }

        echo '<div id="message" class="updated fame de"><p><strong>' .
            'Options "' . $plugname . '" saved.' .
            '</strong></p></div>';

    } else if (isset($_POST['reset'])) {


        foreach ($plugoptions as $value) {
            delete_option($value['id']);
        }

        echo '<div id="message" class="updated fade"><p><strong>' .
            'Options "' . $plugname . '" reseted.' .
            '</strong></p></div>';

    }

}


function options($plugoptions, $plugname, $shortname)
{

    $returnURL = "http://".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];


    if (isset($_REQUEST["appName"]) && isset($_REQUEST["appSecret"]) ){
       
        $ssi_recieved['ssi_app_secret'] = $_REQUEST["appSecret"];
        $ssi_recieved['ssi_widget_name'] = $_REQUEST["appName"];
    }
    include(SSI_TEMPLATES_DIR."options.php");

} ?>
