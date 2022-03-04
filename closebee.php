<?php
    /**
     * Plugin Name: Closebee Ecommerce Tailor
     * Description: Closebee is the tool to stich different ecommerce components into one platform
     * Author: Closebee Technology Team
     * Author URI: https://pearnode.com
     * Version: 1.0.0.0
     * Plugin URI: https://closebee.com
     */
    
    $org = (object) array();
    $profile = (object) array();
    $user = (object) array();
    $post_args = array(
        'timeout' => '5', 
        'redirection' => '5', 
        'httpversion' => '1.0', 
        'blocking' => true, 
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'), 
        'cookies' => array(),
        'method'  => 'POST',
        'data_format' => 'body'
    );
    
    $plugin_dir = plugin_dir_path( __FILE__ );
    $plugin_dir_name = "";
    $cred_file = $plugin_dir."credentials.json";
    
    require_once $plugin_dir."includes/ClosebeePluginActivator.php";
    require_once $plugin_dir."includes/ClosebeePluginDeactivator.php";
    
    function closebee_json_basic_auth_handler( $user ) {
        if (!empty($user)) {
            return $user;
        }
        if(!isset($_SERVER['PHP_AUTH_USER'])) {
            return $user;
        }
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        remove_filter( 'determine_current_user', 'closebee_json_basic_auth_handler', 20 );
        $user = wp_authenticate( $username, $password );
        add_filter( 'determine_current_user', 'closebee_json_basic_auth_handler', 20 );
        if ( is_wp_error( $user ) ) {
            return null;
        }
        return $user->ID;
    }
    
    function closebee_json_basic_auth_error( $error ) {
        if (!empty($error)) {
            return $error;
        }
        global $wp_json_basic_auth_error;
        return $wp_json_basic_auth_error;
    }
    
    function closebee_plugin_activate(){
        global $plugin_dir_name;
        $dr = plugin_basename(__FILE__);
        $drarr = explode('/', $dr);
        $plugin_dir_name = $drarr[0];
        error_log("Plugin directory [$plugin_dir_name]");
        ClosebeePluginActivator::activate(get_site_url());
    }

    function closebee_plugin_deactivate(){
        ClosebeePluginDeactivator::deactivate(get_site_url());
    }
    
    function closebee_plugin_settings() {
        global $post_args, $plugin_dir, $plugin_dir_name;
        global $org, $profile, $user, $cred_file;
        $out = "";
        if(file_exists($cred_file)){
            $out = file_get_contents($cred_file);
        }
        if($out != ""){
            $cred = json_decode($out);
            $org = $cred->org;
            $profile = $cred->profile;
            $user = $cred->user;
        }
        add_action('wp_enqueue_scripts', "load_style_dependencies");
        add_action('wp_enqueue_scripts', "load_script_dependencies");
        $dr = plugin_basename(__FILE__);
        $drarr = explode('/', $dr);
        $plugin_dir_name = $drarr[0];
        include($plugin_dir."includes/ui/settings/common-header.php");

		if(isset($profile->code)){
		     $surl = get_site_url();
		     $rdata = array('oc' => $org->code, 'pc' => $profile->code, 'surl' => $surl);
		     $post_args['body'] = json_encode($rdata);
		     $out = wp_remote_post('https://api.pearnode.com/closebee/site/plugin/activate.php', $post_args);
		     $robj = (object) $out;
		     $body = $robj->body;
		     $site = json_decode($body);
        ?>
    		<div class="card-header w-100">
    			<div class="row w-100 m-0">
    				<div class="col-12 pl-1">
    					<span style="font-size: 1.1rem;">Hello, <b><?php echo $user->full_name; ?> </b></span>	
    					<span style="margin-left:5px;">from <b><?php echo $org->name;?></b></span>
    				</div>
    			</div>
    		</div>
	    	<script>
    	    	var oc = '<?php echo $org->code; ?>';
    	    	var pc = '<?php echo $profile->code; ?>';
    	    	var uck = '<?php echo $user->ck; ?>';
    	    	var sid = '<?php echo $site->id; ?>';
    	    	var sname = '<?php echo $site->site_name; ?>';

        		function launchApp(){
            		var url = "https://app.closebee.com/wp_launch.html?oc=" + oc + "&pc=" + pc + "&uck=" + uck;
            		window.open(url, "closebee_app");
        		}
        	</script>
		    <?php 
    		    if(!isset($site->config)){
    		        $site->config = (object) array();
    		    }
    		    $sconfig = $site->config;
    		    if(isset($sconfig->scanned) && ($sconfig->scanned)){
    		        include($plugin_dir."includes/ui/settings/launch-app.php");
		        }else {
		            include($plugin_dir."includes/ui/settings/unscanned.php");
		        }
        }else {
            include($plugin_dir."includes/ui/settings/attach-token.php");
        }
        include($plugin_dir."includes/ui/settings/common-footer.php");
    }
    
    function handle_submit_closebee_registration_form(){
        global $post_args;
        global $org, $profile, $user, $cred_file;
        add_action('wp_enqueue_scripts', "load_style_dependencies");
        add_action('wp_enqueue_scripts', "load_script_dependencies");
        if(isset($_POST['authtoken'])){
            $token = $_POST['authtoken'];
            $ddata = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));
            $rdata = array('oc' => $ddata->oc, 'pc' => $ddata->pc, 'uck' => $ddata->uck);
            $post_args['body'] = json_encode($rdata);
            $out = wp_remote_post('https://api.pearnode.com/extn/org/bizdetails.php', $post_args);
            $robj = (object) $out;
            file_put_contents($cred_file, $robj->body);
            exit(wp_redirect('admin.php?page=closebee-plugin-settings'));
        }
    }
    
    function handle_submit_closebee_navigation_form(){
        add_action('wp_enqueue_scripts', "load_style_dependencies");
        add_action('wp_enqueue_scripts', "load_script_dependencies");
        if(isset($_POST['navslug'])){
            $slug = $_POST['navslug'];
            exit(wp_redirect("admin.php?page=$slug"));
        }
    }
    
    function closebee_plugin_page_site() {
        global $post_args, $plugin_dir, $plugin_dir_name;
        global $org, $profile, $user, $cred_file;
        add_action('wp_enqueue_scripts', "load_style_dependencies");
        add_action('wp_enqueue_scripts', "load_script_dependencies");
        $out = "";
        if(file_exists($cred_file)){
            $out = file_get_contents($cred_file);
        }
        if($out != ""){
            $cred = json_decode($out);
            $org = $cred->org;
            $profile = $cred->profile;
            $user = $cred->user;
            
            $rdata = array('oc' => $org->code, 'pc' => $profile->code, 'surl' => get_site_url());
            $post_args['body'] = json_encode($rdata);
            $out = wp_remote_post('https://api.pearnode.com/closebee/site/details_url.php', $post_args);
            $robj = (object) $out;
            $body = $robj->body;
            $site = json_decode($body);
            if(isset($site->id)){
                $dr = plugin_basename(__FILE__);
                $drarr = explode('/', $dr);
                $plugin_dir_name = $drarr[0];
                include($plugin_dir."includes/ui/site/pages.php");
            }else {
                exit(wp_redirect('admin.php?page=closebee-plugin-settings'));
            }
        }else {
            exit(wp_redirect('admin.php?page=closebee-plugin-settings'));
        }
    }

    
    function closebee_plugin_page_inventory() {
        global $post_args, $plugin_dir, $plugin_dir_name;
        global $org, $profile, $user, $cred_file;
        add_action('wp_enqueue_scripts', "load_style_dependencies");
        add_action('wp_enqueue_scripts', "load_script_dependencies");
        $out = "";
        if(file_exists($cred_file)){
            $out = file_get_contents($cred_file);
        }
        if($out != ""){
            $cred = json_decode($out);
            $org = $cred->org;
            $profile = $cred->profile;
            $user = $cred->user;
            $dr = plugin_basename(__FILE__);
            $drarr = explode('/', $dr);
            $plugin_dir_name = $drarr[0];
            include($plugin_dir."includes/ui/inventory/items.php");
        }else {
            exit(wp_redirect('admin.php?page=closebee-plugin-settings'));
        }
    }
    
    function closebee_plugin_page_woocommerce() {
        global $post_args, $plugin_dir, $plugin_dir_name;
        global $org, $profile, $user, $cred_file;
        add_action('wp_enqueue_scripts', "load_style_dependencies");
        add_action('wp_enqueue_scripts', "load_script_dependencies");
        $out = "";
        if(file_exists($cred_file)){
            $out = file_get_contents($cred_file);
        }
        if($out != ""){
            $cred = json_decode($out);
            $org = $cred->org;
            $profile = $cred->profile;
            $user = $cred->user;
            
            $rdata = array('oc' => $org->code, 'pc' => $profile->code, 'surl' => get_site_url());
            $post_args['body'] = json_encode($rdata);
            $out = wp_remote_post('https://api.pearnode.com/closebee/site/details_url.php', $post_args);
            $robj = (object) $out;
            $body = $robj->body;
            $site = json_decode($body);
            if(isset($site->id)){
                $wc_consumer_key = "";
                $wc_consumer_secret = "";
                if(isset($site->config)){
                    $sconfig = $site->config;
                    if(isset($sconfig->commerce)){
                        if($sconfig->commerce == "woocommerce"){
                            if(isset($sconfig->woocommerce)){
                                $wc_consumer_key = $sconfig->woocommerce->woocommerce_consumer_key;
                                $wc_consumer_secret = $sconfig->woocommerce->woocommerce_consumer_secret;
                            }
                        }
                    }
                }
                $dr = plugin_basename(__FILE__);
                $drarr = explode('/', $dr);
                $plugin_dir_name = $drarr[0];
                include($plugin_dir."includes/ui/woocommerce/woocommerce.php");
            }else {
                exit(wp_redirect('admin.php?page=closebee-plugin-settings'));
            }
        }else {
            exit(wp_redirect('admin.php?page=closebee-plugin-settings'));
        }
    }
    
    function closebee_do_admin_init(){
		add_menu_page('Closebee', 'Closebee Beta', 'manage_options', 'closebee-plugin-settings', 'closebee_plugin_settings', 'dashicons-superhero', 5);
		add_submenu_page('closebee-plugin-settings', 'Closebee Settings', 'Settings', 'manage_options', 'closebee-plugin-settings', 'closebee_plugin_settings');
		add_submenu_page('closebee-plugin-settings', 'Closebee Plugin Site Pages', 'Pages', 'manage_options', 'closebee-plugin-page-site', 'closebee_plugin_page_site');
		add_submenu_page('closebee-plugin-settings', 'Closebee Plugin Inventory', 'Catalog', 'manage_options', 'closebee-plugin-page-inventory', 'closebee_plugin_page_inventory');
		add_submenu_page('closebee-plugin-settings', 'Closebee Plugin Woocommerce', 'Woocommerce', 'manage_options', 'closebee-plugin-page-woocommerce', 'closebee_plugin_page_woocommerce');
		if (get_option('my_plugin_do_activation_redirect', false)) {
		    delete_option('my_plugin_do_activation_redirect');
		    wp_redirect('admin.php?page=closebee-plugin-settings');
		}
    }
 
    function load_style_dependencies(){
        wp_enqueue_style('closebee-font-awsome', plugins_url('includes/assets/css/fontawsome-6.0.0-all-min.css', __FILE__));
        wp_enqueue_style('closebee-bootstrap-4.3.1', plugins_url('includes/assets/css/bootstrap-4.6.1.min.css', __FILE__));
        wp_enqueue_style('closebee-bootstrap-theme', plugins_url('includes/assets/css/bs_theme.css', __FILE__));
        wp_enqueue_style('closebee-screen-resolution', plugins_url('includes/assets/css/screen_resolution.css', __FILE__));
        wp_enqueue_style('closebee-nprogress', plugins_url('includes/assets/css/nprogress.css', __FILE__));
        wp_enqueue_style('closebee-select2', plugins_url('includes/assets/css/select2-4.1.0-rc.min.css', __FILE__));
        wp_enqueue_style('closebee-select2-bootstrap', plugins_url('includes/assets/css/select2-bootstrap4.min.css', __FILE__));
        wp_enqueue_style('closebee-datepicker', plugins_url('includes/assets/css/datepicker-min.css', __FILE__));
        wp_enqueue_style('closebee-daterangepicker', plugins_url('includes/assets/css/daterangepicker-3.0.3.css', __FILE__));
    }
    
    function load_script_dependencies(){
        wp_enqueue_script('closebee-jquery', plugins_url('includes/assets/js/jquery-1.12.4.min.js', __FILE__));
        wp_enqueue_script('closebee-popper', plugins_url('includes/assets/js/popper-1.15.0.min.js', __FILE__));
        wp_enqueue_script('closebee-bootstrap', plugins_url('includes/assets/js/bootstrap-4.6.1.min.js', __FILE__));
        wp_enqueue_script('closebee-swal', plugins_url('includes/assets/js/swal-2.9.17.1.min.js', __FILE__));
        wp_enqueue_script('closebee-nprogress', plugins_url('includes/assets/js/nprogress.js', __FILE__));
        wp_enqueue_script('closebee-mustache', plugins_url('includes/assets/js/mustache.min.js', __FILE__));
        wp_enqueue_script('closebee-moment', plugins_url('includes/assets/js/moment.min.js', __FILE__));
        wp_enqueue_script('closebee-datepicker', plugins_url('includes/assets/js/datepicker.min.js', __FILE__));
        wp_enqueue_script('closebee-daterangepicker', plugins_url('includes/assets/js/daterangepicker-3.0.3.min.js', __FILE__));
        wp_enqueue_script('closebee-chartjs', plugins_url('includes/assets/js/chartjs-3.7.0.min.js', __FILE__));
        wp_enqueue_script('closebee-color', plugins_url('includes/assets/js/jquery-color-2.0.0.js', __FILE__));
        wp_enqueue_script('closebee-cformatter', plugins_url('includes/assets/js/currency-formatter-2.0.0.min.js', __FILE__));
        wp_enqueue_script('closebee-select2', plugins_url('includes/assets/js/select2-4.1.0.js', __FILE__));
        wp_enqueue_script('closebee-api-registry', plugins_url('includes/assets/js/pearnode-commons-api-registry.js', __FILE__));
        wp_enqueue_script('closebee-init', plugins_url('includes/assets/js/pearnode-commons-init.js', __FILE__));
        wp_enqueue_script('closebee-imodel', plugins_url('includes/assets/js/pearnode-commons-inventory-model.js', __FILE__));
        wp_enqueue_script('closebee-ifunctions', plugins_url('includes/assets/js/pearnode-commons-inventory-functions.js', __FILE__));
        wp_enqueue_script('closebee-utils', plugins_url('includes/assets/js/pearnode-commons-util.js', __FILE__));
        wp_enqueue_script('closebee-cbifunctions', plugins_url('includes/assets/js/pearnode-commons-cb-inventory-functions.js', __FILE__));
        wp_enqueue_script('closebee-dtcontrols', plugins_url('includes/assets/js/pearnode-datecontrols.js', __FILE__));
    }
    
    register_activation_hook( __FILE__, 'closebee_plugin_activate' );
    register_deactivation_hook( __FILE__, 'closebee_plugin_deactivate' );
    
    add_filter('rest_authentication_errors', 'closebee_json_basic_auth_error');
    add_filter('determine_current_user', 'closebee_json_basic_auth_handler', 20);
    add_filter('the_content', 'closebee_parse_content', 25);
    
    add_action('admin_menu', 'closebee_do_admin_init');
    add_action('admin_post_closebee_registration_form', 'handle_submit_closebee_registration_form');
    add_action('admin_post_closebee_navigation_form', 'handle_submit_closebee_navigation_form');
    
    add_action('in_admin_header', function () {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }, 1000);
     