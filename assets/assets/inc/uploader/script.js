function btnUploadClick(el,event){
    $(el).parent().parent().children('.fileupload').click();
    event.preventDefault();
    return true;
}
function changeFileUplod(el,event){
    $(el).parent().submit();
    event.preventDefault();
    return true;
}
function uploadSubmit(el,fId,event){
    event.preventDefault();
    if ($(el).find('.fileupload').val() != '') {
        $(el).addClass('d-none');
        let p=$(el).parent().find('.type').val(),t=$(el).parent().find('.url').val(),formData = new FormData(el);
        $.ajax({
            type:'POST',
            url: '/includes/upload_media/handler/'+t+'/'+p,
            data:formData,
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){
                    myXhr.upload.addEventListener('progress',function (e){
                        if(e.lengthComputable){
                            var max = e.total,current = e.loaded,Percentage = (current * 100)/max;
                            if(Percentage <= 100){
                                Percentage=Math.ceil(Percentage)+'%';
                                $('#progress-'+fId).find(".file-progress-bar").text(Percentage);
                                $('#progress-'+fId).width(Percentage);
                            }
                        } 
                    }, false);
                }
                return myXhr;
            },
            cache:false,
            contentType: false,
            processData: false,
            success:function(data){
                let d = JSON.parse(data);
                if(d.success){
                    let r,u=window.location.origin+'/'+d.url.split('---').join('/');
                    if(d.type=='video'){
                        r='<video class="videoPlay rounded-10" controls><source src="'+ u +'" type="'+d.t+'"></video><a onclick="removeMedia(this);">remove pic</a>';
                    }else{
                        r='<img width="100%" height="100%" src="'+u+'"><a onclick="removeMedia(this);">remove pic</a>';
                    }
                    $('#'+fId).addClass('d-none');
                    $('#'+fId).parent().find('.mediaType').val(d.type);
                    $('#'+fId).parent().find(".file-name").val(d.name);
                    $('#progress-'+fId).addClass('d-none');
                    $('#media-'+fId).html(r);
                    $('#media-'+fId).removeClass('d-none');
                    swal('عملیات موفق', 'عملیات موفق بود', 'success', {button: false}).then(function(){
                        return true;
                    })
                }else{
                    $('#'+fId).removeClass('d-none');
                    $('#progress-'+fId).find(".file-progress-bar").removeClass('background-green');
                    $('#progress-'+fId).find(".file-progress-bar").addClass('background-red');
                    swal('خطای محاسباتی' ,d.error, 'error', {button: false});
                    return true;
                }
            },error: function(data){
                $('#'+fId).removeClass('d-none');
                $('#progress-'+fId).find(".file-progress-bar").removeClass('background-green');
                $('#progress-'+fId).find(".file-progress-bar").addClass('background-red');
                swal('خطای اتصال', 'ارتباط با سرور قطع شده', 'error', {button: false});
                return true;
            }
        });
    }
}