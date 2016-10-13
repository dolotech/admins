function gm_action_do(url, params, width ){
    $("button[id='gm_action_button']").attr("disabled", "disabled");
    $.get(url, params, function(data){
        $("#op-popup-content").html(data);
    });
}

function windowOpen(url, target){
    var a = document.createElement("a");
    a.setAttribute("href", url);
    if(target == null){
        target = '';
    }
    a.setAttribute("target", target);
    document.body.appendChild(a);
    if(a.click){
        a.click();
    }else{
        try{
            var evt = document.createEvent('Event');
            a.initEvent('click', true, true);
            a.dispatchEvent(evt);
        }catch(e){
            window.open(url);
        }
    }
    document.body.removeChild(a);
}

function getGmLinks(options, args){
    var links = '';
    for(var key in options){  
        var opt = options[key];
        if(opt.title == "-"){
            links += "<hr />";
            continue;
        }
        var data = eval("("+opt.data+")");
        var requestArgs = data.args;
        var url = opt.url;
        for(var ak in requestArgs){  
            var requestKey = requestArgs[ak];
            if("undefined"==typeof(args[requestKey])){  
                url = '';
                break;  
            }else{
                url += '&' + requestKey + '=' + args[requestKey];
            } 
        }
        if(url != ''){
            if(opt.target == 'dialog'){
                links += ' <button type="button" data-url="'+url+'" class="am-btn am-btn-xs am-text-primary am-round am-margin-xs op-modal-open-js">'+opt.title+'</button>';
            }else if(opt.target == 'page'){
                links += ' <button type="button" data-url="'+url+'" class="am-btn am-btn-xs am-text-primary am-round am-margin-xs op-page-js">'+opt.title+'</button>';
            }else if(opt.target == 'new_page'){
                links += ' <button type="button" data-url="'+url+'" class="am-btn am-btn-xs am-text-primary am-round am-margin-xs op-new-page-js">'+opt.title+'</button>';
            }
        }
    }
    return links;
}

function more(obj){
    var tdsObj = $(obj).parent().parent().children();
    var content = '';
    $("#gm-table thead tr th").each(function(i, e){
        var title = $(e).text();
        if(title != '' && i != (tdsObj.length - 1)){
            var text = tdsObj.eq(i).text().trim();
            content += '<strong><small>'+title+'</small></strong>：';
            if(text.length > 20){
                content += '<br />';
            }
            content += '<small>'+tdsObj.eq(i).text()+'</small>';
            content += '<br />';
        }
    });
    $("#op-name").html('详情<hr />');
    $("#op-offcanvas-content").html(content);
    $('#op-offcanvas').offCanvas('open');
}

