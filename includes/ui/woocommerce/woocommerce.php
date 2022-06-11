<!doctype html>
<html lang="en">
	<?php wp_head(); ?>
	<style type="text/css">
    	  html.wp-toolbar {
    	      margin-top: 0px !important;
    	      padding-top : 0px important;
    	  }
    	  .wp-submenu {
    	      margin-left: 0px !important;
    	  }
	</style>
	<script>
    	var oc = '<?php echo esc_attr($org->code); ?>';
    	var pc = '<?php echo esc_attr($profile->code); ?>';
    	var uid = '<?php echo esc_attr($user->id); ?>';
    	var uck = '<?php echo esc_attr($user->ck); ?>';
    	var sid = '<?php echo esc_attr($site->id); ?>';
    	var sname = '<?php echo esc_attr($site->site_name); ?>';
    	
	 	function scanSite(){
	 		var lhtml = '<img src="<?php echo esc_attr(plugins_url()."/".$plugin_dir_name."/includes/assets/"); ?>images/loader-snake-blue.gif" style="width: 1.5vw;"/>';
			var wcckey = $('#woocommerce_consumer_key').val().trim();
			var wcsec = $('#woocommerce_consumer_secret').val().trim();
			if(wcckey == ""){
				$('#woocommerce_consumer_key').addClass('is-invalid');
				return false;
			}else{
				$('#woocommerce_consumer_key').removeClass('is-invalid');
			}
			if(wcsec == ""){
				$('#woocommerce_consumer_secret').addClass('is-invalid');
				return false;
			}else{
				$('#woocommerce_consumer_secret').removeClass('is-invalid');
			}
			var pdata = {'oc': oc,'pc': pc, 'sid' : sid, 'woocommerce_consumer_key': wcckey, 'woocommerce_consumer_secret': wcsec};
	    	var postUrl = "https://api.pearnode.com/closebee/site/plugin/woocommerce.php";
	    	$.post(postUrl, JSON.stringify(pdata), function(data) {
	    		submitNavigationForm('closebee-plugin-page-site');
	    	});
		    return false;
		}
		
		function submitNavigationForm(navslug){
			var form = document.getElementById('navigation_form');
			var hiddenField = document.createElement('input');
		    hiddenField.type = 'hidden';
		    hiddenField.name = 'navslug';
		    hiddenField.value = navslug;
		    form.appendChild(hiddenField);
		    form.submit();
		    return false;
		}
	</script>
	
	<body style="overflow-x:hidden;">
		<div class="row w-100 m-0 p-2">
        	<div class="card-header bg-light w-100 mt-2" style="font-weight: bold;">
        		Integrate Woocommerce with Closebee
        	</div>
        	<div class="card-body w-100 p-1 mt-1">
        		<div class="row w-100 m-0 mb-2 mt-1">
        			<div class="col-5 p-0">
        				<img src="<?php echo esc_attr(plugins_url()."/".$plugin_dir_name."/includes/assets/"); ?>images/woocommerce.png" 
        					class="shadow shadow-sm rounded w-100" 
        					style="height: 40vh;border-radius: 16px; "/>
        			</div>
        			<div class="col-7">
        				<ul class="w-100 ml-4" style="font-size: 15px !important;list-style-type: square;">
                          <li class="p-1" style="text-decoration:underline;">Steps to configure Woocommerce with Closebee</li>
                          <li>
                              <ol>
                                  <li class="p-1">Goto Menu > Woocommerce > Settings > Advanced > REST API</li>
                                  <li class="p-1">Click on the Add key button</li>
                                  <li class="p-1">Enter description as "Closebee Woocommerce"</li>
                                  <li class="p-1">Change Permission to "Read / Write"</li>
                                  <li class="p-1">Click on "Generate API Key" button</li>
                                  <li class="p-1">Copy the "Consumer Key" and "Consumer Secret" paste it below</li>
                              </ol>
                          </li>
                          <li class="p-1">Click on the "Start Integration" button</li>
                        </ul>
        			</div>
        		</div>
        		<div class="form-group mt-1">
        		    <label for="woocommerce_consumer_key"><b>Woocommerce Consumer Key</b></label>
        		    <input type="text" class="form-control" id="woocommerce_consumer_key" name="woocommerce_consumer_key" 
        		    	required="required"  value="<?php echo esc_attr($wc_consumer_key);?>"/>
        	    	<small id="wcckeyhelp" class="form-text text-muted">Enter the woocommerce Consumer Key you generated here</small>
        		</div>
        		<div class="form-group mt-1">
        		    <label for="woocommerce_consumer_secret"><b>Woocommerce Consumer Secret</b></label>
        		    <input type="text" class="form-control" id="woocommerce_consumer_secret" name="woocommerce_consumer_secret" 
        		    	required="required" value="<?php echo esc_attr($wc_consumer_secret);?>">
        	    	<small id="wccsechelp" class="form-text text-muted">Enter the woocommerce Consumer Secret you generated here</small>
        		</div>
        	</div>
        	<div class="card-footer w-100">
        		<button class="btn btn-primary w-100" onclick="return scanSite();">
        			<b>Start Integration</b>
        		</button>
        	</div>
		</div>
	</body>
</html>