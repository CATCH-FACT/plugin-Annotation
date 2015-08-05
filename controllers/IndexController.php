<?php 
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 */
 
/**
 * Controller for editing and viewing Annotation plugin settings.
 */
class Annotation_IndexController extends Omeka_Controller_AbstractActionController
{
    
    /**
     * Index action.
     */
    public function indexAction()
    {
        $this->redirect('annotation/annotation');
        if(!is_allowed('Annotation_Settings', 'edit')) {
            $this->redirect('annotation/items');
        }
    }
}
