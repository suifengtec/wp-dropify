/*
* @Author: suifengtec
* @Date:   2016-09-26 17:56:37
* @Last Modified by:   suifengtec
* @Last Modified time: 2016-09-27 14:55:30
*/

console.log(wpDropifyI10n);

jQuery(document).ready(function($) {


    $('#wp-dropify-upload-trigger').on('click',function(e){

            $('#wp-dropify-popup').bPopup({
                    /*uses jQuery easing plugin*/
                    easing : 'easeOutBack',
                    speed : 450,
                    /*
                    slideIn
                    slideBack
                    slideDown
                    slideUp
                    */
                    transition :'slideDown',
                    closeClass : 'b-close',
                    opacity : 0.6,
                    positionStyle : 'fixed'
            });

    });

    $('#wp-dropify-tip-content').hide();
    console.log( $('.dropify-render img').attr('src') );

	var dropifyUploadFunc = function (){

		console.log('uploadCallback fire!');

        var wpDropifyArgs = {
                action:'wp_dropify_upload_handler',
                nonce: $('.dropify-event').data('nonce'),
                user_id: wpDropifyI10n.user_id,
                data: $('.dropify-render img').attr('src')
            };
            console.log('wpDropifyArgs');
            console.log(wpDropifyArgs);
        $.ajax({
            url : wpDropifyI10n.url,
            type: 'POST',
            dataType: 'json',
            data: wpDropifyArgs,
            success: function( resp ) {
                console.log('resp');
                console.log(resp);
                if ( resp.success===true ) {
                   $('#wp-dropify-img-url').val(resp.data.msg);
                   /*
                   '+resp.data.msg+'
                    */
                   $('#wp-dropify-priview-trigger').show();
                   $('#wp-dropify-tip-content').html('<span style="color:green;padding:10px 0;text-align:center;">封面上传成功!</span>').css('display','block');

                   $('#wp-dropify-uploaded-img').attr('src',resp.data.msg);

                  
                   $('.dropify-wrapper.has-preview .dropify-upload').css('display','none');
                     
                }else{
                     $('#wp-dropify-tip-content').html('<span style="color:red;padding:10px 0;text-align:center;">封面上传失败:'+resp.data.msg+' </span>').css('display','block');
                }
            }
            ,error:function(res){
             window.console.log('ERROR');
             window.console.log(res);
            }

        });
	}

/*==============================================*/
$('#wp-dropify-preview-popup .b-close').hide();
$('#wp-dropify-priview-trigger').on('click',function(e){
e.preventDefault();
            $('#wp-dropify-preview-popup').bPopup({
                    /*uses jQuery easing plugin*/
                    easing : 'easeOutBack',
                    speed : 450,
                    /*
                    slideIn
                    slideBack
                    slideDown
                    slideUp
                    */
                    transition :'slideDown',
                    closeClass : 'b-close',
                    opacity : 0.6,
                    positionStyle : 'fixed'
            });
});

$('#wp-drpoify-close-priview').on('click',function(e){
e.preventDefault();


$('#wp-dropify-preview-popup .b-close').trigger('click');


})

/*==============================================*/
    var wpDropifyEvent = $('.dropify-event').dropify({
        messages: {
            'default': '拖入或点选一个文件',
            'replace': '拖动文件到这里或者点击选择一个文件可以替换这个哦！',
            'remove':  '移除',
            'upload':  '上传',
            'error':   '矮油，出错了，重试一下？'
        },
		callbacks: {
	       	uploadCallback : dropifyUploadFunc
	    }
    });

   wpDropifyEvent.on('dropify.beforeClear', function(event, element){
        /*return confirm("确定要移除 \"" + element.filename + "\" 吗?");*/


        return confirm("确定要移除这个文件吗?");



    });

    wpDropifyEvent.on('dropify.afterClear', function(event, element){

        $('#wp-dropify-tip-content').html('').css('display','none');

        /*
        ajax delete upload file/attachment.
         */
        

    });

    wpDropifyEvent.on('dropify.beforeUpload', function(event, element){

      /*$('#acgfs-cover-upload-btn').trigger('click');*/

        /*return confirm("确定要上传文件吗?");*/

    });
    wpDropifyEvent.on('dropify.afterUpload', function(event, element){
       /*alert('文件上传动作');*/
    });

});