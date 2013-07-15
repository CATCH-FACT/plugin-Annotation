<?php 
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright 
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

    public function addExistingElementAction()
    {
        if ($this->_getParam('from_post') == 'true') {
            $elementTempIdOut = $this->_getParam('elementTempIdOut');
            $elementTempIdIn = $this->_getParam('elementTempIdIn');
            $elementIdOut = $this->_getParam('elementIdOut');
            $elementIdIn = $this->_getParam('elementIdIn');
            $elementToolId = $this->_getParam('elementToolId');
            $elementOut = $this->_helper->db->getTable('Element')->find($elementIdOut);
            $elementIn = $this->_helper->db->getTable('Element')->find($elementIdIn);
            if ($elementOut) {
                $elementDescription = $element->description;
            }
            $elementOrder = $this->_getParam('elementOrder');
            $elementPromptValue = $element->prompt;
        } else {
            $elementTempIdOut = '' . time();
            $elementTempIdIn = '' . time();
            $elementIdOut = '';
            $elementIdIn = '';
            $elementToolId = '';
            $elementDescription = '';
            $elementOrder = intval($this->_getParam('elementCount')) + 1;
            $elementPromptValue = '';
        }
    
        $stemOut = Omeka_Form_ItemTypes::ELEMENTS_TO_ADD_INPUT_NAME . "[$elementTempIdOut]";
        $stemIn = Omeka_Form_ItemTypes::ELEMENTS_TO_ADD_INPUT_NAME . "[$elementTempIdIn]";
        $elementIdOutName = $stemOut .'[idout]';
        $elementIdInName = $stemIn .'[idin]';
        $elementToolName = $stemOut .'[toolid]';
        $elementOrderName = $stemOut .'[order]';
        $elementPromptName = $stemOut . '[prompt]';
        $elementLongName = $stemOut . '[long_text]';
        
        $item_type_id = $this->_getParam('itemTypeId');
        $this->view->assign(array(
                'element_id_out_name' => $elementIdOutName,
                'element_id_out_value' => $elementIdOut,
                'element_id_in_name' => $elementIdInName,
                'element_id_in_value' => $elementIdIn,
                'element_tool_name' => $elementToolName,
                'element_tool_value' => $elementToolId,
                'element_description' => $elementDescription,
                'element_order_name' => $elementOrderName,
                'element_order_value' => $elementOrder,
                'element_prompt_name' => $elementPromptName,
                'element_prompt_value' => $elementPromptValue,
                'element_long_name' => $elementLongName,
                'item_type_id' => $item_type_id 
        ));
    }
    
    public function changeExistingElementAction()
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
                                $elementInfo['prompt'] = $elementTable->find($elementInfo['idout'])->name;
                            }
                            $annotationEl = new AnnotationTypeElement();
                            $annotationEl->element_id_out = $elementInfo['idout'];
                            $annotationEl->element_id_in = $elementInfo['idin'];
                            $annotationEl->tool_id = $elementInfo['toolid'];
                            $annotationEl->prompt = $elementInfo['prompt'];
                            $annotationEl->order = $elementInfo['order'];
                            $annotationEl->long_text = $elementInfo['long_text'];
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
                            $annotationEl->tool_id = $elementInfo['toolid'];
                            $annotationEl->prompt = $elementInfo['prompt'];
                            $annotationEl->order = $elementInfo['order'];                        
                            $annotationEl->long_text = $elementInfo['long_text'];
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
