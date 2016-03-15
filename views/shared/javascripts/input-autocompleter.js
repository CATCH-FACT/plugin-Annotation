function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds) {
            break;
        }
    }
}


// Parse an item and create an title/value hash table with all the properties available
function getFields(results) {
    if (results.value == undefined) {
        return results;
    }
    else {
        return results.value;
    }
}

// Parse an item and create an title/value hash table with all the properties available
function addIdValue(results) {
    return results.value;
}

function autoMultiInternal(element, collection, url) {
}


function autoMultiAdd(element, url) {
    jQuery(element).autocomplete({
        minLength: 1,
        source: function(request, response) {
            jQuery.ajax({
                beforeSend: function(request)
                {
                    request.setRequestHeader("Accept", "application/json;odata=verbose;charset=utf-8");
                },
                url: url + request.term,
                dataType: "json",
                success: function(data) {
                    response(jQuery.map(data.data,
                        function(item) {
                            return {
                                fields: getFields(item)
                            }
                        }
                    ));
                },
                error: function(data) {
                    return "None found";
                }
            });
        },
        focus: function(event, ui) {
            jQuery(element).val(ui.item.fields.id);
            return false;
        },
        // Run this when the item is selected
        select: function(event, ui) {
            jQuery(element).val(ui.item.fields.id);
            return false;
        },
        appendTo: jQuery('#menu-container')
    }).data("ui-autocomplete")._renderItem = function(ul, item) {
        return jQuery("<li>").append("<p style='color:red'>" + item.fields.id + "</p>")
        .appendTo(ul);
    };
};

function autoMultiExternal(element, url) {
    jQuery(element).autocomplete({
        minLength: 1,
        source: function(request, response) {
            jQuery.ajax({
                beforeSend: function(request)
                {
                    request.setRequestHeader("Accept", "application/json;odata=verbose;charset=utf-8");
                },
                url: url + request.term,
                dataType: "json",
                success: function(data) {
                    response(jQuery.map(data.data,
                    function(item) {
                        return {
                            fields: getFields(item)
                        }
                    }));
                },
                error: function(data) {
                    //   console.log(data);
                    return "None found";
                }
            });
        },
        focus: function(event, ui) {
            jQuery(element).val(ui.item.fields.id);
            return false;
        },
        // Run this when the item is selected
        select: function(event, ui) {
            jQuery(element).val(ui.item.fields.id);
            return false;
        },
        appendTo: jQuery('#menu-container')
    }).data("ui-autocomplete")._renderItem = function(ul, item) {
        if (jQuery(element)[0].value.toUpperCase() == item.fields.id) {
            return jQuery("<li style='color:green'>").append("<a>" + item.fields.id + (item.fields.title ? " - " + item.fields.title : "")  + "</a>")
            .appendTo(ul);
        }
        else {
            return jQuery("<li>").append("<a>" + item.fields.id + (item.fields.title ? " - " + item.fields.title : "") + "</a>")
            .appendTo(ul);
        }
    };
};

function autocompleteAdd(elementNumber, url) {
    jQuery(".container").on("click", "#element-" + elementNumber + " textarea",
    function() {
        jQuery("textarea[id^=Elements-" + elementNumber + "]").each(function(index, el) {
            autoMultiAdd(jQuery(el), url);
        });
    });
};

function autocompleteExternal(elementNumber, url) {
    jQuery(".container").on("click", "#element-" + elementNumber + " textarea",
    function() {
        jQuery("textarea[id^=Elements-" + elementNumber + "]").each(function(index, el) {
            autoMultiExternal(jQuery(el), url);
        });
    });
};

function dateTranslator(elementNumber) {
    jQuery(".container").on("click", "#element-" + elementNumber + " textarea",
    function() {
        jQuery("textarea[id^=Elements-" + elementNumber + "]").each(function(index, el) {
            jQuery(el)._renderItem = function(div, item) {
                return jQuery("<li style='color:green'>").append("<p>" + item.fields.id + " - " + item.fields.title + "</p>")
                .appendTo(div);
            };
        });
    });
}

/*jQuery(document).ready(function() {
    console.log("autocompleter added");
    server = "http://bookstore.ewi.utwente.nl/afact/";
//    server = "http://127.0.0.1/testing/resting/";
    autocompleteAdd(     "43", server + "identifier.json?style=list&code=");        //identificatie code helper
    autocompleteExternal("52", server + "motif.json?style=list&code=");             //motif helper
    autocompleteExternal("49", server + "folktaletype.json?style=list&search=");    //folktaletype helper
    dateTranslator("40");                           //date helper
    autocompleteExternal("39", server + "creator.json?style=list&search=");         //verteller(creator) helper
    autocompleteExternal("60", server + "collector.json?style=list&search=");       //verzamelaar helper
    //medewerker helper
    autocompleteExternal("93", server + "namedentities_location.json?style=list&search=");   //Named entities PLACES helper
    autocompleteExternal("63", server + "namedentities_other.json?style=list&search=");     //Named entities OTHER helper
    autocompleteExternal("65", server + "place_of_action.json?style=list&search=");         //plaats van handelen helper
    autocompleteExternal("67", server + "corpus.json?style=list&search=");         //CORPUS helper
    //kloekehelper??
    
});*/