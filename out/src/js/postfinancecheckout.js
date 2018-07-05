(function ($) {
    window.PostFinanceCheckout = {
        handler: null,
        methodConfigurationId: null,
        running: false,
        loaded: false,

        initialized: function () {
            $('#PostFinanceCheckout-iframe-spinner').hide();
            $('#PostFinanceCheckout-iframe-container').show();
            $('#button-confirm').removeAttr('disabled');
            $('#button-confirm').click(function (event) {
            	event.preventDefault();
                PostFinanceCheckout.handler.validate();
                $('#button-confirm').attr('disabled', 'disabled');
                return false;
            });
            this.loaded = true;
        },
        
        heightChanged: function () {
        	if(this.loaded && $('#PostFinanceCheckout-iframe-container > iframe').height() == 0) {
        		$('#PostFinanceCheckout-iframe-container').parent().parent().hide();
        	}
        },

        submit: function () {
            if (PostFinanceCheckout.running) {
                return;
            }
            PostFinanceCheckout.running = true;
            var params = '&stoken=' + $('input[name=stoken]').val();
            params += '&sDeliveryAddressMD5=' + $('input[name=sDeliveryAddressMD5]').val();
            params += '&challenge=' + $('input[name=challenge]').val();
            $.getJSON('index.php?cl=order&fnc=pfcConfirm' + params, '', function (data, status, jqXHR) {
                if (data.status) {
                    PostFinanceCheckout.handler.submit();
                } else {
                    PostFinanceCheckout.addError(data.message);
                    $('#button-confirm').removeAttr('disabled');
                }
                PostFinanceCheckout.running = false;
            }).fail((function(jqXHR, textStatus, errorThrown) {
                alert("Something went wrong: " + errorThrown);
            }));
        },

        validated: function (result) {
            if (result.success) {
                PostFinanceCheckout.submit();
            } else {
                if (result.errors) {
                    for (var i = 0; i < result.errors.length; i++) {
                        PostFinanceCheckout.addError(result.errors[i]);
                    }
                }
                $('#button-confirm').removeAttr('disabled');
            }
        },

        init: function (methodConfigurationId) {
            if (typeof window.IframeCheckoutHandler === 'undefined') {
                setTimeout(function () {
                    PostFinanceCheckout.init(methodConfigurationId);
                }, 500);
            } else {
                PostFinanceCheckout.methodConfigurationId = methodConfigurationId;
                PostFinanceCheckout.handler = window
                    .IframeCheckoutHandler(methodConfigurationId);
                PostFinanceCheckout.handler.setInitializeCallback(this.initialized);
                PostFinanceCheckout.handler.setValidationCallback(this.validated);
                PostFinanceCheckout.handler.setHeightChangeCallback(this.heightChanged);
                PostFinanceCheckout.handler.create('PostFinanceCheckout-iframe-container');
            }
        },

        addError: function (message) {
            $('#content').find('p.alert-danger').remove();
            $('#content').prepend($("<p class='alert alert-danger'>" + message + "</p>"));
            $('html, body').animate({
                scrollTop: $('#content').find('p.alert-danger').offset().top
            }, 200);
        }
    }
})(jQuery);