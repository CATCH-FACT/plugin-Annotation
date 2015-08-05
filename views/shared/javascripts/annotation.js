function setUpSettingsWysiwyg() {
    jQuery(window).load(function() {
        Omeka.wysiwyg({
           mode: "specific_textareas",
           editor_selector: "html-editor",
           forced_root_block: ""
        });
    });
}


Omeka.textfieldScroll = function () {
    var $save   = $("#save"),
        $window = $(window),
        offset  = $save.offset(),
        topPadding = 62,
        $contentDiv = $("#content");
    if (document.getElementById("save")) {
        $window.scroll(function () {
            if($window.scrollTop() > offset.top && $window.width() > 767 && ($window.height() - topPadding - 85) >  $save.height()) {
                $save.stop().animate({
                    marginTop: $window.scrollTop() - offset.top + topPadding
                    });
            } else {
                $save.stop().animate({
                    marginTop: 0
                });
            }
        });
    }
};