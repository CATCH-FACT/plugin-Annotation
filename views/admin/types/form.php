<?php 
    $itemTypeOptions = get_db()->getTable('AnnotationType')->getPossibleItemTypes();
    $itemTypeOptions = array('' => 'Select an Item Type') + $itemTypeOptions;
    $collections = get_db()->getTable('Collection')->findPairsForSelectForm();
    $collections = array('' => 'Select a Collection') + $collections;
    $toolsArray = get_db()->getTable('AnnotationTool')->findElementsForSelect();
    $elementsArray = get_table_options(
            'Element', null,
                array(
                    'element_set_name' => ElementSet::ITEM_TYPE_NAME,
                    'sort' => 'alpha',
                    'item_type_id' => $annotation_type->item_type_id
                )
            );
    
?>
<form method='post'>  
<section class='seven columns alpha'>
<?php if($action == 'add'): ?>
    <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Item Type"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("The Item Type, from your site's list of types, you would like to use."); ?></p>
            <div class="input-block">
               <?php echo $this->formSelect('item_type_id', $annotation_type->item_type_id, array(), $itemTypeOptions); ?>
            </div>
        </div>
     </div>
    <?php else: ?>
        <input type="hidden" id="item_type_id" value="<?php echo $annotation_type->item_type_id; ?>"/>
    <?php endif; ?>

    <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Display Name"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("The label you would like to use for this annotation type. If blank, the Item Type name will be used."); ?></p>
            <div class="input-block">
             <?php echo $this->formText('display_name', $annotation_type->display_name, array()); ?>
            </div>
        </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Collection"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("The collection which to add the item to (adjustable in annotation mode)."); ?></p>
             <div class="input-block">
                <?php echo $this->formSelect('collection_id', $annotation_type->collection_id, array(), $collections); ?>
             </div>
         </div>
      </div>

     <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Allow File Upload Via Form"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("Enable or disable file uploads through the public annotation form. If set to &#8220;Required,&#8220; users must add a file to their annotation when selecting this item type."); ?></p>
            <div class="input-block">
               <?php echo $this->formSelect('file_permissions', __('%s', $annotation_type->file_permissions), array(), AnnotationType::getPossibleFilePermissions()); ?>
            </div>
        </div>
     </div>  
    

     <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Tags annotation tool"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("Select the tool for automatically annotating tags."); ?></p>
            <div class="input-block">
            <?php echo $this->formSelect(
                    'tags_tool_id', $annotation_type->tags_tool_id,
                    array('class' => 'element-drop-down'), $toolsArray ); ?>
            </div>
        </div>
     </div>  


    <div id="type-element-list" class="seven columns alpha">
         <h2> Annotation fields</h2>
        <ul id="annotation-type-elements" class="sortable">
        <?php
        foreach ($annotationTypeElements as $annotationElement):
            if ($annotationElement):
        ?>
        
            <li class="type-element">
                <div class="sortable-item">
                <?php if (is_allowed('Annotation_Types', 'delete-element')): ?>
                <a id="return-element-link-<?php echo html_escape($annotationElement->id); ?>" href="" class="undo-delete"><?php echo __('Undo'); ?></a>
                <a id="remove-element-link-<?php echo html_escape($annotationElement->id); ?>" href="" class="delete-element"><?php echo __('Remove'); ?></a>
                <?php endif; ?>

                <?php if ($annotationElement->Tool): ?>
                <span class='prompt'><?php echo __('Annotation tool:'); ?></span>
                <strong><?php echo html_escape($annotationElement->Tool->display_name); ?></strong>
                <?php endif; ?>
                
                <span class='prompt'><?php echo __('Output field:'); ?></span>
                <strong><?php echo html_escape($annotationElement->Element->name); ?></strong>
                <br>
                <span class='prompt'><?php echo __('Comments'); ?></span>
                <?php echo $this->formText("elements[$annotationElement->id][prompt]" , $annotationElement->prompt); ?>
                
                <?php echo "<hr>";?>
                
                <span class='long-text'><?php echo __('Large input field'); ?></span>
                <?php echo $this->formCheckbox("elements[$annotationElement->id][long_text]", null, array('checked'=>$annotationElement->long_text)); ?>
                
                <span class='repeated-field'><?php echo __('Repeated values'); ?></span>
                <?php echo $this->formCheckbox("elements[$annotationElement->id][repeated_field]", null, array('checked'=>$annotationElement->repeated_field)); ?>

                <?php echo "<br>";?>

                <span class='score-slider'><?php echo __('Score slider'); ?></span>
                <?php echo $this->formCheckbox("elements[$annotationElement->id][score_slider]", null, array('checked'=>$annotationElement->score_slider)); ?>
                <span class='score-slider'> (when idx available in metadata)</span>

                <?php echo "<br>";?>

                <span class='date-range-picker'><?php echo __("Date picker: "); ?></span>

                <span class='date-picker'><?php echo __('Single date'); ?></span>
                <?php echo $this->formCheckbox("elements[$annotationElement->id][date_picker]", null, array('checked'=>$annotationElement->date_picker)); ?>

                <span class='date-range-picker'><?php echo __('Date range'); ?></span>
                <?php echo $this->formCheckbox("elements[$annotationElement->id][date_range_picker]", null, array('checked'=>$annotationElement->date_range_picker)); ?>
                
                <?php echo "<hr>";?>
                
                <span class='auto-complete'><?php echo __('Autocomplete options '); ?></span>
                <?php echo $this->formCheckbox("elements[$annotationElement->id][autocomplete_on]", null, array('checked'=>$annotationElement->autocomplete_on)); ?>

                <?php echo "<br>";?>
                <span class='auto-complete-element'><?php echo __('Search in element'); ?></span>
                <?php echo $this->formSelect(
                    $annotationElement->autocomplete_main_id, $annotationElement->autocomplete_main_name, //set in controller like english_name
                    array('class' => 'existing-element-drop-down'), $elementsArray );
                ?>

                <?php echo "<br>";?>
                <span class='auto-complete-element'><?php echo __('Extra search element (i.e. text, title)'); ?></span>
                <?php echo $this->formSelect(
                    $annotationElement->autocomplete_extra_id, $annotationElement->autocomplete_extra_name, //set in controller like english_name
                    array('class' => 'existing-element-drop-down'), $elementsArray );
                ?>

                <?php echo "<br>";?>
                <span class='auto-complete-item'><?php echo __('Search in Itemtype'); ?></span>
                <?php echo $this->formSelect(
                    $annotationElement->autocomplete_itemtype_id, $annotationElement->autocomplete_itemtype_name, //set in controller like english_name
                    array('class' => 'existing-element-drop-down'), $elementsArray );
                ?>
                <?php echo "<br>";?>
                <span class='auto-complete-collection'><?php echo __('Search in Collection'); ?></span>
                <?php echo $this->formSelect(
                    $annotationElement->autocomplete_collection_id, $annotationElement->autocomplete_collection_name, //set in controller like english_name
                    array('class' => 'existing-element-drop-down'), $collections );
               ?>
                
                
                <?php echo $this->formHidden("elements[$annotationElement->id][order]", $annotationElement->order, array('size' => 2, 'class' => 'type-element-order')); ?>
                <?php echo $this->formHidden("elements[$annotationElement->id][toolid]", $annotationElement->tool_id, array('size' => 2, 'class' => 'type-element-toolid')); ?>

                <?php echo $this->formHidden("elements[$annotationElement->id][id]", $annotationElement->idout, array('size' => 2, 'class' => 'type-element-toolid')); ?>
                <?php echo $this->formHidden("elements[$annotationElement->id][id]", $annotationElement->idin, array('size' => 2, 'class' => 'type-element-toolid')); ?>
                </div>
                
                <div class="drawer-contents"> 
                    Annotation of element: 
                    <strong><?php echo html_escape($annotationElement->Element->name); ?></strong>
                    <?php if ($annotationElement->Tool): ?>                    
                        with the tool: 
                        <strong><?php echo html_escape($annotationElement->Tool->display_name); ?></strong>
                    <?php endif; ?>
                </div>
            </li>
            <?php else: ?>
                <?php if (!$annotationElement->exists()):  ?>
                <?php echo $this->action(
                    'add-new-type-element', 'annotation-types', null,
                    array(
                        'from_post' => true,
                        'elementTempId' => $elementTempId,
                        'elementName' => $element->name,
                        'elementDescription' => $element->description,
                        'elementToolId' => $elementToolId,
                        'elementOrder' => $elementOrder
                    )
                );
                ?>
                <?php else: ?>
                <?php echo $this->action(
                    'add-existing-type-element', 'annotation-types', null,
                    array(
                        'from_post' => true,
                        'elementTempId' => $elementTempId,
                        'elementToolId' => $elementToolId,
                        'elementId' => $element->id,
                        'elementOrder' => $elementOrder
                    )
                );
                ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; // end for each $elementInfos ?> 
            <li>
                <div class="add-new">
                    <?php echo __('Add Element'); ?>
                </div>
                <div class="drawer-contents">
                    <button id="add-existing-type-element" name="add-existing-type-element"><?php echo __('Add Element'); ?></button>
                </div>
            </li>
        </ul>
        <?php echo $this->formHidden('elements_to_remove'); ?>
    </div>
</section>

<section class='three columns omega'>
    <div id='save' class='panel'>
            <input type="submit" class="big green button" value="<?php echo __('Save Changes');?>" id="submit" name="submit">
            <?php if($annotation_type->exists()): ?>
            <?php   echo link_to($annotation_type, 'delete-confirm', __('Delete'), array('class' => 'big red button delete-confirm')); ?>
            <?php endif; ?>
    </div>
</section>
</form>
