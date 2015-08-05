<?php 
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 */
 
/**
 * Controller for editing and viewing Contribution plugin contributors.
 */
class Annotation_AnnotatorsController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('User');
    }

    
    public function showAction()
    {
        $id = $this->getParam('id');
        $user = $this->_helper->db->getTable('User')->find($id);
        $this->view->contributor = $user;
        $items = $this->_helper->db->getTable('AnnotationAnnotatedItem')->findBy(array('annotator'=>$user->id));
        $this->view->items = $items;
    }
}
