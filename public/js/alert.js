
(function() {

    $.extend($.fn, {

        //提示框组件
        alert: function(options) {

            var defaults = {
                tip: '',
                cancelBtnLbl: '取消',
                confirmBtnLbl: '确定',
                maskColor: '#000',
                cancelCallback: null,
                confirmCallback: null
            };

            var settings = $.extend(defaults, options || {}),
                $this;

            function initialize() {
                var HTML = '<div style="background-color:rgba(0,0,0,0.95);opacity:.5;position:fixed;z-index:99999;left:0px;top:0px;width:100%;height:100%;"></div><div style="width: 80%;margin: auto;position: fixed;left: 50%;bottom: 20%;-webkit-transform:translate(-50%,-50%);-moz-transform:translate(-50%,-50%);transform:translate(-50%,-50%);text-align: center;z-index:100000;display:table;"><div style="border-radius: 5px 5px 0 0 ;display:table;width:100%;border-bottom:2px solid #eeeeee;background-color:rgba(255,255,255,1);"><span style="display:table-cell;line-height:50px;vertical-align:middle;text-align:center;font-size:16px;color:#ff2500;padding:20px 10px;">' + settings.tip + '</span></div><div style="border-radius: 0 0  5px 5px;background-color:rgba(255,255,255,1);display:table;width:100%;"><!--span style="display:table-cell;height:50px;line-height:50px;vertical-align:middle;" id="alertBtn">' + settings.cancelBtnLbl + '</span--><span style="font-size:16px;display:table-cell;height:50px;line-height:50px;vertical-align:middle;">' + settings.confirmBtnLbl + '</span></div></div>';
                $this = $(HTML).appendTo($('body'));
                var $btn = $this.children('div:eq(1)');
                $btn.children().eq(0).off('click', cancelBtnClickHandler).on('click', cancelBtnClickHandler);
                $btn.children().eq(1).off('click', confirmBtnClickHandler).on('click', confirmBtnClickHandler);
            }

            //取消按钮事件
            function cancelBtnClickHandler() {
                $this.remove();
                if (settings.cancelCallback && typeof settings.cancelCallback == 'function') {
                    settings.cancelCallback();
                }
            }

            function confirmBtnClickHandler() {
                $this.remove();
                if (settings.confirmCallback && typeof settings.confirmCallback == 'function') {
                    settings.confirmCallback();
                }
            }

            initialize();

        },

    });

})(jQuery)