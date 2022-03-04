<!doctype html>
<html lang="en">

<head>
	<?php wp_head(); ?>
	
	<style type="text/css">
		 .scrollcontainer {
		    overflow-y: auto;
		    min-height: 10vh;
		    max-height: 86vh;
		}
		.scrollcontainer thead th {
		    position: sticky;
		    top: 0;
		    background-color: #fff;
		    background: #fff;
		    height: 3vh;
		}
	</style>	
	
	<script id="path_sel_tmpl" type="text/html">
		<div class="card-deck justify-content-center">
		{{#records}}
			<div class="card mb-1 item_row" style="min-width: 18rem; max-width: 18rem;margin-top:0px !important;padding:0.25rem !important;">
				<div class="card-header d-flex justify-content-center">
					<div class="col-auto"><span class="badge badge-primary" id="wtext_{{id}}">{{widgets}}</span></div>
				</div>
				<div class="card-header d-flex justify-content-center">
					<div class="col-auto"><span class="badge badge-secondary mt-1">{{seq}}</span></div>
					<a href="{{site_url}}{{page_url}}" target="_blank"
						data-toggle="popover" data-trigger="hover" data-placement="top" data-content="{{page_url}}">
						<span style="font-size:13px; font-weight:bold;">{{display_id}}</span>
					</a>
				</div>
				<div class="card-header d-flex justify-content-center" id="ph_growth_{{id}}">
					<div class="row w-100 m-0" style="height: 10vh !important;">
						<canvas id="page_growth_{{id}}_chart" style="width:100%;height:100%;"></canvas>
					</div>
				</div>
				<div class="card-body" >
					<div class="row w-100 d-flex justify-content-center m-0">
						<img class="card-img-top rounded-lg shadow border-2" src="{{img_url}}" style="height:10rem; width:10rem;">
					</div>
				</div>
				<div class="card-footer">
					<div class="row d-flex justify-content-center">
						<button class="btn btn-sm btn-light" onclick="return enableWidget({{site_ref}}, {{id}});"
							data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Enable / Disable Closebee widget">
							<img src="<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/z.png" style="max-width:1.4vw;max-height:2.0vh;"/>
						</button>
						<button class="btn btn-sm btn-light" onclick="return inactivatePage({{id}})"
							data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Stop Monitoring Page">
							<img src="<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/bin.png" style="max-width:1.4vw"/>
						</button>
					</div>
				</div>
			</div>
		{{/records}} 
		</div>
	</script>
		
	<script type="text/javascript">
		var oc = '<?php echo esc_attr($org->code); ?>';
		var pc = '<?php echo esc_attr($profile->code); ?>';
		var sid = '<?php echo esc_attr($site->id); ?>';
		var uck = '<?php echo esc_attr($user->ck); ?>';
		var processing_img = '<?php echo esc_attr(plugins_url()."/".$plugin_dir_name."/includes/assets/"); ?>images/ajax-loader.gif';
		
	    var site = {};
	    var paths = [];
	    var tothits = 0;
	    var selectedPage = {};
	    var widgetMap = {};
	    var pageMap = {};
		var psum = {'direct' : 0, 'google' : 0, 'facebook' : 0, 'total' : 0};
		var ssum = {};
		var schartStore = {};
		
	    $(document).ready(function() {
	    	displayPageListing();
		});

		function displayPageListing() {
			NProgress.start();
			$('#page_listing_container').show();
			$('#page_records_container').hide();
			$('#page_dashboard_container').hide();
			
			var purl = 'https://api.pearnode.com/closebee/site/page/allpages_site.php'; 
			var pdata = {'oc' : oc, 'pc' : pc, 'sid': sid};
			$.post(purl, JSON.stringify(pdata), function(data) {
				site = $.parseJSON(data);
				$('#site_name').text(site.site_name);
				paths = site.paths;
				$('#pages_text').text(paths.length + " pages");
				$.each(paths, function(idx, path){
					path.site_url = site.site_url;
					path.site_ref = sid;
					path.seq = idx + 1;
					if(path.display_id == ""){
						path.display_id = "open";
					}
					var wstr = path.widgets.trim();
					if(wstr == "" || wstr == "none"){
						path.widgets = "No Widget";
					}
					path.page_url = path.page_url.replaceAll('//', '/');					
					pageMap[path.id] = path;
				});
				$('#total_qty').text(paths.length + " paths");
				$('#total_value').text(tothits + " hits");
				if(paths.length > 0){
					$('#no_pages_container').hide();
					paths[0].selected = true;
					var tmpl = document.getElementById('path_sel_tmpl').innerHTML;
					var html = Mustache.render(tmpl, {'records' : paths});
					$('#page_records_container').html(html);
					$('#page_records_container').fadeIn(500);
					$('[data-toggle="popover"]').popover();
				}else {
					$('#page_listing_container').hide();
					$('#page_records_container').hide();
					$('#no_pages_container').fadeIn(200);
				}
				var postUrl = "https://api.pearnode.com/closebee/site/page/stats/week.php"; 
			    $.post(postUrl, JSON.stringify(pdata), function(data) {
			    	var stats = $.parseJSON(data);
			    	renderGrowthChart($.parseJSON(data));
				});
				NProgress.done()
			});
		}
		
		function renderGrowthChart(gmap){
    		var pagedata = gmap.week.page.data;
    		var mkeys = Object.keys(pageMap);
    		$.each(mkeys, function(idx, mkey){
    			var pg = pageMap[mkey];
    			var daysobj = pagedata[pg.page_url];
    			var lineds = {'data' : [], 'type' : 'line', 'backgroundColor' : '#3E45F4', 'order' : 0, 'borderColor': '#070A52', 'lineTension': 0.7, 
						fill: {target: 'origin', above: '#9DA2F8'}};
    			var daykeys = Object.keys(daysobj);
    			$.each(daykeys, function(idx, daykey){
					lineds.data.push(parseInt(daysobj[daykey]))
	    		});
    			var sname = "page_growth_" + pg.id;
				var chart = schartStore[sname];
				if(typeof chart != "undefined"){
					chart.destroy()
				}
				var data = {'labels': daykeys};
				data.datasets = [lineds];
				var chartData = {
					type: 'line',
					data: data,
					options: {
						maintainAspectRatio : false,
						responsive: true,
						responsiveAnimationDuration: 1000,
						plugins:{legend: {display: false}},
						animation: {animateScale: true, animateRotate: true},
						scales: {
							 x: {display: false}
				        }
					}
		    	};
	    		var elem = document.getElementById(sname + '_chart');
	    		if(typeof elem != "undefined" && elem !== null){
		    		chart = new Chart(elem, chartData);
		    		schartStore[sname] = chart;
	    		}else {
	    			console.log("Element ["+ sname + '_chart' + "] not found");
	    		}
    		});
    	}
		
		function enableWidget(site_ref, page_ref){
			NProgress.start();
			selectedPage = pageMap[page_ref];
			loadWidgets(function(){
				$('#widget_apply_modal').modal('show');
			    NProgress.done();
			});
			return false;
		}
		
		function loadWidgets(callback) {
		   var purl = "https://api.pearnode.com/closebee/widget/list.php";
		   var pdata = {'oc': oc,'pc': pc};
		   var html = "<option value='none_" + selectedPage.id + "'>No widget</option>";
		   $.post(purl, JSON.stringify(pdata), function(resp) {
			    widgets = $.parseJSON(resp);
			    if(widgets.length > 0){
			    	$.each(widgets, function(idx, widget) {
			    		widget.created = moment(widget.created_at).fromNow();
			    		widget.oc = oc;
			    		widget.pc = pc;
			    		widgetMap[widget.id] = widget;
			    		if(widget.item_name == ""){
			    			widget.wcat = "Enquiry Widget";
			    		}else {
			    			widget.wcat = "Lead Widget";
			    		}
			    		html += "<option value='" + widget.id +"_" + selectedPage.id+ "'>" 
			    			+ widget.id + ", " + widget.item_name + ", " + widget.wcat + "</option>";
					})
			    }
			    $('#widget_select').html(html);
			    if(typeof callback != "undefined"){
			    	callback();
			    }
		   })
		}
		
		function updatePage(sid, pgid, ptype) {
		   if(typeof ptype == "undefined"){
			   ptype = "page";
		   }
		   var purl = "https://api.pearnode.com/closebee/site/integ/wordpress/page/fetch.php";
		   if(ptype == "post"){
			   purl = "https://api.pearnode.com/closebee/site/integ/wordpress/post/fetch.php";
		   }
		   NProgress.start();
		   var pdata = {'oc': oc,'pc': pc, 'sid' : sid, 'pgid' : pgid};
		   $.post(purl, JSON.stringify(pdata), function(resp) {
			   // TODO add a toast to display the message.
			   NProgress.done();
		   })
		}
		
		function applyWidgetToPage(){
			showProcessingLoader("Updating page.. Please wait.");
			var sval = $('#widget_select').val();
			var sarr = sval.split('_');
			var wid = sarr[0];
			var pgid = sarr[1];
			var purl = "https://api.pearnode.com/closebee/site/page/apply_widget.php";
			var pdata = {'oc': oc,'pc': pc, 'sid' : sid, 'pgid' : pgid, 'wid' : wid};
			$.post(purl, JSON.stringify(pdata), function(data){
				if(wid == "none"){
					$('#wtext_' + pgid).text('No Widget');
				}else {
					$('#wtext_' + pgid).text(wid);	
				}
				$('#widget_apply_modal').modal('hide');
				hideProcessingLoader();
			})
		}
		
		function scanSite(callback){
			$('#modal_site_name').text(site.site_name);
			$('#scan_modal').modal('show');
	    	var pdata = {'oc': oc,'pc': pc, 'sid' : sid};
			var postUrl = "https://api.pearnode.com/closebee/site/scan/page_open.php"; 
			$('#page_scan_result').html('<img src="<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/loader-snake-blue.gif" style="width: 1.5vw;"/>');
		    $.post(postUrl, JSON.stringify(pdata), function(data) {
		    	var robj = $.parseJSON(data);
		    	var pgstatus = robj.status;
		    	if(pgstatus.status == "success"){
			    	$('#page_scan_result').html(robj.fetch_ctr + " found, " + robj.add_ctr + " added, " + robj.update_ctr + " updated");
			    	postUrl = "https://api.pearnode.com/closebee/site/scan/post_open.php"; 
					$('#post_scan_result').html('<img src="<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/loader-snake-blue.gif" style="width: 1.5vw;"/>');
				    $.post(postUrl, JSON.stringify(pdata), function(data) {
				    	var robj = $.parseJSON(data);
				    	var psstatus = robj.status;
				    	if(psstatus.status == "success"){
					    	$('#post_scan_result').html(robj.fetch_ctr + " found, " + robj.add_ctr + " added, " + robj.update_ctr + " updated");
				    	}else {
				    		$('#post_scan_result').html("Error in scanning : <b style='color:red;'>" + pgstatus.code + "</b>");
				    	}
				    	if(typeof callback != "undefined"){
				    		callback();
				    	}
				    });
		    	}else {
		    		$('#page_scan_result').html("Error in scanning : <b style='color:red;'>" + pgstatus.code + "</b>");
		    		if(typeof callback != "undefined"){
			    		callback();
			    	}
		    	}
		    });
		    return false;
		}
		
		function showDashboard(){
    		$('#page_listing_container').hide();
			$('#page_dashboard_container').fadeIn(1000);
			var pages = Object.values(pageMap);
			psum = {'direct' : 0, 'google' : 0, 'facebook' : 0, 'total' : 0};
    		$.each(pages, function(idx, p){
    			var stats = p.stats;
    			psum.direct += stats.direct;
    			psum.google += stats.google;
    			psum.facebook += stats.facebook;
    			ssum[p.page_url] = p.hitctr;
    		});
    		psum.total = psum.direct + psum.google + psum.facebook;
    		displayHitDistribution();
    		displayProviderDistribution();
    	}
		
		function displayHitDistribution(){
			var sname = "hits";
			var chart = schartStore[sname];
			if(typeof chart != "undefined"){
				chart.destroy()
			}
    		var skeys = Object.keys(ssum);
    		var svals = [];
    		var COLORS = [];
    		$.each(skeys, function(idx, key){
    			COLORS.push(textToColor(key));
    			svals.push(ssum[key]);
    		})
    		var chartData = {
				type: 'doughnut',
				data: {
					datasets: [{data: svals, backgroundColor: COLORS}],
					labels: skeys
				},
				options: {
					maintainAspectRatio : false,
					responsive: true,
					responsiveAnimationDuration: 1000,
					legend: {position: 'top'},
					title: {display: true, text: "Hits by page"},
					animation: {animateScale: true, animateRotate: true}
				}
    		};
    		var elem = document.getElementById(sname + '_chart');
    		chart = new Chart(elem, chartData);
    		schartStore[sname] = chart;
    	}
		
		function displayProviderDistribution(){
			var sname = "provider";
			var chart = schartStore[sname];
			if(typeof chart != "undefined"){
				chart.destroy()
			}
    		var skeys = Object.keys(psum);
    		var svals = [];
    		var COLORS = [];
    		$.each(skeys, function(idx, key){
    			COLORS.push(textToColor(key));
    			svals.push(psum[key]);
    		})
    		var chartData = {
				type: 'bar',
				data: {
					datasets: [{'label' : 'Provider', data: svals, backgroundColor: COLORS}],
					labels: skeys
				},
				options: {
					indexAxis: 'y',
					maintainAspectRatio : false,
					responsive: true,
					responsiveAnimationDuration: 1000,
					legend: {position: 'top'},
					title: {display: true, text: "Hits by provider"},
					animation: {animateScale: true, animateRotate: true}
				}
    		};
    		var elem = document.getElementById(sname + '_chart');
    		chart = new Chart(elem, chartData);
    		schartStore[sname] = chart;
    	}
		
		function textToColor(s) {
			 var hc = Math.round(Math.abs(hashCode(s)));
		  	 var hue = Math.floor(hc * 25) * 10;
		  	 var hex = $.Color({ hue: hue, saturation: 1.2, lightness: 0.3, alpha: 0.5 }).toHexString();
		  	 hex = "#" + hex.replace(/[^a-zA-Z0-9]/g, '');
			 //console.log(hex);
		  	 return hex;
		};
		
		function hashCode(str) {
		  	return str.split('').reduce((prevHash, currVal) => (((prevHash << 8) - prevHash) + currVal.charCodeAt(0))|0, 0);
		}
		
		function inactivatePage(id) {
			var pg = pageMap[id];
			Swal.fire({
				  title: 'Do you really want to inactivate the page ' + pg.page_url + ' ?',
				  icon: 'question',
				  showCancelButton: true,
				  confirmButtonText: 'Yes',
		   }).then((result) => {
			  if (typeof result.dismiss == "undefined") {
				  NProgress.start();
				  var pdata = {'oc': oc,'pc': pc, 'sid': sid, 'id' : id};
				  var postUrl = "https://api.pearnode.com/closebee/site/page/remove.php"; 
			      $.post(postUrl, JSON.stringify(pdata), function(data) {
			    	NProgress.done();
			    	displayPageListing();
				 });
			  } 
		    })
		}

		function launchAppFunction(){
    		var url = "https://app.closebee.com/wp_launch.html?oc=" + oc + "&pc=" + pc + "&uck=" + uck + "&fn=Sites";
    		window.open(url, "closebee_app");
    		return false;
		}
	</script>
</head>

<body>
	<div class="container-fluid" style="overflow-y:hidden;">
		<div class="row w-100 rounded" style="margin-left: 0px;">
			<div class="row w-100 mb-1 rounded bg-light p-2 border-2" style="margin-left: 0px;">
				<div class="col-9 p-1" style="font-size: 13px;">
					<span class="badge badge-info my-auto" id="site_name" style="font-size:15px;margin-left: 5px;"></span>
					<span class="badge badge-secondary my-auto" id="pages_text" style="font-size:14px;margin-left: 5px;"></span>
				</div>
				<div class="col-3 p-1 d-flex justify-content-end" style="font-size: 13px;">
					<div class="btn-group" role="group">
						<button class="btn btn-sm btn-outline-secondary" onclick="return scanSite();" id="scan_site_btn">
							<b>Scan</b>
						</button>
						<button class="btn btn-sm btn-outline-secondary" onclick="return displayPageListing();" id="refresh_site_btn"
							data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Refresh">
							<img src="<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/refresh.png" 
								style="max-width:1.4vw;vertical-align:middle !important;"/>
						</button>
						<button class="btn btn-sm btn-outline-secondary" onclick="return displayPageListing();"
							data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Show Page Listing">
							<img src="<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/list.png" 
								style="max-width:1.4vw;vertical-align:middle !important;"/>
						</button>
						<button class="btn btn-sm btn-outline-secondary" onclick="return showDashboard();"
							data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Show Path Dashboard">
							<img src="<?php echo plugins_url()."/".$plugin_dir_name."/includes/assets/"; ?>images/dashboard.png" 
								style="max-width:1.4vw;vertical-align:middle !important;"/>
						</button>
						<button class="btn btn-sm btn-primary" data-toggle="popover" data-trigger="hover" data-placement="top" 
							data-content="Manage in App" onclick="return launchAppFunction();">
							Manage
						</button>
					</div>
				</div>
			</div>
			
			<div id="page_listing_container" style="min-height: 62vh;" class="w-100 p-1 m-0">
				<div id="page_records_container" class="row w-100 scrollcontainer m-0 p-1 justify-content-center" style="overflow-x:hidden;display: none;"></div>
			</div>
			
			<div class="row w-100 m-0 p-1" id="page_dashboard_container" style="overflow-x:hidden;display: none;">
				<div class="row w-100 m-0">
    		   		<div class="container-fluid">
    		   			<div class="card-header">
    						<b>Distribution by <span class="badge badge-secondary" style="font-size: 14px;">Traffic Provider</span></b>
    					</div>
    			   		<div class="card-body" style="height: 30vh !important;">
    						<canvas id="provider_chart" style="width:100%;height:100%;"></canvas>
    					</div>		
    		   		</div>
				</div>
				<div class="row w-100 m-0">
    		   		<div class="container-fluid">
    		   			<div class="card-header">
    		   				<b>Distribution by <span class="badge badge-secondary" style="font-size: 14px;">Page hits</span></b>
    		   			</div>
    			   		<div class="card-body" style="height: 80vh !important;">
    						<canvas id="hits_chart" style="width:100%;height:100%;"></canvas>
    					</div>		
    				</div>
				</div>
		    </div>
			
			<div id="no_pages_container" class="row w-100" style="overflow-x:hidden;display: none;min-height: 62vh;">
				<div class="alert alert-light w-100 my-auto text-center">No pages found</div>
			</div>
		</div>
		
		<div class="modal fade" id="widget_apply_modal" tabindex="-1" role="dialog" aria-hidden="true">
		  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		    <div class="modal-content">
		      <div class="modal-header bg-light">
		        <h5 class="modal-title">Closebee update <span id="modal_page_url"></span></h5>
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span>
		        </button>
		      </div>
		      <div class="modal-body">
		      		<div class="row w-100 p-2 mb-2 mt-2 border-1 shadow-sm" id="widget_container">
		      			<select id="widget_select" class="form-control form-select"></select>
		      		</div>
		      		<p class="w-100 mt-2 p-3" style="color:gray;">
		      			Default positioned widgets need placeholders. Cannot be applied automatically and not shown here.
		      		</p>
		      </div>
		      <div class="modal-footer">
		        <div class="row w-100 bg-light p-2 m-0">
		        	<div class="col-8">
		        		<button type="button" class="btn btn-primary w-100" onclick="applyWidgetToPage();">Apply</button>	
		        	</div>
		        	<div class="col-4">
		        		<button type="button" class="btn btn-secondary w-100" data-dismiss="modal">Close</button>	
		        	</div>
		        </div>
		      </div>
		    </div>
		  </div>
		</div>
		
		<div class="modal fade" id="scan_modal" tabindex="-1" role="dialog" aria-hidden="true">
		  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <h5 class="modal-title">Scanning <span id="modal_site_name" class="badge badge-info" style="font-size: 14px;"></span></h5>
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span>
		        </button>
		      </div>
		      <div class="modal-body">
		      		<div class="row w-100 p-2 mb-2 mt-2 border-1 shadow-sm">
		      			<div class="col-6">
		      				<span>Scanning <b>Pages</b></span>
		      			</div>
		      			<div class="col-6 d-flex justify-content-center">
		      				<div id="page_scan_result"><b>Waiting..</b></div>
		      			</div>
		      		</div>
		      		<div class="row w-100 p-2 mb-2 mt-2 border-1 shadow-sm">
		      			<div class="col-6">
		      				<span>Scanning <b>Posts</b></span>
		      			</div>
		      			<div class="col-6 d-flex justify-content-center">
		      				<div id="post_scan_result"><b>Waiting..</b></div>
		      			</div>
		      		</div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		      </div>
		    </div>
		  </div>
		</div>
	</div>
</body>
</html>