jQuery( document ).ready(function($) {
    var apiKeyChangedCallbackFunction = function(){
        if($('input#bcdn_enable_url_token').is(':checked')){
            $('input#bcdn_url_authentication_key').closest('tr').show();
        }else{
            $('input#bcdn_url_authentication_key').closest('tr').hide();
        }
    }
    
    apiKeyChangedCallbackFunction();
    $('input#bcdn_enable_url_token').on('change', function(){
        apiKeyChangedCallbackFunction();
    });


    $(".print-select2").select2({
        placeholder: "Select Products", 
        dropdownAutoWidth: false
    });
    
});