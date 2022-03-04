var formatter = OSREC.CurrencyFormatter.getFormatter({ currency: 'INR' });		

var words = new Array();
words[0] = '';
words[1] = 'One';
words[2] = 'Two';
words[3] = 'Three';
words[4] = 'Four';
words[5] = 'Five';
words[6] = 'Six';
words[7] = 'Seven';
words[8] = 'Eight';
words[9] = 'Nine';
words[10] = 'Ten';
words[11] = 'Eleven';
words[12] = 'Twelve';
words[13] = 'Thirteen';
words[14] = 'Fourteen';
words[15] = 'Fifteen';
words[16] = 'Sixteen';
words[17] = 'Seventeen';
words[18] = 'Eighteen';
words[19] = 'Nineteen';
words[20] = 'Twenty';
words[30] = 'Thirty';
words[40] = 'Forty';
words[50] = 'Fifty';
words[60] = 'Sixty';
words[70] = 'Seventy';
words[80] = 'Eighty';
words[90] = 'Ninety';

function convertNumberToWords(amount) {
	amount = parseFloat(amount).toFixed(2);
    var atemp = amount.split(".");
    var paise = 0;
    var paise_words = '';
    if(atemp.length > 1){
    	paise = atemp[1];
    	if(paise != 0){
    		var wordTens = paise / 10;
    		var decimal = wordTens - Math.floor(wordTens);
    		if(decimal == 0){
    			paise_words = " " + words[paise] + ' Paise only.';
    		}else {
    			var paiseInt = parseInt(paise);
        		if(paiseInt <= 20){
        			paise_words = paise_words + " " + words[paiseInt] + ' Paise only.';
        		}else if (paiseInt > 20){
            		wordTens = ~~wordTens;
            		wordTens = wordTens * 10;
            		paise_words = words[wordTens];
            		paise_words += " " + words[parseInt(decimal * 10) + 1] + ' Paise only.';
        		}else if( paiseInt == 0){
        			paise_words += " zero Paise only."
        		}
    		}
    	}else {
    		paise_words = ' only.';
    	}
    }
    var number = atemp[0].split(",").join("");
    var n_length = number.length;
    var words_string = "";
    if (n_length <= 9) {
        var n_array = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0);
        var received_n_array = new Array();
        for (var i = 0; i < n_length; i++) {
            received_n_array[i] = number.substr(i, 1);
        }
        for (var i = 9 - n_length, j = 0; i < 9; i++, j++) {
            n_array[i] = received_n_array[j];
        }
        for (var i = 0, j = 1; i < 9; i++, j++) {
            if (i == 0 || i == 2 || i == 4 || i == 7) {
                if (n_array[i] == 1) {
                    n_array[j] = 10 + parseInt(n_array[j]);
                    n_array[i] = 0;
                }
            }
        }
        value = "";
        for (var i = 0; i < 9; i++) {
            if (i == 0 || i == 2 || i == 4 || i == 7) {
                value = n_array[i] * 10;
            } else {
                value = n_array[i];
            }
            if (value != 0) {
                words_string += words[value] + " ";
            }
            if ((i == 1 && value != 0) || (i == 0 && value != 0 && n_array[i + 1] == 0)) {
                words_string += "Crores ";
            }
            if ((i == 3 && value != 0) || (i == 2 && value != 0 && n_array[i + 1] == 0)) {
                words_string += "Lakhs ";
            }
            if ((i == 5 && value != 0) || (i == 4 && value != 0 && n_array[i + 1] == 0)) {
                words_string += "Thousand ";
            }
            if (i == 6 && value != 0 && (n_array[i + 1] != 0 && n_array[i + 2] != 0)) {
                words_string += "Hundred and ";
            } else if (i == 6 && value != 0) {
                words_string += "Hundred ";
            }
        }
        words_string = words_string.split("  ").join(" ");
        words_string += " and ";
    }
    
    words_string += paise_words;
    return words_string;
}

function convertNumberToText(amount) {
	amount = parseFloat(amount).toFixed(2);
    var atemp = amount.split(".");
    var number = atemp[0].split(",").join("");
    var n_length = number.length;
    var words_string = "";
    if (n_length <= 9) {
        var n_array = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0);
        var received_n_array = new Array();
        for (var i = 0; i < n_length; i++) {
            received_n_array[i] = number.substr(i, 1);
        }
        for (var i = 9 - n_length, j = 0; i < 9; i++, j++) {
            n_array[i] = received_n_array[j];
        }
        for (var i = 0, j = 1; i < 9; i++, j++) {
            if (i == 0 || i == 2 || i == 4 || i == 7) {
                if (n_array[i] == 1) {
                    n_array[j] = 10 + parseInt(n_array[j]);
                    n_array[i] = 0;
                }
            }
        }
        value = "";
        for (var i = 0; i < 9; i++) {
            if (i == 0 || i == 2 || i == 4 || i == 7) {
                value = n_array[i] * 10;
            } else {
                value = n_array[i];
            }
            if (value != 0) {
                words_string += words[value] + " ";
            }
            if ((i == 1 && value != 0) || (i == 0 && value != 0 && n_array[i + 1] == 0)) {
                words_string += "Crores ";
            }
            if ((i == 3 && value != 0) || (i == 2 && value != 0 && n_array[i + 1] == 0)) {
                words_string += "Lakhs ";
            }
            if ((i == 5 && value != 0) || (i == 4 && value != 0 && n_array[i + 1] == 0)) {
                words_string += "Thousand ";
            }
            if (i == 6 && value != 0 && (n_array[i + 1] != 0 && n_array[i + 2] != 0)) {
                words_string += "Hundred and ";
            } else if (i == 6 && value != 0) {
                words_string += "Hundred ";
            }
        }
        words_string = words_string.split("  ").join(" ");
    }
    return words_string;
}

function convertWordToNumber(word) {
	var words = new Array();
    words['zero'] = 0;
    words['one'] = 1;
    words['two'] = 2;
    words['three'] = 3;
    words['four'] = 4;
    words['five'] = 5;
    words['six'] = 6;
    words['seven'] = 7;
    words['eight'] = 8;
    words['nine'] = 9;
    words['ten'] = 10;
    
    words['first'] = 1;
    words['second'] = 2;
    words['third'] = 3;
    words['fourth'] = 4;
    words['fifth'] = 5;
    words['sixth'] = 6;
    words['seventh'] = 7;
    words['eighth'] = 8;
    words['ninth'] = 9;
    words['tenth'] = 10;
    
    return words[word];
}

function toCamelCase(str){
   return str.split(' ').map(function(word,index){
     // If it is the first word make sure to lowercase all the chars.
     if(index == 0){
      return word.toLowerCase();
     }
     // If it is not the first word only upper case the first char and lowercase the rest.
     return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
   }).join('');
}

function toTitleCase(str) {
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

function convertObjectToString(jobj){
	var buffer = '';
	$.each(jobj, function(key, val) {
		if(val.constructor === Array){
			// Ignore for now.
		}else {
			if(val.trim() == ""){
				val = "NA";
			}
			if(key.indexOf("_") != -1){
				var pkey = key.split("_").join(" ");
				key = toTitleCase(pkey);
			}else {
				key = toTitleCase(key);
			}
			buffer += key + ' : ' + val + ', ';
		}
	});
	return buffer;
}

function convertObjectToNewLineString(jobj){
	var lines = new Array();
	$.each(jobj, function(key, val) {
		if(val.trim() == ""){
			val = "NA";
		}
		if(key.indexOf("_") != -1){
			var pkey = key.split("_").join(" ");
			key = toTitleCase(pkey);
		}else {
			key = toTitleCase(key);
		}
		lines.push(key + ' : ' + val);
	});
	return lines;
}

function getReqParam(name){
	 var name = (new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search);
	 if(name !== null){
		 return decodeURIComponent(name[1]);
	 }else {
		 return "";
	 }
}

function getReqParamRef(name){
	 var name = (new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search);
	 if(name !== null){
		 var paramRefStr = decodeURIComponent(name[1]);
		 return parseInt(paramRefStr);
	 }else {
		 return -1;
	 }
}

function chunkArray(myArray, chunk_size){
    var index = 0;
    var arrayLength = myArray.length;
    var tempArray = [];
    for (index = 0; index < arrayLength; index += chunk_size) {
        myChunk = myArray.slice(index, index+chunk_size);
        // Do something if you want with the group
        tempArray.push(myChunk);
    }

    return tempArray;
}

function jsonCopy(srcObj) {
	return JSON.parse(JSON.stringify(srcObj));
}

function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function isValidMobile(mobNo) {
    var mob = /^[1-9]{1}[0-9]{9}$/;
    return mob.test(mobNo);
}

function closeMessage(callback){
	try {
		swal.close();
	} catch (e) {
		console.log("Error");
	}
	setTimeout(callback, 200);
}

function showMessage(title, msg, type, callback){
	closeMessage(function(){
		Swal.fire({title: title, html: msg, icon: type, showConfirmButton:true, allowEscapeKey: false, allowOutsideClick: false, onClose: function(){
			if(typeof callback !== "undefined"){
				callback();
			}
		}});
	})
}

function showConfirm(title, msg, type, callback){
	try {
		swal.close();
	} catch (e) {
		// TODO: handle exception
	}
	Swal.fire({
	  title:title ,
	  html: msg,
	  icon: type,
	  showCancelButton: true,
	  confirmButtonText: 'Confirm',
	  cancelButtonText: 'Cancel',
	  reverseButtons: true
	}).then((result) => {
	  if (result.value) {
		  if(typeof callback !== "undefined"){
				callback();
			}
	  }else if (result.dismiss) {
		 swal.close();
	  }
	});
}

function showProcessingLoader(msg){
	Swal.fire({title: msg, text: "Please wait...", imageUrl: processing_img, showConfirmButton:false, allowEscapeKey: false, allowOutsideClick: false});
}

function hideProcessingLoader(){
	Swal.close();
}
function showProcessing(msg){
	Swal.fire({title: msg, text: "Please wait...", imageUrl: processing_img, showConfirmButton:false, allowEscapeKey: false, allowOutsideClick: false});
}

function hideProcessing(){
	Swal.close();
}

function generateStrUniqueId(){
	  return chr4() + chr4() +
	    '-' + chr4() +
	    '-' + chr4() +
	    '-' + chr4() +
	    '-' + chr4() + chr4() + chr4();
}

function chr4(){
  return Math.random().toString(16).slice(-4);
}