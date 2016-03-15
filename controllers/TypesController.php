<?php 
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 */
 
/**
 * Controller for editing and viewing Annotation plugin item types.
 */
class Annotation_TypesController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('AnnotationType');
    }
    
    public function addAction()
    {
        $typeRecord = new AnnotationType();
        $this->view->action = 'add';
        $this->view->annotation_type = $typeRecord;
        $this->_processForm($typeRecord);
    }

    public function editAction()
    {
        $typeRecord = $this->_helper->db->findById();
        $this->view->action = 'edit';
        $this->view->annotation_type = $typeRecord;
        $this->_processForm($typeRecord);        
    }
    
    
    /**
     * Index action; simply forwards to browse.
     */
    public function indexAction()
    {
        $this->_redirect('annotation/types/browse');
    }
    
    public function showAction()
    {
        $this->_redirect('/');
    }


    public function addTypeElementAction()
    {
        if ($this->_getParam('from_post') == 'true') {
            $elementTempId = $this->_getParam('elementTempId');
            $elementId = $this->_getParam('elementId');
            $elementToolId = $this->_getParam('elementToolId');
            $element = $this->_helper->db->getTable('Element')->find($elementId);
            if ($element) {
                $elementDescription = $element->description;
                $elementEnglishName = strtolower($element->name);
            }
            $elementOrder = $this->_getParam('elementOrder');
            $elementPromptValue = $element->prompt;
            $elementHtml = $this->_getParam('elementHtml');
            $elementAutocomplete = $this->_getParam('elementAutocomplete');
            $elementAutocompleteMainId = $this->_getParam('elementAutocompleteMain');
            $elementAutocompleteExtraId = $this->_getParam('elementAutocompleteExtraId');
            $elementAutocompleteItemtypeId = $this->_getParam('elementAutocompleteItemtypeId');
            $elementAutocompleteCollectionId = $this->_getParam('elementAutocompleteCollectionId');
            $elementFieldScrollId = $this->_getParam('elementFieldScrollId');
        } else {
            $elementTempId = '' . time();
            $elementId = '';
            $elementToolId = '';
            $elementDescription = '';
            $elementOrder = intval($this->_getParam('elementCount')) + 1;
            $elementPromptValue = '';
            $elementHtml = '';
            $elementAutocomplete = '';
            $elementAutocompleteMainId = '';
            $elementAutocompleteExtraId = '';
            $elementAutocompleteItemtypeId = '';
            $elementAutocompleteCollectionId = '';
            $elementFieldScrollId = '';
            
        }
    
        $stem = Omeka_Form_ItemTypes::ELEMENTS_TO_ADD_INPUT_NAME . "[$elementTempId]";
        $elementIdName = $stem .'[id]';
        $elementToolName = $stem .'[toolid]';
        $elementOrderName = $stem .'[order]';
        $elementPromptName = $stem . '[prompt]';
        $elementHtmlName = $stem . '[html]';
        $elementLongName = $stem . '[long_text]';
        $elementRepeatedName = $stem . '[repeated_field]';
        $elementScoresliderName = $stem . '[score_slider]';
        $elementDatepickerName = $stem . '[date_picker]';
        $elementDateRangepickerName = $stem . '[date_range_picker]';
        $elementEnglishName = $stem . '[english_name]';

        $elementAutocompleteName = $stem . '[autocomplete]';
        $elementAutocompleteMainName = $stem . '[autocomplete_main_id]';
        $elementAutocompleteExtraName = $stem . '[autocomplete_extra_id]';
        $elementAutocompleteItemtypeName = $stem . '[autocomplete_itemtype_id]';
        $elementAutocompleteCollectionName = $stem . '[autocomplete_collection_id]';
        $elementFieldScrollName = $stem . '[field_scroll]';
        
        $item_type_id = $this->_getParam('itemTypeId');
        
        $this->view->assign(array(
                'element_id_name' => $elementIdName,
                'element_id_value' => $elementId,
                'element_english_name' => $elementEnglishName,
                'element_tool_name' => $elementToolName,
                'element_tool_value' => $elementToolId,
                'element_description' => $elementDescription,
                'element_order_name' => $elementOrderName,
                'element_order_value' => $elementOrder,
                'element_prompt_name' => $elementPromptName,
                'element_prompt_value' => $elementPromptValue,
                'element_long_name' => $elementLongName,
                'element_repeated_name' => $elementRepeatedName,
                'element_html_name' => $elementHtmlName,
                'element_scoreslider_name' => $elementScoresliderName,
                'element_datepicker_name' => $elementDatepickerName,
                'element_daterangepicker_name' => $elementDateRangepickerName,
                'element_autocomplete_name' => $elementAutocompleteName,
                'element_autocomplete_main_name' => $elementAutocompleteMainName,
                'element_autocomplete_extra_name' => $elementAutocompleteExtraName,
                'element_autocomplete_itemtype_name' =>$elementAutocompleteItemtypeName,
                'element_autocomplete_collection_name' => $elementAutocompleteCollectionName,
                'element_field_scroll_name' => $elementFieldScrollName,
                'element_autocomplete_value' => $elementAutocomplete,
                'element_autocomplete_main_value' => $elementAutocompleteMainId,
                'element_autocomplete_extra_value' => $elementAutocompleteExtraId,
                'element_autocomplete_itemtype_value' =>$elementAutocompleteItemtypeId,
                'element_autocomplete_collection_value' => $elementAutocompleteCollectionId,
                'element_field_scroll_value' => $elementFieldScrollId,
                'item_type_id' => $item_type_id 
        ));
    }
    
    public function changeTypeElementAction()
    {
        $elementId = $this->_getParam('elementId');
        $element = $this->_helper->db->getTable('Element')->find($elementId);
    
        $elementDescription = '';
        if ($element) {
            $elementDescription = $element->description;
        }
    
        $data = array();
        $data['elementDescription'] = $elementDescription;
    
        $this->_helper->json($data);
    }
    
    protected function  _getAddSuccessMessage($record)
    {
        return 'Type successfully added.';
    }

    protected function _getEditSuccessMessage($record)
    {
        return 'Type successfully updated.';
    }

    protected function _getDeleteSuccessMessage($record)
    {
        return 'Type deleted.';
    }
    
    private function _processForm($record)
    {
        $elementTable = $this->_helper->db->getTable('Element');
        $annotationElTable = $this->_helper->db->getTable('AnnotationTypeElement');
        if ($this->getRequest()->isPost()) {
            try {
                $record->setPostData($_POST);
                if ($record->save()) {
                    if(isset($_POST['elements-to-add'])) {
                        foreach($_POST['elements-to-add'] as $tempId=>$elementInfo) {
                            if(empty($elementInfo['prompt'])) {
                                $elementInfo['prompt'] = $elementTable->find($elementInfo['id'])->name;
                            }
                            if(empty($elementInfo['english_name'])) {
                                $elementInfo['english_name'] = strtolower($elementTable->find($elementInfo['id'])->name);
                            }
                            
                            $annotationEl = new AnnotationTypeElement();
                            $annotationEl->element_id = $elementInfo['id'];
                            $annotationEl->english_name = $elementInfo['english_name'];
                            $annotationEl->tool_id = $elementInfo['toolid'];
                            $annotationEl->prompt = $elementInfo['prompt'];
                            $annotationEl->order = $elementInfo['order'];
                            $annotationEl->long_text = $elementInfo['long_text'];
                            $annotationEl->repeated_field = $elementInfo['repeated_field'];
                            $annotationEl->html = $elementInfo['html'];
                            $annotationEl->score_slider = $elementInfo['score_slider'];
                            $annotationEl->date_picker = $elementInfo['date_picker'];
                            $annotationEl->date_range_picker = $elementInfo['date_range_picker'];
                            $annotationEl->autocomplete = $elementInfo['autocomplete'];
                            $annotationEl->autocomplete_main_id = $elementInfo['autocomplete_main_id'];
                            $annotationEl->autocomplete_extra_id = $elementInfo['autocomplete_extra_id'];
                            $annotationEl->autocomplete_itemtype_id = $elementInfo['autocomplete_itemtype_id'];
                            $annotationEl->autocomplete_collection_id = $elementInfo['autocomplete_collection_id'];
                            $annotationEl->field_scroll = $elementInfo['field_scroll'];
                            $annotationEl->type_id = $record->id;
                            $annotationEl->save();
                        }                        
                    }

                    $toRemove = isset($_POST['elements_to_remove']) ? explode(',', $_POST['elements_to_remove']) : array();
                    
                    foreach($_POST['elements'] as $id=>$elementInfo) {
                        if(!in_array($id, $toRemove)) {
                            $annotationEl = $annotationElTable->find($id);
                            if(empty($elementInfo['prompt'])) {
                                $elementInfo['prompt'] = $elementTable->find($annotationEl->element_id)->name;
                            }
                            if(empty($elementInfo['english_name'])) {
                                $elementInfo['english_name'] = strtolower($elementTable->find($annotationEl->element_id)->name);
                            }
                            
                            $annotationEl->english_name = $elementInfo['english_name'];
                            $annotationEl->tool_id = $elementInfo['toolid'];
                            $annotationEl->prompt = $elementInfo['prompt'];
                            $annotationEl->order = $elementInfo['order'];
                            $annotationEl->long_text = $elementInfo['long_text'];
                            $annotationEl->html = $elementInfo['html'];
                            $annotationEl->repeated_field = $elementInfo['repeated_field'];
                            $annotationEl->score_slider = $elementInfo['score_slider'];
                            $annotationEl->date_picker = $elementInfo['date_picker'];
                            $annotationEl->date_range_picker = $elementInfo['date_range_picker'];
                            $annotationEl->autocomplete = $elementInfo['autocomplete'];
                            $annotationEl->autocomplete_main_id = $elementInfo['autocomplete_main_id'];
                            $annotationEl->autocomplete_extra_id = $elementInfo['autocomplete_extra_id'];
                            $annotationEl->autocomplete_itemtype_id = $elementInfo['autocomplete_itemtype_id'];
                            $annotationEl->autocomplete_collection_id = $elementInfo['autocomplete_collection_id'];
                            $annotationEl->field_scroll = $elementInfo['field_scroll'];
                            $annotationEl->save();
                        }
                    }
                    foreach($toRemove as $contribElId) {
                        $contribEl =$annotationElTable->find($contribElId);
                        if($contribEl) {
                            $contribEl->delete();    
                        } 
                    }
                    $this->_helper->redirector('browse');
                    return;
                }

            // Catch validation errors.
            } catch (Omeka_Validate_Exception $e) {
                $this->_helper->flashMessenger($e);
            }            
        }
    }
}
