<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 * @subpackage Models
 */

/**
 * An Element the user will be able to annotate for some type.
 *
 * @package Annotation
 * @subpackage Models
 */
class AnnotationTypeElement extends Omeka_Record_AbstractRecord
{
    public $type_id;
    public $element_id;
    public $tool_id;
    public $prompt;
    public $english_name;
    public $order;
    public $long_text;
    public $html;
    public $repeated_field;
    public $score_slider; #add a score slider to restrict annotation values (js)
    public $date_picker; #adds a date picker (js)
    public $date_range_picker; #adds a date range picker (js)
    public $autocomplete;
    public $autocomplete_main_id;
    public $autocomplete_extra_id;
    public $autocomplete_itemtype_id;
    public $autocomplete_collection_id;
    public $field_scroll;
    
    protected $_related = array('AnnotationType' => 'getType',
                                'Element'        => 'getElement',
                                'Tool'           => 'getTool');

    protected function _validate()
    {
        if(empty($this->element_id)) {
            $this->addError('element', 'You must select an element to annotate.');
        }
    }

    
    /**
     * Get the type associated with this type element.
     *
     * @return AnnotationType
     */
    public function getScoreslider()
    {
        return $this->_db->getTable('AnnotationType')->find($this->score_slider);
    }

    /**
     * Get the type associated with this type element.
     *
     * @return AnnotationType
     */
    public function getType()
    {
        return $this->_db->getTable('AnnotationType')->find($this->type_id);
    }
    
    /** alias for get element **/
    public function getElement()
    {
        return $this->_db->getTable('Element')->find($this->element_id);
    }

    public function getTool()
    {
        return $this->_db->getTable('AnnotationTool')->find($this->tool_id);
    }

}
