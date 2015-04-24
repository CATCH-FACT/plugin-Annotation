if (!Omeka) {
    var Omeka = {};
}

Omeka.AnnotationTypes = {};

(function ($) {
    /**
     * Enable drag and drop sorting for elements.
     */
    Omeka.AnnotationTypes.enableSorting = function () {
        $('.sortable').sortable({
            items: 'li.type-element',
            forcePlaceholderSize: true,
            forceHelperSize: true,
            placeholder: 'ui-sortable-highlight',
            axis: 'y',
            containment: '#annotation-type-elements',
            update: function (event, ui) {
                $(this).find('.type-element-order').each(function (index) {
                    $(this).val(index + 1);
                });
            }
        });
    };

    /**
     * Add link that collapses and expands content.
     */
    Omeka.AnnotationTypes.addHideButtons = function () {
        $('.sortable .drawer-contents').each(function () {
            if( $(this).prev().hasClass("sortable-item") ) {
                $(this).hide();
            }
        });
        $('div.sortable-item').each(function () {
            $(this).append('<div class="drawer"></div>');
        });
        $('.drawer').click( function (event) {
                event.preventDefault();
                $(event.target).parent().next().toggle();
                $(this).toggleClass('opened');
            })
            .mousedown(function (event) {
                event.stopPropagation();
            });
    };

    /**
     * Add AJAX-enabled buttons to item type form for adding and removing elements.
     *
     * @param {string} addNewRequestUrl
     * @param {string} addRequestUrl
     * @param {string} changeElementUrl
     */
    Omeka.AnnotationTypes.manageAnnotationTypes = function (addNewTypeRequestUrl, addTypeRequestUrl, changeTypeElementUrl) {
        /**
         * Activate dropdown for selecting from elements.
         */
        function activateSelectTypeElementDropdowns() {
            $('select.type-element-drop-down').change(function () {
                var dropDown = $(this);
                var typeElementId = dropDown.val();
                var addTypeElementIdPrefix = 'add-type-element-id-';
                var addTypeElementId = this.getAttribute('id');
                if (addTypeElementId) {
                    var typeElementTempId = addTypeElementId.substring(addTypeElementIdPrefix.length);
                    $.ajax({
                        url: changeTypeElementUrl,
                        dataType: 'json',
                        data: {typeElementId: typeElementId, typeElementTempId: typeElementTempId},
                        success: function (response) {
                            var typeElementDescriptionCol = dropDown.parent().next();
                            if(response.typeElementDescription) {
                                typeElementDescriptionCol.html('<div class="type-element-description">' + response.typeElementDescription + '</div>');
                                typeElementDescriptionCol.toggle();
                            } else {
                                typeElementDescriptionCol.hide();
                            }
                        },
                        error: function () {
                            alert('Unable to get selected type-element data.');
                        }
                    });
                }
            });
        }
        
        /**
         * Turn all the links into AJAX requests that will mark the element for deletion and update the list.
         */
        function activateRemoveTypeElementLinks() {

            $(document).on('click', '.delete-element', function (event) {
                event.preventDefault();
                toggleTypeElements(this);
            });
            $('a.type-undo-delete').click( function (event) {
                event.preventDefault();
                toggleTypeElements(this);
            });
        }
        
        function toggleTypeElements(button) {
            var typeElementsToRemove = $('#type-elements_to_remove');
            var removeTypeElementLinkPrefix = 'remove-type-element-link-';
            var removeTypeElementLinkId = button.getAttribute('id');
            if ($(button).hasClass('delete-element')) {
                if (removeTypeElementLinkId !== null) {
                    var typeElementId = removeTypeElementLinkId.substring(removeTypeElementLinkPrefix.length);
                    if (typeElementId) {
                        typeElementsToRemove.attr('value', typeElementsToRemove.attr('value') + typeElementId + ',');
                    }
                    $(button).prevAll('.type-element-order').attr('name', '');
                    $(button).parent().addClass('deleted');
                    $(button).parent().next().addClass('deleted');
                    $(button).prev().toggle();
                    $(button).toggle();
                } else {
                    var row = $(button).parent().parent();
                    row.remove();
                }
            } else {
                if (removeTypeElementLinkId) {
                    var typeElementId = removeTypeElementLinkId.substring(removeTypeElementLinkPrefix.length);
                    $(button).prevAll('.type-element-order').attr('name', 'type-elements[' + typeElementId + '][order]');
                }
                var removeIds = typeElementsToRemove.attr('value').split(',');
                var typeElementId = removeTypeElementLinkId.substring(removeTypeElementLinkPrefix.length);
                var atts = '';
                for(var i=0; i<removeIds.length; i++) {
                    if((removeIds[i] != typeElementId) && removeIds[i] != "") {
                        atts += removeIds[i] + ','; 
                    }
                }
                typeElementsToRemove.attr('value', atts);
                $(button).parent().removeClass('deleted');
                $(button).parent().next().removeClass('deleted');
                $(button).next().toggle();
                $(button).toggle();
            }
        }

        $('#add-type-element').click( function (event) {
            event.preventDefault();
            var elementCount = $('#annotation-type-elements li').length;
            var requestUrl;
            //dig up the selected item-type id, or prevent action if one isn't selected
            var itemTypeId = $('#item_type_id').val();
            if(itemTypeId == '') {
                alert('Please choose an item type above before adding elements.');
                return;
            }
                requestUrl = addTypeRequestUrl;
            $.ajax({
                url: requestUrl,
                dataType: 'text',
                data: {elementCount: elementCount, itemTypeId: itemTypeId},
                success: function (responseText) {
                    var response = responseText || 'no response text';
                    $('#add-type-element').parent().before(response);
                },
                error: function () {
                    alert('Unable to get a new element.');
                }
            });
        });

        activateRemoveTypeElementLinks();
    };
})(jQuery);
