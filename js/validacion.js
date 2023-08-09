jQuery(document).ready(function ($) {
    
    $("#contactForm").validate({
        rules: {
            nombre: {
                required: true,
                minlength: 3,
            },
            telefono: {
                required: true,
                number: true,
                minlength: 9,
                maxlength: 9,
            },
            email: {
                required: true,
                email: true,
            },
            asunto: {
                required: true,
                minlength: 20
            },
            mensaje: {
                required: true,
                minlength: 50
            },
            privacyPolicy: {
                required: true,
                checkPolicy: true 
            }
        },
        messages: {
            nombre:{
                required: "El campo nombre es obligatorio",
                minlength: "El nombre debe tener 3 carácteres"
            },
            telefono:{
                required: "El campo telefono es obligatorio",
                number: "El teléfono debe ser únicamente dígitos",
                minlength: "Debe tener 9 dígitos",
                maxlength: "Debe tener 9 dígitos"
            },
            email: {
                required: "El campo email es obligatorio",
                email: "Debes introducir un email válido"
            },
            asunto:{
                required: "Debes introducir un asunto",
                minlength: "El asunto debe tener 20 carácteres como mínimo"
            },
            mensaje:{
                required: "El mensaje es obligatorio",
                minlength: "El mensaje debe tener 50 carácteres como mínimo"
            },
            privacyPolicy: "Debes aceptar las políticas de privacidad."
        },
        submitHandler: function (form) {
            //AJAX
            $.ajax({
                url: myScriptData.ajaxurl, 
                type: 'POST',
                data: {
                    action: 'guardar_datos', 
                    nombre: $('#nombre').val(),
                    email: $("#email").val(),
                    telefono: $("#telefono").val(),
                    asunto: $("#asunto").val(),
                    mensaje: $("#mensaje").val(),
                    privacyPolicy: $("#policy").val(),
                }
            })
            .done(function(response) {
                if (response.success) {
                    $( ".form_success" ).show();
                    const formulario = $('#contactForm');
                    formulario[0].reset();
                    setTimeout(function(){ 
                        $( ".form_success" ).hide();
                    }, 5000);
                } else {
                    alert(response.data.message);
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.log("Error en la solicitud: " + textStatus + ", " + errorThrown);
            });
        }
    });
    
    $.validator.addMethod("checkPolicy", function (value, element) {
        return $(element).prop("checked");
    }, "Debes aceptar las políticas de privacidad.");
});
