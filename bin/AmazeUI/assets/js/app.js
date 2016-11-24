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

function getHeadBar() {
    return [
        {
            name:  "管理员",
            icon: "am-icon-users",
            items:    [
                {icon:"am-icon-group",path:"/users/group_list.html", name:"用户组管理"},
                {icon:"am-icon-user",path:"/users/list.html", name:"后台用户账号管理"},
                {icon:"am-icon-eye",path:"#", name:"后台操作日志"},
                {icon:"am-icon-edit",path:"/users/edit.html", name:"修改我的密码"}]
        },
        {
            name:  "系统设置",
            icon: "am-icon-bars",
            items:    [
                {icon:"am-icon-user",path:"#", name:"资料"},
                {icon:"am-icon-cog",path:"#", name:"设置"},
                {icon:"am-icon-sign-out",path:"#",action:"javascript:onlogout()", name:"退出"}]
        }
    ]
}


function getSideBar() {
    return [
        {
            name:"玩家管理",
            icon:"am-icon-user",
            items: [
            { icon:"am-icon-table",path:"/roles/list.html",  name:"玩家列表"}

          /*  {icon:"am-icon-table",path:"/roles/listonline.html",name: "在线玩家"},
            {icon:"am-icon-table",path:"/roles/gainrank.html", name:"每日盈利排名"},
            {icon:"am-icon-table",path:"/roles/winrank.html", name:"胜局排名"},
            {icon:"am-icon-table",path:"/roles/coinrank.html", name:"等级排名"},
            {icon:"am-icon-table",path:"/roles/levelrank.html",name: "等级排名"}*/
            ]
        },
        {
            name:  "发放记录",
            icon:"am-icon-drupal",
            items:    [
            {icon:"am-icon-table",path:"/operation/provide.html", name:"道具/钻石发放"},
            {icon:"am-icon-table",path:"/operation/providerecord.html", name:"发放记录"}
/*
            {icon:"am-icon-table",path:"/operation/email.html", name:"发送邮件"},
            {icon:"am-icon-table",path:"/operation/emaillist.html", name:"邮件记录"}*/
            ]
        },
        {
            name: "日志管理",
            icon:"am-icon-eye",
            items:      [
            {icon:"am-icon-table",path:"/operation/privaterecord.html", name:"私人局记录"},
            {icon:"am-icon-table",path:"/operation/matchrecord.html",name: "比赛场记录"},
            {icon:"am-icon-table",path:"/operation/normalrecord.html", name:"金币场记录"},
       /*     {icon:"am-icon-table",path:"/operation/exchangerecord.html", name:"虚拟兑换记录"},
            {icon:"am-icon-table",path:"/operation/exchangerecord.html", name:"实物兑换记录"},*/
            {icon:"am-icon-table",path:"/operation/privatecreate.html", name:"私人房创建日志"},
            {icon:"am-icon-table",path:"/operation/loginrecord.html",name: "登录日志"}]
        },
        {
            name: "订单管理",
            icon:"am-icon-shield",
            items:     [
            {icon:"am-icon-table",path:"/operation/change.html", name:"充值记录"}]
        },
        {
            name: "数据统计",
            icon:"am-icon-file",
            items:     [
                {icon:"am-icon-table",path:"/statistics/online.html",name: "在线统计"},
                {icon:"am-icon-table",path:"/statistics/newuser.html",name: "新增统计"},
                {icon:"am-icon-table",path:"/statistics/remainder.html",name: "留存统计"}
                ]

        }
    ]
}
