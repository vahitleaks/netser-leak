/*

FORM VALIDATION

*/

form_fields = Array();

TYP_NOT_NULL = 1    // kesin girilmeli
TYP_TEXT = 2;       // [tum karakterler]
TYP_DIGIT = 4;      // [0-9]
TYP_ALPHA = 8;      // [0-9] [a-b] [A-Z]
TYP_DROPDOWN = 16;  // combobox (-1) olmamali
TYP_EMAIL = 32;     // gecerli email adresi
TYP_PASSWORD = 64;
TYP_RADIO = 128;
TYP_RIGHT = 256;

header = "Lütfen aþaðýdaki eksikleri tamamlayýnýz:\n-------------------------------------------------------------\n";

function check_form(frm){
  var why = "";
  for (i = 0 ; i < form_fields.length ; i++) {
    if (form_fields[i][0] != "") {
//    right check
      if (form_fields[i][2] == TYP_RIGHT) {
         if (!right_check(form_fields[i][0]))
         {
            alert("Hakkýnýz Yok");
            return false;
         }
      }else{

            if (form_fields[i][2] == TYP_RADIO) {
               why += form_route(radio_group(document.all(form_fields[i][0])), form_fields[i][1], form_fields[i][2]);
            }
            else {
                why += form_route((document.all(form_fields[i][0]).value), form_fields[i][1], form_fields[i][2]);
            } // else 
        } // right else
    }//
  } // for if
  if (why != "") {
     alert(header+why);
     return false;
  }
  return true;
}

function form_route(field,warning,type) {
  var field;
  var warning;
  var type;
  var $error = "";
  if (type & TYP_NOT_NULL) { $error += isEmpty(field,warning); }
  if (type & TYP_DROPDOWN) { $error += checkDropdown(field,warning); }
  if (type & TYP_RADIO) { $error += checkRadio(field,warning); }
  if (type & TYP_DIGIT) { $error += checkDigit(field,warning); }
  if (type & TYP_EMAIL) { $error += checkEmail(field); }
  return $error;
}

function checkDigit(field,warning)
{
  var error = "";
  if (isNaN(field)) {
     error = warning + "\n";
  }    
  return error;
}
function checkEmail (strng) {
var error="";
//if (strng == "") {
//   error = "EMAIL adresini giriniz.\n";
//}

    var emailFilter=/^.+@.+\..{2,3}$/;
    if (!(emailFilter.test(strng))) { 
       error = "Lütfen geçerli bir e-mail adresi giriniz.\n";
    }
    else {
//test email for illegal characters
       var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/
         if (strng.match(illegalChars)) {
          error = "Girdiðiniz e-mail geçersiz karakterler içermektedir.\n";
       }
    }
return error;
}


// phone number - strip out delimiters and check for 10 digits

function checkPhone (strng) {
var error = "";
if (strng == "") {
   error = "You didn't enter a phone number.\n";
}

var stripped = strng.replace(/[\(\)\.\-\ ]/g, ''); //strip out acceptable non-numeric characters
    if (isNaN(parseInt(stripped))) {
       error = "The phone number contains illegal characters.";
  
    }
    if (!(stripped.length == 10)) {
	error = "The phone number is the wrong length. Make sure you included an area code.\n";
    } 
return error;
}


// password - between 6-8 chars, uppercase, lowercase, and numeral

function checkPassword (strng) {
var error = "";
if (strng == "") {
   error = "You didn't enter a password.\n";
}

    var illegalChars = /[\W_]/; // allow only letters and numbers
    
    if ((strng.length < 6) || (strng.length > 8)) {
       error = "The password is the wrong length.\n";
    }
    else if (illegalChars.test(strng)) {
      error = "The password contains illegal characters.\n";
    } 
    else if (!((strng.search(/(a-z)+/)) && (strng.search(/(A-Z)+/)) && (strng.search(/(0-9)+/)))) {
       error = "The password must contain at least one uppercase letter, one lowercase letter, and one numeral.\n";
    }  
return error;    
}    


// username - 4-10 chars, uc, lc, and underscore only.

function checkUsername (strng) {
var error = "";
if (strng == "") {
   error = "You didn't enter a username.\n";
}


    var illegalChars = /\W/; // allow letters, numbers, and underscores
    if ((strng.length < 4) || (strng.length > 10)) {
       error = "The username is the wrong length.\n";
    }
    else if (illegalChars.test(strng)) {
    error = "The username contains illegal characters.\n";
    } 
return error;
}       


// non-empty textbox

function isEmpty(strng, warn) {
  var error = "";
  if (strng.length == 0) {
     error = warn + "\n";
  }
  return error;	  
}

// was textbox altered

function isDifferent(strng) {
var error = ""; 
  if (strng != "Can\'t touch this!") {
     error = "You altered the inviolate text area.\n";
  }
return error;
}

// exactly one radio button is chosen

function checkRadio(checkvalue,warn) {
  var error = "";
  if (!checkvalue) {
     error = warn + "\n";
  }
  return error;
}

// valid selector from dropdown list

function checkDropdown(choice,warn) {
  var error = "";
  if (choice == -1) {
    error = warn + "\n";
  }    
  return error;
}

function radio_group(radiogroup) 
{
   for(var j = 0 ; j < radiogroup.length ; ++j) 
   {
      if(radiogroup[j].checked) 
      {
	      return true;
       }
   }
   return false;
 }



/*******************************************************************************   
* Controls a TextArea size, if it is overflow; warn the user and sets the content to max size

  usage:  onkeyup = "javascript:isOverFlow(this,30)"  
  objTextArea : the textarea will be controlled
  maxLen : maximum length of the TextArea

  OWNER : SEYKAY
  DATE  : 12.02.02 

*******************************************************************************/
  function isOverFlow(objTextArea,maxLen)
  {  
       var txtVal = objTextArea.value ;
       var cnt; 
       var AvailCnt = maxLen - txtVal.length;
       if (AvailCnt < 1)
       { 
          alert("Alanýn boyutunu aþtýnýz");
          txtVal = txtVal.substring(0,maxLen);
          objTextArea.value = txtVal ; 
          cnt = 0; 
       }
       else
       {
         cnt = AvailCnt;
       } 
        document.all(objTextArea.name + "_COUNTER").value = cnt;
   }
 
 function right_check(right_name)
 { 
   if (isNaN(rights[right_name])) 
         return(false); // right is not found 
      else 
         return(true); // right is found 
 } 
