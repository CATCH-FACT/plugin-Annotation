<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 */

class Annotation_CloneController extends Omeka_Controller_AbstractActionController
{
    
    public function init()
    {
        $this->_helper->db->setDefaultModelName('Item');
//        $this->session = new Zend_Session_Namespace();
    }
    
    
    public function cloneAction(){
        
        $this->_helper->db->setDefaultModelName('Item');

        $id = $this->getParam('id');

        $record = $this->_helper->db->findById($id);
        
        $itemTypeId = $record->item_type_id;

        $elementsTexts = $record->getAllElementTextsByElement();
        
        $this->view->assign(compact('itemTypeId', 'record', 'elementsTexts', 'allElements', 'id'));

        $columnNames = get_table_options(
                'Element', null,
                    array(
                        'sort' => 'alpha',
                    )
                );
        
        $columnNames = $columnNames["Itemtype metadata"] + $columnNames["Dublin Core"];
        
        require_once ANNOTATION_FORMS_DIR . '/CloneForm.php';
        
        $form = new Annotation_Form_CloneForm(array(
            'itemTypeId' => $itemTypeId,
            'columnNames' => $columnNames,
            'elementsTexts' => $elementsTexts,
            'record' => $record
        ));
        
        $this->view->form = $form;
        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            $this->_helper->flashMessenger(__('Invalid form input. Please try again.'), 'error');
            return;
        }

        $cloneValues = $this->view->form->getCloneValues();

        $user = current_user();
        
        $elementTable = get_db()->getTable('Element');
        
        $itemTypeId = $record->item_type_id;
        $collectionId = $record->collection_id;

        $tags = $record->getTags();
        
        $itemMetadata = array(
            Builder_Item::IS_PUBLIC      => 0,
            Builder_Item::IS_FEATURED    => 0,
            Builder_Item::ITEM_TYPE_ID   => $itemTypeId,
            Builder_Item::COLLECTION_ID  => $collectionId,
            Builder_Item::TAGS           => $tags,
        );

        $fileMetadata = $record->getFiles();
        $itemTypeElements = $record->getItemTypeElements();

        $new_record = new Item();
        $new_record->setOwner($user);
        $new_record->save();

        $new_record = update_item($new_record, $itemMetadata, array(), $fileMetadata);

        foreach($cloneValues as $colId => $subIds){
            foreach($subIds as $id => $value){
                print $value;
                $elementType = $elementTable->find($colId);
                $new_record->addTextForElement($elementType,  $value,  false);
            }
        }

        $new_record->save();

        $this->_helper->flashMessenger(__('The Item was succesfully cloned! Press Edit or Annotate to continue editing.'), 'success');
        
        $this->_helper->redirector->gotoUrl('/items/show/' . $new_record->id); //after all is ok: redirect to the next step*/
    }
    
    public function prePrint($identifier, $i){
        print "<pre>";
        print_r($identifier);
        print "<br>";
        print_r($i);
        print "</pre>";
    }
    
    /**
     * Index action.
     */
    public function clonedAction(){
        
    }
    
}