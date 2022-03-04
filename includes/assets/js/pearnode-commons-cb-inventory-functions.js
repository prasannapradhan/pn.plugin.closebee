var enurl = "https://enricher.pearnode.com:41981";
var iseq = 0;
var pctr = 0;
var cctr = 0;
var _it = "";
var _idt = "";
var _gt = "";

var prod_search_options = {
    getValue: "name", 
    list: { 
	    match: {enabled: true}, 
	    maxNumberOfElements: 10,
		onChooseEvent: function() {
			selectedProduct = $(".product_search").getSelectedItemData();
		},
		onKeyEnterEvent: function(){
			selectedProduct = $(".product_search").getSelectedItemData();
		}
	}, 
    placeholder: "Search..", 
    theme: "blue"
};

var stock_search_options = {
    getValue: "name", 
    list: { 
	    match: {enabled: true}, 
	    maxNumberOfElements: 10,
		onChooseEvent: function() {
			selectedStockItem = $(".stock_search").getSelectedItemData();
		},
		onKeyEnterEvent: function(){
			selectedStockItem = $(".stock_search").getSelectedItemData();
		}
	}, 
    placeholder: "Search..", 
    theme: "blue"
};

var service_search_options = {
    getValue: "name", 
    list: { 
	    match: {enabled: true}, 
	    maxNumberOfElements: 10,
		onChooseEvent: function() {
			selectedService = $(".service_search").getSelectedItemData();
		},
		onKeyEnterEvent: function(){
			selectedService = $(".service_search").getSelectedItemData();
		}
	}, 
    placeholder: "Search..", 
    theme: "blue"
};

var asset_search_options = {
    getValue: "name", 
    list: { 
	    match: {enabled: true}, 
	    maxNumberOfElements: 10,
		onChooseEvent: function() {
			selectedAsset = $(".asset_search").getSelectedItemData();
		},
		onKeyEnterEvent: function(){
			selectedAsset = $(".asset_search").getSelectedItemData();
		}
	}, 
    placeholder: "Search..", 
    theme: "blue"
};

function renderItemsDisplay(dispItems, tmpl, callback){
	if(dispItems.length == 0){
		if(typeof callback != "undefined"){
			callback();
		}
	}
	iseq = 0;
	$('#iitems_body').empty();
	var csize = 500;
	var delay = 300;
	var chunkedArr = chunkArray(dispItems, csize);
	$.each(chunkedArr, function(key, chunkedItems){
		setTimeout(function() {
			renderItemRecordSet(chunkedItems, tmpl, callback);
		}, cctr * delay);
		cctr++;
	});
}

function renderItemRecordSet(iitems, tmpl, callback){
	var buff = "";
	$.each(iitems, function(key, item){
		if(item.category == "uncategorized"){
			item.categorize_html = Mustache.render(icattmpl, item);
			delete item.categorized;
		}
		if(typeof ctmap != "undefined"){
			ctmap[item.category] = ctmap[item.category] + 1;
		}
		item.drate = formatter(item.rate);
		item.dsale_rate = formatter(item.sale_rate);
		item.dpurchase_rate = formatter(item.purchase_rate);
		item.dmrp = formatter(item.mrp);
		item.seq = ++iseq;
		buff += Mustache.render(tmpl, item);
		if(_gt != ""){
			buff += Mustache.render(_gt, item);
		}
		if(_idt != ""){
			buff += Mustache.render(_idt, item);
		}
	});
	$('#iitems_body').append(buff);
	$('[data-toggle="popover"]').popover();
	pctr++;
	if(pctr == cctr){
		if(typeof callback != "undefined"){
			callback();
		}
	}
}

function setGroupDefaultImage(gname, gcode, giurl, icode){
	$('#grp_msg_' + icode).text("Setting group picture..");
	$('.grp_img_' + gcode).attr("src", giurl);

	var group = inventoryGroupMap[gcode];
	var postData = {'oid' : oid, 'pid' : pid, 'uid': uid, 'mod': 'INVENTORY'};
	postData.code = gcode;
	postData.name = gname;
	postData.giurl = giurl;
	group.img_url = giurl;
	
	var url = 'https://api.pearnode.com/api/org/inventory/group/update_dimg.php'; 
	NProgress.start();
	$.post(url, JSON.stringify(postData), function(data) {
		$('#item_grp_row_' + icode).fadeOut(2000);
		NProgress.done();
		$('#grp_msg_' + icode).text("");
	});
}

function toggleGroupDetails(icode){
	if($('#item_grp_row_' + icode).is(':visible')){
		$('#item_grp_row_' + icode).fadeOut(400);
	}else {
		NProgress.start();
		$('#grp_sstr_' + icode).on("keyup", function(e) {
 	        var key = e.which;
 	        e.preventDefault();
 	        if (key == 13) {
 	        	renderGroupMedia(icode);
 	        }
 	     });
		renderGroupMedia(icode);
	}
}

function renderGroupMedia(icode){
	$('#item_grp_row_' + icode).show();
	var item = itemCodeMap[icode];
	var gss = $('#grp_sstr_' + icode).val().trim();
	var gn = item.parent_name;
	var gcode = item.parent_code;
	if(gss == ""){
		gss = gn.toLowerCase().replace(/[^a-zA-Z ]/g, "");
		var words = gss.split(" "); //Turns the string into an array of words
		var longWords = []; //Initialize array
		for(var i = 0; i<words.length; i++){
		    if(words[i].length > 2) {
		        longWords.push(words[i]);
		    }
		}
		gss = longWords.join(" "); 
	}
	var url = enurl + "/ig/ps?t=" + encodeURI(gss)  + "&ig=" + encodeURI(gn) + "&oid=" + oid + "&pid=" + pid + "&fs=true&clr=true";
	$.get(url, function(data, status){
		var medias = $.parseJSON(data);
		renderGroupImages(medias, icode, gn, gcode);
		$('#grp_sstr_' + icode).val(gss);
		NProgress.done();
	});
}

function renderGroupImages(medias, icode, gname, gcode){
	$('#grp_mc_'+ icode).hide();
	if(medias.length > 0 ){
    	for(var i = 0; i < medias.length; i++){
			var mediaObj = medias[i];
			mediaObj.name = '';
			mediaObj.description = '';
			mediaObj.icode = icode;
			mediaObj.lcode= sha1(mediaObj.itl);
			mediaObj.code = gcode;
			mediaObj.gname= gname;
			mediaObj.gcode = gcode;
		}
    	var pics_html = Mustache.render(gtmpl.innerHTML, {'records' : medias});
		$('#grp_mc_'+ icode).html(pics_html);
	}else {
		$("#grp_mc_" + icode).html("No Pictures available. You can always search one.")
	}
	$('#grp_mc_'+ icode).fadeIn(1000);
}

function toggleItemDetails(icode){
	if($('#item_details_row_' + icode).is(':visible')){
		$('#item_details_row_' + icode).fadeOut(400);
	}else {
		NProgress.start();
		$('#item_details_sstr_' + icode).on("keyup", function(e) {
 	        var key = e.which;
 	        e.preventDefault();
 	        if (key == 13) {
 	        	renderItemMedia(icode);
 	        }
 	     });
		renderItemMedia(icode);
	}
}

function renderItemMedia(icode){
	$('#item_details_row_' + icode).show();
	var iitem = itemCodeMap[icode];
	var iss = $('#item_details_sstr_' + icode).val().trim();
	if(iss == ""){
		iss = iitem.name.toLowerCase().replace(/[^a-zA-Z ]/g, "");
		var words = iss.split(" "); //Turns the string into an array of words
		var longWords = []; //Initialize array
		for(var i = 0; i<words.length; i++){
		    if(words[i].length > 2) {
		        longWords.push(words[i]);
		    }
		}
		iss = longWords.join(" "); 
	}
	var url = enurl + "/i/ps?t=" + encodeURI(iss)  + "&in=" + encodeURI(iitem.name) + "&icat=" + iitem.category +  "&oid=" + oid + "&pid=" + pid + "&fs=true&clr=true";
	$.get(url, function(data, status){
		var medias = $.parseJSON(data);
		renderItemImages(medias, icode);
		$('#item_details_sstr_' + icode).val(iss);
		NProgress.done();
	});
}

function renderItemImages(medias, icode){
	$('#item_details_mc_'+ icode).hide();
	if(medias.length > 0 ){
    	for(var i = 0; i < medias.length; i++){
			var mediaObj = medias[i];
			mediaObj.name = '';
			mediaObj.description = '';
			mediaObj.code = icode;
			mediaObj.lcode= sha1(mediaObj.itl);
		}
    	var pics_html = Mustache.render(imtmpl.innerHTML, {'records' : medias});
		$('#item_details_mc_'+ icode).html(pics_html);
	}else {
		$("#item_details_mc_" + icode).html("No Pictures available. You can always search one.")
	}
	$('#item_details_mc_'+ icode).fadeIn(1000);
}

function setItemDefaultImage(iurl, icode){
	$('#item_details_msg_' + icode).text("Setting item picture..");
	$('.inv_img_' + icode).attr("src", iurl);

	var iitem = itemCodeMap[icode];
	iitem.img_url = iurl;
	var postData = {'oid' : oid, 'pid' : pid, 'uid': uid, 'mod': 'INVENTORY'};
	postData.item = iitem;
	
	var url = 'https://api.pearnode.com/api/org/inventory/update_dimg.php'; 
	NProgress.start();
	$.post(url, JSON.stringify(postData), function(data) {
		$('#item_details_row_' + icode).fadeOut(2000);
		NProgress.done();
		$('#item_details_msg_' + icode).text("");
	});
}

function categorizeUncategorizedGroupItems(gcode){
	var cat = $('#grp_category_select_' + gcode).val();
	var postData = {'oid' : oid, 'pid' : pid, 'uid': uid, 'icat' : cat, 'gcode' : gcode};
	var url = 'https://api.pearnode.com/api/org/inventory/group/categorize_items.php'; 
	showProcessingLoader("Please wait..");
	$.post(url, JSON.stringify(postData), function(data) {
		hideProcessingLoader();
		parent.renderActiveView();
	});
}

function removeUncategorizedItem(iid, callback){
	NProgress.start();
	var postData = {'oid' : oid, 'pid' : pid, 'iid' : iid};
	$.post(UNCATEGORIZED_ITEM_REMOVE, JSON.stringify(postData), function(response){
		$('#item_row_' + iid).fadeOut(300);
		if(typeof callback != "undefined"){
			NProgress.done();
			callback();
		}
	});
}

function removeCategorizedItem(iid, iref, category, callback){
	NProgress.start();
	var postData = {'oid' : oid, 'pid' : pid, 'iid' : iid, 'iref' : iref, 'cat' : category};
	$.post(CATEGORIZED_ITEM_REMOVE, JSON.stringify(postData), function(response){
		$('#item_row_' + iid).fadeOut(300);
		if(typeof callback != "undefined"){
			NProgress.done();
			callback();
		}
	});
}
		
function openItemChangeView(code, category){
	var winprotocol = window.location.protocol;
	var winhost = window.location.hostname;
	var burl = winprotocol + "//"+ winhost + "/view/inventory/" + category + "/_update.html";
	var url = burl+'?oid=' + oid + '&pid=' + pid + '&uid=' + uid +'&c='+ code;
	window.location.href = url;
}