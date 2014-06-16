$( function() {
    manage_form_post.init();
} );

var manage_form_post = function(){
    function _init(){
        //Open popin job post
        $( "#jobSend" ).on( "click", function() {
            $( "#JobFormModal").modal("show")
            $('#sendFormJob')[0].reset();
            $("input:file").uniform();
            $('.error').html('');
        } );
        //Ajax request for submitting form
        $('#sendFormJob').on('submit',function(){
            var form = $(this)
            var values = new FormData(form[0]);
            //Envoi de contentId de contact dans la requête ajax
            values.append('contactID',$('#contact_id').val())
            $("input[type=submit]").attr("disabled", "disabled");
            $.ajax({
                type : form.attr( 'method' ),
                url : form.attr( 'action' ),
                data : values,
                processData : false,
                contentType : false,
                success : function(data) {

                    $.each(data.successInputs,function(index,value){
                        console.log(value.elementId);
                        $('#'+value.elementId).html('');
                    })
                    if(!data.success){
                        $.each(data.errors,function(index,value){
                            $('#'+value.elementId).html(value.errorMessage);
                        })
                        Recaptcha.reload();
                    }
                    else{
                        $('.success').html(data.message);
                        setTimeout(function(){
                            $( "#JobFormModal" ).modal( "hide" );
                        },2000);
                        form[0].reset();
                        $('.success').html('');
                    }
                    $("input[type=submit]").removeAttr('disabled');
                }
            });
            return false;
        })
    }
    return {init: _init}
}();


