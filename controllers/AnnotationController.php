<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */
 
/**
 * Controller for annotations themselves.
 */
class Annotation_AnnotationController extends Omeka_Controller_AbstractActionController
{   
    protected $_captcha;
    
    /*
    Same as tags autocomplete but with more parameters
    returns a page with a simple list
    */
    function autocompleteAction(){
//        $element_id, $collection_id, $like, $style, $response, $api_response_code, $sorting
        $searchText = $this->_getParam('term');
        $element_id = $this->_getParam('autocomplete_main_id');
        $element_xtra_id = $this->_getParam('autocomplete_extra_id');
        $itemtype_id = $this->_getParam('autocomplete_itemtype_id');
        $collection_id = $this->_getParam('autocomplete_collection_id');
        $sorting = $this->_getParam('sorting');
        if (!$sorting) $sorting = "ASC";
        $this->_helper->db->setDefaultModelName('ElementText');

        $tagText = "rood";
        if (empty($tagText)) {
            $this->_helper->json(array());
        }

        $sql_query = "SELECT `items`.`id`, `_advanced_0`.`text`
                        FROM `omeka_items` AS `items`
                        INNER JOIN `omeka_collections` AS `collections` ON items.collection_id = collections.id
                        INNER JOIN `omeka_item_types` AS `item_types` ON items.item_type_id = item_types.id
                        INNER JOIN `omeka_element_texts` AS `_advanced_0` ON _advanced_0.record_id = items.id 
                        AND _advanced_0.record_type = 'Item' ";

        $sql_query .= $element_id ? "AND _advanced_0.element_id = " . $element_id . " ": " ";
//        $sql_query .= $element_xtra_id ? "OR _advanced_0.element_id = " . $element_xtra_id . ") ": ") ";

        $sql_query .= $collection_id ? "AND (collections.id = '" . $collection_id . "') " : " ";
        $sql_query .= $itemtype_id ? "AND (item_types.id = '" . $itemtype_id . "') " : " ";
        $sql_query .= "AND (_advanced_0.text LIKE ? ) ";
        $sql_query .= "GROUP BY `items`.`id` ORDER BY `_advanced_0`.`text` ";
        $sql_query .= $sorting . " LIMIT 15";
        
        $results = $this->_helper->db->getTable()->fetchPairs($sql_query, array('%' . $searchText . '%'));
        
        if ((count($results) > 0) && $element_xtra_id){
            foreach ($results as $id=>$val){
                $subquery = "SELECT * 
                            FROM  `omeka_element_texts` 
                            WHERE  `record_id` = " . $id . "
                            AND `element_id` = " . $element_xtra_id . "
                            LIMIT 0 , 1";
//                print $subquery;
                $subresults = $this->_helper->db->getTable()->fetchAll($subquery);
//                print_r($subresults[0]["text"]);
                $row = array();
                $row['label'] = $val;
                $row['value'] = $subresults[0]['text'];
                $response[] = $row;
            }
        }
        else{
            foreach ($results as $id=>$val){
                $row = array();
                $row['label'] = $val;
                $row['value'] = "";
                $response[] = $row;
            }
        }
        
        $this->_helper->json($response);
    }
    
    
    /**
     * Index action; simply forwards to annotateAction.
     */
    public function indexAction()
    {
        _log("Forwarding to annotate.");
        if (isset($_GET['annotation_type'])){
            _log($_GET['annotation_type']);
            $this->_setupAnnotateSubmit($_GET['annotation_type']);
            $this->view->typeForm = $this->view->render('annotation/type-form.php');
            $this->view->saveForm = $this->view->render('annotation/save-form.php');
        }
        $this->_forward('add');
    }
    
    public function editAction(){
        $id = $this->getParam('id');
        $this->_helper->db->setDefaultModelName('Item');
        
        $record = $this->_helper->db->findById();
        
//        parent::editAction();
        
        print "<pre>";
        print_r($record);
        print "</pre>";
        
        //$type = array("id" => 1);
        
//        $this->view->typeForm = $this->view->render('annotation/type-form.php');
//        $this->view->saveForm = $this->view->render('annotation/save-form.php');
        $this->view->assign(compact('id', 'type'));
        
//        parent::editAction();
    }
    
    public function existingAction(){
        // Get all the element sets that apply to the item.
        $this->view->elementSets = $this->_getItemElementSets();
        if (!Zend_Registry::isRegistered('file_derivative_creator') && is_allowed('Settings', 'edit')) {
            $this->_helper->flashMessenger(__('The ImageMagick directory path has not been set. No derivative images will be created. If you would like Omeka to create derivative images, please set the path in Settings.'));
        }
        parent::editAction();
    }
    
    public function myAnnotationsAction()
    {
        
        $user = current_user();
        $contribItemTable = $this->_helper->db->getTable('AnnotationAnnotatedItem');
                
        $contribItems = array();
        if(!empty($_POST)) {            
            foreach($_POST['annotation_public'] as $id=>$value) {
                $contribItem = $contribItemTable->find($id);
                if($value) {
                    $contribItem->public = true;
                } else {
                    $contribItem->makeNotPublic();
                }
                $contribItem->public = $value;
                $contribItem->anonymous = $_POST['annotation_anonymous'][$id];

                if($contribItem->save()) {
                    $this->_helper->flashMessenger( __('Your annotations have been updated.'), 'success');
                } else {
                    $this->_helper->flashMessenger($contribItem->getErrors());
                }
                
                $contribItems[] = $contribItem;
            }
        } else {
            $contribItems = $contribItemTable->findBy(array('annotator'=>$user->id));
        }
        
        $this->view->contrib_items = $contribItems;
        
    }

    /**
     * Action for main annotation form.
     *  redirect to actual annotation 
     */
    public function addAction()
    {

        if ($this->_processForm($_POST)) {
            $this->_helper->flashMessenger("Data accepted. Pre-annotated Item created.", 'success');
            $route = $this->getFrontController()->getRouter()->getCurrentRouteName();
            $this->_helper->_redirector->gotoRoute(array('action' => 'doannotation'), $route);
#            $this->_helper->_redirector->gotoRoute(array('action' => 'thankyou'), $route);

        } else {
            $typeId = null;
            if (isset($_POST['annotation_type']) && ($postedType = $_POST['annotation_type'])) {
                $typeId = $postedType;
            } else if ($defaultType = get_option('annotation_default_type')) {
                $typeId = $defaultType;
            }
            if ($typeId) {
                if($user = current_user()) {
                    $this->_setupAnnotateSubmit($typeId);
                    $this->view->typeForm = $this->view->render('annotation/type-form.php');
                    $this->view->saveForm = $this->view->render('annotation/save-form.php');
                }
            }

            if(isset($this->_profile) && !$this->_profile->exists()) {
                $this->_helper->flashMessenger($this->_profile->getErrors(), 'error');
                return;
            }

        }
    }
    
    /**
     * Displays a "Thank You" message to users who have annotated an item 
     * through the public form.
     */
    public function doannotationAction(){
        $this->_helper->flashMessenger( __('Your annotations has been successfully added.'), 'success');
    }
    
    /**
     * Action for AJAX request from annotate form.
     */
    public function typeFormAction()
    {
        $this->_setupAnnotateSubmit($_POST['annotation_type']);
    }

    /**
     * Action for AJAX request from annotate form.
     */
    public function saveFormAction()
    {
        $this->_setupAnnotateSubmit($_POST['annotation_type']);
    }


    protected function set_view_variables_for_form(){
        $elementId = (int)$_POST['element_id'];
        $recordType = $_POST['record_type'];
        $recordId  = (int)$_POST['record_id'];
        $annotationId  = (int)$_POST['annotation_id'];

        // Re-index the element form posts so that they are displayed in the correct order
        // when one is removed.
        $_POST['Elements'][$elementId] = array_merge($_POST['Elements'][$elementId]);

        $element = $this->_helper->db->getTable('Element')->find($elementId);
        
        $annotationTypeElement = $this->_helper->db->getTable('AnnotationTypeElement')->findByElementIdAndAnnotationId($elementId, $annotationId); //specifically the annotation ID
        
        $record = $this->_helper->db->getTable($recordType)->find($recordId);

        if (!$record) {
            $record = new $recordType;
        }
        $this->view->assign(compact('annotationTypeElement', 'element', 'record')); //assigning the variables to the view
    }

    /**
     * Action for AJAX request from type form.
     * element and record are registered here
     */
    public function elementFormToolAction(){
        $this->set_view_variables_for_form();
    }

    /**
     * Action for AJAX request from type form.
     * element and record are registered here
     */
    public function elementFormElementAction(){
        $annotationTypeElements = $this->_helper->db->getTable('AnnotationTypeElement')->findByAutocomplete();
        $this->view->assign(compact('annotationTypeElements')); //assigning the variables to the view
    }

    /**
     * Action for AJAX request from type form.
     * element and record are registered here
     */
    public function elementFormNoaddAction(){        
        $this->set_view_variables_for_form();
    }

    /**
     * Action for AJAX request from type form.
     * element and record are registered here
     */
    public function elementFormAction(){        
        $this->set_view_variables_for_form();
    }

    
    /**
     * Displays terms of service for annotation.
     */
    public function termsAction()
    {
    }
    
    /**
     * Displays a "Thank You" message to users who have annotated an item 
     * through the public form.
     */
    public function thankyouAction()
    {
    }
    
    /**
     * Common tasks whenever displaying submit form for annotation.
     *
     * @param int $typeId AnnotationType id
     */
    public function _setupAnnotateSubmit($typeId)
    {
        // Override default element form display        
        $this->view->addHelperPath(ANNOTATION_HELPERS_DIR, 'Annotation_View_Helper');
        $item = new Item;
        $this->view->item = $item;
        
        $type = get_db()->getTable('AnnotationType')->find($typeId);
        $this->view->type = $type;
        
        //setup profile stuff, if needed
        $profileTypeId = get_option('annotation_user_profile_type');
        if(plugin_is_active('UserProfiles') && $profileTypeId && current_user()) {
            $this->view->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
            $profileType = $this->_helper->db->getTable('UserProfilesType')->find($profileTypeId);
            $this->view->profileType = $profileType;
            
            $profile = $this->_helper->db->getTable('UserProfilesProfile')->findByUserIdAndTypeId(current_user()->id, $profileTypeId);
            if(!$profile) {
                $profile = new UserProfilesProfile();
                $profile->type_id = $profileTypeId;
            }
            $this->view->profile = $profile;            
        }
    }
    
    /**
     * Handle the POST for adding an item via the public form.
     * 
     * Validate and save the annotation to the database.  Save the ID of the
     * new item to the session.  Redirect to the consent form. 
     * 
     * If validation fails, render the Annotation form again with errors.
     *
     * @param array $post POST array
     * @return bool
     */
    protected function _processForm($post)
    {   
        if (!empty($post)) {
            //for the "Simple" configuration, look for the user if exists by email. Log them in.
            //If not, create the user and log them in.
            $user = current_user();
            if(!$user) {
                return false;
            }
            // The final form submit was not pressed.
            if (!isset($post['form-submit'])) {
                print "processing form: no form-submit in post.<br>";
                return false;
            }
            
            if (!$this->_validateAnnotation($post)) {
                print "annotation not validated.";
                return false;
            }
            print "processing form<br>";
            
            $annotationTypeId = trim($post['annotation_type']);
            if ($annotationTypeId !== "" && is_numeric($annotationTypeId)) {
                $annotationType = get_db()->getTable('AnnotationType')->find($annotationTypeId);
                $itemTypeId = $annotationType->getItemType()->id;
            } else {
            	$this->_helper->flashMessenger(__('You must select a type for your annotation.'), 'error');
                return false;
            }

            $itemMetadata = array('item_type_id' => $itemTypeId);
                                  
            $itemMetadata['featured'] = (int) $post['annotation-featured'];
            $itemMetadata['public'] = (int) $post['annotation-public'];
            $itemMetadata['collection_id'] = (int) $post['collection_id'];
            
            $fileMetadata = $this->_processFileUpload($annotationType);

            // This is a hack to allow the file upload job to succeed
            // even with the synchronous job dispatcher.
            if ($acl = get_acl()) {
                $acl->allow(null, 'Items', 'showNotPublic');
            }
            try {
                //in case we're doing Simple, create and save the Item so the owner is set, then update with the data
                $item = new Item();
                $item->setOwner($user);
                $item->save();
                $item = update_item($item, $itemMetadata, array(), $fileMetadata);
            } catch(Omeka_Validator_Exception $e) {
                $this->flashValidatonErrors($e);
                return false;
            } catch (Omeka_File_Ingest_InvalidException $e) {
                // Copying this cruddy hack
                if (strstr($e->getMessage(), "The file 'annotated_file' was not uploaded")) {
                   $this->_helper->flashMessenger("You must upload a file when making a {$annotationType->display_name} annotation.", 'error');
                } else {
                    $this->_helper->flashMessenger($e->getMessage());
                }
                return false;
            } catch (Exception $e) {
                $this->_helper->flashMessenger($e->getMessage());
                return false;
            }
            $this->_addElementTextsToItem($item, $post['Elements']);
            // Allow plugins to deal with the inputs they may have added to the form.
            fire_plugin_hook('annotation_save_form', array('annotationType'=>$annotationType,'item'=>$item, 'post'=>$post));
            
            $item->save();
            
            $this->temp_item = $item;
            
            $this->_linkItemToAnnotatedItem($item, $annotator, $post); 
            return true;
        }
        return false;
    }

    
    /**
     * Deals with files specified on the annotation form.
     *
     * @param AnnotationType $annotationType Type of annotation.
     * @return array File upload array.
     */
    protected function _processFileUpload($annotationType) {
        if ($annotationType->isFileAllowed()) {
            $options = array();
            if ($annotationType->isFileRequired()) {
                $options['ignoreNoFile'] = false;
            } else {
                $options['ignoreNoFile'] = true;
            }

            $fileMetadata = array(
                'file_transfer_type' => 'Upload',
                'files' => 'annotated_file',
                'file_ingest_options' => $options
            );

            // Add the whitelists for uploaded files
            $fileValidation = new AnnotationFileValidation;
            $fileValidation->enableFilter();

            return $fileMetadata;
        }
        return array();
    }

    protected function _linkItemToAnnotatedItem($item, $annotator, $post)
    {
        $linkage = new AnnotationAnnotatedItem;
        $linkage->annotator_id = $annotator->id;
        $linkage->item_id = $item->id;
        $linkage->annotation_type_id = $post['annotation_type'];
        $linkage->public = $post['annotation-public'];
        $linkage->finished = $post['annotation-finished'];
        $linkage->anonymous = false;
        $linkage->save();
    }
    
    /**
     * Adds ElementTexts to item.
     *
     * @param Item $item Item to add texts to.
     * @param array $elements Array of element inputs from form
     */
    protected function _addElementTextsToItem($item, $elements)
    {
        $elementTable = get_db()->getTable('Element');
        foreach($elements as $elementId => $elementTexts) {
            $element = $elementTable->find($elementId);
            foreach($elementTexts as $elementText) {
                if (!empty($elementText['text'])) {
                    $item->addTextForElement($element, $elementText['text']);
                }
            }
        }
    }
    
    /**
     * Validate the annotation form submission.
     * 
     * Will flash validation errors that occur.
     * 
     * Verify the validity of the following form elements:
     *      Terms agreement
     *      
     * @return bool
     */
    protected function _validateAnnotation($post)
    {
        return true;
    }
    
}