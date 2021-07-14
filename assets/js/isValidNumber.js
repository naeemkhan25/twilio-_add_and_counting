
var plugin_url=isValidnumber.plugin_Urls;
var input = document.querySelector("#inwpnotifier_phone"),
    country_code = document.querySelector("#country_code"),
    errorMsg = document.querySelector("#error-msg"),
    validMsg = document.querySelector("#valid-msg");
    validation=document.querySelector(".profile-php ,#profile-page ,#submit");
// var data=input.value.trim();
// console.log(data);
// here, the index maps to the error code returned from getValidationError - see readme
var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

// initialise plugin
var iti = window.intlTelInput(input, {
  utilsScript: plugin_url + '/assets/js/utils.js'
});

var reset = function() {
  input.classList.remove("error");
  errorMsg.innerHTML = "";
  errorMsg.classList.add("hide");
  validMsg.classList.add("hide");
};
input.addEventListener('blur', function() {
  reset();
  if (input.value.trim()) {
    if (iti.isValidNumber()) {
      validMsg.classList.remove("hide");
    } else {
      input.classList.add("error");
      var errorCode = iti.getValidationError();
      errorMsg.innerHTML = errorMap[errorCode];
      errorMsg.classList.remove("hide");
    }
  }
});



validation.addEventListener("click",function (){

 if(input.value.trim()){
   if(iti.isValidNumber()){
     return true;
   }else{
     input.value='';
     country_code.value='';
   }
 }
});
// on blur: validate

// on keyup / change flag: reset
input.addEventListener('change', reset);
input.addEventListener('keyup', reset);