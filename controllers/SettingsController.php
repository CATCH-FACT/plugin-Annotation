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
class Annotation_SettingsController extends Omeka_Controller_AbstractActionController
{
    /**
     * Index action; simply forwards to annotateAction.
     */
    public function indexAction()
    {
        $this->_redirect('annotation/settings/edit');
    }
    
    /**
     * Edit action
     */
    public function editAction()
    {
        $form = $this->_getForm();
        $defaults = $this->_getOptions();
        $form->setDefaults($defaults);
        
        if (isset($_POST['submit'])) {
            if ($form->isValid($_POST)) {
                $this->_setOptions($form->getValues());
                $this->_helper->flashMessenger(__('Settings have been saved.'));
            } else {
                $this->flashError('There were errors found in your form. Please edit and resubmit.');
            }
        }
        
        $this->view->form = $form;
    }
    
    /**
     * Returns the options that are specified in the $_options property.
     *
     * @return array Array of option names.
     */
    private function _getOptions()
    {
        $options = array();
        $cnt = new AnnotationPlugin();
        foreach ($cnt->pluginOptions() as $option) {
            $options[$option] = get_option($option);
        }
        return $options;
    }
    
    /**
     * Sets options that appear in both the form and $_options.
     *
     * @param array $newOptions array of $optionName => $optionValue.
     */
    private function _setOptions($newOptions)
    {
        $cnt = new AnnotationPlugin();
        foreach ($newOptions as $optionName => $optionValue) {
            if (in_array($optionName, $cnt->pluginOptions())) {
                set_option($optionName, $optionValue);
            }
        }
    }
    
    private function _getForm()
    {
        $form = new Omeka_Form_Admin(array('type'=>'annotation_settings'));
        
        $collections = get_db()->getTable('Collection')->findPairsForSelectForm();
        $collections = array('' => __('No collection')) + $collections;        
        
        $form->addElementToEditGroup('select', 'annotation_collection_id', array(
            'label'        => __('Annotation Collection'),
            'description'  => __('The collection to which annotations will be added. Changes here will only affect new annotations.'),
            'multiOptions' => $collections
        ));
        
        $form->addElementToEditGroup('select', 'annotation_incomplete_collection_id', array(
            'label'        => __('Incomplete Items Collection'),
            'description'  => __('The collection with incomplete Items that have to be Annotated.'),
            'multiOptions' => $collections
        ));
    
        if(plugin_is_active('MetaMetaData')) {
            $form->addElementToEditGroup('checkbox', 'annotation_metametadata', array(
                'label' => __("Add MetaMetaData for Automatic Annotations"),
                'description' => __("Check this option if you want to keep track of automatically annotated values."),
                ),
                array('checked'=> (bool) get_option('annotation_metametadata') ? 'checked' : '')         
            );
        }
        
       return $form;
    }
}
