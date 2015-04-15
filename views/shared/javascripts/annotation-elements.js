if (!Omeka) {
    console.log("no omeka js loaded..");
    var Omeka = {};
}

Omeka.Elements = {};

(function ($) {
    /**
     * Send an AJAX request to update a <div class="field"> that contains all
     * the form inputs for an element.
     *
     * @param {jQuery} fieldDiv
     * @param {Object} params Parameters to pass to AJAX URL.
     * @param {string} elementFormPartialUri AJAX URL.
     * @param {string} recordType Current record type.
     * @param {string} recordId Current record ID.
     */
    Omeka.Elements.elementFormRequest = function (fieldDiv, params, elementFormPartialUri, recordType, recordId, model) {
        var elementId = fieldDiv.attr('id').replace(/element-/, '');
        
        fieldDiv.find('input, textarea, select').each(function () {
            var element = $(this);
            // Workaround for annoying jQuery treatment of checkboxes.
            if (element.is('[type=checkbox]')) {
                params[this.name] = element.is(':checked') ? '1' : '0';
            } else {
                // Make sure TinyMCE saves to the textarea before we read
                // from it
                if (element.is('textarea')) {
                    var mce = tinyMCE.get(this.id);
                    if (mce) {
                        mce.save();
                    }
                }
                params[this.name] = element.val();
            }
        });
        
        recordId = typeof recordId !== 'undefined' ? recordId : 0;
        
        params.element_id = elementId;
        params.record_id = recordId;
        params.record_type = recordType;

        $.ajax({
            url: elementFormPartialUri,
            type: 'POST',
            dataType: 'html',
            data: params, //parameters sent as GET parameters
            success: function (response) {
                console.log("AJAX CALL response");
                console.log(params);
                console.log(response);
                fieldDiv.find('textarea').each(function () {
                    tinyMCE.execCommand('mceRemoveControl', false, this.id);
                });
                fieldDiv.html(response);
                fieldDiv.trigger('omeka:elementformload');
            }
        });
    };


    /**
     * Send an AJAX request to update a <div class="field"> that contains all
     * the form inputs for an element.
     *
     * @param {jQuery} fieldDiv
     * @param {Object} params Parameters to pass to AJAX URL.
     * @param {string} elementFormPartialUri AJAX URL.
     * @param {string} recordType Current record type.
     * @param {string} recordId Current record ID.
     */
    Omeka.Elements.elementFormFillRequest = function (fieldDiv, params, elementFormPartialUri, elementFormPartialUriTool, allFields, recordType, recordId, model) {
        
        var annotationValues = [""];
        var elementId = fieldDiv.attr('id').replace(/element-/, '');
//        var toolId = fieldDiv.attr('tool-id');
        recordId = typeof recordId !== 'undefined' ? recordId : 0;
        
        params.element_id = elementId;
        params.record_id = recordId;
        params.record_type = recordType;

        console.log("fillrequest");
        console.log(elementFormPartialUrlTool);
        console.log(fieldDiv);

        for (var i = 0; i < annotationValues.length; i++) {
            params["Elements[" + elementId + "][" + i + "][text]"] = annotationValues[i];
        }
        
        //fetch the necesary tool information
        $.ajax({
            url: elementFormPartialUriTool,
            type: 'POST',
            dataType: 'json',
            data: params,
//            data: {tool_id: toolId}, //nodig?
            success: function (toolResponse) {
//                console.log("TOOL AJAX CALL tool succes");
//                console.log(toolResponse);
//                console.log(toolResponse.command);
//                console.log(toolResponse.post_arguments);
                
                if (toolResponse.post_arguments != ""){
                    var post_arguments = JSON.parse(toolResponse.post_arguments);
                    for (var attrname in post_arguments) { allFields[attrname] = JSON.stringify(post_arguments[attrname]); }
                }
                //if succesfull: use tool to fetch annotation data
                $.ajax({
                    url: toolResponse.command,
                    type: 'POST',
                    dataType: toolResponse.output_format,
                    data: allFields,
                    success: function (response) {
                        if (response["status"] == "ERROR"){
                            alert('Error in response from server: ' + response["message"]);
                            return;
                        }
                        //dig to the right leaf                        
                        var jsonxml_value_node = toolResponse.jsonxml_value_node.split(".");
                        var node = response;
                        for (var i = 0; i < jsonxml_value_node.length; i++) {
                            node = node[jsonxml_value_node[i]];
                        }
//                        console.log(node);
                        
                        //make separate fields when the returned response node is an array
                        //but when slidebar and idx: order set and concat based on score
                        if( Object.prototype.toString.call( node ) === '[object Array]' ) {
                            if (toolResponse.jsonxml_idx_sub_node in node[0]){ //idx nodes present
                                var sliders = jQuery("#slide_element_41");
                                var slider_value = jQuery("#span_element_" + elementId).text();
                                
                                node.sort(function(a,b){return b.score - a.score;});
                                
                                numsentences = Math.ceil(node.length * slider_value / 100);
                                
                                var sentences = node.splice(0, numsentences);

                                sentences.sort(function(a,b){return a.idx - b.idx;});
                                
                                for (var i = 0; i < sentences.length; i++) {
                                    if (toolResponse.jsonxml_idx_sub_node in sentences[i]){ //concatenate to one field (based on set score / amount sentences)
                                        params["Elements[" + elementId + "][" + 0 + "][text]"] += sentences[i][toolResponse.jsonxml_value_sub_node] + " ";
                                    }
                                }
                            }
                            else{ //no idx nodes present
                                for (var i = 0; i < node.length; i++) {
                                    params["Elements[" + elementId + "][" + i + "][text]"] = node[i];
                                }
                            }
                        }
//                        if( typeof node === 'string' ) {
                        else{ //string, int, float (single)
                            params["Elements[" + elementId + "][" + 0 + "][text]"] = node;
                        }
                        
                        $.ajax({
                            url: elementFormPartialUri,
                            type: 'POST',
                            dataType: 'html',
                            data: params,
                            success: function (response) {
                                console.log("AJAX CALL response: element fill");
                                fieldDiv.find('textarea').each(function () {
                                    tinyMCE.execCommand('mceRemoveControl', false, this.id);
                                });
                                fieldDiv.html(response);
                                fieldDiv.trigger('omeka:elementformload');
                            }
                        });
                    }
                });
            }
        });
    };


    /**
     * Set up add/remove element buttons for ElementText inputs.
     *
     * @param {Element} element The element to search at and below.
     * @param {string} elementFormPartialUrl AJAX URL for form inputs.
     * @param {string} recordType Current record type.
     * @param {string} recordId Current record ID.
     */
    Omeka.Elements.makeElementControls = function (element, elementFormPartialUrl, recordType, recordId, model) {
        console.log("element control turned on");
//        console.log(element);               //<- whole document
//        console.log(elementFormPartialUrl); //what element-form to load
//        console.log(recordType);            //to load an Item? / File? / collection?
//        console.log(recordId);              //
        var annotationSelector = '.annotate-element';
        var addSelector = '.add-element';
        var removeSelector = '.remove-element';
        var fieldSelector = 'div.field';
        var inputBlockSelector = 'div.input-block';
        var context = $(element);
        var fields;

//        console.log(context);

        if (context.is(fieldSelector)) {
            fields = context;
        } else {
            fields = context.find(fieldSelector);
        }

        // Show remove buttons for fields with 2 or more inputs.
        fields.each(function () {
            var removeButtons = $(this).find(removeSelector);
            if (removeButtons.length > 1) {
                removeButtons.show();
            } else {
                removeButtons.hide();
            }
        });

        //get all date pickers
        var daterangepickers = jQuery(".date_range_picker");
        var datepickers = jQuery(".date_picker");

        daterangepickers.each(function() {

            $(this).dateRangePicker(
        	{
        		language:'custom',
                format: 'YYYY-MM-DD',
            	separator: ' ',
        		startOfWeek: 'monday',
        		shortcuts : 
        		{
        			'prev-days': null,
        			'prev': null,
        			'next-days':null,
        			'next':null
        		},
        		customShortcuts: 
        		[
        			//if return an array of two dates, it will select the date range between the two dates
        			{
        				name: 'vandaag',
        				dates : function()
        				{
        					var start = moment().toDate();
        					var end = moment().toDate();
        					return [start, end];
        				}
        			},
        			{
        				name: '13e',
        				dates : function()
        				{
        					var start = moment('1201-01-01').toDate();
        					var end = moment("1300-12-31").toDate();
        					// start.setDate(1);
        					// end.setDate(30);
        					return [start,end];
        				}
        			},
        			{
        				name: '14e',
        				dates : function()
        				{
        					var start = moment('1301-01-01').toDate();
        					var end = moment("1400-12-31").toDate();
        					// start.setDate(1);
        					// end.setDate(30);
        					return [start,end];
        				}
        			},
        			{
        				name: '15e',
        				dates : function()
        				{
        					var start = moment('1401-01-01').toDate();
        					var end = moment("1500-12-31").toDate();
        					// start.setDate(1);
        					// end.setDate(30);
        					return [start,end];
        				}
        			},
        			{
        				name: '16e',
        				dates : function()
        				{
        					var start = moment('1501-01-01').toDate();
        					var end = moment("1600-12-31").toDate();
        					return [start,end];
        				}
        			},
        			{
        				name: '17e',
        				dates : function()
        				{
        					var start = moment('1601-01-01').toDate();
        					var end = moment("1700-12-31").toDate();
        					return [start,end];
        				}
        			},
        			{
        				name: '18e',
        				dates : function()
        				{
        					var start = moment('1701-01-01').toDate();
        					var end = moment("1800-12-31").toDate();
        					return [start,end];
        				}
        			},
        			{
        				name: '19e',
        				dates : function()
        				{
        					var start = moment('1801-01-01').toDate();
        					var end = moment("1900-12-31").toDate();
        					return [start,end];
        				}
        			},
        			{
        				name: '20e',
        				dates : function()
        				{
        					var start = moment('1901-01-01').toDate();
        					var end = moment("2000-12-31").toDate();
        					return [start,end];
        				}
        			}
    		    ]
        	});
        });
        
        datepickers.each(function() {

            $(this).dateRangePicker(
        	{
        //	    autoClose: true, when dates are selected
        		language:'custom',
                format: 'YYYY-MM-DD',
            	separator: ' ',
            	singleDate : true,
        		startOfWeek: 'monday',
        		shortcuts : 
        		{
        			'prev-days': null,
        			'prev': null,
        			'next-days':null,
        			'next':null
        		},
        		customShortcuts: 
        		[
        			//if return an array of two dates, it will select the date range between the two dates
        			{
        				name: 'vandaag',
        				dates : function()
        				{
        					var start = moment().toDate();
        					var end = moment().toDate();
        					return [start, end];
        				}
        			},
        			{
        				name: '13e',
        				dates : function()
        				{
        					var start = moment('1201-01-01').toDate();
        					var end = moment("1300-12-31").toDate();
        					// start.setDate(1);
        					// end.setDate(30);
        					return [start,end];
        				}
        			},
        			{
        				name: '14e',
        				dates : function()
        				{
        					var start = moment('1301-01-01').toDate();
        					var end = moment("1400-12-31").toDate();
        					// start.setDate(1);
        					// end.setDate(30);
        					return [start,end];
        				}
        			},
        			{
        				name: '15e',
        				dates : function()
        				{
        					var start = moment('1401-01-01').toDate();
        					var end = moment("1500-12-31").toDate();
        					// start.setDate(1);
        					// end.setDate(30);
        					return [start,end];
        				}
        			},
        			{
        				name: '16e',
        				dates : function()
        				{
        					var start = moment('1501-01-01').toDate();
        					var end = moment("1600-12-31").toDate();
        					return [start,end];
        				}
        			},
        			{
        				name: '17e',
        				dates : function()
        				{
        					var start = moment('1601-01-01').toDate();
        					var end = moment("1700-12-31").toDate();
        					return [start,end];
        				}
        			},
        			{
        				name: '18e',
        				dates : function()
        				{
        					var start = moment('1701-01-01').toDate();
        					var end = moment("1800-12-31").toDate();
        					return [start,end];
        				}
        			},
        			{
        				name: '19e',
        				dates : function()
        				{
        					var start = moment('1801-01-01').toDate();
        					var end = moment("1900-12-31").toDate();
        					return [start,end];
        				}
        			},
        			{
        				name: '20e',
        				dates : function()
        				{
        					var start = moment('1901-01-01').toDate();
        					var end = moment("2000-12-31").toDate();
        					return [start,end];
        				}
        			}
    		    ]
        	});
        });
        
        //get all sliders
        var sliders = jQuery(".slider");
        //set each slider again (based on model)
        sliders.each(function() {
            var value = parseInt($(this).text(), 10), availableTotal = 400;

            //retrieve the slider id
            var fieldDiv = $(this).parents(fieldSelector);
            var elementId = fieldDiv.attr('id').replace(/element-/, '');

            $(this).siblings(".slidervalue").text(model.slider_values()[elementId]);

            $(this).empty().slider({
                value: model.slider_values()[elementId],
                min: 0,
                max: 100,
                range: "max",
                step: 5,
                animate: 100,
                slide: function(event, ui) {
                    // Update display to current value
                    //set slider value in model
                    slider_array = model.slider_values();
                    slider_array[elementId] = ui.value;
                    model.slider_values(slider_array);
                    jQuery(this).siblings(".slidervalue").text(ui.value);
                }
            });
        });

        // When a generate metadata button is clicked, make an AJAX request based on the specified toolhat is connected to the field.
        context.find(annotationSelector).click(function (event) {
            event.preventDefault(); 
//            annotationValues = ["ja", "nee"];
            var fieldDiv = $(this).parents(fieldSelector);
            annotationSelector = '.annotate-element';

            //fetch the data from the form to turn into parameter data
            var allFields = {};
            $(".textinput").each(function(i, fld){
                if ($(fld).val()){
                    allFields[$(fld).attr("element-name").toLowerCase()] = $(fld).val();
                }
            })

            elementFormPartialUrlNoadd = elementFormPartialUrl + "-noadd";
            elementFormPartialUrlTool = elementFormPartialUrl + "-tool";
            //we need the whole document (to send data values to the webapps)
            Omeka.Elements.elementFormFillRequest(fieldDiv, {add: '1'}, elementFormPartialUrlNoadd, elementFormPartialUrlTool, allFields, recordType, recordId, model);
//            setTimeout(function(){ Omeka.Elements.elementFormFillRequest(fieldDiv, {add: '1'}, elementFormPartialUrlNoadd, elementFormPartialUrlTool, allFields, recordType, recordId)}, 200);
            
        });

        // When an add button is clicked, make an AJAX request to add another input.
        context.find(addSelector).click(function (event) {
            event.preventDefault();
            var fieldDiv = $(this).parents(fieldSelector);
            Omeka.Elements.elementFormRequest(fieldDiv, {add: '1'}, elementFormPartialUrl, recordType, recordId, model);
        });

        // When a remove button is clicked, remove that input from the form.
        context.find(removeSelector).click(function (event) {
            event.preventDefault();
            var removeButton = $(this);

            // Don't delete the last input block for an element.
            if (removeButton.parents(fieldSelector).find(inputBlockSelector).length === 1) {
                return;
            }

//            if (!confirm('Do you want to delete this input?')) {
//                return;
//            }

            var inputBlock = removeButton.parents(inputBlockSelector);
            inputBlock.find('textarea').each(function () {
                tinyMCE.execCommand('mceRemoveControl', false, this.id);
            });
            inputBlock.remove();

            // Hide remove buttons for fields with one input.
            $(fieldSelector).each(function () {
                var removeButtons = $(this).find(removeSelector);
                if (removeButtons.length === 1) {
                    removeButtons.hide();
                }
            });
        });
    };

    /**
     * Enable the WYSIWYG editor for "html-editor" fields on the form, and allow
     * checkboxes to create editors for more fields.
     *
     * @param {Element} element The element to search at and below.
     */
    Omeka.Elements.enableWysiwyg = function (element) {
        $(element).find('div.inputs .use-html-checkbox').each(function () {
            var textarea = $(this).parents('.input-block').find('textarea');
            if (textarea.length) {
                var textareaId = textarea.attr('id');
                var enableIfChecked = function () {
                    if (this.checked) {
                        tinyMCE.execCommand("mceAddControl", false, textareaId);
                    } else {
                        tinyMCE.execCommand("mceRemoveControl", false, textareaId);
                    }
                };

                enableIfChecked.call(this);

                // Whenever the checkbox is toggled, toggle the WYSIWYG editor.
                $(this).click(enableIfChecked);
            }
        });
    };
})(jQuery);
