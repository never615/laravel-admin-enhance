/**
 * js一些公用方法
 * Created by never615 on 06/11/2016.
 */

;(function ($, window) {

    //------------ X-editable初始化-------------
    $.fn.editable.defaults.error = function (response, newValue) {
        if (response.responseJSON && response.responseJSON.error) {
            return response.responseJSON.error;
        } else {
            if (response.responseJSON.errors) {
                var msg = "";
                $.each(response.responseJSON.errors, function (k, v) {
                    msg += v + "\n";
                });
                return msg;
            } else {
                return response.statusText + ":" + response.status
            }
        }
    };

    // $.fn.editable.defaults.emptytext = "空";
    //turn to inline mode
//     $.fn.editable.defaults.mode = 'inline';
//     $.fn.editable.defaults.ajaxOptions = {type: "PUT"};

//------------ X-editable初始化 结束-------------
})(jQuery, window);
