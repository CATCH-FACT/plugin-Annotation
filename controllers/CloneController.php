<?php

class Annotation_CloneController extends Omeka_Controller_AbstractActionController
{
    
    public function cloneAction(){
        
        $this->session->testvalue = "*********************************** testvalue 1";
        
//        $elementTable = get_db()->getTable('Element');
        
        $this->session->id = $this->getParam('id');
        
        $this->_helper->db->setDefaultModelName('Item');
        
        $this->session->record = $this->_helper->db->findById($id);
        
        $this->session->itemTypeId = $record->item_type_id;
        
        $elementsTexts = $record->getAllElementTexts();
        
        $allElements = get_table_options(
                'Element', null,
                    array(
                        'sort' => 'alpha',
                    )
                );
        $this->session->allElements = $allElements["Itemtype metadata"] + $allElements["Dublin Core"];

        require_once CSV_IMPORT_DIRECTORY . '/forms/CloneForm.php';
        $form = new CsvImport_Form_Mapping(array(
            'itemTypeId' => $this->session->itemTypeId,
            'allElements' => $this->session->allElements,
            'record' => $this->session->record
        ));
        $this->view->form = $form;
        
        //Generate choice form for cloning    
        $this->view->assign(compact('itemTypeId', 'record', 'elementsTexts', 'allElements'));

        if (!$this->getRequest()->isPost()) {
            return;
        }
        
//        $this->session->id = $id;

        $this->_helper->redirector->goto('cloned'); //after all is ok: redirect to the next step

    }
    
    /**
     * Index action.
     */
    public function clonedAction(){
        
        _log("***********************************1");
        _log(print_r($this->getRequest(), true));
        
        
        $post = false;
        
        if ($args['post']) {
            $post = $args['post'];
            _log(print_r($post, true));
        }

        _log("***********************************");
        _log(print_r($this->session, true));
        _log("***********************************");
        
        $user = current_user();
        
        $elementTable = get_db()->getTable('Element');
        
        $record = $this->_helper->db->findById($this->session->id);
        
        $itemTypeId = $record->item_type_id;
        $collectionId = $record->collection_id;

        //collect choices from form and generate item
        
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
        $elementsTexts = $record->getAllElementTexts();

        $new_record = new Item();
        $new_record->setOwner($user);
        $new_record->save();

        $new_record = update_item($new_record, $itemMetadata, array(), $fileMetadata);

        foreach($elementsTexts as $elementName => $element) {
            $elementTypeId = $element->element_id;
            $elementType = $elementTable->find($elementTypeId);
            if (!empty($element['text'])) {
                _log($element->text);
                $new_record->addTextForElement($elementType,  $element->text,  $element->html);
            }
        }
        
        $new_record->save();
        
        $this->view->assign(compact('record', 'new_record', 'testvalue'));
    }
    
    public function init()
    {
        $this->session = new Zend_Session_Namespace('Annotation');
        $this->_helper->db->setDefaultModelName('Item');
    }

}