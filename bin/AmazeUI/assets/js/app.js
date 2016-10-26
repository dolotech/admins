(function ($) {
    'use strict';
    $(function () {
        var $fullText = $('.admin-fullText');
        $('#admin-fullscreen').on('click', function () {
            $.AMUI.fullscreen.toggle();
        });

        $(document).on($.AMUI.fullscreen.raw.fullscreenchange, function () {
            $fullText.text($.AMUI.fullscreen.isFullscreen ? '退出全屏' : '开启全屏');
        });
    });
})(jQuery);


//获取指定名称的cookie的值
function getcookie(objname) {
    var arrstr = document.cookie.split("; ");
    for (var i = 0; i < arrstr.length; i++) {
        var temp = arrstr[i].split("=");
        if (temp[0] == objname) return unescape(temp[1]);
    }
}
function onlogout() {
    $.Confirm("你要退出系统吗？",function (bool) {
        $.Alert("====")
        if (bool){
            $.post({

                type: "POST",
                url: "/users/logout",
                data: {},
                dataType: "json",
                success: function (data) {
                   // $.Alert(data["msg"])
                //   window.location.href ="/users/login.html"
                }
            });
        }
    })

}