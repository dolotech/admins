/*!
 * dtg.dialog.js ~ Copyright (c) 2014 dericktang, https://github.com/dericktang/dtg.dialog.js
 * Released under MIT license
 */
(function($) {
    var options = {
		okBtn: '&nbsp;确定&nbsp;',
		cancelBtn: '&nbsp;取消&nbsp;',
		isUsedDefaultStyle: false,
		isStyle:''
	};
	
	$.dtggetUUID = function(){
	   var date = new Date();
	   var uuid = date.getTime();
	   return uuid;
	};
	
	var uuid = $.dtggetUUID();
	
	var defaultStyle = "<style>.dialog"+uuid+"{position:fixed;box-shadow:0px 2px 0px 2px #ccc;border:1px solid #007aff;left:35%;top: 35%;text-align:center;z-index:999999;background:white;color:#595959;border-radius:5px;width:auto;width:30%}.dialog"+uuid+"  p{line-height:44px;}.dialog"+uuid+"  h1{margin:0;font-size:16px;text-align:left;padding:8px 8px 8px 8px;background: #007aff;border-top-left-radius:4px;border-top-right-radius:4px;color:white}.dialog"+uuid+" input{width:40%;margin: 8px;margin-top:0px;background:#007aff;border:0;color:white;border-radius:4px;line-height:22px;height:22px;}</style>";
	
    function DtgDialog(style){
	   if( style == null ) {
	      if(!options.isUsedDefaultStyle)
	         $("head").prepend(defaultStyle);
	   }else{
	      options.isStyle = style;
	   }
	};
	
	DtgDialog.prototype.alert = function(message, title){
       show(message, title,'alert',false);
    };
	
	DtgDialog.prototype.confirm = function(message, callback, title){
       show(message, title,'confirm',callback);
	};
	
	var show = function (msg ,title, target ,callback){
           if( title == null ) title = '提示';
	   if( msg == null ) msg = '';
	   var uid = $.dtggetUUID();
	   var html =  '<div id="dialog'+uid+'" class="dialog'+uuid+' '+options.isStyle+'">' +
			       '<h1>'+title.replace(/\n/g, '<br />')+'</h1>' +
			       '<p>' +msg.replace(/\n/g, '<br />') + '</p>' ;
				   
			       
	   if(target=='alert'){
		 html += '<input type="button" value="' + options.okBtn + '" id="dialog_ok'+uid+'" />';
	   }
	   if(target=='confirm'){
		 html += '<input type="button" value="' + options.okBtn + '" id="dialog_ok'+uid+'" />'+
		         '<input type="button" value="' + options.cancelBtn + '" id="dialog_cancel'+uid+'" />';
	   }
	   html += '</div>';
	   $("BODY").append(html);
	   $("BODY").append('<div id="dialog_overlay'+uid+'"></div>');
					$("#dialog_overlay"+uid).css({
						position: 'absolute',
						zIndex: 999998,
						top: '0px',
						left: '0px',
						width: '100%',
						height: $(document).height()
					});
	   $("#dialog_cancel"+uid).click( function() {
	   	   hidden(uid);
		   if( callback ) callback(false);
	   });
	   $("#dialog_ok"+uid).click( function() {
	   	   hidden(uid);
		   if( callback ) callback(true);
	   });
	   $("#dialog_ok"+uid).focus().keypress( function(e) {
	   	if( e.keyCode == 13 || e.keyCode == 27 ) $("#dialog_ok"+uid).trigger('click');
		if( callback ) callback(true);
	   });
	   $("#dialog_cancel"+uid).focus().keypress( function(e) {
	   	if( e.keyCode == 13 || e.keyCode == 27 ) $("#dialog_cancel"+uid).trigger('click');
		if( callback ) callback(false);
	   });
	};
	
	var hidden = function(uid){
	  $("#dialog"+uid).remove();
	  $("#dialog_overlay"+uid).remove();
	};

	$.Alert = function(message, title, style) {
	    var al;
    	if( style == null ) {
		  al = new DtgDialog();
		}else{
		  al = new DtgDialog(style);
		}
		al.alert(message, title);
	};
	
	$.Confirm = function(message, callback, title, style){
	    var cf;
    	if( style == null ) {
		  cf = new DtgDialog();
		}else{
		  cf = new DtgDialog(style);
		}
		cf.confirm(message, callback, title);
	};
})($);
