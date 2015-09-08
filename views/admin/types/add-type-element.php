<li class="element">
    <a href="" class="delete-element"><?php echo __('Remove'); ?></a>
    <div class="sortable-item">
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
        
        $autocompleteArray = get_table_options(
                'Element', null,
                    array(
                        'element_set_name' => ElementSet::ITEM_TYPE_NAME,
                        'sort' => 'alpha'
                    )
                );
        
        $autocompleteArray['Dublin Core'] = $dcElements['Dublin Core'];
        
        echo "<span class='input'>" . __('Metadata field:') . "</span>";
        echo $this->formSelect(
            $element_id_name, $element_id_value,
            array('class' => 'element-drop-down'), $elementsArray );

        echo "<span class='tool'>" . __('Tool:') . "</span>";
        echo $this->formSelect(
            $element_tool_name, $element_tool_value,
            array('class' => 'element-drop-down'), $toolsArray );
            
        echo "<hr>";

        echo "<span class='comments'>" . __("Comments:") . "</span>";
        echo $this->formText($element_prompt_name, $element_prompt_value, array('class'=>'prompt'));

        echo "<span class='html'>" . __('Html') . "</span>";
        echo $this->formCheckbox($element_html_name, null);

        echo "<hr>";
        
        echo "<span class='long-text'>" . __('Large text field') . "</span>";
        echo $this->formCheckbox($element_long_name, null);

        echo "<span class='long-text'>" . __('Repeated value allowed') . "</span>";
        echo $this->formCheckbox($element_repeated_name, null);

        echo "<span class='long-text'>" . __('Scrolling textfield') . "</span>";
        echo $this->formCheckbox($element_field_scroll_name, null);
        
        echo "<br>";

        echo "<span class='long-text'>" . __('Score slider') . "</span>";
        echo $this->formCheckbox($element_scoreslider_name, null);

        echo "<span class='long-text'>" . __('Date single picker') . "</span>";
        echo $this->formCheckbox($element_datepicker_name, null);

        echo "<span class='long-text'>" . __('Date range picker') . "</span>";
        echo $this->formCheckbox($element_daterangepicker_name, null);

        echo "<hr>";

        echo "<span class='auto-complete'>" . __('Autocomplete options') . "</span>";
        echo $this->formCheckbox($element_autocomplete_name, null);

        echo "<br>";
        echo "<span class='auto-complete-element'>" . __('Search in element') . "</span>";
        echo $this->formSelect(
            $element_autocomplete_main_name, $element_autocomplete_main_value,
            array('class' => 'element-drop-down'), $autocompleteArray );

        echo "<br>";
        echo "<span class='auto-complete-element'>" . __('Extra search element (i.e. text, title)') . "</span>";
        echo $this->formSelect(
            $element_autocomplete_extra_name, $element_autocomplete_extra_value, //set in controller like english_name
            array('class' => 'element-drop-down'), $autocompleteArray );
            
        echo "<br>";
        echo "<span class='auto-complete-item'>" . __('Search in Itemtype') . "</span>";
        echo $this->formSelect(
            $element_autocomplete_itemtype_name, $element_autocomplete_itemtype_value, //set in controller like english_name
            array('class' => 'element-drop-down'), $itemTypeOptions );

        echo "<br>";
        echo "<span class='auto-complete-collection'>" . __('Search in Collection') . "</span>";
        echo $this->formSelect(
            $element_autocomplete_collection_name, $element_autocomplete_collection_value, //set in controller like english_name
            array('class' => 'element-drop-down'), $collections );
        
        echo $this->formHidden(
            $element_order_name, $element_order_value,
            array('class' => 'element-order')
        );
        
        ?>
    </div>
    <div class="drawer-contents"></div>
</li>
