<?php
/**
 * @version
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2016
 * @package Annotation
 */
 
/**
 * Controller for making duplicates
 */
class Annotation_DuplicateController extends Omeka_Controller_AbstractActionController
{   
    protected $_captcha;
    
    /**
     * Index action
     */
    public function indexAction(){
        
        $this->_helper->db->setDefaultModelName('Item');
        
        $id = $this->getParam('id');

        $record = $this->_helper->db->findById($id);
        
        $itemTypeId = $record->item_type_id;

        $elementsTexts = $record->getAllElementTextsByElement();
        
        $this->view->assign(compact('itemTypeId', 'record', 'elementsTexts', 'allElements', 'id'));
        
    }


    public function init(){
    }
}