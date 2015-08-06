<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 */

require_once VIEW_HELPERS_DIR . DIRECTORY_SEPARATOR . 'ElementForm.php';

/**
 * Overrides Omeka's ElementForm helper to allow for custom display of fields.
 */
class Annotation_View_Helper_AnnotationElementForm extends Omeka_View_Helper_ElementForm
{
//    protected $_annotationTypeElement;
//    protected $_element;
//    protected $that;
    
    public function AnnotationElementForm(Element $element, Omeka_Record_AbstractRecord $record, $options = array()){

        $that = $this ? $this : get_view();
        
        if (isset($options['annotationTypeElement'])){ $this->_annotationTypeElement = $options['annotationTypeElement']; }
        else $this->_element = $element; //JUST IN CASE

        $divWrap = isset($options['divWrap']) ? $options['divWrap'] : true;
        $extraFieldCount = isset($options['extraFieldCount']) ? $options['extraFieldCount'] : null;

        $this->_element = $element;

        // This will load all the Elements available for the record and fatal error
        // if $record does not use the ActsAsElementText mixin.
        $record->loadElementsAndTexts();
        $this->_record = $record;
        
        // Filter the components of the element form display
        //generating the input fields
        $inputsComponent = $this->_displayFormFields($extraFieldCount);
        $labelComponent = $that->_getfieldLabel();

        $addInputComponent = $this->view->formSubmit('add_element_' . $that->_annotationTypeElement['element_id'], 
                                                    __('Add Input'),
                                                    array('class'=> 'add-element'));
                                                    
        $addAnnotationComponent = $this->_annotationTypeElement->score_slider ? 
                                    '<br>Lengte: <span id="' . 'span_element_' . $that->_annotationTypeElement['element_id'] . 
                                    '" class="slidervalue" data-bind="text: slider_values()[' . $that->_annotationTypeElement['element_id'] . 
                                    ']">0</span> <br> <div style="width:63%; float:left" id="' . 'slide_element_' . 
                                    $that->_annotationTypeElement['element_id'] . '" class="slider"></div>' : "";
        $addAnnotationComponent .= $this->view->formSubmit('annotate-element_' . $that->_annotationTypeElement['element_id'], 
                                                        __('Determine automatically'),
                                                        array('class'=>'annotate-element',
                                                                'style'=>"width:28%; background:#0080FF; float:left"));
//                                                              'data-bind' => "click: annotate(" . $that->_annotationTypeElement['element_id'] . ")")); //knockout system

        $components = array(
            'label' => $labelComponent,
            'inputs' => $inputsComponent,
            'add_input' => $addInputComponent,
            'add_annotation' => $addAnnotationComponent,
            'html' => null 
        );

        $elementSetName = $this->_annotationTypeElement->set_name;
        $recordType = get_class($record);
        $filterName = array('AnnotationElementForm', $recordType, $elementSetName, $that->_annotationTypeElement->name);
        
        $components = apply_filters(
            $filterName,
            $components,
            array('record' => $record, 
                   'element' => $element, 
                   'options' => $options)
        );

        if ($components['html'] !== null) {
            return strval($components['html']);
        }

        // Compose html for element form
        $html = $divWrap ? '<div class="field" id="element-' . html_escape($that->_annotationTypeElement->element_id) . '">' : '';

        $html .= '<div class="eight columns alpha">';
        $html .= $this->_getLabelTooltip();

        //only add annotation button if a tool is specified
        $html .= $this->_annotationTypeElement->tool_id ? $components['add_annotation'] : "";

//        $html .= $this->_annotationTypeElement->repeated_field ? $components['add_input'] : "";
        $html .= '</div>'; // Close div

        $html .= '<div class="inputs six columns omega">';

        $html .= $components['inputs'];

        $html .= '&nbsp';
    
        $html .= $this->_annotationTypeElement->repeated_field ? $components['add_input'] : ""; //add button travels along

        $html .= "</div>\n"; // Close 'inputs' div
        
        
        $html .= $divWrap ? "</div>\n\n" : ''; // Close 'field' div

        return $html;
    }

    protected function _getLabelTooltip(){
        $htmlr = "<label>";
        $htmlr .= $this->_getFieldInputLabel();
        $htmlr .= ' <img class="masterTooltip" style="width:15px;height:15px;vertical-align:middle" src="' . img("info-icon.png") . '" alt="info" title="' . $this->_getFieldLabel() . '" />';
        $htmlr .= '</label>';
        return $htmlr;
    }
    
    
    // creates the input fields
    protected function _displayFormFields($extraFieldCount = null){
        $fieldCount = $this->_getFormFieldCount() + (int) $extraFieldCount;
        $html = '';
        for ($i=0; $i < $fieldCount; $i++) {
            $html .= '<div class="input-block">';

            $fieldStem = $this->_getFieldNameStem($i);

            $html .= '<div class="input">';
            
            $html .= $this->_displayFormInput($fieldStem, $this->_getValueForField($i));
            
            $html .= '</div>';

            $html .= $this->_displayFormControls();

            $html .= $this->_displayHtmlFlag($fieldStem, $i);

            $html .= '</div>';
        }
        return $html;
    }
    
    /**
     * Get the button that will allow a user to remove this form input.
     * The submit input has a class of 'add-element', which is used by the
     * Javascript to do stuff.
     *
     * @return string
     */
    protected function _getControlsComponent()
    {
        $html = '<div class="controls">'
              . $this->view->formSubmit(null, 
                                       __('Remove'),
                                       array('class' => 'remove-element red button'))
              . '</div>';

        return $html;
    }
    
    //the original
    protected function _getInputsComponent($extraFieldCount = null)
    {
        $fieldCount = $this->_getFormFieldCount() + (int) $extraFieldCount;
        $html = '';
        for ($i=0; $i < $fieldCount; $i++) {
            $html .= $this->view->annotationElementInput(
                $this->_element, $this->_record, $i,
                $this->_getValueForField($i), $this->_getHtmlFlagForField($i));
        }
        return $html;
    }
    
    /**
     * Uses the type's alias to display rather than the element name.
     */
    protected function _getFieldInputLabel()
    {
        return __(html_escape($this->_element->name));
//        return html_escape($this->_annotationTypeElement->Element->name);
    }
        
    /**
     * Uses the type's alias to display rather than the element name.
     */
    protected function _getFieldLabel()
    {
        return html_escape($this->_annotationTypeElement->prompt);
    }
    
    /**
     * Removes "Remove input" button from element output
     */
    protected function _displayFormControls(){
    }
    
    /**
     * Removes "Use HTML" checkbox from element output
     */
    protected function _displayHtmlFlag($inputNameStem, $index)
    {}
    
    protected function _displayFieldLabel()
    {
        return '<label>'.__($this->_getFieldLabel()).'</label>';
    }
    
    protected function _displayValidationErrors()
    {
        flash($this->_annotationTypeElement->prompt);
    }
    
    protected function _getFieldNameStem($index)
    {
        return "Elements[".$this->_element->id."][$index]";
//        return "Elements[".$this->_annotationTypeElement->element_id."][$index]";
    }
    
    // checks if simplevocab is installed
    protected function _displayFormInput($inputNameStem, $value, $options=array())
    {
        
        if(plugin_is_active('SimpleVocab')) {
            $simpleVocabTerm = get_db()->getTable('SimpleVocabTerm')->findByElementId($this->_element->id);
            if ($simpleVocabTerm){
                $terms = explode("\n", $simpleVocabTerm->terms);
                $selectTerms = array('' => 'Select Below') + array_combine($terms, $terms);
                return get_view()->formSelect(
                    $inputNameStem . '[text]', 
                    $value, 
                    array('style' => 'width: 250px; font-size:20px; margin-left:3px;'), 
                    $selectTerms
                );
            }
        }
        
        $fieldDataType = $this->_getElementDataType();

        // Plugins should apply a filter to this blank HTML in order to display it in a certain way.
        $html = '';

        $filterName = $this->_getPluginFilterForFormInput();

        //$html = apply_filters($filterName, $html, $inputNameStem, $value, $options, $this->_record, $this->_element);
        $html = apply_filters($filterName, $html, array('view'=>$this));
        
        // Short-circuit the default display functions b/c we already have the HTML we need.
        if (!empty($html)) {
            return $html;
        }

        $classtype = 'textinput';
        $classtype .= $this->_annotationTypeElement->date_range_picker ? ' date_range_picker' : '';
        $classtype .= $this->_annotationTypeElement->date_picker ? ' date_picker' : '';
        $classtype .= $this->_annotationTypeElement->autocomplete ? ' autocomplete' : '';
        $classtype .= $this->_annotationTypeElement->field_scroll ? ' field_scroll' : '';

        if($this->_annotationTypeElement->long_text) {
            $html = $this->view->formTextarea(
                $inputNameStem . '[text]',
                $value,
                array('element-name'=>$this->_element->name, 'class'=> $classtype, 'rows'=>15, 'cols'=>120));
        }
        else{
            $html = $this->view->formText(
            $inputNameStem . '[text]',
            $value,
            array('element-name'=>$this->_element->name, 
                'class'=> $classtype,
                'style' => 'width: 250px; font-size:16px; margin-left:3px;')
                );
        }
        
        $html .= $this->_getControlsComponent(); //remove button
        
        return $html;
    }
    
    protected function _getElementDataType()
    {
        return $this->_element['data_type_name'];
    }
    protected function _getPluginFilterForFormInput()
    {
        return array(
            'Form',
            get_class($this->_record),
            $this->_element->set_name,
            $this->_element->name
        );
    }
}
