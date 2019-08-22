(function ($) {
    window.PostFinanceCheckout = {
        handler: null,
        methodConfigurationId: null,
        running: false,
        loaded: false,
        initCalls: 0,
        initMaxCalls: 10,

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
            $('[name=PostFinanceCheckout-iframe-loaded').attr('value', 'true');
        },
        
        fallback: function() {
        	$('#PostFinanceCheckout-payment-information').toggle();
        	$('#button-confirm').removeAttr('disabled');
        },
        
        heightChanged: function () {
        	if(this.loaded && $('#PostFinanceCheckout-iframe-container > iframe').height() == 0) {
        		$('#PostFinanceCheckout-iframe-container').parent().parent().hide();
        	}
        },
        
        getAgbParameter: function() {
            var agb = $('#checkAgbTop');
            if(!agb.length) {
                agb = $('#checkAgbBottom');
            }
            if(agb.length && agb[0].checked) {
                return '&ord_agb=1';
            }
            return '';
        },

        submit: function () {
            if (PostFinanceCheckout.running) {
                return;
            }
            PostFinanceCheckout.running = true;
            var params = '&stoken=' + $('input[name=stoken]').val();
            params += '&sDeliveryAddressMD5=' + $('input[name=sDeliveryAddressMD5]').val();
            params += '&challenge=' + $('input[name=challenge]').val();
            params += this.getAgbParameter(),
            $.getJSON('index.php?cl=order&fnc=pfcConfirm' + params, '', function (data, status, jqXHR) {
                if (data.status) {
                    PostFinanceCheckout.handler.submit();
                }
                else {
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
        	this.initCalls++;
            if (typeof window.IframeCheckoutHandler === 'undefined') {
            	if(this.initCalls < this.initMaxCalls) {
	                setTimeout(function () {
	                    PostFinanceCheckout.init(methodConfigurationId);
	                }, 500);
            	} else {
            		this.fallback();
            	}
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