$( function() {
    manage_form_post.init();
} );

var manage_form_post = function(){
    function _init(){
        //Open popin job post
        $( "#jobSend" ).on( "click", function() {
            $('#sendFormJob')[0].reset();
            $("input:file").uniform();
            $( "#JobFormModal").modal("show")
            $('.error').html('');
        } );
        //Ajax request for submitting form
        $(document).on('submit','#sendFormJob',function(){
            var form = $(this)
            var values = new FormData(form[0]);
            //Envoi de contentId de contact dans la requête ajax
            values.append('contactID',$('#contact_id').val())
			values.append('title',$('#title').val())
            $("input[type=submit]").attr("disabled", "disabled");
            $('.ajax-loader').show();
            $.ajax({
                type : form.attr( 'method' ),
                url : form.attr( 'action' ),
                data : values,
                processData : false,
                contentType : false,
                success : function(data) {
                    $('.ajax-loader').hide();
                    $.each(data.successInputs,function(index,value){
                        $('#'+value.elementId).html('');
                    })
                    if(!data.success){
                        $.each(data.errors,function(index,value){
                            $('#'+value.elementId).html(value.errorMessage);
                        })
                        Recaptcha.reload();
                    }
                    else{
                        $( "#JobFormModal" ).modal( "hide" );
                        Recaptcha.reload();
                        $( "#Confirmation" ).find( ".modalBody" ).html( "" );
                        $( "#Confirmation" ).find( ".modalBody" ).append('<div class="confirmation">'+data.message+'</div>');
                        $( "#Confirmation" ).modal( "show" );
                        setTimeout(function(){
                            $( "#Confirmation" ).modal( "hide" );
                        },3000);
                        form[0].reset();
                    }
                    $("input[type=submit]").removeAttr('disabled');
                }
            });
            return false;
        })
    }
    return {init: _init}
}();


