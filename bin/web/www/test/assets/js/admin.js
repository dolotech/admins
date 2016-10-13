function gm_action_do(url, params, width ){
    $("button[id='gm_action_button']").attr("disabled", "disabled");
    $.get(url, params, function(data){
        $("#op-popup-content").html(data);
        // var $modal = $('#op-popup');
        // $modal.modal('close');
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
