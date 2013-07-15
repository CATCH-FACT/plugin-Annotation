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
        
        echo "<span class='input'>" . __('Input field:') . "</span>";
        echo $this->formSelect(
            $element_id_in_name, $element_id_in_value,
            array('class' => 'existing-element-drop-down'), $elementsArray );

        echo "<span class='output'>" . __('Output field:') . "</span>";
        echo $this->formSelect(
            $element_id_out_name, $element_id_out_value,
            array('class' => 'existing-element-drop-down'), $elementsArray );
        echo "<br>";

        echo "<span class='tool'>" . __('Tool:') . "</span>";
        echo $this->formSelect(
            $element_tool_name, $element_tool_value,
            array('class' => 'existing-element-drop-down'), $toolsArray );
        echo "<br>";

        echo "<span class='comments'>" . __("Comments:") . "</span>";
        echo $this->formText($element_prompt_name, $element_prompt_value, array('class'=>'prompt'));
        ?>

        <br>
        <span class='long-text'><?php echo __('Large text'); ?></span>
        <?php echo $this->formCheckbox($element_long_name, null);    ?>        
        <?php
        echo $this->formHidden(
            $element_order_name, $element_order_value,
            array('class' => 'element-order')
        );
        ?>
        <a href="" class="delete-element"><?php echo __('Remove'); ?></a>
    </div>
    <div class="drawer-contents"></div>
</li>
