<script>
    function scanSite(){
		$('#modal_site_name').text(sname);
		$('#scan_modal').modal('show');
    	var pdata = {'oc': oc,'pc': pc, 'sid' : sid};
		var postUrl = "https://api.pearnode.com/closebee/site/scan/page_open.php"; 
		$('#page_scan_result').html('<img src="<?php echo esc_attr(plugins_url()."/".$plugin_dir_name."/includes/assets/"); ?>images/loader-snake-blue.gif" style="width: 1.5vw;"/>');
	    $.post(postUrl, JSON.stringify(pdata), function(data) {
	    	var robj = $.parseJSON(data);
	    	var pgstatus = robj.status;
	    	if(pgstatus.status == "success"){
		    	$('#page_scan_result').html(robj.fetch_ctr + " found, " + robj.add_ctr + " added, " + robj.update_ctr + " updated");
		    	postUrl = "https://api.pearnode.com/closebee/site/scan/post_open.php"; 
				$('#post_scan_result').html('<img src="<?php echo esc_attr(plugins_url()."/".$plugin_dir_name."/includes/assets/"); ?>images/loader-snake-blue.gif" style="width: 1.5vw;"/>');
			    $.post(postUrl, JSON.stringify(pdata), function(data) {
			    	var robj = $.parseJSON(data);
			    	var psstatus = robj.status;
			    	if(psstatus.status == "success"){
				    	$('#post_scan_result').html(robj.fetch_ctr + " found, " + robj.add_ctr + " added, " + robj.update_ctr + " updated");
				    	var postUrl = "https://api.pearnode.com/closebee/site/scan/update.php"; 
				    	 $.post(postUrl, JSON.stringify(pdata), function(data) {
 					    	// TODO navigate to site settings page.
	 					    submitNavigationForm('closebee-plugin-page-site');
				    	 });
			    	}else {
			    		$('#post_scan_result').html("Error in scanning : <b style='color:red;'>" + pgstatus.code + "</b>");
			    	}
			    });
	    	}else {
	    		$('#page_scan_result').html("Error in scanning : <b style='color:red;'>" + pgstatus.code + "</b>");
	    	}
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
<div class="card-body row justify-content-center" style="min-height: 30vh;"  id="scan_container">
	<div class="row w-100 m-0 justify-content-center">
		<h4 class="my-auto">Your site is now ready for Integration</h4>
	</div>
	<div class="row w-100 m-0 justify-content-center">
		<button class="btn btn-primary w-25 my-auto" onclick="return scanSite();">Integrate now</button>
	</div>
	<form action='<?php echo esc_attr(get_admin_url()); ?>admin-post.php' method='post' id="navigation_form">
        <input type='hidden' name='action' value='closebee_navigation_form' />
    </form>
</div>
