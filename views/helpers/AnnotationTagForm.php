<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 */

require_once VIEW_HELPERS_DIR . DIRECTORY_SEPARATOR . 'ElementForm.php';

/**
 * Overrides Omeka's ElementForm helper to allow for custom display of fields.
 */
class Annotation_View_Helper_AnnotationTagForm extends Omeka_View_Helper_ElementForm
{
    
    public function AnnotationTagForm($record, $options){

        $that = $this ? $this : get_view();
        $tags = $record->getTags();

        if (isset($options['annotationType'])){ $this->_annotationType = $options['annotationType']; }

        $html_tags = $this->view->formSubmit('annotate-tags', 
                                        __('Determine automatically'),
                                        array('class'=>'annotate-tags',
                                            'style'=>"width:28%; background:#0080FF; float:left"));

        $html = '<div id="tag-form" class="field">';
        $html .= '    <input type="hidden" name="tags-to-add" id="tags-to-add" value="" />';
        $html .= '    <input type="hidden" name="tags-to-delete" id="tags-to-delete" value="" />';
        $html .= '    <div id="add-tags">';
        $html .= '        <label>' . __('Add Tags') .'</label>';
        $html .= '        <input element-name="tags" type="text" name="tags" size="20" id="tags" class="textinput" value="" />';
        
        $html .= '        <p id="add-tags-explanation" class="explanation">' . __('Separate tags with %s', option('tag_delimiter')) . '</p>';

        $html .= $this->_annotationType->tags_tool_id ? $html_tags : "";

        $html .= '        <input type="submit" name="add-tags-button" id="add-tags-button" class="green button" value="' . __('Add Tags') .'" />';
        $html .= '    </div>';

        $html .= '    <div id="all-tags">';
        if ($tags){
            $html .= '        <h3>' . __('Approved Tags') . '</h3>';

            $html .= '        <div class="tag-list">';
            $html .= '        <ul id="all-tags-list">';
            foreach( $tags as $tag ){
                $html .= '                <li>';
                $html .= '                    <span element-name="tags" class="tag">' . $tag->name . '</span>'; 
                $html .= '                          <span class="undo-remove-tag"><a href="#">' . __('Undo') . '</a></span>';
                $html .= '                          <span class="remove-tag"><a href="#">' . __('Remove') . '</a></span>';
                $html .= '                </li>';
            }
            $html .= '        </ul>';
            $html .= '        </div>';
        }
        $html .= '    </div>';
        $html .= '</div>';

        return $html;
    }

}