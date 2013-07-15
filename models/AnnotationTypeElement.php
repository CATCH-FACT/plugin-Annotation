<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
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
    public $element_id_out;
    public $tool_id;
    public $element_id_in;
    public $prompt;
    public $order;
    public $long_text;
    
    protected $_related = array('AnnotationType' => 'getType',
                                'ElementOut'          => 'getElementOut',
                                'ElementIn'          => 'getElementIn',
                                'Tool'          => 'getTool');

    protected function _validate()
    {
        if(empty($this->element_id_out)) {
            $this->addError('element', 'You must select an element to annotate.');
        }
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
    
    /**
     * Get the Element associated with this type element.
     *
     * @return Element
     */
    public function getElementOut()
    {
        return $this->_db->getTable('Element')->find($this->element_id_out);
    }

    public function getElementIn()
    {
        return $this->_db->getTable('Element')->find($this->element_id_in);
    }

    public function getTool()
    {
        return $this->_db->getTable('AnnotationTool')->find($this->tool_id);
    }

}
