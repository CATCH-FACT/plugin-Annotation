<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 */
//defined('ANNOTATION_DIRECTORY') or define('ANNOTATION_DIRECTORY', dirname(__FILE__));


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
        'admin_items_form_item_types',
        'define_acl',
        'define_routes',
        'admin_plugin_uninstall_message',
        'admin_items_search',
//        'admin_items_show',
        'admin_items_show_sidebar',
        'admin_items_browse_detailed_each',
        'item_browse_sql',
//        'before_save_item',
        'after_delete_item',
        'admin_items_browse_simple_each',
        'initialize'
    );

    protected $_filters = array(
        'admin_navigation_main',
        'simple_vocab_routes',
        'admin_dashboard_panels',
        'admin_dashboard_stats'
        );

    protected $_options = array(
        'annotation_page_path',
        'annotation_email_sender',
        'annotation_email_recipients',
        'annotation_consent_text',
        'annotation_collection_id',
        'annotation_incomplete_collection_id',
        'annotation_default_type',
        'annotation_user_profile_type',
        'annotation_simple',
        'annotation_simple_email'
    );

    /**
     * Upgrade the plugin.
     */
    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
        $db = $this->_db;

        if (version_compare($oldVersion, '0.2', '<=')) {
            $sql = "ALTER TABLE `$db->AnnotationTypeElement` ADD `field_scroll` INT UNSIGNED NOT NULL DEFAULT '0'";
            $db->query($sql, array('other_error', 'error'));
        }
    }

    /**
     * Appends some more stats to the dashboard
     * 
     * @return void
     **/
    function filterAdminDashboardStats($stats)
    {   
        if ($contribution_collection_id = get_option('contribution_collection_id')){
            $collection = get_record_by_id('Collection', $contribution_collection_id);
            $stats[] = array(link_to_items_in_collection(metadata($collection, 'total_items'), $props = array(), $action = 'browse', $collectionObj = $collection), __('Unannotated Items'));
        }
        return $stats;
    }

    /**
     * Append search to dashboard
     * 
     * @return void
     **/
    function filterAdminDashboardPanels($panels){
        array_unshift($panels, $this->_addDashboardAnnotationStuff($panels)); //pushing the rest down!
        return $panels;
    }

    function _addDashboardAnnotationStuff($panels){
        
        $db = $this->_db;
        $annotation_types = $db->getTable('AnnotationType')->findAll();
        
        $html = "<H1>" . __("Annotation control") . "</H1><br>";

        if ($contribution_collection_id = get_option('contribution_collection_id')){
            $html .= "<H2>" . __("Existing unannotated Items") . "</H2>";
            $html .= "<br>";
            $collection = get_record_by_id('Collection', $contribution_collection_id);
            $html .= link_to_items_in_collection(__("Unannotated Items Collection: <br>") . metadata($collection, array('Dublin Core', 'Title')) . " (" . metadata($collection, 'total_items') . ")", $props = array(), $action = 'browse', $collectionObj = $collection);
            $html .= "<br><br>";
        }

        $html .= "<H2>" . __("New annotation") . "</H2>";
        
        $html .= '<table>';
        $html .= '    <thead id="types-table-head">';
        $html .= "        <tr>";
        $html .= "            <th>" . __("Name") . "</th>";
        $html .= "            <th>" . __("Annotated Items") . "</th>";
        $html .= "            <th>" . __("Annotate a new item") . "</th>";
        $html .= "        </tr>";
        $html .= "    </thead>";
        $html .= '    <tbody id="types-table-body">';
        
        foreach ($annotation_types as $type){
            $html .= "<tr>";
            $html .= "<td><strong>" . metadata($type, 'display_name') . " (" . __($type->ItemType->name) . ")</strong></td>";
            $html .= "<td><a href='" . url('items/browse/annotated/1/type/' . $type->item_type_id) . "'>" . __("View") . "</a></td>";
            $html .= "<td><a href='" . url('annotation/annotation?annotation_type=' . $type->id) . "' class='add button green'>" . __("New") . ": " . metadata($type, 'display_name') . "</a></td>";
            $html .= "</tr>";
        }
        $html .= '    </tbody>';
        $html .= '</table>';
        
        $annotated_items = get_recent_annotated_items(5);
        $browseHeadings[__('Item')] = null;
        $browseHeadings[__('Publication Status')] = null;
        $browseHeadings[__('Date Added')] = 'added';
        
        $html .= "<H2>" . __("Recently annotated Items") . "</H2>";

        $html .= '<table>';
        $html .= '    <thead id="types-table-head">';
        $html .= '        <tr>';
        $html .= browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => '')); 
        $html .= '        </tr>';
        $html .= '    </thead>';
        $html .= '    <tbody id="types-table-body">';
        
        foreach($annotated_items as $contribItem){

            $item = $contribItem->Item;
            $annotator = $contribItem->Annotator;
            if($annotator->id) {
                $annotatorUrl = url('annotation/annotators/show/id/' . $annotator->id);
            }
                
            if ($item->public) {
                $status = __('Public');
            } else {
                if($contribItem->public) {
                    $status = __('Needs review');
                } else {
                    $status = __('Private annotation');
                }
            }

            $html .= '<tr>';
            $html .= '    <td>' . link_to($item, 'show', metadata($item, array('Dublin Core', 'Title'))) . '</td>';
            $html .= '        <td>' . $status . '</td>';
            $html .= '        <td>' . format_date(metadata($item, 'added'));
            if(!is_null($annotator->id)){
                if($contribItem->anonymous && (is_allowed('Annotation_Items', 'view-anonymous') || $annotator->id == current_user()->id)){
                    $html .= '<span>(' . __('Anonymous') . ')</span>';
                }
//                $html .= ' <a href=' . $annotatorUrl . '>' . metadata($annotator, 'name') . '</a>';
                $html .= " " . __("by") . " <b>" . metadata($annotator, 'name') . "</b>";
            }
            
            $html .= '    </tr>';
        }
        
        $html .= '    </tbody>';
        $html .= '</table>';

    	return $html;
    }

    public function setUp() 
    {
        parent::setUp();
        if(plugin_is_active('UserProfiles')) {
            $this->_hooks[] = 'user_profiles_user_page';
        }
        
    }
    
    public function hookAdminItemsBrowseSimpleEach($item){
        $item = get_current_record('item');
        echo '<ul style="margin: 0; padding: 0;">';
        echo '  <li style="display: inline-block;">';
        echo '      <a style="color:#80BFFF" href="' . url('annotation/annotation/edit/id/' . $item->id) . '">' . __('Annotate') . '</a>';
        echo '  </li>';
        echo '&nbsp&middot&nbsp';

        echo '  <li style="display: inline-block;">';
        echo '      <a style="color:#80BFFF" href="' . url('annotation/clone/clone/id/' . $item->id) . '">' . __('Clone') . '</a>';
        echo '  </li>';
        echo '</ul>';
    }

    
    function link_to_item($text = null, $props = array(), $action = 'show', $item = null)
    {
        if (!$item) {
            $item = get_current_record('item');
        }
        $text = (!empty($text) ? $text : strip_formatting(metadata($item, array('Dublin Core', 'Title'))));
        return link_to($item, $action, $text, $props);
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
            `item_type_id` INT UNSIGNED NOT NULL,   #what kind of item type will be annotated
            `collection_id` INT UNSIGNED NOT NULL, #to which collection will the annotation be added?
            `display_name` VARCHAR(255) NOT NULL, #name of the annotaton type
            `tags_tool_id`  INT UNSIGNED NULL, #special field for the tool that will add the tags
            `file_permissions` ENUM('Disallowed', 'Allowed', 'Required') NOT NULL DEFAULT 'Disallowed',
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        // a table for fields that are automatically annotated
        $sql = "CREATE TABLE IF NOT EXISTS `$db->AnnotationTypeElement` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `type_id` INT UNSIGNED NOT NULL,
            `element_id` INT UNSIGNED NOT NULL,
            `tool_id` INT UNSIGNED NULL, #no element id in because tool will receive all available tale data as json
            `prompt` VARCHAR(255) NOT NULL,
            `english_name` VARCHAR(255) NOT NULL,
            `order` INT UNSIGNED NOT NULL,
            `long_text` BOOLEAN DEFAULT FALSE,
            `repeated_field` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            `score_slider`  TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',           # for: text build-up ()when idx) / annotation threshold (when no idx)
            `date_range_picker` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            `date_picker` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            `autocomplete` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',            #autocomplete flag
            `autocomplete_main_id` INT UNSIGNED NOT NULL DEFAULT '0',           #in which element are we going to search?
            `autocomplete_extra_id` INT UNSIGNED NOT NULL DEFAULT '0',          #maybe some extra field needs to be checked?
            `autocomplete_itemtype_id` INT UNSIGNED NOT NULL DEFAULT '0',       #do we need to restrict to a certain Itemtype?
            `autocomplete_collection_id` NOT NULL DEFAULT '0',                  #do we need to restrict to a certain Collection?
            `field_scroll`  NOT NULL DEFAULT '0',                               #an option to let a field scroll during annotation
            PRIMARY KEY (`id`),
            UNIQUE KEY `type_id_element_id` (`type_id`, `element_id`),
            KEY `order` (`order`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);
        
        // to keep track of items that are annotated
        $sql = "CREATE TABLE IF NOT EXISTS `$db->AnnotationAnnotatedItem` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_id` INT UNSIGNED NOT NULL,
            `annotation_type_id` INT UNSIGNED NOT NULL,   #what kind of annotation type was used?
            `finished` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            `public` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            `anonymous` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `item_id` (`item_id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        // Definition of webservices that will generate annotation values
        $sql = "CREATE TABLE IF NOT EXISTS `$db->AnnotationTools` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
#            `tool_type` ENUM('bash', 'web'),
            `display_name` VARCHAR(255) NOT NULL,
            `description` VARCHAR(255) NULL,
            `command` VARCHAR(255) NOT NULL,
            `get_arguments` VARCHAR(255) NULL,
            `post_arguments` TEXT NULL,
            `output_format` ENUM('raw', 'xml', 'json') NOT NULL,
            `jsonxml_value_node` VARCHAR(255) NOT NULL,         #the main node with the values
            `jsonxml_score_node` VARCHAR(255) NULL,
            `jsonxml_value_sub_node` VARCHAR(255) NULL,         #the if the separate values also are an array
            `jsonxml_score_sub_node` VARCHAR(255) NULL,
            `jsonxml_idx_sub_node` VARCHAR(255) NULL,           #the idx node is for the buildup of small texts based on a score slider
            `tag_or_separator` VARCHAR(255) NULL,
            `order` INT UNSIGNED NULL,
            `validated` ENUM('yes', 'no') NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM;";
        $this->_db->query($sql);

        $this->_createDefaultAnnotationTypes();
        set_option('annotation_email_recipients', get_option('administrator_email'));
    }

    

    //
    public function hookAdminItemsFormItemTypes(){
         queue_js_file('input-autocompleter');
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
        $acl->allow(array('super', 'admin', 'contributor'), 'Annotation_Annotation');
        $acl->allow(null, 'Annotation_Annotation', array('add', 'doannotation', 'element-form-noadd', 'element-form-element', 'element-form-tool', 
                                                        'element-form-tagtool', 'element-form', 'save-form', "tag-form", "type-form", "autocomplete"));

        $acl->addResource('Annotation_Clone');
        $acl->allow(null, 'Annotation_Clone', array('clone', 'cloned'));

        
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

            $router->addRoute('cloneAdmin',
                new Zend_Controller_Router_Route('annotation/clone/:action/*',
                    array('module' => 'annotation',
                          'controller' => 'clone',
                          'action' => 'clone')));


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
            $label = __('Assisted Annotation');
        } else {
            $uri = url('annotation/index');
            $label = __('Assisted Annotation');
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
     * ONLY WHEN LOGGED IN!
     *
     * @param array $nav
     * @return array
     */
    public function filterPublicNavigationMain($nav)
    {
        if ($user = current_user()){ #only when logged in
            $nav[] = array( 'label' => __('Annotate an Item'),
                            'uri'   => annotation_annotate_url(),
                            'visible' => true
            );
        }
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
//        _log("==== Appending routes to simple vocab");
        $routes[] = array('module' => 'annotation',
                          'controller' => 'annotation',
                          'actions' => array('add', 'doannotation', 'element-form-noadd', 'element-form-tool', 'element-form', "tag-form", "type-form", "save-form"));
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
        
        $item = get_current_record('item');
        echo '<a href="' . url('annotation/annotation/edit/id/' . $item->id) . '" class="big blue button">' . __('Annotate Item') . '</a>';
        echo '<a href="' . url('annotation/clone/clone/id/' . $item->id) . '" class="big green button">' . __('Clone') . '</a>';        
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
        
        //setting up some annotation types
        $aType = new AnnotationType;
        $aType->item_type_id = 12;
        $aType->collection_id = 4;
        $aType->display_name = 'Verteller';
        $aType->file_permissions = 'Allowed';
        $aType->save();

        $bType = new AnnotationType;
        $bType->item_type_id = 18;
        $bType->collection_id = 1;
        $bType->display_name = 'Testverhaal';
        $bType->file_permissions = 'Allowed';
        $bType->tags_tool_id = 8;
        $bType->save();


        $storyType = new AnnotationType;
        $storyType->item_type_id = 18;
        $storyType->collection_id = 1;
        $storyType->display_name = 'Volksverhaal';
        $storyType->file_permissions = 'Allowed';
        $storyType->tags_tool_id = 8;
        $storyType->save();
        
        //setting up some tools
        $toolExtreme = new AnnotationTool;
        $toolExtreme->display_name = "Extreme waarde detector";
        $toolExtreme->description = "Detecteert extreme waarden in tekst";
        $toolExtreme->command = "http://bookstore.ewi.utwente.nl:24681/extreme";
        $toolExtreme->get_arguments = "";
        //WARNING: STRONG DUTCH PROFANITY BELOW
        $toolExtreme->post_arguments = '{
            "extreme_terms":
            {
                "not_allowed":[
                    "cock", "cocks", "lul", "pik", "piemel", "piemels", "kut", "neuk", "neuken", "rampetampen", "beffen", "dildo", "dildo\'s", "vibrator", 
                    "vibrators", "masturberen", "masturbatie", "vingeren", "kloten", "kloot", "hoer", "hoeren", "bordeel", "bordelen", "temeier", "verkracht", 
                    "verkrachten", "pedo", "pedofiel", "homo", "homo\'s", "cock sucker", "homofiel", "faggot", "faggots", "castreren", "castratie", "tampeloerus", 
                    "sperma", "fist", "fuck", "plompzakken", "pis", "pissen", "gemecht", "gemacht", "cum", "bdsm", " sm ", "masochisme", "sadisme", "bondage", 
                    "bruinwerk", "bruinwerker", "flikkers", "orgie", "sodom", "dp ", "deep throat", "tieten", "druiper", "herpes", "soa", "klaarkomen", "orgasme", 
                    "travestiet", "memmen", "godverdomme", "kanker", "tering", "tyfus", "hoer", "mongool", "debiel", "jostie", "klootzak", "nazi", "nikker", "neger", 
                    "neger.", "nigger", "niggers", "vuile turk", "kutmarokkaan", "spleetoog", "spleetogen", "jappen", "roodhuiden", "roodhuid", "zwartjoekel", "zwartje", 
                    "zandneger", "zandnegers", "spaghettivreter", "spaghettivreters", "chink", "chicks", "olijfkakker", "olijfkakkers", "Hitler", "dom blond", 
                    "dom blondje", "optieften", "optyfen", "opgetieft", "opgetyft", "opzouten", "opkankeren", "opgekankerd", "oprotten", "opgerot", "Parkinson"
                ], 
                "combinatory":[
                    "trekken", "rukken", "naaien", "pompen", "paal", "castreren", "castratie", "zeik", "stront", "zaad", "sperma", "fist", "fuck", "pis", "naaien", 
                    "tongzoen", "gemecht", "gemacht", "pijpen", "sadisme", "binden", "bindt vast", "naad", "spleet", "poot", "poten", "kont", "reet", "ballen", 
                    "spuit", "fluit", "bevredigen", "tongen", "Tongzoen", "flikker", "godverdomme", "kanker", "tering", "tyfus", "hoer", "schijt", "stront", "kak", 
                    "mongool", ".mongool", "debiel", "jostie", "klootzak", "nazi", "nikker", "neger", "spleetoog", "jappen", "roodhuiden", "roodhuid", "spleetogen", 
                    "zwartjoekel", "Turk", "Turk", "Turken", "Marokkaan", "Marokkanen", "Antilliaan", "Antillianen", "Surinamer", "Surinamers", "mocro", "mocro\'s", 
                    "jood", "joods", "joden", "luie", "vuile", "smerige", "dief", "dieven", "crimineel", "criminelen", "stelen", "steelt", "fiets", "kliko", "vuilnisbelt", 
                    "afval", "moordenaar", "gevangenis", "Nederlander", "Belg", "Duitser", "buitenlander", "allochtoon", "allochtonen", "dom", "vies", "verkrachting", 
                    "werkloos", "ww", "GAK", "moe", "uitkering", "zwartjoekel", "zwartje", "gore", "sodemieter", "gastarbeider", "gas", "concentratiekamp", "douche", 
                    "Hitler", "Ethiopië", "triatlon", "hardlopen", "rennen", "wijf", "keuken", "mokkel", "slet", ".slet", "aanrecht", "ketting", "dom blond", "koning", 
                    "prins", "vuilnisbakken", "vuilnisbak", "vuilnis", "optieften", "optyfen", "opzouten", "opkankeren", "oprotten", "zakkenvuller", "belasting", 
                    "Juliana", "Bernhard", "Beatrix", "Claus", "Willem-Alexander", "Maxima", "president", "Amalia", "Mabel", "depressie", "God", "Allah", "Mohammed", 
                    "pedo", "varken", "ziek", "sex", "gas", "mongool", ".mongool"
                ]
            }
        }'; //send a list of extreme words?
        $toolExtreme->output_format = "json";
        $toolExtreme->jsonxml_value_node = "annotation.value";
        $toolExtreme->jsonxml_score_node = "score";
        $toolExtreme->jsonxml_score_sub_node = "";
        $toolExtreme->jsonxml_value_sub_node = "";
        $toolExtreme->jsonxml_idx_sub_node = "";
        $toolExtreme->save();

        $toolCountclass = new AnnotationTool;
        $toolCountclass->display_name = "Word count class";
        $toolCountclass->description = "Telt het aantal woorden in de tekst, en bepaalt een klasse";
        $toolCountclass->command = "http://bookstore.ewi.utwente.nl:24681/wordcountclass";
        $toolCountclass->get_arguments = "";
        $toolCountclass->post_arguments = "";
        $toolCountclass->output_format = "json";
        $toolCountclass->jsonxml_value_node = "annotation.value";
        $toolCountclass->jsonxml_score_sub_node = "";
        $toolCountclass->jsonxml_value_sub_node = "";
        $toolCountclass->jsonxml_idx_sub_node = "";
        $toolCountclass->save();

        $toolDescription = new AnnotationTool;
        $toolDescription->display_name = "Summary";
        $toolDescription->description = "Maakt een samenvatting van de tekst";
        $toolDescription->command = "http://bookstore.ewi.utwente.nl:24681/summary";
        $toolDescription->get_arguments = "";
        $toolDescription->post_arguments = "";
        $toolDescription->output_format = "json";
        $toolDescription->jsonxml_value_node = "annotation.summary";
        $toolDescription->jsonxml_score_sub_node = "score";
        $toolDescription->jsonxml_value_sub_node = "sentence";
        $toolDescription->jsonxml_idx_sub_node = "idx";
        $toolDescription->save();
        
        $toolCount = new AnnotationTool;
        $toolCount->display_name = "Word count";
        $toolCount->description = "Telt het aantal woorden in de tekst";
        $toolCount->command = "http://bookstore.ewi.utwente.nl:24681/wordcount";
        $toolCount->get_arguments = "";
        $toolCount->post_arguments = "";
        $toolCount->output_format = "json";
        $toolCount->jsonxml_value_node = "annotation.value";
        $toolCount->jsonxml_score_sub_node = "";
        $toolCount->jsonxml_value_sub_node = "";
        $toolCount->jsonxml_idx_sub_node = "";
        $toolCount->save();

        $toolThree = new AnnotationTool;
        $toolThree->display_name = "Language detection";
        $toolThree->description = "Detecteert de taal van de tekst";
        $toolThree->command = "http://bookstore.ewi.utwente.nl:24681/language";
        $toolThree->get_arguments = "";
        $toolThree->post_arguments = "";
        $toolThree->output_format = "json";
        $toolThree->jsonxml_value_node = "annotation.language"; 
        $toolThree->jsonxml_score_sub_node = "";
        $toolThree->jsonxml_value_sub_node = "";
        $toolThree->jsonxml_idx_sub_node = "";
        $toolThree->save();

        $toolFour = new AnnotationTool;
        $toolFour->display_name = "Subgenre detection";
        $toolFour->description = "Detecteert het subgenre van de tekst";
        $toolFour->command = "http://bookstore.ewi.utwente.nl:24681/subgenre";
        $toolFour->get_arguments = "";
        $toolFour->post_arguments = "";
        $toolFour->output_format = "json";
        $toolFour->jsonxml_value_node = "annotation.subgenre"; 
        $toolFour->jsonxml_score_sub_node = "";
        $toolFour->jsonxml_value_sub_node = "";
        $toolFour->jsonxml_idx_sub_node = "";
        $toolFour->save();

        //summary
        //annotation.summary consists of array [score, idx, sentence]
        $toolFive = new AnnotationTool;
        $toolFive->display_name = "Summary generation";
        $toolFive->description = "Maakt een samenvatting van de tekst";
        $toolFive->command = "http://bookstore.ewi.utwente.nl:24681/summary";
        $toolFive->get_arguments = "";
        $toolFive->post_arguments = "";
        $toolFive->output_format = "json";
        $toolFive->jsonxml_value_node = "annotation.summary";
        $toolFive->jsonxml_score_sub_node = "score";
        $toolFive->jsonxml_value_sub_node = "sentence";
        $toolFive->jsonxml_idx_sub_node = "idx";
        $toolFive->save();

        $toolSix = new AnnotationTool;
        $toolSix->display_name = "Keywords/Tags generation";
        $toolSix->description = "Maakt lijst trefwoorden van de tekst";
        $toolSix->command = "http://bookstore.ewi.utwente.nl:24681/keywords";
        $toolSix->get_arguments = "";
        $toolSix->post_arguments = "";
        $toolSix->output_format = "json";
        $toolSix->jsonxml_value_node = "annotation.keywords";
        $toolSix->jsonxml_score_sub_node = "score";
        $toolSix->jsonxml_value_sub_node = "keyword";
        $toolSix->jsonxml_idx_sub_node = "";
        $toolSix->save();

        $toolSeven = new AnnotationTool;
        $toolSeven->display_name = "Named Entities other";
        $toolSeven->description = "Maakt lijst met named entities zonder locaties";
        $toolSeven->command = "http://bookstore.ewi.utwente.nl:24681/nerother";
        $toolSeven->get_arguments = "";
        $toolSeven->post_arguments = "";
        $toolSeven->output_format = "json";
        $toolSeven->jsonxml_value_node = "annotation.entities";
        $toolSeven->jsonxml_score_sub_node = "score";
        $toolSeven->jsonxml_value_sub_node = "keyword";
        $toolSeven->jsonxml_idx_sub_node = "";
        $toolSeven->save();
        
        $toolEight = new AnnotationTool;
        $toolEight->display_name = "Named Entities locations";
        $toolEight->description = "Maakt lijst named entities locaties uit de tekst";
        $toolEight->command = "http://bookstore.ewi.utwente.nl:24681/nerlocations";
        $toolEight->get_arguments = "";
        $toolEight->post_arguments = "";
        $toolEight->output_format = "json";
        $toolEight->jsonxml_value_node = "annotation.entities";
        $toolEight->jsonxml_score_sub_node = "score";
        $toolEight->jsonxml_value_sub_node = "keyword";
        $toolEight->jsonxml_idx_sub_node = "";
        $toolEight->save();
        
        //input type elements
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $bType->id;
        $textElement->element_id = 1;
        $textElement->prompt = 'Voer de originele tekst in';
        $textElement->english_name = 'text';
        $textElement->order = 1;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = true;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $bType->id;
        $textElement->element_id = 93;
        $textElement->prompt = 'De namen van locaties die gevonden kunnen worden in de tekst';
        $textElement->english_name = 'named entity location';
        $textElement->order = 28;
        $textElement->tool_id = 10;                  //add tool when available
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = true;              //automplete, yes please
        $textElement->autocomplete_main_id = 93;        //look in identifiers
        $textElement->autocomplete_extra_id = 0;        //and show the titles
        $textElement->autocomplete_itemtype_id = 18;    //don't restrict to certain item type
        $textElement->autocomplete_collection_id = 1;   //but only look in collection volksverhalen
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 1;
        $textElement->prompt = 'Voer de originele tekst in';
        $textElement->english_name = 'text';
        $textElement->order = 1;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = true;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 41;
        $textElement->prompt = 'De samenvatting van de tekst';
        $textElement->english_name = 'description';
        $textElement->order = 20;
        $textElement->tool_id = $toolDescription->id;
        $textElement->score_slider = true;
        $textElement->long_text = true;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 62; //$dcTitleElement->id;
        $textElement->prompt = 'Extreme values in text';
        $textElement->english_name = 'extreme';
        $textElement->order = 26;
        $textElement->tool_id = $toolExtreme->id;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 61;
        $textElement->prompt = 'Literary text';
        $textElement->english_name = 'literary';
        $textElement->order = 25;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 43;
        $textElement->prompt = 'Het identificatienummer';
        $textElement->english_name = 'identifier';
        $textElement->order = 8;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 40;
        $textElement->prompt = 'Voer de datum in';
        $textElement->english_name = 'date';
        $textElement->order = 4;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = true;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 60;
        $textElement->prompt = 'De verzamelaar van het verhaal';
        $textElement->english_name = 'collector';
        $textElement->order = 2;                        
        $textElement->tool_id = false;                  //no tool for auto annotation
        $textElement->score_slider = false;             //we're not making any text
        $textElement->long_text = false;                //a small field will do
        $textElement->repeated_field = true;            //there can be multiple collectors
        $textElement->date_picker = false;              //this is no date
        $textElement->date_range_picker = false;        //this is no date
        $textElement->autocomplete = true;           //automplete, yes please
        $textElement->autocomplete_main_id = 50;        //look in titles
        $textElement->autocomplete_extra_id = false;    //and nowhere else
        $textElement->autocomplete_itemtype_id = false; //dont' restrict to certain item type
        $textElement->autocomplete_collection_id = 9;   //but only look in collection verzamelaars
        $textElement->autocomplete = true;
        $textElement->autocomplete_main_id = 50;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = 9;
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 39;
        $textElement->prompt = 'De verteller van het verhaal';
        $textElement->english_name = 'creator';
        $textElement->order = 3;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = true;              //automplete, yes please
        $textElement->autocomplete_main_id = 50;        //look in titles
        $textElement->autocomplete_extra_id = false;    //and nowhere else
        $textElement->autocomplete_itemtype_id = false; //dont' restrict to certain item type
        $textElement->autocomplete_collection_id = 4;   //but only look in collection vertellers
        $textElement->save();

/*        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 37;
        $textElement->prompt = 'De medewerker die het verhaal in de verhalenbank invoert (overbodig)';
        $textElement->english_name = 'contributor';
        $textElement->order = 9;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();
*/        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 50;
        $textElement->prompt = 'De titel van het verhaal';
        $textElement->english_name = 'title';
        $textElement->order = 9;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 48;
        $textElement->prompt = 'De bron van het verhaal';
        $textElement->english_name = 'source';
        $textElement->order = 5;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 51;
        $textElement->prompt = 'Het type bron van het verhaal (keuze)';
        $textElement->english_name = 'type';
        $textElement->order = 6;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = true;
        $textElement->autocomplete_main_id = 51;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = 1;
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 47;
        $textElement->prompt = 'Heeft het Meertens de rechten van dit verhaal?';
        $textElement->english_name = 'rights';
        $textElement->order = 7;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 53;
        $textElement->prompt = 'Overig commentaar of informatie over de tekst, of de manier waarop deze verkregen is.';
        $textElement->english_name = 'commentary';
        $textElement->order = 40;
        $textElement->tool_id = false;
        $textElement->score_slider = false;
        $textElement->long_text = true;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 44;
        $textElement->prompt = 'De taal waarin het verhaal verteld is';
        $textElement->english_name = 'language';
        $textElement->order = 24;
        $textElement->tool_id = $toolThree->id;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 58;
        $textElement->prompt = 'Het subgenre van het verhaal';
        $textElement->english_name = 'subgenre';
        $textElement->order = 22;
        $textElement->tool_id = $toolFour->id;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 49;
        $textElement->prompt = 'Het verhaaltype of verhaaltypen';
        $textElement->english_name = 'subject';
        $textElement->order = 21;
        $textElement->tool_id = false;                  //add tool when available
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = true;           //automplete, yes please
        $textElement->autocomplete_main_id = 43;        //look in identifiers
        $textElement->autocomplete_extra_id = 50;       //and show the titles (and show titles as well?)
        $textElement->autocomplete_itemtype_id = false; //dont' restrict to certain item type
        $textElement->autocomplete_collection_id = 3;   //but only look in collection verhaaltypen
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 52;
        $textElement->prompt = 'De motieven die gevonden kunnen worden in de tekst';
        $textElement->english_name = 'motif';
        $textElement->order = 23;
        $textElement->tool_id = false;                  //add tool when available
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = true;              //automplete, yes please
        $textElement->autocomplete_main_id = 43;        //look in identifiers
        $textElement->autocomplete_extra_id = 50;       //and show the titles
        $textElement->autocomplete_itemtype_id = false; //dont' restrict to certain item type
        $textElement->autocomplete_collection_id = 3;   //but only look in collection verhaaltypen
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 63;
        $textElement->prompt = 'De namen (niet locaties) die gevonden kunnen worden in de tekst';
        $textElement->english_name = 'named entity';
        $textElement->order = 27;
        $textElement->tool_id = 9;                  //add tool when available
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = true;              //automplete, yes please
        $textElement->autocomplete_main_id = 63;        //look in identifiers
        $textElement->autocomplete_extra_id = 0;       //and show the titles
        $textElement->autocomplete_itemtype_id = 18; //dont' restrict to certain item type
        $textElement->autocomplete_collection_id = 1;   //but only look in collection volksverhalen
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 93;
        $textElement->prompt = 'De namen van locaties die gevonden kunnen worden in de tekst';
        $textElement->english_name = 'named entity location';
        $textElement->order = 28;
        $textElement->tool_id = 10;                  //add tool when available
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = true;              //automplete, yes please
        $textElement->autocomplete_main_id = 93;        //look in identifiers
        $textElement->autocomplete_extra_id = 0;        //and show the titles
        $textElement->autocomplete_itemtype_id = 18;    //don't restrict to certain item type
        $textElement->autocomplete_collection_id = 1;   //but only look in collection volksverhalen
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 65;
        $textElement->prompt = 'De hoofdlocatie waar te tekst zich afspeelt, of over gaat';
        $textElement->english_name = 'place of action';
        $textElement->order = 29;
        $textElement->tool_id = false;                  //add tool when available
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = true;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = true;              //automplete, yes please
        $textElement->autocomplete_main_id = 65;        //look in identifiers
        $textElement->autocomplete_extra_id = 0;        //and show the titles
        $textElement->autocomplete_itemtype_id = 18;    //restrict to certain item type
        $textElement->autocomplete_collection_id = 1;   //but only look in collection volksverhalen
        $textElement->save();

        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 68;
        $textElement->prompt = 'De kloekecode van waar het verhaal vandaan komt';
        $textElement->english_name = 'kloeke georeference';
        $textElement->order = 30;
        $textElement->tool_id = false;                  //add tool when available
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = true;              //automplete, yes please
        $textElement->autocomplete_main_id = 30;        //look in other kloekecodes for now
        $textElement->autocomplete_extra_id = 0;        //nope
        $textElement->autocomplete_itemtype_id = 0;     //don't restrict to certain item type
        $textElement->autocomplete_collection_id = 0;   //no collection
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 94;
        $textElement->prompt = 'De hoeveelheid woorden in de tekst';
        $textElement->english_name = 'word count';
        $textElement->order = 32;
        $textElement->tool_id = $toolCount->id;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();
        
        $textElement = new AnnotationTypeElement;
        $textElement->type_id = $storyType->id;
        $textElement->element_id = 95;
        $textElement->prompt = 'De klasse van de hoeveelheid woorden in de tekst';
        $textElement->english_name = 'word count group';
        $textElement->order = 33;
        $textElement->tool_id = $toolCountclass->id;
        $textElement->score_slider = false;
        $textElement->long_text = false;
        $textElement->repeated_field = false;
        $textElement->date_picker = false;
        $textElement->date_range_picker = false;
        $textElement->autocomplete = false;
        $textElement->autocomplete_main_id = false;
        $textElement->autocomplete_extra_id = false;
        $textElement->autocomplete_itemtype_id = false;
        $textElement->autocomplete_collection_id = false;
        $textElement->save();
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
/*
            $publicMessage = '';
            if(is_allowed($item, 'edit')) {
                if($annotatedItem->public) {
                    $publicMessage = __("This item can be made public.");
                } else {
                    $publicMessage = __("This item cannot be made public.");
                }
                $html .= "<p><strong>$publicMessage</strong></p>";
            }*/
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
