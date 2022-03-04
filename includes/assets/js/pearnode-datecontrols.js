var selDate;
var startDate;
var endDate;
var singleDate = true;

var momStartDate = {};
var momEndDate = {};
var momDate = {};

var drp = {};
var dp = {};

var dt = "";
var sdt = "";
var edt = "";

function dateFilterInit(callback){
	var selDateFound = false;
	var selDateStorage = sessionStorage.getItem('selDate');
    if (selDateStorage !== null) {
    	selDateFound = true;
    	selDate = new String(selDateStorage).valueOf();
    	momDate = moment(selDate);
    }
    var startDateFound = false;
	var startDateStorage = sessionStorage.getItem('startDate');
    if (startDateStorage !== null) {
    	startDateFound = true;
    	startDate = new String(startDateStorage).valueOf();
    	momStartDate = moment(startDate);
    }
    var endDateFound = false;
    var endDateStorage = sessionStorage.getItem('endDate');
    if (endDateStorage !== null) {
    	endDateFound = true;
    	endDate = new String(endDateStorage).valueOf();
    	momEndDate = moment(endDate);
    }
    
    if(startDateFound && endDateFound){
    	$('#chosen-date').text('Period : ' + momStartDate.format("DD-MM-YYYY") + ' / ' + momEndDate.format("DD-MM-YYYY"));
    	drp = $('#dateRangeChooser').daterangepicker({
    		startDate: momStartDate.format("MM/DD/YYYY"),
    		endDate: momStartDate.format("MM/DD/YYYY")
    	});
    }else {
    	if(!selDateFound){
        	momDate = moment();
        	selDate = momDate.format("YYYYMMDD");
    	}
    	$('#chosen-date').text('Date : ' + momDate.format("DD-MM-YYYY"));
    	drp = $('#dateRangeChooser').daterangepicker();
    }
	
	dp = $('#datechooser').datepicker();
    dp.on('pick.datepicker', function (e) {
		e.preventDefault(); // Prevent to pick the date
		momDate = moment(e.date);
		var dispDate = momDate.format("DD-MM-YYYY")
		selDate = momDate.format("YYYYMMDD");

		if(typeof ndt !== "undefined"){
			ndt = "";
		}
		sessionStorage.setItem('selDate', selDate);
		sessionStorage.removeItem('startDate');
		sessionStorage.removeItem('endDate');
		
		$('#chosen-date').text('Date : ' + dispDate);
		singleDate = true;
		st = selDate;
		
		$(this).datepicker('hide');
		if(typeof callback !== "undefined"){
			callback();
		}
	});
	drp.on('apply.daterangepicker', function(ev, picker) {
		var startUnix = parseInt(picker.startDate / 1000);
		var endUnix = parseInt(picker.endDate / 1000);
		
		momStartDate = moment.unix(startUnix);
		startDate = momStartDate.format("YYYYMMDD");
		sdt = startDate;
		
		momEndDate = moment.unix(endUnix);
		endDate = momEndDate.format("YYYYMMDD");
		edt = endDate;
		
		if(typeof ndt !== "undefined"){
			ndt = "";
		}
		sessionStorage.setItem('startDate', startDate);
		sessionStorage.setItem('endDate', endDate);
		sessionStorage.removeItem('selDate');
		
		$('#chosen-date').text('Period : ' + momStartDate.format("DD-MM-YYYY") 
				+ ' / ' + momEndDate.format("DD-MM-YYYY"));
		singleDate = false;
		
		if(typeof callback !== "undefined"){
			callback();
		}
	});

	if(typeof callback !== "undefined"){
		callback();
	}
}

function moveNextDate(callback){
    var selDateStorage = sessionStorage.getItem('selDate');
    if (selDateStorage !== null) {
    	selDate = new String(selDateStorage).valueOf();
    }else {
	    selDate = moment().format('YYYYMMDD');
	    sessionStorage.setItem('selDate', selDate);
    }
    var momObj = moment(selDate);
    var nextMomObj = momObj.add(1, 'days');
    
    var dispDate = nextMomObj.format("DD-MM-YYYY")
    selDate = nextMomObj.format('YYYYMMDD');
    
    if(typeof ndt !== "undefined"){
		ndt = "";
	}
    sessionStorage.setItem('selDate', selDate);
	singleDate = true;
    
	$('#chosen-date').text('Date : ' + dispDate);
	if(typeof callback !== "undefined"){
		callback(selDate);
	}
}

function movePreviousDate(callback){
    var selDateStorage = sessionStorage.getItem('selDate');
    if (selDateStorage !== null) {
    	selDate = new String(selDateStorage).valueOf();
    }else {
	    selDate = moment().format('YYYYMMDD');
	    sessionStorage.setItem('selDate', selDate);
    }
    var momObj = moment(selDate);
    var prevMomObj = momObj.subtract(1, 'days');
    var dispDate = prevMomObj.format("DD-MM-YYYY")
    selDate = prevMomObj.format('YYYYMMDD');

    if(typeof ndt !== "undefined"){
		ndt = "";
	}
    sessionStorage.setItem('selDate', selDate);
	singleDate = true;
    
	$('#chosen-date').text('Date : ' + dispDate);
	if(typeof callback !== "undefined"){
		callback(selDate);
	}
}

function loadInfo(){
    var selDateStorage = sessionStorage.getItem('selDate');
    if (selDateStorage !== null) {
    	selDate = new String(selDateStorage).valueOf();
    }else {
	    selDate = moment().format('YYYYMMDD');
	    sessionStorage.setItem('selDate', selDate);
    }
    var dispDate = moment(selDate).format("DD-MM-YYYY")
    $('#chosen-date').text('Date : ' + dispDate);
    loadInfoForDate(selDate);
}
