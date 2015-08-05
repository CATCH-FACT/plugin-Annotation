<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 */

class Annotation_ItemsController extends Omeka_Controller_AbstractActionController
{
    
    public function _getBrowseRecordsPerPage()
    {
        if (is_admin_theme()) {
            return (int) get_option('per_page_admin');
        } else {
            return (int) get_option('per_page_public');
        }
    }    
    
    public function init()
    {
        $this->_helper->db->setDefaultModelName('AnnotationAnnotatedItem');
    }
    
    
    
}