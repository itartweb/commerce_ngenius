(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.ngeniusIframe = {
    attach: function (context, settings) {
      var $id = settings.commerce_ngenius.id;

      window.NI.mountCardInput($id, {
          style: 'style',
          // Style configuration you can pass to customize the UI
          apiKey: settings.commerce_ngenius.apiKey,
          // API Key for WEB SDK from the portal
          outletRef: settings.commerce_ngenius.outletRef,
          // outlet reference from the portal
          onSuccess: 'onSuccess',
          // Success callback if apiKey validation succeeds
          onFail: 'onFail',
          // Fail callback if apiKey validation fails
          onChangeValidStatus: (function (_ref) {
            // var isCVVValid = _ref.isCVVValid,
            //   isExpiryValid = _ref.isExpiryValid,
            //   isNameValid = _ref.isNameValid,
            //   isPanValid = _ref.isPanValid;
            // console.log(isCVVValid, isExpiryValid, isNameValid, isPanValid);
          })
        });

      $('.commerce-checkout-flow', context).submit(function(e) {
        var postData = {};
        window.NI.generateSessionId().then(function (response) {
          postData.session_id = response.session_id;
          postData.payment_gateway_id = settings.commerce_ngenius.payment_gateway_id;
          //console.log(postData);

          $.ajax({
            url: settings.commerce_ngenius.session_id_save_url,
            type: 'POST',
            data: postData,
            async: false,
            success: function(data, textStatus, jQxhr) {
              if (data) {
                // console.log(data);
                // console.log(window.NI.paymentStates);

                if (data.paymentResponse.state == 'AWAIT_3DS') {
                  // Clear object.
                  $('#' + $id).html('');
                  window.NI.handlePaymentResponse(data.paymentResponse, {
                    mountId: $id,
                    style: {
                      width: 500,
                      height: 300
                    }
                  }).then(function (response) {
                    var status = response.status;
                    //console.log(response);
                    if (status === window.NI.paymentStates.AUTHORISED || status === window.NI.paymentStates.CAPTURED) {
                      // @todo
                      //$('#' + $id).html('<h3>successful 3ds</h3>');
                      //console.log('successful 3ds');

                      $.ajax({
                        url: settings.commerce_ngenius.check_payment_url,
                        type: 'POST',
                        data: response,
                        async: false,
                        success: function(data, textStatus, jQxhr) {
                          if (data && data.status == 'complete') {
                            // @todo create redirect to complete.
                            $(location).prop('href', settings.commerce_ngenius.complete_url);
                          }
                          else {
                            // @todo create redirect to cancel.
                            $(location).prop('href', settings.commerce_ngenius.cancel_url);
                          }
                        }
                      });

                    } else if (status === window.NI.paymentStates.FAILED || status === window.NI.paymentStates.THREE_DS_FAILURE) {
                      // @todo
                      // $('#' + $id).html('<h3>failure 3ds</h3>');
                      // console.log('failure 3ds');

                      $.ajax({
                        url: settings.commerce_ngenius.check_payment_url,
                        type: 'POST',
                        data: response,
                        async: false,
                        success: function(data, textStatus, jQxhr) {
                          if (data) {
                            // @todo create redirect to cancel.
                            $(location).prop('href', settings.commerce_ngenius.cancel_url);
                          }
                        }
                      });

                    }
                  }).catch(function (error) {
                    return console.error(error);
                  });
                }
                else {
                  // Without 3DS.
                  window.NI.handlePaymentResponse(data.paymentResponse)
                    .then(function (response) {
                      if (response.status === window.NI.paymentStates.AUTHORISED ||
                        response.status === window.NI.paymentStates.CAPTURED) {
                        // @todo
                        // $('#' + $id).html('<h3>successful</h3>');
                        // console.log('successful');

                        $.ajax({
                          url: settings.commerce_ngenius.check_payment_url,
                          type: 'POST',
                          data: response,
                          async: false,
                          success: function(data, textStatus, jQxhr) {
                            if (data && data.status == 'complete') {
                              // @todo create redirect to complete.
                              $(location).prop('href', settings.commerce_ngenius.complete_url);
                            }
                            else {
                              // @todo create redirect to cancel.
                              $(location).prop('href', settings.commerce_ngenius.cancel_url);
                            }
                          }
                        });

                      } else if (response.status === window.NI.paymentStates.FAILED) {
                        // @todo
                        // $('#' + $id).html('<h3>failure</h3>');
                        // console.log('failure');

                        $.ajax({
                          url: settings.commerce_ngenius.check_payment_url,
                          type: 'POST',
                          data: response,
                          async: false,
                          success: function(data, textStatus, jQxhr) {
                            if (data && data.status) {
                              // @todo create redirect to cancel.
                              $(location).prop('href', settings.commerce_ngenius.cancel_url);
                            }
                          }
                        });

                      }
                    }).catch(function (error) {
                    return console.error(error);
                  });
                }
              }
            }
          });

        }).catch(function (error) {
          return console.error(error);
        });

        e.preventDefault();
        return false;
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
