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
        if (bool){
            $.post({

                type: "POST",
                url: "/users/logout",
                data: {},
                dataType: "json",
                success: function (data) {
                   location.href ="/users/login.html"
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

//调用方法 如
//post('pages/statisticsJsp/excel.action', {html :prnhtml,cm1:'sdsddsd',cm2:'haha'});
function post(URL, PARAMS) {
    var temp = document.createElement("form");
    temp.action = URL;
    temp.method = "post";
    temp.style.display = "none";
    for (var x in PARAMS) {
        var opt = document.createElement("textarea");
        opt.name = x;
        opt.value = PARAMS[x];
        // alert(opt.name)
        temp.appendChild(opt);
    }
    document.body.appendChild(temp);
    temp.submit();
    return temp;
}


var MyComponent = Vue.extend({
    template : '<div class="admin-sidebar am-offcanvas" id="admin-offcanvas">'+
    '  <div class="am-offcanvas-bar admin-offcanvas-bar">'+
    '<ul class="am-list admin-sidebar-list">'+
    ' <li><a href="admin-index.html"><span class="am-icon-home"></span> 首页</a></li>'+
    ' <li class="admin-parent">'+
    ' <a class="am-cf" data-am-collapse="{target: '+"'#collapse-nav'" +'}"><span class="am-icon-file"></span> 页面模块 <span class="am-icon-angle-right am-fr am-margin-right"></span></a>'+
' <ul class="am-list am-collapse admin-sidebar-sub am-in" id="collapse-nav">'+
' <li><a href="/roles/list.html" class="am-cf"><span class="am-icon-check"></span> 个人资料<span class="am-icon-star am-fr am-margin-right admin-icon-yellow"></span></a></li>'+
'  <li><a href="/roles/listonline.html"><span class="am-icon-puzzle-piece"></span> 帮助页</a></li>'+
'  <li><a href="admin-gallery.html"><span class="am-icon-th"></span> 相册页面<span class="am-badge am-badge-secondary am-margin-right am-fr">24</span></a></li>'+
 '<li><a href="admin-log.html"><span class="am-icon-calendar"></span> 系统日志</a></li>'+
'   <li><a href="admin-404.html"><span class="am-icon-bug"></span> 404</a></li>'+
'   </ul>'+
'  </li>'+
'   <li><a href="admin-table.html"><span class="am-icon-table"></span> 表格</a></li>'+
'  <li><a href="admin-form.html"><span class="am-icon-pencil-square-o"></span> 表单</a></li>'+
'  <li><a href="#"><span class="am-icon-sign-out"></span> 注销</a></li>'+
'   </ul>'+

'   <div class="am-panel am-panel-default admin-sidebar-panel">'+
'   <div class="am-panel-bd">'+
'  <p><span class="am-icon-bookmark"></span> 公告</p>'+
'  <p>时光静好，与君语；细水流年，与君同。—— Amaze UI</p>'+
'</div>'+
'</div>'+

'<div class="am-panel am-panel-default admin-sidebar-panel">'+
'  <div class="am-panel-bd">'+
' <p><span class="am-icon-tag"></span> wiki</p>'+
' <p>Welcome to the Amaze UI wiki!</p>'+
'</div>'+
'</div>'+
'</div>'+
'</div>'
})

Vue.component('my-component',  MyComponent)