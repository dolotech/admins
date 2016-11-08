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
function add0(m){return m<10?'0'+m:m }
function format(shijianchuo)
{
//shijianchuo是整数，否则要parseInt转换
    var time = new Date(shijianchuo);
    var y = time.getFullYear();
    var m = time.getMonth()+1;
    var d = time.getDate();
    var h = time.getHours();
    var mm = time.getMinutes();
    var s = time.getSeconds();
    return y+'-'+add0(m)+'-'+add0(d)+' '+add0(h)+':'+add0(mm)+':'+add0(s);
}


function ipToNumber(ip) {
    var num = 0;
    if(ip == "") {
        return num;
    }
    var aNum = ip.split(".");
    if(aNum.length != 4) {
        return num;
    }
    num += parseInt(aNum[0]) << 24;
    num += parseInt(aNum[1]) << 16;
    num += parseInt(aNum[2]) << 8;
    num += parseInt(aNum[3]) << 0;
    num = num >>> 0;//这个很关键，不然可能会出现负数的情况
    return num;
}

function numberToIp(number) {
    var ip = "";
    if(number <= 0) {
        return ip;
    }
    var ip3 = (number << 0 ) >>> 24;
    var ip2 = (number << 8 ) >>> 24;
    var ip1 = (number << 16) >>> 24;
    var ip0 = (number << 24) >>> 24

    ip += ip3 + "." + ip2 + "." + ip1 + "." + ip0;

    return ip;
}  