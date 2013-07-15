
function toggleProfileEdit() {
    jQuery('div.annotation-userprofile').toggle();
    jQuery('span.annotation-userprofile-visibility').toggle();
}

function enableAnnotationAjaxForm(url) {
    jQuery(document).ready(function() {
        // Div that will contain the AJAX'ed form.
        var form = jQuery('#annotation-type-form');
        // Select element that controls the AJAX form.
        var annotationType = jQuery('#annotation-type');
        // Elements that should be hidden when there is no type form on the page.
        var elementsToHide = jQuery('#annotation-confirm-submit, #annotation-annotator-metadata');
        // Duration of hide/show animation.
        var duration = 0;

        // Remove the noscript-fallback type submit button.
        jQuery('#submit-type').remove();

        // When the select is changed, AJAX in the type form
        annotationType.change(function () {
            var value = this.value;
            elementsToHide.hide();
            form.hide(duration, function() {
                form.empty();
                if (value != "") {
                    jQuery.post(url, {annotation_type: value}, function(data) {
                       form.append(data); 
                       form.show(duration, function() {
                           form.trigger('annotation-form-shown');
                           elementsToHide.show();
                           //in case profile info is also being added, do the js for that form
                           jQuery(form).trigger('omeka:elementformload');
                           jQuery('.annotation-userprofile-visibility').click(toggleProfileEdit);
                       });
                    });
                }
            });
        });
    });
}

