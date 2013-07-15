<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */

define('ANNOTATION_PLUGIN_DIR', dirname(__FILE__));
define('ANNOTATION_HELPERS_DIR', ANNOTATION_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'helpers');
define('ANNOTATION_FORMS_DIR', ANNOTATION_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'forms');

require_once ANNOTATION_HELPERS_DIR . DIRECTORY_SEPARATOR . 'ThemeHelpers.php';


/**
 * Annotation plugin class
 *
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */
class AnnotationPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'define_acl',
        'define_routes',
        'admin_plugin_uninstall_message',
        'admin_items_search',
        'admin_items_show_sidebar',
        'admin_items_browse_detailed_each',
        'item_browse_sql',
        'before_save_item',
        'after_delete_item',
        'initialize'
    );

    protected $_filters = array(
        'admin_navigation_main',
        'public_navigation_main',
        'simple_vocab_routes',
        'item_search_filters',
        'guest_user_links'
        );

    protected $_options = array(
        'annotation_page_path',
        'annotation_email_sender',
        'annotation_email_recipients',
        'annotation_consent_text',
        'annotation_collection_id',
        'annotation_default_type',
        'annotation_user_profile_type',
        'annotation_simple',
        'annotation_simple_email'
    );

    public function setUp() 
    {
        parent::setUp();
        if(plugin_is_active('UserProfiles')) {
            $this->_hooks[] = 'user_profiles_user_page';
        }
    }
    
    /**
     * Add the translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }    
    
    /**
     * Annotation install hook
     */
    public function hookInstall()
    {
        $db = $this->_db;
        $sql = "CREATE TABLE IF NOT EXISTS `$db->AnnotationType` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_type_id` INT UNSIGNED NOT NULL,
            `display_name` VARCHAR(255) NOT NULL,
            `file_permissions` ENUM('Disallowed', 'Allowed', 'Required') NOT NULL DEFAULT 'Disallowed',
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_type_id` (`item_type_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `$db->AnnotationTypeElement` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `type_id` INT UNSIGNED NOT NULL,
            `element_id_out` INT UNSIGNED NOT NULL,
            `tool_id` INT UNSIGNED NULL,
            `element_id_in` INT UNSIGNED NOT NULL,
            `prompt` VARCHAR(255) NOT NULL,
            `order` INT UNSIGNED NOT NULL,
            `long_text` BOOLEAN DEFAULT TRUE,
            PRIMARY KEY (`id`),
            UNIQUE KEY `type_id_element_id` (`type_id`, `element_id_in`, `element_id_out`),
            KEY `order` (`order`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `$db->AnnotationAnnotatedItem` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_id` INT UNSIGNED NOT NULL,
            `public` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            `anonymous` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_id` (`item_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `$db->AnnotationTools` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tool_type` ENUM('bash', 'web'),
            `display_name` VARCHAR(255) NOT NULL,
            `description` VARCHAR(255) NULL,
            `command` VARCHAR(255) NOT NULL,
            `arguments` VARCHAR(255) NULL,
            `output_type` ENUM('bash', 'web', 'file') NOT NULL,
            `output_format` ENUM('raw', 'xml', 'json') NOT NULL,
            `tag_or_separator` VARCHAR(255) NULL,
            `order` INT UNSIGNED NULL,
            `validated` ENUM('yes', 'no') NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

#        $this->_createDefaultAnnotationTypes();
        set_option('annotation_email_recipients', get_option('administrator_email'));        
    }

    /**
     * Annotation uninstall hook
     */
    public function hookUninstall()
    {
        // Delete all the Annotation options
        foreach ($this->_options as $option) {
            delete_option($option);
        }
        $db = $this->_db;
        // Drop all the Annotation tables
        $sql = "DROP TABLE IF EXISTS
            `$db->AnnotationType`,
            `$db->AnnotationTypeElement`,
            `$db->AnnotationAnnotator`,
            `$db->AnnotationTool`,
            `$db->AnnotationAnnotatedItem`,
            `$db->AnnotationAnnotatorField`,
            `$db->AnnotationAnnotatorValue`;";
        $this->_db->query($sql);
    }

    public function hookUpgrade($args)
    {
    }

    public function hookAdminPluginUninstallMessage()
    {
        echo '<p><strong>Warning</strong>: Uninstalling the Annotation plugin
            will remove all information about annotators, as well as the
            data that marks which items in the archive were annotated.</p>
            <p>The annotated items themselves will remain.</p>';
    }

    /**
     * Annotation define_acl hook
     * Restricts access to admin-only controllers and actions.
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('Annotation_Annotation');
        $acl->allow(array('super', 'admin', 'researcher', 'contributor'), 'Annotation_Annotation');
        if(get_option('annotation_simple')) {
            $acl->allow(null, 'Annotation_Annotation', array('show', 'annotate', 'thankyou', 'my-annotations', 'type-form'));            
        } else {
            $acl->allow('guest', 'Annotation_Annotation', array('show', 'annotate', 'thankyou', 'my-annotations', 'type-form'));
        }
        
        $acl->allow(null, 'Annotation_Annotation', array('annotate', 'terms'));
        
        $acl->addResource('Annotation_Annotators');
        $acl->allow(null, 'Annotation_Annotators');
        
        $acl->addResource('Annotation_Items');
        $acl->allow(null, 'Annotation_Items');
        $acl->deny('guest', 'Annotation_Items');
        $acl->deny(array('researcher', 'contributor'), 'Annotation_Items', 'view-anonymous');
        $acl->addResource('Annotation_Types');
        $acl->allow(array('super', 'admin'), 'Annotation_Types');
        $acl->addResource('Annotation_Settings');
        $acl->allow(array('super', 'admin'), 'Annotation_Settings');
        $acl->addResource('Annotation_Tools');
        $acl->allow(array('super', 'admin'), 'Annotation_Tools');
    }

    /**
     * Annotation define_routes hook
     * Defines public-only routes that set the annotation controller as the
     * only accessible one.
     */
    public function hookDefineRoutes($args)
    {
        $router = $args['router'];
        // Only apply custom routes on public theme.
        // The wildcards on both routes make these routes always apply for the
        // annotation controller.

        // get the base path
        $bp = get_option('annotation_page_path');
        if ($bp) {
            $router->addRoute('annotationCustom',
                new Zend_Controller_Router_Route("$bp/:action/*",
                    array('module'     => 'annotation',
                          'controller' => 'annotation',
                          'action'     => 'annotate')));
        } else {
                
            $router->addRoute('annotationDefault',
                  new Zend_Controller_Router_Route('annotation/:action/*',
                        array('module'     => 'annotation',
                              'controller' => 'annotation',
                              'action'     => 'annotate')));
                
        }
      
        if(is_admin_theme()){
            $router->addRoute('annotationAdmin',
                new Zend_Controller_Router_Route('annotation/:controller/:action/*',
                    array('module' => 'annotation',
                          'controller' => 'index',
                          'action' => 'index')));
        }    
    }

    /**
     * Append a Annotation entry to the admin navigation.
     *
     * @param array $nav
     * @return array
     */
    public function filterAdminNavigationMain($nav)
    {          
        $annotationCount = get_db()->getTable('AnnotationAnnotatedItems')->count();
        if($annotationCount > 0) {
            $uri = url('annotation/items');
            $label = __('Annotated Items');
        } else {
            $uri = url('annotation/index');
            $label = __('Annotation');
        }        
        
        $nav[] = array(
            'label' => $label,
            'uri' => $uri,
            'resource' => 'Annotation_Annotation',
            'privilege' => 'browse'
        );
        return $nav;
    }

    /**
     * Append a Annotation entry to the public navigation.
     *
     * @param array $nav
     * @return array
     */
    public function filterPublicNavigationMain($nav)
    {
       $nav[] = array(
        'label' => __('annotate an Item'),
        'uri'   => annotation_annotate_url(),
        'visible' => true
       );
        return $nav;
    }

    /**
     * Append routes that render element text form input.
     *
     * @param array $routes
     * @return array
     */
    public function filterSimpleVocabRoutes($routes)
    {
       
        $routes[] = array('module' => 'annotation',
                          'controller' => 'annotation',
                          'actions' => array('type-form', 'annotate'));
        return $routes;
    }

    public function filterItemSearchFilters($displayArray, $args)
    {
        $request_array = $args['request_array'];
        if(isset($request_array['status'])) {
            $displayArray['Status'] = $request_array['status'];
        }
        if(isset($request_array['annotator'])) {
            $displayArray['Annotator'] = $this->_db->getTable('User')->find($request_array['annotator'])->name;
        }
        return $displayArray;
    }
    
    /**
     * Append Annotation search selectors to the advanced search page.
     *
     * @return string HTML
     */
    public function hookAdminItemsSearch()
    {
        $html = '<div class="field">';
        $html .= '<div class="two columns alpha">';
        $html .= get_view()->formLabel('annotated', 'Annotation Status');
        $html .= '</div>';
        $html .= '<div class="inputs five columns omega">';
        $html .= '<div class="input-block">';
        $html .= get_view()->formSelect('annotated', null, null, array(
           ''  => 'Select Below',
           '1' => 'Only Annotated Items',
           '0' => 'Only Non-Annotated Items'
        ));
        $html .= '</div></div></div>';
        echo $html;
    }

    public function hookAdminItemsShowSidebar($args)
    {
        
        $htmlBase = $this->_adminBaseInfo($args);
        echo "<div class='panel'>";
        echo "<h4>" . __("Annotation") . "</h4>";
        echo $htmlBase;
        echo "</div>";
    }

    public function hookAdminItemsBrowseDetailedEach($args)
    {
        echo $this->_adminBaseInfo($args);       
    }

    /**
     * Deal with Annotation-specific search terms.
     *
     * @param Omeka_Db_Select $select
     * @param array $params
     */
    public function hookItemBrowseSql($args)
    {
    
    $select = $args['select'];
    $params = $args['params'];
  
        if (($request = Zend_Controller_Front::getInstance()->getRequest())) {
            $db = get_db();
           
            $annotated = $request->get('annotated');
        
            if (isset($annotated)) {
                if ($annotated === '1') {
                    $select->joinInner(
                            array('cci' => $db->AnnotationAnnotatedItem),
                            'cci.item_id = items.id',                            
                            array()
                     );
                } else if ($annotated === '0') {
                    $select->where("items.id NOT IN (SELECT `item_id` FROM {$db->AnnotationAnnotatedItem})");
                }
            }

            $annotator_id = $request->get('annotator_id');
            if (is_numeric($annotator_id)) {
                $select->joinInner(
                        array('cci' => $db->AnnotationAnnotatedItem),
                       'cci.item_id = items.id',                     
                        array('annotator_id')
                );
                $select->where('cci.annotator_id = ?', $annotator_id);
            }
        }
    }

    /**
     * Create reasonable default entries for annotation types.
     */
    private function _createDefaultAnnotationTypes()
    {
        $elementTable = $this->_db->getTable('Element');
        
        $storyType = new AnnotationType;
        $storyType->item_type_id = 1;
        $storyType->display_name = 'Story';
        
        $storyType->file_permissions = 'Allowed';
        $storyType->save();
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $dcTitleElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Title');
        $textElement->element_id = $dcTitleElement->id;
        $textElement->prompt = 'Title';
        $textElement->order = 1;
        $textElement->long_text = false;
        $textElement->save();
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $itemTypeMetadataTextElement = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', 'Text');
        $textElement->element_id = $itemTypeMetadataTextElement->id;
        $textElement->prompt = 'Story Text';
        $textElement->order = 2;
        $textElement->long_text = true;
        $textElement->save();

/*        $imageType = new AnnotationType;
        $imageType->item_type_id = 6;
        $imageType->display_name = 'Image';
        $imageType->file_permissions = 'Required';
        $imageType->save();
*/
/*
        $descriptionElement = new AnnotationTypeElement;
        $descriptionElement->type_id = $imageType->id;
        $dcDescriptionElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Description');
        $descriptionElement->element_id = $dcDescriptionElement->id;
        $descriptionElement->prompt = 'Image Description';
        $descriptionElement->order = 1;
        $descriptionElement->long_text = true;
        $descriptionElement->save();
*/
    }
    
    public function hookBeforeSaveItem($args){
      $item = $args['record'];
      if($item->exists()) {
          //prevent admins from overriding the annotater's assertion of public vs private
          $annotationItem = $this->_db->getTable('AnnotationAnnotatedItem')->findByItem($item);
          if($annotationItem) {
              if(!$annotationItem->public && $item->public) {
                  $item->public = false;
                  Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->addMessage("Cannot override annotator's desire to leave annotation private", 'error');
              }
          }          
      }
    }  

    public function hookAfterDeleteItem($args)
    {
        $item = $args['record'];
        $annotationItem = $this->_db->getTable('AnnotationAnnotatedItem')->findByItem($item);
        if($annotationItem) {
            $annotationItem->delete();
        }
    }
    
    public function hookUserProfilesUserPage($args)
    {
        $user = $args['user'];
        $annotationCount = $this->_db->getTable('AnnotationAnnotatedItem')->count(array('annotator'=>$user->id));
        if($annotationCount !=0) {
            echo "<a href='" . url('annotation/annotators/show/id/' . $user->id) . "'>Annotated Items ($annotationCount)";
        }
    }
    
    public function filterGuestUserLinks($nav)
    {
        $nav['Annotation'] = array('label'=>'My Annotations',
                                     'uri'=> annotation_annotate_url('my-annotations')                
                                    );
        return $nav;
    } 
   
    private function _adminBaseInfo($args) 
    {
        $item = $args['item'];
        $annotatedItem = $this->_db->getTable('AnnotationAnnotatedItem')->findByItem($item);
        if($annotatedItem) {
            $html = '';
            $name = $annotatedItem->getAnnotator()->name;
            $html .= "<p><strong>" . __("Annotated by:") . "</strong><span class='annotation-annotator'> $name</span></p>";

            $publicMessage = '';
            if(is_allowed($item, 'edit')) {
                if($annotatedItem->public) {
                    $publicMessage = __("This item can be made public.");
                } else {
                    $publicMessage = __("This item cannot be made public.");
                }
                $html .= "<p><strong>$publicMessage</strong></p>";
            }
            return $html;
        }
    }
    
    private function _annotatorsToGuestUsers($annotatorsData)
    {
        $map = array(); //annotator->id => $user->id
        foreach($annotatorsData as $index=>$annotator) {
            $user = new User();
            $user->email = $annotator['email'];
            $user->name = $annotator['name'];
            //make sure username is 6 chars long and unique
            //base it on the email to lessen character restriction problems
            $explodedEmail = explode('@', $user->email);
            $username = $explodedEmail[0];
            $username = str_replace('.', '', $username);
            $user->username = $username;
            $user->active = true;
            $user->role = 'guest';
            $user->setPassword($user->email);
            $user->save();
            $map[$annotator['id']] = $user->id;
            $activation = UsersActivations::factory($user);
            $activation->save();
            release_object($user);
            release_object($activation);
        }        
        return $map;
    }    
   
    public function _mapOwners($contribItemData, $map)
    {
        $itemTable = $this->_db->getTable('Item');
        foreach($contribItemData as $contribItem) {
            $item = $itemTable->find($contribItem['item_id']);
            $item->owner_id = $map[$contribItem['annotator_id']];
            $item->save();
            release_object($item);
        }
    }
    
    public function pluginOptions()
    {
        return $this->_options;
    }
}
