class Item {
	constructor(iobj){
		for (var property in iobj) {
		  if (iobj.hasOwnProperty(property)) {
			  this[property] = iobj[property];
		  }
		}
		this.id = parseInt(this.id);
		this.dname = toIMTitleCase(this.name);
		this.dpn = toIMTitleCase(this.parent_name);
		this.dunit = toIMTitleCase(this.unit);

		if(typeof this.category == "undefined"){
			this.category = "uncategorized";
		}else {
			this.category = this.category.toLowerCase();
			if(this.category != "uncategorized"){
				this.categorized = true;
			}
		}
		if(typeof this.sc == "undefined"){
			this.sc = 0;
		}
		if (typeof this.purchase_rate == 'undefined') {
			this.purchase_rate = 0;
		}
		if (typeof this.sale_rate == 'undefined' ) {
			this.sale_rate = 0;
		}
		if (typeof this.mrp == 'undefined' ) {
			this.mrp = 0;
		}
		if(this.sale_rate != 0){
			this.rate = this.sale_rate
		}else if (this.purchase_rate != 0){
			this.rate = this.purchase_rate;
		}else if(this.mrp != 0){
			this.rate = this.mrp;
		}
		var attributes = iobj.attrs;
		if(typeof attributes !== "undefined"){
			for (var j = 0; j < attributes.length; j++) {
				var aobj = iobj.attrs[j];
				var an = aobj.an;
				var ans = an.split(".").join("_");
				if((typeof this[ans] == "undefined") || (this[ans] == "") || (this[ans] == 0) || (this[ans] === null)){
					this[ans] = aobj.av.trim();
				}
			}
		}
		if(this.mrp == 0){
			this.mrp = this.sale_rate;
		}
		
		this.purchase_rate = parseFloat(this.purchase_rate);
		this.sale_rate = parseFloat(this.sale_rate);
		this.mrp = parseFloat(this.mrp);
		this.gst_percent = parseFloat(this.gst_percent);
		
		if (typeof this.g_img_url == 'undefined') {
			this.g_img_url = '';	
		}
		if (typeof this.img_url == 'undefined') {
			this.img_url = '';	
		}else if(this.img_url == '/assets/images/item.png'){
			this.img_url = '';	
		}else if(this.img_url == ""){
			this.img_url = '';	
		}
		if(this.img_url === null){
			this.img_url = '';	
		}
		if(this.img_url.indexOf("ftp.pearnode.com") != -1){
			this.img_url = this.img_url.replace("ftp.pearnode.com", "ftp-158c3.kxcdn.com")
		}
	}
}

class LiteItem {
	constructor(iobj){
		this.id = parseInt(iobj.id);
		this.name = iobj.n;
		this.dname = toIMTitleCase(iobj.n);
		this.code = iobj.c;
		this.parent_name = iobj.pn;
		this.dpn = toIMTitleCase(iobj.pn);
		this.parent_code = iobj.pc;
		this.sale_rate = iobj.sr;
		this.purchase_rate = iobj.pr;
		this.mrp = iobj.mr;
		if(this.mrp == 0){
			this.mrp = this.sale_rate;
		}
		this.unit = iobj.u;
		this.category = iobj.ct;
		this.img_url = iobj.img;
		this.gst_percent = parseFloat(iobj.gstp);
		this.gst_hsn = iobj.gsth;
		this.purchase_rate = parseFloat(this.purchase_rate);
		this.sale_rate = parseFloat(this.sale_rate);
		this.mrp = parseFloat(this.mrp);
	}
}

function toIMTitleCase(str) {
   if(str.trim() == ""){
	   return "NA";
   }
   var splitStr = str.toLowerCase().split(' ');
   for (var i = 0; i < splitStr.length; i++) {
       // You do not need to check if i is larger than splitStr length, as your for does that for you
       // Assign it back to the array
       splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
   }
   // Directly return the joined string
   return splitStr.join(' '); 
}