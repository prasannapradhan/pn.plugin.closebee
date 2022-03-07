var site_name = "";
var site_url = "";
var cip = "";
var channel = {};
var cid = '';
var wres = {};
var maxz = 9999999999;
var ud = {'id' : -1};

jQuery(document).ready(function() {
	initializeWidget();
	loadUserDetails();
	console.log("Widget handler loaded");
});

function initializeWidget(){
	try{
		site_name = window.location.hostname;
		site_url = window.location.href;
	}catch(e){
	}
	jQuery('div').each(function(){
		var zindex = jQuery(this).css('z-index');
		if(!isNaN(zindex)){
			if(zindex > maxz){
				maxz = zindex + 1;
			}
		}
	});
}

function openUserAddressWidget() {
	try {
		var curl = "https://app.closebee.com/view/widget/_user.html";
	    var iurl = 'https://app.closebee.com/process/action.php';
		jQuery.post(iurl, {}, function(resp) {
			var co = jQuery.parseJSON(resp);
			cip = co.cipd;
			var fc = jQuery('#cb_user_address_frame_container');
			if(fc.length == 0){
				var fw = 0;
				var fh = 0;
				if(typeof wtype == "undefined"){
					wtype = "service";
				}
				wres = getWindowResolution();
				fw =  wres.width * 0.40;
				fh =  wres.height * 0.80;
				var fhtml = '<div class="cb_frame_container" id="cb_user_address_frame_container" style="z-index:'+ maxz +';">';
				fhtml += '<iframe class="cb_address_widget_frame" id="cb_user_address_frame" allow="geolocation" src="'+ curl 
					+'" style="border: none;height:'+ fh +'px;" width="100%"></iframe>';
				fhtml += '</div>'
				
				jQuery('body').append(fhtml);
				jQuery('#cb_user_address_frame_container').css('height', fh + 'px');
				jQuery('#cb_user_address_frame_container').css('z-index:', maxz);
				jQuery('#cb_user_address_frame_container').css('width', fw + 'px');
				jQuery('#cb_user_address_frame_container').css('border-radius', '8px 8px 8px 8px');
				jQuery('#cb_user_address_frame_container').css('background','transparent');
				jQuery('#cb_user_address_frame_container').css('top','50px');
				jQuery('#cb_user_address_frame_container').css('position','fixed');
				jQuery('#cb_user_address_frame_container').css('background', 'rgb(0, 0, 0)');
				jQuery('#cb_user_address_frame_container').css('opacity', '0.5');
				jQuery('#cb_user_address_frame_container').css('filter', 'Alpha(Opacity=50)');
			}
			jQuery('#cb_user_address_frame_container').show();
		});
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