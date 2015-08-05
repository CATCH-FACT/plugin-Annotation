<?php 
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 */
 
/**
 * Controller for editing and viewing Annotation plugin item tools.
 */
class Annotation_ToolsController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('AnnotationTool');
    }
    
    
    /**
     * Determine whether or not the Tool has been correctly installed and
     * configured.
     * 
     * @return boolean True if the command line return status is 0 when
     * attempting to run ImageMagick's convert utility, false otherwise.
     */
    public function checkToolAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $imPath = $this->_getParam('path-to-convert');
#        $isValid = Omeka_File_Derivative_Image_Creator::isValidImageMagickPath($imPath);
        $isValid = Annotation_File_Derivative_Image_Creator::isValidImageMagickPath($imPath);
        $this->getResponse()->setBody(
            $isValid ? '<div class="success">' . __('The tool performs correctly.') . '</div>' 
                     : '<div class="error">' . __('The tool does not work.') . '</div>');
    }
    
    public function addAction()
    {
        $toolRecord = new AnnotationTool();
        $this->view->action = 'add';
        $this->view->annotation_tool = $toolRecord;
        $this->_processForm($toolRecord);
    }

    public function editAction()
    {
        $toolRecord = $this->_helper->db->findById();
        $this->view->action = 'edit';
        $this->view->annotation_tool = $toolRecord;
        $this->_processForm($toolRecord);        
    }
    
    /**
     * Index action; simply forwards to browse.
     */
    public function indexAction()
    {
        $this->_redirect('annotation/tools/browse');
    }
    
    public function showAction()
    {
        $this->_redirect('/');
    }

    protected function  _getAddSuccessMessage($record)
    {
        return 'Tool successfully added.';
    }

    protected function _getEditSuccessMessage($record)
    {
        return 'Tool successfully updated.';
    }

    protected function _getDeleteSuccessMessage($record)
    {
        return 'Tool deleted.';
    }
    
    private function _processForm($record)
    {
        $elementTable = $this->_helper->db->getTable('Element');
        $annotationElTable = $this->_helper->db->getTable('AnnotationToolElement');
        if ($this->getRequest()->isPost()) {
            try {
                $record->setPostData($_POST);
                if ($record->save()) {
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
