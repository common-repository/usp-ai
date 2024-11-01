jQuery(document).ready(function(){
    checkGenrateImageFields();
    jQuery('.wp-usp-no-credit').hide();
    jQuery('#wp-media-grid').prepend(`<a class="button wp-usp-media-generate-top" href="?page=generate-usp-ai-images">Generate AI Images</a>`);
    jQuery(document).on('keyup','#wp-usp-description-generate-image',function(){
        checkGenrateImageFields();
    });

    jQuery(document).on('click','.uspai-ai-repetative-content',function(e){
        jQuery('.uspai-ai-repetative-content').removeClass('active');
        jQuery('.uspai-ai-repetative-content').toggle();
        jQuery(this).addClass('active');
        jQuery(".dropdown-content").toggleClass("position-toggle");
        jQuery('.uspai-ai-repetative-content.active').show();
    });

    //Ajax to get populate model
    var apiUrlModels = 'https://api.usp.ai/models';
    jQuery('#wp-usp-image-model-loader').show();
    jQuery.ajax({
            url: ajax_var.ajaxurl,
            type: 'POST',
            dataType: "json",
            data: {action: 'usp_ai_get_model',apiUrlModels:apiUrlModels},
            success: function(response) {
                jQuery('#wp-usp-image-model-loader').show();
                if(response.status==404){
                  jQuery('.wp-usp-api-error').text('Something went wrog contact our support team !!!');
                }else{
                    var uspaiDropdownModel = `<div class="dropdown-model">
                          <label> Model </label>
                          <div class="wp-usp-ai-image dropdown-content">`;
                    jQuery.each(response.data,function (index, value){
                        if(index==0){
                            uspaiDropdownModel += `<div class="uspai-ai-repetative-content active" model-id="`+value.id+`">
                                <div class="usp-ai-col img">
                                    <img src="`+value.coverImage+`" height="50" width="50"><label><b>`+value.name+`</b><br>`+value.description+`</label>
                                </div>
                            </div>`;
                        }else{
                            uspaiDropdownModel += `<div class="uspai-ai-repetative-content" model-id="`+value.id+`">
                                <div class="usp-ai-col img">
                                    <img src="`+value.coverImage+`" height="50" width="50"><label><b>`+value.name+`</b><br>`+value.description+`</label>
                                </div>
                            </div>`;
                        }
                    });
                    uspaiDropdownModel+=`</div>
                        </div>`;
                    jQuery('.wp-usp-image-select.model').html(uspaiDropdownModel);
                }
            }
    });

    // Ajax to generate image
    jQuery(document).on('click','#wp-usp-submit-generate-image', function(e) {
        e.preventDefault();
        jQuery('#wp-usp-image-loader').show();
        jQuery('#wp-usp-image-img-tag').hide();
        jQuery('#wp-usp-submit-generate-image').prop('disabled', true);
        jQuery('#wp-usp-submit-generate-image').removeClass("enable");
        jQuery('#wp-usp-submit-generate-image').addClass("disabled");
        var msg = jQuery('#wp-usp-description-generate-image').val();
        var ratio = jQuery('#wp-usp-ratio-generate-image').val();
        var modelId = jQuery('.uspai-ai-repetative-content.active').attr('model-id');
        var apiUrlPrompt = 'https://api.usp.ai/prompt';
        var apiUrlCredit = 'https://api.usp.ai/credits';
        var alt = (msg+(jQuery('#usp-ai-alt-text').val()!=''?'-'+jQuery('#usp-ai-alt-text').val():'')).toLowerCase().split(' ').join('-');
        jQuery.ajax({
            url: ajax_var.ajaxurl,
            type: 'POST',
            dataType: "json",
            data: {action: 'usp_ai_get_image',apiUrl:apiUrlPrompt,apiUrlCredit:apiUrlCredit,msg:msg,ratio:ratio,model_id:modelId},
            success: function(response) {
                jQuery('#wp-usp-image-loader').hide();
                jQuery('#wp-usp-submit-generate-image').prop('disabled', false);
                jQuery('#wp-usp-submit-generate-image').removeClass("disabled");
                jQuery('#wp-usp-submit-generate-image').addClass("enable");
                if(response.status==404){
                    if(response.credit == 0){
                        jQuery('.credit-no').text(response.credit);
                        jQuery('.overlay').addClass('opened');
                        jQuery('.wp-usp-no-credit').addClass('modal-opened');
                        jQuery('.wp-usp-no-credit').css('display', 'flex');
                    }else{
                      jQuery('.wp-usp-api-error').text('Please add valid api username or key at USP Setting!!!');
                    }
                }else{
                    jQuery('#wp-usp-generated-image').addClass(ratio);
                    jQuery('#wp-usp-generated-image').html('<img class="'+ratio+'" id="wp-usp-image-img-tag" src="'+response.imgUrl+'" alt="'+alt+'">');
                    jQuery('.wp-usp-msg').html('<span>'+msg+'</span>');
                    jQuery('.credit-no').text(response.credit);
                    jQuery('.wp-usp-clear').show();
                    jQuery('.credit').show();
                    jQuery('.wp-usp-submit').text('Rerun');
                    jQuery('.wp-usp-submit').addClass('rerun');
                }
            }
        });
    });

    // Ajax to add settings
    jQuery(document).on('click','#wp-usp-setting-submit', function(e) {
        e.preventDefault();
        jQuery('#wp-usp-image-loader').show();
        var email = jQuery('#wp-usp-setting-email-field').val();
        var key = jQuery('#wp-usp-setting-key-field').val();
        var apiUrl = 'https://api.usp.ai/credits';
        jQuery.ajax({
            url: ajax_var.ajaxurl,
            type: 'POST',
            dataType: "json",
            data: {action: 'usp_ai_set_setting',apiUrl:apiUrl,email:email,key:key},
            success: function(response) {
                jQuery('#wp-usp-image-loader').hide();
                console.log(response.status,'response.status');
                if(response.status==404||response.status==500||response.status==300){
                    if(response.status==404){
                        jQuery('.wp-usp-api-error').text('Please add valid api username or key at USP Setting!!!');
                    }else if(response.status==300){
                        jQuery('.wp-usp-api-error').text('Please fill email or key at USP Setting!!!');
                    }else{
                        jQuery('.wp-usp-api-error').text('Something went wrong');
                    }
                    jQuery('.wp-usp-api-error').removeClass('text-green');
                    jQuery('.wp-usp-api-error').addClass('text-red');
                }else{
                    console.log(response.status,'response.status');
                    jQuery('.wp-usp-api-error').text('USP API Setting added successfully');
                    jQuery('.wp-usp-api-error').removeClass('text-red');
                    jQuery('.wp-usp-api-error').addClass('text-green');
                }
            }
        });
    });

    // Ajax to upload image
    jQuery(document).on('click','#wp-usp-generate-use-image', function(e) {
        e.preventDefault();
        jQuery('#wp-usp-image-loader').show();
        var src = jQuery('#wp-usp-image-img-tag').attr('src');
        var alt = jQuery('#wp-usp-image-img-tag').attr('alt');
        jQuery.ajax({
            url: ajax_var.ajaxurl,
            type: 'POST',
            dataType: "json",
            data: {action: 'usp_ai_upload_image',src:src,alt:alt},
            success: function(response) {
                jQuery('#wp-usp-image-loader').hide();
                if(response.status==200){
                    window.location.href = 'upload.php?item='+response.attachment_id;
                }else{
                    jQuery('.wp-usp-api-error').text('Invalid Image');
                    jQuery('.wp-usp-api-error').removeClass('text-green');
                    jQuery('.wp-usp-api-error').addClass('text-red');
                }
            }
        });
    });

    jQuery(document).on('click','.close', function(e) {
        jQuery('.overlay').removeClass('opened');
        jQuery('.wp-usp-no-credit').removeClass('modal-opened');
        jQuery('.wp-usp-no-credit').hide();
    });
    jQuery(document).on('click','#wp-usp-ratio-get-it-link', function(e) {
        jQuery('.overlay').removeClass('opened');
        jQuery('.wp-usp-no-credit').removeClass('modal-opened');
        jQuery('.wp-usp-no-credit').hide();
    });
});

function checkGenrateImageFields(){
    var msg = jQuery('#wp-usp-description-generate-image').val();
    if(msg==''){
        jQuery('#wp-usp-submit-generate-image').prop('disabled', true);
        jQuery('#wp-usp-submit-generate-image').removeClass("enable");
        jQuery('#wp-usp-submit-generate-image').addClass("disabled");
    }else{
        jQuery('#wp-usp-submit-generate-image').prop('disabled', false);
        jQuery('#wp-usp-submit-generate-image').removeClass("disabled");
        jQuery('#wp-usp-submit-generate-image').addClass("enable");
    }
}

