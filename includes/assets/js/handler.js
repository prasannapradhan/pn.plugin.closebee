var site_name = "";
var site_url = "";
var cip = "";
var channel = {};
var cid = '';
var wres = {};
var maxz = 9999999999;
var ud = {'id' : -1};

jQuery(document).ready(function() {
	try{
		initializeWidget();
		loadUserDetails();
		console.log("Widget handler loaded");
		jQuery('#autofill_address').on("click", openUserAddressWidget);
	}catch(e){
	}
});

function initializeWidget(){
	try{
		site_name = window.location.hostname;
		site_url = window.location.href;
		console.log("Site name [" + site_name + "] and url [" + site_url + "]");
	}catch(e){
	}
	try{
		jQuery('div').each(function(){
			var zindex = jQuery(this).css('z-index');
			if(!isNaN(zindex)){
				if(zindex > maxz){
					maxz = zindex + 1;
				}
			}
		});
	}catch(e){
	}
}

function openUserAddressWidget() {
	try {
		var curl = "https://app.closebee.com/view/widget/_user.html";
		var fc = jQuery('#cb_user_address_frame');
		if(fc.length != 0){
			jQuery('#cb_user_address_frame').remove();
		}
		wres = getWindowResolution();
		var fw =  wres.width * 0.40;
		var fh =  wres.height * 0.70;
		var rw =  (wres.width - fw)	/ 2;
		var rh =  (wres.height - fh) / 2;		
		var fhtml = '<iframe class="cb_address_widget_frame" id="cb_user_address_frame" ' 
		+'allow="geolocation" src="https://app.closebee.com/view/widget/_user.html" style="border: 4px solid grey;border-radius:8px;"></iframe>';
		
		jQuery('body').first().append(fhtml);
		jQuery('#cb_user_address_frame').css('height', fh + 'px');
		jQuery('#cb_user_address_frame').css('z-index', maxz);
		jQuery('#cb_user_address_frame').css('width', fw + 'px');
		jQuery('#cb_user_address_frame').css('top', rh + 'px');
		jQuery('#cb_user_address_frame').css('left', rw + 'px');
		jQuery('#cb_user_address_frame').css('position','fixed');
		jQuery('#cb_user_address_frame').fadeIn(50);
	}catch(e){
		console.log("Error loading widget");
	}
	return false;
}

function getWindowResolution(){
	 var sResolution = {'width':'','height':''};
	 sResolution.width = screen.width;
	 sResolution.height= screen.height;
	 return sResolution;
}

var loadedFrames = false;
function getReqParam(name){
	 var name = (new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search);
	 if(name !== null){
		 return decodeURIComponent(name[1]);
	 }else {
		 return "";
	 }
}

function loadUserDetails(){
 	try {
 		var udjson = sessionStorage.getItem('cb.app.user.details');
		if ((typeof udjson !== 'undefined') && (udjson !== '') && (udjson !== null)) {
			var udetails = $.parseJSON(udjson);
			if((typeof udetails.login_id != "undefined") && (typeof udetails.id != "undefined")){
				ud = udetails;
			}else {
				ud = {'id' : -1};
			}
		}else {
			ud = {'id' : -1};
		}
	} catch (e) {
		console.error("Error in loading user details");
	}
}

function triggerInfoSubmit(uck, uadc){
	var url = new URL(site_url);
	if(typeof uck != "undefined"){
		if(url.searchParams.get('uck') === null){
			url.searchParams.append('uck', uck);
		}else {
			url.searchParams.set('uck', uck);
		}
		if(typeof uadc != "undefined"){
			if(url.searchParams.get('uadc') === null){
				url.searchParams.append('uadc', uadc);
			}else {
				url.searchParams.set('uadc', uadc);
			}
		}
	}
	window.location.href = url;
	jQuery('#cb_user_address_frame_container').fadeOut(500);
}

window.addEventListener('message', function(event) {
    if(event.data.evt_id === 'widget_loaded'){
    }else if(event.data.evt_id === 'redirect_sigin'){
    	 window.postMessage(event.data, "*");
    }else if(event.data.evt_id === 'close_widget'){
		if(typeof event.data != "undefined"){
	    	var mdata = event.data;		
			if(typeof mdata.data != "undefined"){
				var uck = mdata.data.uck;
				if(typeof mdata.data.uadc != "undefined"){
					var uadc = mdata.data.uadc;
	    			triggerInfoSubmit(uck, uadc);
				}else {
					triggerInfoSubmit(uck);
				}
			}
		}else {
			triggerInfoSubmit();
		}
    }
});