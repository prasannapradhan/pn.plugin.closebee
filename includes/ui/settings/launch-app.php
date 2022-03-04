<div class="card-body row justify-content-center" style="min-height: 30vh;">
	<div class="row w-100 m-0 justify-content-center">
		<div class="col-6 border-1">
			<div class="row w-100 m-0 justify-content-left">
    			<h2 class="my-auto alert alert-light w-100 pl-0 pt-0">Congratulations !!</h2>
    			<div class="row w-100 m-0 justify-content-left mt-2">
    				<span>Your site is now integrated !!!</span>
    			</div>
    			<div class="row w-100 m-0 justify-content-left mt-1">
    				<span style="font-size:12px;color:grey;">* A standard lead widget has been created and attached to all your site pages. </span>
    			</div>
            	<div class="row w-100 m-0 justify-content-left mt-3">
            		<div class="row w-100 m-0 mt-2">
            			<span>Limited features are available in the plugin. </span>
            		</div>
            		<div class="row w-100 m-0 mt-1">
            			<span>We recommend you use our app for using all the features.</span>
            		</div>
            		<div class="row w-100 m-0 mt-3">
	            		<button class="btn btn-primary w-75" onclick="return launchApp();">Launch App</button>
            		</div>
            	</div>
			</div>
		</div>
		
		<div class="col-6">
			<div class="row w-100 m-0 bg-light p-2 border-1 rounded alert alert-light"><b>Plugin Navigation</b></div>
			<div class="row w-100 m-0 mt-2">
				<form action='<?php echo esc_attr(get_admin_url()); ?>admin-post.php' method='post' id="navigation_form">
    				<script>
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
    				<ul class="w-100 ml-3" style="font-size: 15px !important;list-style-type: square;">
                      <li class="p-1">You can find all your site pages in 
                      	<a href="#" onclick="return submitNavigationForm('closebee-plugin-page-site')"><b>Pages</b></a> page.
                      </li>
                      <li class="p-1">Advanced features, change operations and much more are found in the <a href="#" onclick="return launchApp();"><b>Closebee App</b></a>
                      </li>
                      <li class="p-1">To raise a support issue at <a href="https://support.pearnode.com" 
                      	target="pearnode_support"><b>Support Portal</b></a> or mail us <b style="color:grey;">support@pearnode.com</b></li>
                    </ul>
                    <input type='hidden' name='action' value='closebee_navigation_form' />
                </form>
			</div>
		</div>
	</div>
</div>
