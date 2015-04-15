<li class="element">
    <div class="sortable-item">
        <?php
        
#        $toolsArray = get_table_options('AnnotationTool');
        $toolsArray = get_db()->getTable('AnnotationTool')->findElementsForSelect();
        
        $elementsArray = get_table_options(
                'Element', null,
                    array(
                        'element_set_name' => ElementSet::ITEM_TYPE_NAME,
                        'sort' => 'alpha',
                        'item_type_id' => $item_type_id
                    )
                );

        $dcElements = get_table_options(
                'Element', null,
                    array(
                        'element_set_name' => 'Dublin Core',
                        'sort' => 'alpha',
                    )
                );
        $elementsArray['Dublin Core'] = $dcElements['Dublin Core'];
        
        echo "<span class='input'>" . __('Metadata field:') . "</span>";
        echo $this->formSelect(
            $element_id_name, $element_id_value,
            array('class' => 'existing-element-drop-down'), $elementsArray );

        echo "<span class='tool'>" . __('Tool:') . "</span>";
        echo $this->formSelect(
            $element_tool_name, $element_tool_value,
            array('class' => 'existing-element-drop-down'), $toolsArray );
        echo "<br>";

        echo "<span class='comments'>" . __("Comments:") . "</span>";
        echo $this->formText($element_prompt_name, $element_prompt_value, array('class'=>'prompt'));

        echo "<br>";
        
        echo "<span class='long-text'>" . __('Large text field') . "</span>";
        echo $this->formCheckbox($element_long_name, null);

        echo "<span class='long-text'>" . __('Repeated value allowed') . "</span>";
        echo $this->formCheckbox($element_repeated_name, null);
        
        echo "<br>";

        echo "<span class='long-text'>" . __('Score slider') . "</span>";
        echo $this->formCheckbox($element_scoreslider_name, null);

        echo "<span class='long-text'>" . __('Date single picker') . "</span>";
        echo $this->formCheckbox($element_datepicker_name, null);

        echo "<span class='long-text'>" . __('Date range picker') . "</span>";
        echo $this->formCheckbox($element_daterangepicker_name, null);

        echo "<hr>";?>
         
         <span class='auto-complete'><?php echo __('Autocomplete options '); ?></span>
         <?php echo $this->formCheckbox($element_autocomplete_on_name)); ?>

         <?php echo "<br>";?>
         <span class='auto-complete-element'><?php echo __('Search in element'); ?></span>
         <?php echo $this->formSelect(
             $element_autocomplete_main_id, $element_autocomplete_main_name, //set in controller like english_name
             array('class' => 'existing-element-drop-down'), $elementsArray );
         ?>

         <?php echo "<br>";?>
         <span class='auto-complete-element'><?php echo __('Extra search element (i.e. text, title)'); ?></span>
         <?php echo $this->formSelect(
             $element_autocomplete_extra_id, $element_autocomplete_extra_name, //set in controller like english_name
             array('class' => 'existing-element-drop-down'), $elementsArray );
         ?>

         <?php echo "<br>";?>
         <span class='auto-complete-item'><?php echo __('Search in Itemtype'); ?></span>
         <?php echo $this->formSelect(
             $element_autocomplete_main_id, $element_autocomplete_main_name, //set in controller like english_name
             array('class' => 'existing-element-drop-down'), $elementsArray );
         ?>
         <?php echo "<br>";?>
         <span class='auto-complete-collection'><?php echo __('Search in Collection'); ?></span>
         <?php echo $this->formSelect(
             $element_autocomplete_collection_id, $element_autocomplete_collection_name, //set in controller like english_name
             array('class' => 'existing-element-drop-down'), $collections );
        
        echo $this->formHidden(
            $element_order_name, $element_order_value,
            array('class' => 'element-order')
        );
        
        ?>
        <a href="" class="delete-element"><?php echo __('Remove'); ?></a>
    </div>
    <div class="drawer-contents"></div>
</li>
