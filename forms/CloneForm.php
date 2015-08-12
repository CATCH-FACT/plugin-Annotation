<?php
/**
 * CsvImport_Form_Mapping class - represents the form on csv-import/index/map-columns.
 *
 * @copyright Meertens Institute 2015
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package CsvImport
 */

class Annotation_Form_CloneForm extends Omeka_Form
{
    
    private $_itemTypeId;
    private $_columnNames = array();
    private $_elementsTexts = array();
    private $_record;

    /**
     * Initialize the form.
     */
    public function init()
    {
        parent::init();
        $this->setAttrib('id', 'clone-mapping');
        $this->setMethod('post'); 

        foreach($this->_elementsTexts as $colExampleId => $colExampleValue){
            foreach($colExampleValue as $aId => $subColExampleValue){
                $rowSubForm = new Zend_Form_SubForm();
                $sel = true;
                if (strlen($colExampleValue[$aId]->text) >= 50) $sel = false; //base on setting in future
//                if ($colExampleValue[$aId]->text != "Jezus") $sel = false;
                $selectElement = $rowSubForm->createElement('checkbox', "clone");
                $selectElement->setChecked($sel);
                $rowSubForm->addElement($selectElement);
                $this->_setSubFormDecorators($rowSubForm);
                $this->addSubForm($rowSubForm, "row$colExampleId id$aId");
            }
        }
        
        $this->addElement('submit', 'submit',
            array('label' => __('Clone'),
                  'class' => 'submit submit-medium'));
    }

    /**
     * Load the default decorators.
     */
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            array('ViewScript', array(
                'viewScript' => 'clone/clone-fields.php',
                'itemTypeId' => $this->_itemTypeId,
                'form' => $this,
                'columnExamples' => $this->_elementsTexts,
                'columnNames' => $this->_columnNames,
            )),
        ));
    }

    /**
     * Set the column names
     * 
     * @param array $columnNames The array of column names (which are strings)
     */
    public function setColumnNames($columnNames)
    {
        $this->_columnNames = $columnNames;
    }

    /**
     * Set the column examples
     * 
     * @param array $columnExamples The array of column examples (which are strings)
     */
    public function setElementsTexts($elementsTexts)
    {
        $this->_elementsTexts = $elementsTexts;
    }

    /**
     * Set the column examples
     * 
     * @param int $itemTypeId The id of the item type
     */
    public function setItemTypeId($itemTypeId)
    {
        $this->_itemTypeId = $itemTypeId;
    }

    /**
     * Set the element delimiter
     * 
     * @param int $elementDelimiter The element delimiter
     */
    public function setElementDelimiter($elementDelimiter)
    {
        $this->_elementDelimiter = $elementDelimiter;
    }

    /**
     * Set the file delimiter
     * 
     * @param int $fileDelimiter The file delimiter
     */
    public function setFileDelimiter($fileDelimiter)
    {
        $this->_fileDelimiter = $fileDelimiter;
    }

    /**
     * Set the tag delimiter
     * 
     * @param int $tagDelimiter The tag delimiter
     */
    public function setTagDelimiter($tagDelimiter)
    {
        $this->_tagDelimiter = $tagDelimiter;
    }
    
    /**
     * Set whether or not to automap column names to elements
     * 
     * @param boolean $flag Whether or not to automap column names to elements
     */
    public function setAutomapColumnNamesToElements($flag)
    {
        $this->_automapColumnNamesToElements = (boolean)$flag;
    }
    
    
    /**
    * Returns array of column maps
    *
    * @return array The array of column maps   
    */
    public function getCloneValues()
    {
        $cloneValues = array();
        foreach($this->_elementsTexts as $colExampleId => $colExampleValue){
            foreach($colExampleValue as $aId => $subColExampleValue){
                if ($values = $this->_getRowValue($colExampleId, $aId, $subColExampleValue)){
                    $cloneValues[$colExampleId][] = $subColExampleValue->text;
                }
            }
        }
        return $cloneValues;
    }

    /**
    * Returns a row element value
    *
    * @param int $index The subform row index
    * @param string $elementName The element name in the row
    * @return mixed The row element value     
    */
    protected function _getRowValue($index, $aId, $elementName)
    {
        return $this->getSubForm("row$index id$aId")->clone->isChecked();
    }


    /**
    * Adds decorators to a subform.
    *
    * @param Zend_Form_SubForm $subForm The subform  
    */
    protected function _setSubFormDecorators($subForm)
    {
        // Get rid of the fieldset tag that wraps subforms by default.
        $subForm->setDecorators(array(
            'FormElements',
        ));

        // Each subform is a row in the table.
        foreach ($subForm->getElements() as $el) {
            $el->setDecorators(array(
                array('decorator' => 'ViewHelper'),
                array('decorator' => 'HtmlTag',
                      'options' => array('tag' => 'td')),
            ));
        }
    }
}
