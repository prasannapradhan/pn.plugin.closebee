var loadedItemGroups = new Array();
var itemGroupMap = {};
var itemCodeMap = {};
var itemNameMap = {};
var ils = {'psize' : 250, pctr : 0};
var iskey = "";

var loadedInventory = new Array();
var inventoryIdMap = {};
var loadedInventoryGroups = new Array();
var inventoryGroupMap = {};

var loadedProducts = new Array();
var productIdMap = {};
var loadedProductGroups = new Array();
var productGroupMap = {};

var loadedStockItems = new Array();
var stockIdMap = {};
var loadedStockGroups = new Array();
var stockGroupMap = {};

var loadedAssets = new Array();
var assetIdMap = {};
var loadedAssetGroups = new Array();
var assetGroupMap = {};

var loadedServices = new Array();
var serviceIdMap = {};
var loadedServiceGroups = new Array();
var serviceGroupMap = {};

var selectedProduct = {};
var selectedStockItem = {};
var selectedAsset = {};
var selectedService = {};
var selectedGroup = {};
var selectedGid = "all";

function createDefaultGroup(){
	var g = {};
	g.name = 'Primary';
	g.code = sha1.hex(g.name);
	g.img_url = '';
	inventoryGroupMap[g.code] = g;
	return g;	
}

function loadInventoryGroupsFromApi(callback){
	$.get('https://api.pearnode.com/api/org/inventory/group/list.php', 
			{'oid' : oid, 'pid' : pid, 'mod': 'INVENTORY'}, function(data) {
		loadedItemGroups = $.parseJSON(data);
		$.each(loadedItemGroups, function(gkey, group){
			if(group.name.trim() != ""){
				itemGroupMap[group.code] = group; 
			}
		});
		if(callback){
			callback(loadedInventoryGroups);
		}
	});
}

function loadInventoryFromApi(callback){
	var iurl = 'https://api.pearnode.com/api/org/inventory/listxy.php';
	var reqData = {'oid' : oid, 'pid' : pid, 'pctr' : ils.pctr, 'skey' : iskey};
	if(selectedGid != "all"){
		reqData.gcode = selectedGid;
	}
	$.post(iurl, JSON.stringify(reqData), function(resp) {
		var parsedResp = $.parseJSON(resp);
		ils = parsedResp.summary;
		var records = parsedResp.data;
		loadedInventory = [];
		$.each(records, function(idx, item){
			var iitem = new Item(item);
			delete iitem.attrs;
			var pcode = iitem.parent_code;
			var group = itemGroupMap[pcode];
			if(typeof group == "undefined"){
				group = createDefaultGroup();
			}
			inventoryGroupMap[group.code] = group; 				
			if((typeof group.img_url != "undefined") && (group.img_url !== null)){
				iitem.g_img_url = group.img_url;
			}
			inventoryIdMap[iitem.id] = iitem;
			itemCodeMap[iitem.code] = iitem;
			itemNameMap[iitem.name] = iitem;
			loadedInventory.push(iitem);
		});
		if(typeof callback !== "undefined"){
			callback(loadedInventory);
		}
	});
}

function loadNextInventory(){
	ils.pctr++;
	if(ils.cnt > (ils.pctr * ils.psize)){
		showProcessingLoader("Loading items...");
		loadInventoryFromApi(function(){
			renderItems(loadedInventory, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr--;
	}
	return false;
}

function loadPreviousInventory(){
	ils.pctr--;
	if(ils.pctr >= 0){
		showProcessingLoader("Loading items...");
		loadInventoryFromApi(function(){
			renderItems(loadedInventory, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr++;
	}
	return false;
}

function loadProductsFromApi(callback){
	var reqUrl = 'https://api.pearnode.com/api/org/inventory/product/listxy.php';
	var reqData = {'oid' : oid, 'pid' : pid, 'pctr' : ils.pctr, 'skey' : iskey};
	if(selectedGid != "all"){
		reqData.gcode = selectedGid;
	}
	$.post(reqUrl, JSON.stringify(reqData), function(resp) {
		var parsedResp = $.parseJSON(resp);
		ils = parsedResp.summary;
		var records = parsedResp.data;
		loadedProducts = [];
		$.each(records, function(idx, item){
			var iitem = new Item(item);
			delete iitem.attrs;
			iitem.category = "product";
			var pcode = iitem.parent_code;
			var group = itemGroupMap[pcode];
			if(typeof group == "undefined"){
				group = createDefaultGroup();				
				group.name = iitem.parent_name;
				group.code = iitem.parent_code;
			}
			productGroupMap[group.code] = group;
			if((typeof group.img_url != "undefined") && (group.img_url !== null)){
				iitem.g_img_url = group.img_url;
			}
			productIdMap[iitem.id] = iitem;
			itemCodeMap[iitem.code] = iitem;
			itemNameMap[iitem.name] = iitem;
			loadedProducts.push(iitem);
		});
		if(typeof callback !== "undefined"){
			callback(loadedProducts);
		}
	});
}

function loadNextProduct(){
	ils.pctr++;
	if(ils.cnt > (ils.pctr * ils.psize)){
		showProcessingLoader("Loading items...");
		loadProductsFromApi(function(){
			renderItems(loadedProducts, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr--;
	}
	return false;
}

function loadPreviousProduct(){
	ils.pctr--;
	if(ils.pctr >= 0){
		showProcessingLoader("Loading items...");
		loadProductsFromApi(function(){
			renderItems(loadedProducts, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr++;
	}
	return false;
}

function loadStockItemsFromApi(callback){
	var reqUrl = 'https://api.pearnode.com/api/org/inventory/stock/listxy.php';
	var reqData = {'oid' : oid, 'pid' : pid, 'pctr' : ils.pctr, 'skey' : iskey};
	if(selectedGid != "all"){
		reqData.gcode = selectedGid;
	}
	$.post(reqUrl, JSON.stringify(reqData), function(resp) {
		var parsedResp = $.parseJSON(resp);
		ils = parsedResp.summary;
		var records = parsedResp.data;
		loadedStockItems = [];
		$.each(records, function(idx, item){
			var iitem = new Item(item);
			delete iitem.attrs;
			iitem.category = "stock";
			var pcode = iitem.parent_code;
			var group = itemGroupMap[pcode];
			if(typeof group == "undefined"){
				group = createDefaultGroup();				
				group.name = iitem.parent_name;
				group.code = iitem.parent_code;
			}
			stockGroupMap[group.code] = group;
			if((typeof group.img_url != "undefined") && (group.img_url !== null)){
				iitem.g_img_url = group.img_url;
			}
			stockIdMap[iitem.id] = iitem;
			itemCodeMap[iitem.code] = iitem;
			itemNameMap[iitem.name] = iitem;
			loadedStockItems.push(iitem);
		});
		if(typeof callback !== "undefined"){
			callback(loadedStockItems);
		}
	});
}

function loadNextStock(){
	ils.pctr++;
	if(ils.cnt > (ils.pctr * ils.psize)){
		showProcessingLoader("Loading items...");
		loadStockItemsFromApi(function(){
			renderItems(loadedStockItems, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr--;
	}
	return false;
}

function loadPreviousStock(){
	ils.pctr--;
	if(ils.pctr >= 0){
		showProcessingLoader("Loading items...");
		loadStockItemsFromApi(function(){
			renderItems(loadedStockItems, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr++;
	}
	return false;
}

function loadAssetsFromApi(callback){
	var reqUrl = 'https://api.pearnode.com/api/org/inventory/asset/listx.php';
	var reqData = {'oid' : oid, 'pid' : pid, 'pctr' : ils.pctr, 'skey' : iskey};
	if(selectedGid != "all"){
		reqData.gcode = selectedGid;
	}
	$.post(reqUrl, JSON.stringify(reqData), function(resp) {
		var parsedResp = $.parseJSON(resp);
		ils = parsedResp.summary;
		var records = parsedResp.data;
		loadedAssets = [];
		$.each(records, function(idx, item){
			var iitem = new Item(item);
			delete iitem.attrs;
			iitem.category = "asset";
			var pcode = iitem.parent_code;
			var group = inventoryGroupMap[pcode];
			if(typeof group == "undefined"){
				group = createDefaultGroup();				
				group.name = iitem.parent_name;
				group.code = iitem.parent_code;
			}
			assetGroupMap[group.code] = group;
			if((typeof group.img_url != "undefined") && (group.img_url !== null)){
				iitem.g_img_url = group.img_url;
			}
			assetIdMap[iitem.id] = iitem;
			itemCodeMap[iitem.code] = iitem;
			itemNameMap[iitem.name] = iitem;
			loadedAssets.push(iitem);
		});
		if(typeof callback !== "undefined"){
			callback(loadedAssets);
		}
	});
}

function loadNextAssets(){
	ils.pctr++;
	if(ils.cnt > (ils.pctr * ils.psize)){
		showProcessingLoader("Loading items...");
		loadAssetsFromApi(function(){
			renderItems(loadedAssets, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr--;
	}
	return false;
}

function loadPreviousAssets(){
	ils.pctr--;
	if(ils.pctr >= 0){
		showProcessingLoader("Loading items...");
		loadAssetsFromApi(function(){
			renderItems(loadedAssets, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr++;
	}
	return false;
}

function loadServicesFromApi(callback){
	var reqUrl = 'https://api.pearnode.com/api/org/inventory/service/listxy.php';
	var reqData = {'oid' : oid, 'pid' : pid, 'pctr' : ils.pctr, 'skey' : iskey};
	if(selectedGid != "all"){
		reqData.gcode = selectedGid;
	}
	$.post(reqUrl, JSON.stringify(reqData), function(resp) {
		var parsedResp = $.parseJSON(resp);
		ils = parsedResp.summary;
		var records = parsedResp.data;
		loadedServices = [];
		$.each(records, function(idx, item){
			var iitem = new Item(item);
			delete iitem.attrs;
			iitem.category = "service";
			var pcode = iitem.parent_code;
			var group = inventoryGroupMap[pcode];
			if(typeof group == "undefined"){
				group = createDefaultGroup();				
				group.name = iitem.parent_name;
				group.code = iitem.parent_code;
			}
			serviceGroupMap[group.code] = group;
			if((typeof group.img_url != "undefined") && (group.img_url !== null)){
				iitem.g_img_url = group.img_url;
			}
			serviceIdMap[iitem.id] = iitem;
			itemCodeMap[iitem.code] = iitem;
			itemNameMap[iitem.name] = iitem;
			loadedServices.push(iitem);
		});
		if(typeof callback !== "undefined"){
			callback(loadedServices);
		}
	});
}


function loadNextService(){
	ils.pctr++;
	if(ils.cnt > (ils.pctr * ils.psize)){
		showProcessingLoader("Loading items...");
		loadServicesFromApi(function(){
			renderItems(loadedServices, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr--;
	}
	return false;
}

function loadPreviousService(){
	ils.pctr--;
	if(ils.pctr >= 0){
		showProcessingLoader("Loading items...");
		loadServicesFromApi(function(){
			renderItems(loadedServices, function(){
				hideProcessingLoader();
			});
		});	
	}else {
		ils.pctr++;
	}
	return false;
}

function loadProductVariantFromApi(pid, callback) {
	var reqUrl = 'https://api.pearnode.com/api/org/inventory/product/variant_list_lite.php';
	var reqData = {'oid' : oid, 'pid' : pid, 'pref':pid, 'mod': 'INVENTORY'};
	$.get(reqUrl, reqData, function(data) {
		var parsedData = $.parseJSON(data);
		var variants = new Array();
		$.each(parsedData,function(idx, variant){
			variant.dname = toTitleCase(variant.product_name) + ' - ' + toTitleCase(variant.name) ;
			variant.dsku = toTitleCase(variant.sku);
			variant.dsr = formatter(variant.sale_rate);
			variant.dmr = formatter(variant.mrp);
			variant.dpr = formatter(variant.purchase_rate);
			variants.push(variant);
		});
		loadedVariants = variants;
		if(typeof callback !== "undefined"){
			callback(loadedVariants);
		}
	});
}

function categorizeInventory(id, cat, callback){
	NProgress.start();
	var postData = {'oid' : oid, 'pid' : pid, 'uid': uid, 'inv': {'id' : id, 'category' : cat}};
	var postUrl = "https://api.pearnode.com/api/org/inventory/categorize.php";
	$.post(postUrl, JSON.stringify(postData), function(response){
		NProgress.done();
		if(typeof callback != "undefined"){
			callback(id, cat);
		}
	});
}

function uncategorizeInventory(id, cat, callback){
	NProgress.start();
	var postData = {'oid' : oid, 'pid' : pid, 'uid': uid, 'inv': {'id' : id, 'category' : cat}};
	var postUrl = "https://api.pearnode.com/api/org/inventory/uncategorize.php";
	$.post(postUrl, JSON.stringify(postData), function(response){
		NProgress.done();
		if(typeof callback != "undefined"){
			callback();
		}
	});
}

// SHORT CODE UPDATE
function updateProductShortCodeApi(pobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'product' : pobj};
	$.post(PRODUCT_SC_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
function updateServiceShortCodeApi(sobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'service' : sobj};
	$.post(SERVICE_SC_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
function updateStockShortCodeApi(sobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'stock' : sobj};
	$.post(STOCK_SC_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
// SHORT CODE UPDATE

// SALE RATE UPDATE
function updateProductSaleRateApi(pobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'product' : pobj};
	$.post(PRODUCT_SR_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
function updateServiceSaleRateApi(sobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'service' : sobj};
	$.post(SERVICE_SR_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
function updateStockSaleRateApi(sobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'stock' : sobj};
	$.post(STOCK_SR_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
// SALE RATE UPDATE

// PURCHASE RATE UPDATE
function updateProductPurchaseRateApi(pobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'product' : pobj};
	$.post(PRODUCT_PR_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
function updateServicePurchaseRateApi(sobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'service' : sobj};
	$.post(SERVICE_PR_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
function updateStockPurchaseRateApi(sobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'stock' : sobj};
	$.post(STOCK_PR_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
// PURCHASE RATE UPDATE

// MRP UPDATE
function updateProductMRPApi(pobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'product' : pobj};
	$.post(PRODUCT_MRP_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
function updateServiceMRPApi(sobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'service' : sobj};
	$.post(SERVICE_MRP_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
function updateStockMRPApi(sobj, callback){
	var postData = {'oid' : oid, 'pid' : pid, 'stock' : sobj};
	$.post(STOCK_MRP_UPDATE, JSON.stringify(postData), function(response){
		if(typeof callback !== "undefined"){
			callback();
		}
	});
}
// MRP UPDATE