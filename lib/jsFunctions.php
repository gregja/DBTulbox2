<?php

/**
 * Returns JS code to select automatically the first tab of the page
 * @return string JS code
 */
function selectFirstTab() {
    $jscode = <<<JS
    setTimeout(()=>{
        document.getElementById('dynamictabs').querySelector("[data-tab='1']").childNodes[0].click();
    }, 100);
JS;
    return $jscode;
}

function showDifferedTab() {
    $jscode = <<<JS
    const urlParams = new String(document.location.href).split('?');
    jQuery('#dynamictabs a').on('click',function (e) {
        e.preventDefault();
        var url = jQuery(this).attr("data-url");
        if (typeof url !== "undefined") {
            var pane = $(this), href = this.hash;
            url += '?' + urlParams[1];
            // ajax load from data-url
            jQuery(href).load(url,function(result){      
                pane.tab('show');
            });
        } else {
            $(this).tab('show');
        }
    });
JS;
    return $jscode;
}
