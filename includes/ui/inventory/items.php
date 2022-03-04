<!doctype html>
<html lang="en">
<head>
	<?php wp_head(); ?>
	
	<script type="text/html" id="item_card_template">
		<div class="card-deck justify-content-center">
			{{#records}}
			<div class="card mb-2 item_row" style="min-width: 18rem; max-width: 18rem;margin-top:0px !important;padding:0.25rem !important;">
				<div class="card-header d-flex justify-content-center bg-light" style="min-height:10vh;">
					<div class="col-auto my-auto">
						<span style="font-size:13px; font-weight:bold;">{{dname}}</span>
					</div>
				</div>
				<div class="card-header d-flex justify-content-center bg-light" >
					<div class="col-auto my-auto">
						<span class="badge badge-info" style="font-size:13px; font-weight:bold;">{{category}}</span>
					</div>
				</div>
				<div class="card-body">
					<div class="row w-100 justify-content-center m-0">
						<img class="card-img-top rounded-lg shadow border-2" src="{{img_url}}" style="height:12rem;width:12rem;">
					</div>
				</div>
			</div>
			{{/records}} 
		</div>
	</script>
		
	<script>
    	var oc = '<?php echo esc_attr($org->code); ?>';
    	var pc = '<?php echo esc_attr($profile->code); ?>';
    	var oid = '<?php echo esc_attr($org->id); ?>';
    	var pid = '<?php echo esc_attr($profile->id); ?>';
    	var uid = '<?php echo esc_attr($user->id); ?>';
    	var uck = '<?php echo esc_attr($user->ck); ?>';
		var processing_img = '<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/ajax-loader.gif';
    	var grp_search_options = {list: { match: {enabled: true}, maxNumberOfElements: 10 }, placeholder: "Search..", theme: "blue"};
		var momObj;
		var allgroup = {id : -1, name : 'All Groups', code : 'all'};
		var selectedGid = "all";

		$(document).ready(function() {
			showProcessingLoader('Loading..');
			loadTemplates();
			initView();
			$('#one_search').on("keyup", function(e) {
	 	        var key = e.which;
	 	        e.preventDefault();
	 	        if (key == 13) {
	 	        	searchItems();
	 	        }
	 	    });
		});
		
		function initView(){
			loadInventoryGroupsFromApi(function(){
				loadInventory();
			});
		}		
		
		function loadInventory(callback){
			loadInventoryFromApi(function(loadedInventory){
				if(loadedInventory.length > 0){
					$('#actions_container').fadeIn(200);
					$('#items_container').fadeIn(200);
					renderItems(loadedInventory, function(){
						hideProcessingLoader();
						if(typeof callback != "undefined"){
							callback();
						}
					});
				}else {
					$('#actions_container').hide();
					$('#items_container').hide();
					$('#no_items_container').fadeIn(300);
				}
			});
		}
		
		function loadTemplates(callback){
			_it = document.getElementById('item_card_template').innerHTML;
		    if(typeof callback != "undefined"){
			   callback();
		    }
		}

		function renderGroupSelection(){
  			var glist = Object.values(serviceGroupMap);
   			glist = [allgroup].concat(glist);
			$.each(glist, function(idx, g){
				g.id = g.code;
				g.text = g.name;
			});
			$("#group_select").select2({
				   data: glist,
				   theme: "bootstrap4",
				   placeholder: 'All Groups'
			});
			$('#group_select').off('select2:select');			
			$('#group_select').on('select2:select', function (e) {
				selectedGid = $(this).val();
				loadServices();
			});
	    }
		
		function searchItems(){
			iskey = $('#one_search').val();
			loadServices();
			return false;
		}

		function renderItems(loadedItems, callback){
			renderGroupSelection();
			$('#items_container').append(Mustache.render(_it, {'records' : loadedItems}));
			$('[data-toggle="popover"]').popover();
			renderPaginationMessage();
			if(typeof callback != "undefined"){
				callback();
			}
		}
		
		function renderPaginationMessage(){
			var startp = (ils.psize * ils.pctr) + 1;
			var endp = ils.psize * (ils.pctr + 1);
			var html = '<span class="badge badge-info" style="font-size:13px;">' + startp + ' - ' + endp + ' items / ' + ils.cnt + " items </span>";
			$('#pagination_msg').html(html);
		}

		function openCreate(){
			var url = '/view/inventory/service/_update.html';
			window.location = url;
		}
		
		function editServiceShortCode(id){
			var csc = $('#item_sc_' + id + ':visible').text();
			var html = '<input type="number" class="form-control form-control-sm" id="item_sce_'+ id 
				+'" onblur="return updateServiceShortCode('+ id +');" value="' + csc + '"/>';
			$('#item_sc_' + id + ':visible').html(html);
		}

		function updateServiceShortCode(id){
			var usc = $('#item_sce_' + id + ':visible').val();
			p = serviceIdMap[id];
			p.sc = usc;
			updateServiceShortcode(p, function(){
				console.log("Service short code remote update complete");
			});
			$('#item_sc_' + id + ':visible').html(usc);
		}

		function launchAppFunction(){
    		var url = "https://app.nuzuka.com/wp_launch.html?oc=" + oc + "&pc=" + pc + "&uck=" + uck + "&fn=Services";
    		window.open(url, "nuzuka_app");
    		return false;
		}
	
	</script>
	<style type="text/css">
		.rounded {
			border-radius: 30% !important;
			font-weight: bold;
		}
	</style>
</head>

<body style="overflow-x:hidden;">
	  <div class="row w-100 m-0 p-2 bg-light" style="margin-left: 0px;padding-right:2rem !important;display: none;"  id="actions_container">
	    <div class="col-2 d-flex" style="padding-left: 5px;padding-right: 5px;">
			<input type="text" class="form-control" id="one_search" placeholder="Search..." onfocus="this.select();">
			<button class="btn btn-sm btn-outline-primary" style="margin-left: 2px;"  onclick="return searchItems();">
				<img src="<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/search.png" 
					style="width:14px;height:14px;vertical-align:middle !important;"/>	
			</button>
		</div>
		<div class="col-9 d-flex">
			<div class="badge badge-info straight-badge my-auto" id="pagination_msg" style="font-size:14px;margin-right: 0.5rem;"></div>
			<div class="btn-group" role="group">
				<button class="btn btn-outline-secondary" onclick="return loadNextService();" ><b>&nbsp;&#8658;&nbsp;</b></button>
				<button class="btn btn-outline-secondary" onclick="return loadPreviousService();" ><b>&nbsp;&#8656;&nbsp;</b></button>
			</div>
		</div>
		<div class="col-1">
			<button class="btn btn-primary" onclick="return launchAppFunction();">Manage</button>
		</div>
	  </div>
	  <div class="row w-100 m-0" style="padding-left: 1rem;padding-right: 1rem;">
		  <div class="row w-100 scrollcontainer p-2 justify-content-center" id="items_container" style="display: none;"></div>
		  <div class="row w-100 justify-content-center mt-4" style="margin-left: 0px;display: none;" id="no_items_container">
		  	<div class="alert alert-xl alert-info w-75">No services found. Add a service <a href="#" onclick="launchAppFunction();" class="link-primary"><b>here</b></a></div>
		  </div>
	  </div>
</body>
</html>