$( function() {
    manage_form_post.init();
} );

var manage_form_post = function(){
    function _init(){

        $( "#jobSend" ).on( "click", function() {
            $('#sendFormJob')[0].reset();
            $("input:file").uniform();
            $('.error').html('');
            $('#upload_frame').attr('src',$('#sendFormJob').attr('action'));
            $('#sendFormJob').attr('target','upload_frame');
        });

        //Ajax request for submitting form
        $('#upload_frame').on('load',function(){
            try{
                var oResult = $.parseJSON($(this).contents().find('body').html());
                $.each(oResult.successInputs,function(index,value){
                    $('#'+value.elementId).html('');
                })
                if(!oResult.success){
                    $.each(oResult.errors,function(index,value){
                        $('#'+value.elementId).html(value.errorMessage);
                    })
                    Recaptcha.reload();
                }else{
                    $( "#JobFormModal" ).modal( "hide" );
                    Recaptcha.reload();
                    $( "#Confirmation" ).find( ".modalBody" ).html( "" );
                    $( "#Confirmation" ).find( ".modalBody" ).append('<div class="confirmation">'+oResult.message+'</div>');
                    $( "#Confirmation" ).modal( "show" );
                    setTimeout(function(){
                        $( "#Confirmation" ).modal( "hide" );
                    },3000);
                    form[0].reset();
                }
            }catch(err){}
        });
    }
    return {init: _init}
}();