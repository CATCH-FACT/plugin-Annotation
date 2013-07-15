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
    
    /**
     * Index action; simply forwards to annotateAction.
     */
    public function indexAction()
    {
        $this->_forward('annotate');
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
     */
    public function annotateAction()
    {
        if ($this->_processForm($_POST)) {
            $route = $this->getFrontController()->getRouter()->getCurrentRouteName();
            $this->_helper->_redirector->gotoRoute(array('action' => 'thankyou'), $route);
                        
        } else {

            $typeId = null;
            if (isset($_POST['annotation_type']) && ($postedType = $_POST['annotation_type'])) {
                $typeId = $postedType;
            } else if ($defaultType = get_option('annotation_default_type')) {
                $typeId = $defaultType;
            }

            if ($typeId) {
                if(!get_option('annotation_simple') && $user = current_user()) {
                    $this->_setupAnnotateSubmit($typeId);
                    $this->view->typeForm = $this->view->render('annotation/type-form.php');
                }
            }
            
            if(isset($this->_profile) && !$this->_profile->exists()) {
                $this->_helper->flashMessenger($this->_profile->getErrors(), 'error');
                return;
            }

        }
    }
    
    /**
     * Action for AJAX request from annotate form.
     */
    public function typeFormAction()
    {
        $this->_setupAnnotateSubmit($_POST['annotation_type']);
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
            $simple = get_option('annotation_simple');
            
            if(!$user && $simple) {
                $user = $this->_helper->db->getTable('User')->findByEmail($post['annotation_simple_email']);
            }
            
            // if still not a user, need to create one based on the email address
            if(!$user) {
                $user = $this->_createNewGuestUser($post);
            }            
            
            // The final form submit was not pressed.
            if (!isset($post['form-submit'])) {
                return false;
            }
            
            if (!$this->_validateAnnotation($post)) {
                return false;
            }
            
            $annotationTypeId = trim($post['annotation_type']);
            if ($annotationTypeId !== "" && is_numeric($annotationTypeId)) {
                $annotationType = get_db()->getTable('AnnotationType')->find($annotationTypeId);
                $itemTypeId = $annotationType->getItemType()->id;
            } else {
            	$this->_helper->flashMessenger(__('You must select a type for your annotation.'), 'error');
                return false;
            }

            $itemMetadata = array('public'       => false,
                                  'featured'     => false,
                                  'item_type_id' => $itemTypeId);
            
            $collectionId = get_option('annotation_collection_id');
            if (!empty($collectionId) && is_numeric($collectionId)) {
                $itemMetadata['collection_id'] = (int) $collectionId;
            }
            
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
            
            if( !$simple && !$this->_processUserProfile($post) ) {
                return false;
            }
            
            $this->_linkItemToAnnotatedItem($item, $annotator, $post);
            $this->_sendEmailNotifications($user->email, $item);
            return true;
        }
        return false;
    }
    
    protected function _processUserProfile($post)
    {
        $profileTypeId = get_option('annotation_user_profile_type');
        if($profileTypeId) {
            $user = current_user();
            $profile = $this->_helper->db->getTable('UserProfilesProfile')->findByUserIdAndTypeId($user->id, $profileTypeId);
            if(!$profile) {
                $profile = new UserProfilesProfile();
                $profile->setOwner($user);
                $profile->type_id = $profileTypeId;
                $profile->public = 0;
                $profile->setRelationData(array('subject_id'=>$user->id));
            }    
        }
        $profile->setPostData($post);
        $this->_profile = $profile;
        if(!$profile->save(false)) {
            return false;
        }
        return true;
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
        $linkage->public = $post['annotation-public'];
        $linkage->anonymous = $post['annotation-anonymous'];
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
        if (!@$post['terms-agree']) {
            $this->_helper->flashMessenger(__('You must agree to the Terms and Conditions.'), 'error');
            return false;
        }
        return true;
    }
    
    /**
     * Send an email notification to the user who annotated the Item.
     * 
     * This email will appear to have been sent from the address specified via
     * the 'annotation_email_sender' option.
     * 
     * @param string $email Address to send to.
     * @param Item $item Item that was annotated via the form.
     * @return void
     * @todo Update for new Annotation
     */
    protected function _sendEmailNotifications($toEmail, $item)
    {
        $fromAddress = get_option('annotation_email_sender');
        $siteTitle = get_option('site_title');

        $this->view->item = $item;
    
        //If this field is empty, don't send the email
        if (!empty($fromAddress)) {
            $annotatorMail = new Zend_Mail;
            $body = "<p>" .  __("Thank you for your annotation to %s", get_option('site_title')) . "</p>";
            $body .= "<p>" . __("Your annotation has been accepted and will be preserved in the digital archive. For your records, the permanent URL for your annotation is noted at the end of this email. Please note that annotations may not appear immediately on the website while they await processing by project staff.") . "</p>";
	        $body .= "<p>" . __("Annotation URL (pending review by project staff): %s", record_url($item, 'show', true)) . "</p>";	        
            $body .= get_option('annotation_simple_email');
            
            $annotatorMail->setBodyHtml($body);
            $annotatorMail->setFrom($fromAddress, __("%s Administrator", $siteTitle ));
            $annotatorMail->addTo($toEmail);
            $annotatorMail->setSubject(__("Your %s Annotation", $siteTitle));
            $annotatorMail->addHeader('X-Mailer', 'PHP/' . phpversion());
            try {
                $annotatorMail->send();
            } catch (Zend_Mail_Exception $e) {
                _log($e);
            }
        }
  
        //notify admins who want notification
        $toAddresses = explode(",", get_option('annotation_email_recipients'));
        $fromAddress = get_option('administrator_email');
        
        foreach ($toAddresses as $toAddress) {
            if (empty($toAddress)) {
                continue;
            }
            $adminMail = new Zend_Mail;
            $body = __("A new annotation to %s has been made.", get_option('site_title'));
            set_theme_base_url('admin');
            $body .= __("Annotation URL for review: %s", record_url($item, 'show', true));
            revert_theme_base_url();
            $adminMail->setBodyText($body);
            $adminMail->setFrom($fromAddress, "$siteTitle");
            $adminMail->addTo($toAddress);
            $adminMail->setSubject(__("New %s Annotation", $siteTitle ));
            $adminMail->addHeader('X-Mailer', 'PHP/' . phpversion());
            try {
                $adminMail->send();
            } catch (Zend_Mail_Exception $e) {
                _log($e);
            }
        }
    }
    
    protected function _createNewGuestUser($post)
    {
        $user = new User();
        $email = $post['annotation_simple_email'];
        $split = explode('@', $email);
        $name = $split[0];
        $username = str_replace('@', 'AT', $name);
        $username = str_replace('.', 'DOT', $username);
        $user->email = $email;
        $user->name = $name;
        $user->username = $username;
        $user->role = 'guest';
        $user->save();
        return $user;
    }
    
}
