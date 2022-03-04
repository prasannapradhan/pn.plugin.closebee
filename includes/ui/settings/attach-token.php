<form action='<?php echo esc_attr(get_admin_url()); ?>admin-post.php' method='post' id="registration_form">
	<div class="card-header bg-light w-100 mt-2" style="font-weight: bold;">
		Integrate Closebee to your site 
		<div class="float-right">Not Registered yet ? 
			<a class="link link-primary my-auto" href="https://app.closebee.com/wp_register.html" target="_cbwpregister">Register here</a>
		</div>
	</div>
	<div class="card-body w-100 p-1 mt-2">
		<div class="row w-100 m-0 mb-2 mt-1">
			<div class="col-5 p-0">
				<img src="<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/sitefunction.jpg" 
					class="shadow shadow-sm rounded w-100" 
					style="height: 30vh;border-radius: 16px;;vertical-align:middle !important;"/>
			</div>
			<div class="col-7">
				<ul class="w-100 ml-3" style="font-size: 15px !important;list-style-type: square;">
                  <li class="p-1"><b>First Step</b> is to register your site with 
                  	<a class="link link-primary" href="https://app.closebee.com/wp_register.html" target="_nzkwpregister">Closebee Registrar</a>.
                  </li>
                  <li class="p-1"><b><a href="https://app.closebee.com/wp_register.html" target="nuzuka_site">Closebee</a></b> 
                  	Closebee is the ecommerce tailor which stiches ecommerce, marketplace, enquiry capture, inventory management, 
                  	warehouse management, shipping, billing and accounting through your website.
                  </li>
                  <li class="p-1">It takes <b>less than a minute</b> to register your business and integrate</li>
                  <li class="p-1"><b><a href="https://nuzuka.com" target="nuzuka_site">Closebee</a></b> is currently in Beta and all 
                  	registrations are <b>FREE for life</b></li>
                  <li class="p-1">For any queries you can mail us <b>connect@closebee.com</b>, and for support <b>support@pearnode.com</b></li>
                </ul>
			</div>
		</div>
		<div class="form-group mt-2">
		    <label for="authtoken"><b>Enter Registration token</b></label>
		    <textarea rows="4" class="form-control" id="authtoken" name="authtoken" required="required"></textarea>
	    	<small id="authtokenhelp" class="form-text text-muted">Enter the post registration authorization token here</small>
		</div>
		<input type='hidden' name='action' value='nuzuka_registration_form' />
	</div>
	<div class="card-footer w-100">
		<button class="btn btn-primary w-100" type="submit">
			<b>Start Integration</b>
		</button>
	</div>
</form>
