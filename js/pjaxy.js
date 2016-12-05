jQuery(document).ready(function() {
    jQuery(document).pjax('a', pjaxy_core.container, {timeout: 80000});
    jQuery(pjaxy_core.container).live('pjax:end', function(){
        if(typeof(pjaxy_page_info) != 'undefined') {
            jQuery('body').attr('class', pjaxy_page_info.body_class);
            jQuery('head title').html(pjaxy_page_info.page_title);
            if(pjaxy_page_info.header_img && pjaxy_core.header) {
                jQuery(pjaxy_core.header).attr('src', pjaxy_page_info.header_img)
                    .attr('width', pjaxy_page_info.header_width)
                    .attr('height', pjaxy_page_info.header_height);
            }
        }
    });
});
