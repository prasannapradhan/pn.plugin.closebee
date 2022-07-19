<?php
    /**
     * Plugin Name: Closebee Ecommerce Tailor
     * Description: Closebee is the tool to stich different ecommerce components into one platform
     * Author: Closebee Technology Team
     * Author URI: https://pearnode.com
     * Version: 1.0.0.0
     * Plugin URI: https://closebee.com
     */
    
    global $org, $profile, $user, $billing_user, $billing_address, $post_args;
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
            $ddata = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+', explode('.', $token)[1]))));
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
        add_filter('woocommerce_checkout_get_value', 'closebee_checkout_fields', 10, 2 );
        add_filter('woocommerce_enable_order_notes_field', '__return_false', 9999);
        add_filter('woocommerce_thankyou_order_received_text', 'closebee_thankyou_order_received_text', 10, 2);
        add_action('woocommerce_order_status_changed', 'closebee_order_status_changed', 999, 4);
        add_filter('woocommerce_get_item_data', 'closebee_get_item_data' , 25, 2);
        add_action('woocommerce_new_order_item', 'closebee_new_order_item', 10, 2);
        add_filter('woocommerce_checkout_fields', 'closebee_set_checkout_field_input_value_default' );
    }
    
    function closebee_checkout_coupon_message($notice) {
        return "<a href='#' id='autofill_address'><b>Choose from Address Registry</b></a>";
    }; 
    
    function closebee_before_checkout_form($wccm_autocreate_account) {
        global $billing_user, $billing_address;
        if(isset($_GET['uaid'])){
            $uaid = $_GET['uaid'];
            $rdata = array('uadid' => $uaid);
            $post_args['body'] = json_encode($rdata);
            $out = wp_remote_post('https://api.pearnode.com/api/user/address/idx.php', $post_args);
            $robj = (object) $out;
            $body = $robj->body;
            $billing_address = json_decode($body);
            $_SESSION['uaid'] = $billing_address->id;
            
            $rdata = array('uid' => $billing_address->user_ref);
            $post_args['body'] = json_encode($rdata);
            $out = wp_remote_post('https://api.pearnode.com/api/user/idx.php', $post_args);
            $robj = (object) $out;
            $body = $robj->body;
            $billing_user = json_decode($body);
            $_SESSION['uid'] = $billing_user->id;
            $_SESSION['ulid'] = $billing_user->login_id;
        }else {
            if(isset($_GET['uck'])){
                $uck = $_GET['uck'];
                $rdata = array('ck' => $uck);
                $post_args['body'] = json_encode($rdata);
                $out = wp_remote_post('https://api.pearnode.com/api/user/ckx.php', $post_args);
                $robj = (object) $out;
                $body = $robj->body;
                $billing_user = json_decode($body);
            }
        }
    }; 
    
    function closebee_checkout_fields( $value, $input = '') {
        global $billing_user, $billing_address;
        if(isset($billing_address->id)){
            $checkout_fields = array(
                'billing_first_name'    => $billing_address->first_name,
                'billing_last_name'     => $billing_address->last_name,
                'billing_country'       => $billing_address->country_code,
                'billing_address_1'     => $billing_address->address_line1,
                'billing_address_2'     => $billing_address->address_line2,
                'billing_city'          => $billing_address->city,
                'billing_state'         => $billing_address->state,
                'billing_postcode'      => $billing_address->zip,
                'billing_phone'         => $billing_address->mob,
                'billing_email'         => $billing_address->email,
                'shipping_first_name'   => $billing_address->first_name,
                'shipping_last_name'    => $billing_address->last_name,
                'shipping_country'      => $billing_address->country_code,
                'shipping_address_1'    => $billing_address->address_line1,
                'shipping_address_2'    => $billing_address->address_line2,
                'shipping_city'         => $billing_address->city,
                'shipping_state'        => $billing_address->state,
                'shipping_postcode'     => $billing_address->zip,
            );
            if(isset($checkout_fields[$input])){
                if($input == "billing_state"){
                    $statemap = WC()->countries->get_states($billing_address->country_code);
                    foreach ($statemap as $skey => $svalue) {
                        if($svalue == $billing_address->state){
                            $value = $skey;
                        }
                    }
                }else {
                    $value = $checkout_fields[$input];
                }
            }
        }
        return $value;
    }
    
    function closebee_set_checkout_field_input_value_default($fields){
        unset($fields['billing']['billing_company']);
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
        if(!empty($_POST['seller_select'])) {
            $soid = $_POST['seller_select'];
            $cart_item_data['soid'] = $soid;
        }
        return $cart_item_data;
    }
    
    function closebee_new_order_item($item_id, $cart_item_data) {
        $cdata = (object) $cart_item_data;
        if (isset($cdata->legacy_values)){
            $lv = (object) $cdata->legacy_values;
            if(isset($lv->soid)){
                $soid = $lv->soid;
                wc_add_order_item_meta($item_id, '_soid', $soid);
            }
        }else {
            error_log("cart item data does not contain soid. Ignoring.");
        }
    }
    
    function closebee_order_status_changed($order_id, $old_status, $new_status, $order){
        global $post_args, $billing_user, $billing_address;
        global $org, $profile;
        if($new_status == "processing"){
            updateOrderWithMeta($order_id);
            $rdata = (object) array('surl' => get_site_url(), 'ch_odid' => $order_id, 'uid' => $billing_user->id, 'uaid' => $billing_address->id);
            $nb_post_args = $post_args;
            $nb_post_args['blocking'] = false;
            $ojson = json_encode($rdata);
            $nb_post_args['body'] = $ojson;
            wp_remote_post('https://api.pearnode.com/closebee/site/integ/woocommerce/order/created.php', $nb_post_args);
        }
    }
    
    function updateOrderWithMeta($order_id) {
        global $billing_user, $billing_address;
        global $post_args, $plugin_dir, $plugin_dir_name;
        global $org, $profile, $user, $cred_file;
        $uobj = (object) array('subs_type', 'open');
        $uai = (object) array();
        
        $uid = -1;
        $ulid = "";
        if(isset($_SESSION['uid'])){
            $uid = $_SESSION['uid'];
            $ulid = $_SESSION['ulid'];
            $uobj->id = $uid;
        }
        $billing_user->id = $uid;
        
        $uaid = -1;
        if(isset($_SESSION['uaid'])){
            $uaid = $_SESSION['uaid'];
            $uai->id = $uaid;
        }
        $billing_address->id = $uaid;
        
        $order = wc_get_order($order_id);
        $user_email = "";
        if($uid != -1){
            $user_email = $ulid;
        }else {
            $user_email = $order->get_billing_email();
        }
        if (!is_user_logged_in()){
            $uobj->login_id = $user_email;
            $email = email_exists($user_email);
            $user = username_exists($user_email);
            if ($user == false && $email == false) {
                $random_password = wp_generate_password();
                $first_name = $order->get_billing_first_name();
                $last_name = $order->get_billing_last_name();
                $role = 'customer';
                
                $userx = wp_insert_user(array('user_email' => $user_email, 'user_login' => $user_email,
                    'user_pass' => $random_password, 'first_name' => $first_name, 'last_name' => $last_name,
                    'role' => $role));
                
                $uobj->full_name = $first_name." ".$last_name;
                $uobj->mobile_no = $order->get_billing_phone();
                $uobj->password = $random_password;
                
                $uai->address_line1 = $order->get_billing_address_1();
                $uai->address_line2 = $order->get_billing_address_2();
                if(!trim($uai->address_line2) == ""){
                    $uai->address = $uai->address_line1." , ".$uai->address_line2;
                }else {
                    $uai->address = $uai->address_line1;
                }
                $uai->city = $order->get_billing_city();
                $uai->country_name = $order->get_billing_country();
                $uai->country = $order->get_billing_country();
                $uai->zip = $order->get_billing_postcode();
                $uai->state_name = $order->get_billing_state();
                $uai->state = $order->get_billing_state();
                
                update_user_meta($userx, 'billing_address_1', $uai->address_line1);
                update_user_meta($userx, 'billing_city', $uai->city);
                update_user_meta($userx, 'billing_company', $order->get_billing_company());
                update_user_meta($userx, 'billing_country', $uai->country);
                update_user_meta($userx, 'billing_email', $order->get_billing_email());
                update_user_meta($userx, 'billing_first_name', $first_name);
                update_user_meta($userx, 'billing_last_name',  $last_name);
                update_user_meta($userx, 'billing_phone', $uobj->mobile_no);
                update_user_meta($userx, 'billing_postcode', $uai->zip);
                update_user_meta($userx, 'billing_state', $uai->state);
                
                update_user_meta($userx, 'shipping_address_1', $order->get_shipping_address_1());
                update_user_meta($userx, 'shipping_city', $order->get_shipping_city());
                update_user_meta($userx, 'shipping_company', $order->get_shipping_company());
                update_user_meta($userx, 'shipping_country', $order->get_shipping_country());
                update_user_meta($userx, 'shipping_first_name', $order->get_shipping_first_name());
                update_user_meta($userx, 'shipping_last_name', $order->get_shipping_last_name());
                update_user_meta($userx, 'shipping_method', $order->get_shipping_method());
                update_user_meta($userx, 'shipping_postcode', $order->get_shipping_postcode());
                update_user_meta($userx, 'shipping_state', $order->get_shipping_state());
                
                wc_update_new_customer_past_orders($userx);
                wp_set_current_user($userx);
                wp_set_auth_cookie($userx);
            }else {
                $user = get_user_by('email', $user_email);
                $uobj->login_id = $user_email;
                $uobj->full_name = $user->user_firstname." ".$user->user_lastname;
                $uobj->mobile_no = get_user_meta($user->ID, 'user_phone' , true);
            }
        }else {
            $cuser = (object) wp_get_current_user();
            $uobj->login_id = $cuser->user_email;
            $uobj->full_name =  $cuser->user_firstname." ".$cuser->user_lastname ;
            $uobj->mobile_no = get_user_meta($cuser->ID,'user_phone',true);
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

        if($billing_user->id == -1){
            $uobj->ai = $uai;
            $rdata = (object) array('user' => $uobj);
            $nb_post_args = $post_args;
            $nb_post_args['blocking'] = true;
            $ojson = json_encode($rdata);
            $nb_post_args['body'] = $ojson;
            $out = wp_remote_post('https://api.pearnode.com/api/user/self/createxy.php', $nb_post_args);
            $robj = (object) $out;
            error_log("User create details [".json_encode($robj)."]");
            $body = $robj->body;
            $billing_user = json_decode($body);
            $billing_address = $billing_user->ai;
            unset($billing_user->ai);
        }else {
            $rdata = (object) array('id' => $billing_user->id, 'aid' => $billing_address->id);
            $nb_post_args = $post_args;
            $nb_post_args['blocking'] = true;
            $ojson = json_encode($rdata);
            $nb_post_args['body'] = $ojson;
            $out = wp_remote_post('https://api.pearnode.com/api/user/self/details_id.php', $nb_post_args);
            $robj = (object) $out;
            error_log("User load details [".json_encode($robj)."]");
            $body = $robj->body;
            $billing_user = json_decode($body);
            $billing_address = $billing_user->ai;
            unset($billing_user->ai);
        }
    }
    
    function closebee_do_admin_init(){
		add_menu_page('Closebee', 'Closebee', 'manage_options', 'closebee-plugin-settings', 'closebee_plugin_settings', 'dashicons-superhero', 5);
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
    add_action('admin_menu', 'closebee_do_admin_init');
    add_action('admin_post_closebee_registration_form', 'handle_submit_closebee_registration_form');
    add_action('admin_post_closebee_navigation_form', 'handle_submit_closebee_navigation_form');
    add_action('woocommerce_init', 'action_woocommerce_init', 10, 1); 
    add_action('init', 'register_my_session');
    
    add_action('in_admin_header', function () {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }, 1000);
    
    function register_my_session(){
        if(!session_id() ) {
            session_start();
        }
    }
     