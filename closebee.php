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
    $billing_user = (object) array();
    $billing_address = (object) array();
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
    
    $plugin_dir = plugin_dir_path( __FILE__);
    $plugin_dir_name = "";
    $cred_file = $plugin_dir."credentials.json";
    
    require_once $plugin_dir."includes/ClosebeePluginActivator.php";
    require_once $plugin_dir."includes/ClosebeePluginDeactivator.php";
    
    function closebee_json_basic_auth_handler($user) {
        if (!empty($user)) {
            return $user;
        }
        if(!isset($_SERVER['PHP_AUTH_USER'])) {
            return $user;
        }
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        remove_filter( 'determine_current_user', 'closebee_json_basic_auth_handler', 20);
        $user = wp_authenticate($username, $password);
        add_filter( 'determine_current_user', 'closebee_json_basic_auth_handler', 20);
        if ( is_wp_error($user)) {
            return null;
        }
        return $user->ID;
    }
    
    function closebee_json_basic_auth_error($error) {
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
    		  include($plugin_dir."includes/ui/settings/launch-app.php");
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

    function action_woocommerce_init($params){
        global $org, $profile;
        add_action('wp_enqueue_scripts', "load_widget_dependencies");
        add_action('woocommerce_before_add_to_cart_button' , 'closebee_before_add_to_cart_button', 5);
        add_filter('woocommerce_add_cart_item_data', 'closebee_add_cart_item_data', 10, 3);
        add_action('woocommerce_before_checkout_form', 'closebee_before_checkout_form', 10, 1);
        add_filter('woocommerce_checkout_coupon_message', 'closebee_checkout_coupon_message', 20, 1);
        add_filter('woocommerce_checkout_fields', 'closebee_checkout_fields');
        add_filter('woocommerce_enable_order_notes_field', '__return_false', 9999);
        add_filter('woocommerce_thankyou_order_received_text', 'closebee_thankyou_order_received_text', 10, 2);
        add_action('woocommerce_order_status_changed', 'closebee_order_status_changed', 999, 4);
        add_filter('woocommerce_get_item_data', 'closebee_get_item_data' , 25, 2);
        add_action('woocommerce_new_order_item', 'closebee_new_order_item', 10, 2);
    }
    
    function closebee_checkout_coupon_message($notice) {
        return "<a href='#' id='autofill_address'><b>Login with <i class='fa-brands fa-google'></i> Google / <i class='fa-brands fa-apple'></i> Apple</b></a>";
    }; 
    
    function closebee_before_checkout_form($wccm_autocreate_account) {
        global $billing_user, $billing_address;
        if(isset($_GET['uck'])){
            $uck = $_GET['uck'];
            $rdata = array('ck' => $uck);
            $post_args['body'] = json_encode($rdata);
            $out = wp_remote_post('https://api.pearnode.com/api/user/ckx.php', $post_args);
            $robj = (object) $out;
            $body = $robj->body;
            $billing_user = json_decode($body);
            if(isset($_GET['uadc'])){
                $uadc = $_GET['uadc'];
                $rdata = array('code' => $uadc);
                $post_args['body'] = json_encode($rdata);
                $out = wp_remote_post('https://api.pearnode.com/api/user/address/codex.php', $post_args);
                $robj = (object) $out;
                $body = $robj->body;
                $billing_address = json_decode($body);
            }
        }
    }; 
    
    function closebee_checkout_fields($fields) {
        global $billing_user, $billing_address;
        unset($fields['billing']['billing_company']);
        unset($fields['shipping']['shipping_company']);
        unset($fields['order']['order_comments']);
        if(isset($billing_address->code)){
            $fields['billing']['billing_first_name']['default'] = $billing_address->first_name;
            $fields['billing']['billing_last_name']['default'] = $billing_address->last_name;
            $fields['billing']['billing_country']['default'] = $billing_address->country_name;
            $fields['billing']['billing_address_1']['default'] = $billing_address->address_line1;
            $fields['billing']['billing_city']['default'] = $billing_address->city;
            $fields['billing']['billing_state']['default'] = $billing_address->state;
            $fields['billing']['billing_postcode']['default'] = $billing_address->zip;
            $fields['billing']['billing_phone']['default'] = $billing_address->mob;
            $fields['billing']['billing_email']['default'] = $billing_address->email;
            
            $fields['shipping']['shipping_first_name']['default'] = $billing_address->first_name;
            $fields['shipping']['shipping_last_name']['default'] = $billing_address->last_name;
            $fields['shipping']['shipping_country']['default'] = $billing_address->country_name;
            $fields['shipping']['shipping_address_1']['default'] = $billing_address->address_line1;
            $fields['shipping']['shipping_city']['default'] = $billing_address->city;
            $fields['shipping']['shipping_state']['default'] = $billing_address->state;
            $fields['shipping']['shipping_postcode']['default'] = $billing_address->zip;
            $fields['shipping']['shipping_phone']['default'] = $billing_address->mob;
            $fields['shipping']['shipping_email']['default'] = $billing_address->email;
        }
        return $fields;
    }
    
    function closebee_thankyou_order_received_text($str, $order) {
        if (is_user_logged_in()) return $str;
        $order_email = $order->get_billing_email();
        $email = email_exists($order_email);
        $user = username_exists($order_email);
        if ($user == false && $email == false) {
            $link = get_permalink( get_option( 'woocommerce_myaccount_page_id'));
            $format_link = '<a href="' . $link . '">logged in</a>';
            $str .= sprintf( __( ' An account has been automatically created for you and you are now %s. You will receive an email about this.', 'woocommerce'), $format_link);
        }
        return $str;
    }
 
    function closebee_before_add_to_cart_button(){
        $meta = (object) get_post_meta(get_the_ID());
        if(isset($meta->sons)){
            $sons = $meta->sons;
            $soids = $meta->soids;
            $buff = "<div class='row w-100 m-0' style='margin-bottom:5px;margin-left:5px;'> Sold by :";
            if(sizeof($sons) > 1){
                $sel = "<select id='seller_select' name='seller_select' class='form-control w-100'>";
                foreach ($sons as $key => $son) {
                    $sel .= "<option value='".$soids[$key]."'>".$son."</option>";
                }
                $sel .= "</select>";
                $buff .= $sel;
            }else {
                $buff .="<b><span style='margin-left:5px;'>".$sons[0]."<span>";
                $buff .="<input type='hidden' id='seller_select' name='seller_select' value='".$soids[0]."'/></b>";
            }
            $buff .= "</div>";
            echo $buff;
        }
    }
    
    function closebee_get_item_data($item_data, $cart_item_data) {
        global $post_args;
        global $org, $profile;
        if(isset($cart_item_data['soid'])){
            $soid = $cart_item_data['soid'];
            $rdata = array('soid' => $soid);
            $post_args['body'] = json_encode($rdata);
            $out = wp_remote_post('https://api.pearnode.com/closebee/seller/details.php', $post_args);
            $robj = (object) $out;
            $body = $robj->body;
            $seller = json_decode($body);
            $item_data[] = array('name' => 'Sold By','display' => $seller->org->name);
        }
        return $item_data;
    }
    
    function closebee_add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        global $billing_user, $billing_address;        
        if(!empty($_POST['seller_select'])) {
            $soid = $_POST['seller_select'];
            $cart_item_data['soid'] = $soid;
            if(isset($billing_user->id)){
                $cart_item_data['uid'] = $billing_user->id;
            }
            if(isset($billing_address->id)){
                $cart_item_data['uadid'] = $billing_address->id;
            }
        }
        return $cart_item_data;
    }
    
    function closebee_new_order_item($item_id, $cart_item_data) {
        error_log("Cart item data [".json_encode($cart_item_data)."]");
        $cdata = (object) $cart_item_data;
        if (isset($cdata->legacy_values)){
            $lv = (object) $cdata->legacy_values;
            if(isset($lv->soid)){
                $soid = $lv->soid;
                wc_add_order_item_meta($item_id, '_soid', $soid);
            }
            if(isset($lv->uid)){
                wc_add_order_item_meta($item_id, '_uid', $lv->uid);
            }
            if(isset($lv->uadid)){
                wc_add_order_item_meta($item_id, '_uadid', $lv->uadid);
            }
        }else {
            error_log("cart item data does not contain soid. Ignoring.");
        }
    }
    
    function closebee_order_status_changed($order_id, $old_status, $new_status, $order){
        global $post_args;
        error_log("Closebee order status changed");
        if($new_status == "processing"){
            updateOrderWithMeta($order_id);
            $rdata = (object) array('surl' => get_site_url(), 'ch_id' => $order_id);
            $nb_post_args = $post_args;
            $nb_post_args['blocking'] = false;
            $ojson = json_encode($rdata);
            error_log("Calling update api with [$ojson]");
            $nb_post_args['body'] = $ojson;
            wp_remote_post('https://api.pearnode.com/closebee/site/integ/woocommerce/order/created.php', $nb_post_args);
        }
    }
    
    function updateOrderWithMeta($order_id) {
        $order = wc_get_order($order_id);
        if (!is_user_logged_in()){
            $order_email = $order->get_billing_email();
            $email = email_exists($order_email);
            $user = username_exists($order_email);
            if ($user == false && $email == false) {
                $random_password = wp_generate_password();
                $first_name = $order->get_billing_first_name();
                $last_name = $order->get_billing_last_name();
                $role = 'customer';
                
                $user_id = wp_insert_user(array('user_email' => $order_email, 'user_login' => $order_email,
                    'user_pass' => $random_password, 'first_name' => $first_name, 'last_name' => $last_name,
                    'role' => $role)
                    );
                
                update_user_meta($user_id, 'billing_address_1', $order->get_billing_address_1());
                update_user_meta($user_id, 'billing_city', $order->get_billing_city());
                update_user_meta($user_id, 'billing_company', $order->get_billing_company());
                update_user_meta($user_id, 'billing_country', $order->get_billing_country());
                update_user_meta($user_id, 'billing_email', $order_email);
                update_user_meta($user_id, 'billing_first_name', $order->get_billing_first_name());
                update_user_meta($user_id, 'billing_last_name',  $order->get_billing_last_name());
                update_user_meta($user_id, 'billing_phone', $order->get_billing_phone());
                update_user_meta($user_id, 'billing_postcode', $order->get_billing_postcode());
                update_user_meta($user_id, 'billing_state', $order->get_billing_state());
                update_user_meta($user_id, 'shipping_address_1', $order->get_shipping_address_1());
                update_user_meta($user_id, 'shipping_city', $order->get_shipping_city());
                update_user_meta($user_id, 'shipping_company', $order->get_shipping_company());
                update_user_meta($user_id, 'shipping_country', $order->get_shipping_country());
                update_user_meta($user_id, 'shipping_first_name', $order->get_shipping_first_name());
                update_user_meta($user_id, 'shipping_last_name', $order->get_shipping_last_name());
                update_user_meta($user_id, 'shipping_method', $order->get_shipping_method());
                update_user_meta($user_id, 'shipping_postcode', $order->get_shipping_postcode());
                update_user_meta($user_id, 'shipping_state', $order->get_shipping_state());
                
                wc_update_new_customer_past_orders($user_id);
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
            }
        }
        if(trim($order->get_shipping_address_1()) == ""){
            $order->set_shipping_address_1($order->get_billing_address_1());
            $order->set_shipping_city($order->get_billing_city());
            $order->set_shipping_company($order->get_billing_company());
            $order->set_shipping_country($order->get_billing_country());
            $order->set_shipping_first_name($order->get_billing_first_name());
            $order->set_shipping_last_name($order->get_billing_last_name());
            $order->set_shipping_postcode($order->get_billing_postcode());
            $order->set_shipping_state($order->get_billing_state());
            $order->set_shipping_phone($order->get_billing_phone());
        }
        $order->save();
    }
    
    
    function closebee_do_admin_init(){
		add_menu_page('Closebee', 'Closebee Beta', 'manage_options', 'closebee-plugin-settings', 'closebee_plugin_settings', 'dashicons-superhero', 5);
		add_submenu_page('closebee-plugin-settings', 'Closebee Settings', 'Settings', 'manage_options', 'closebee-plugin-settings', 'closebee_plugin_settings');
		add_submenu_page('closebee-plugin-settings', 'Closebee Plugin Woocommerce', 'Woo Config', 'manage_options', 'closebee-plugin-page-woocommerce', 
		    'closebee_plugin_page_woocommerce');
		if (get_option('my_plugin_do_activation_redirect', false)) {
		    delete_option('my_plugin_do_activation_redirect');
		    wp_redirect('admin.php?page=closebee-plugin-settings');
		}
    }
 
    function load_style_dependencies(){
        wp_enqueue_style('closebee-font-awsome', plugins_url('includes/assets/css/fontawsome-6.0.0-all-min.css', __FILE__));
        wp_enqueue_style('closebee-bootstrap-4.3.1', plugins_url('includes/assets/css/bootstrap-4.6.1.min.css', __FILE__));
        wp_enqueue_style('closebee-nprogress', plugins_url('includes/assets/css/nprogress.css', __FILE__));
    }
    
    function load_script_dependencies(){
        wp_enqueue_script('closebee-jquery', plugins_url('includes/assets/js/jquery-1.12.4.min.js', __FILE__));
        wp_enqueue_script('closebee-bootstrap', plugins_url('includes/assets/js/bootstrap-4.6.1.min.js', __FILE__));
        wp_enqueue_script('closebee-nprogress', plugins_url('includes/assets/js/nprogress.js', __FILE__));
        wp_enqueue_script('closebee-mustache', plugins_url('includes/assets/js/mustache.min.js', __FILE__));
        wp_enqueue_script('closebee-currency-formatter', plugins_url('includes/assets/js/currency-formatter-2.0.0.min.js', __FILE__));
        wp_enqueue_script('closebee-sweetalert', plugins_url('includes/assets/js/sweetalert-2.9.17.min.js', __FILE__));
        wp_enqueue_script('closebee-commons-util', plugins_url('includes/assets/js/pearnode-commons-util.js', __FILE__));
    }
    
    function load_widget_dependencies(){
        wp_enqueue_script('closebee-widget-handler', plugins_url('includes/assets/js/handler.js', __FILE__));
    }
    
    register_activation_hook( __FILE__, 'closebee_plugin_activate');
    register_deactivation_hook( __FILE__, 'closebee_plugin_deactivate');
    
    add_filter('rest_authentication_errors', 'closebee_json_basic_auth_error');
    add_filter('determine_current_user', 'closebee_json_basic_auth_handler', 20);
    add_filter('the_content', 'closebee_parse_content', 25);
    add_action('admin_menu', 'closebee_do_admin_init');
    add_action('admin_post_closebee_registration_form', 'handle_submit_closebee_registration_form');
    add_action('admin_post_closebee_navigation_form', 'handle_submit_closebee_navigation_form');
    add_action('woocommerce_init', 'action_woocommerce_init', 10, 1); 
    
    add_action('in_admin_header', function () {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }, 1000);
     