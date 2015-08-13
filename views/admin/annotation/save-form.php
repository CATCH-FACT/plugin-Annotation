        <?php echo $this->formSubmit('form-submit', __('Save Item'), array('class' => 'submit big green button')); ?>    

        <div id="public-featured">
            <?php if ( is_allowed('Items', 'makePublic') ): ?>
                <div class="public">
                    <label for="annotation-public"><?php echo __('Public'); ?>:</label> 
                    <?php echo $this->formCheckbox('annotation-public', $item->public, null, array('1', '0')); ?>
                    <label for="annotation-finished"><?php echo __('Completed'); ?>:</label> 
                    <?php echo $this->formCheckbox('annotation-finished', $type->finished, null, array('1', '0')); ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="collection-form" class="field">
            <?php echo $this->formLabel('collection-id', __('Collection'));?>
            <div class="inputs">
                <?php 
                    echo $this->formSelect(
                    'collection_id',
                    $type->collection_id,
                    array('id' => 'collection-id'),
                    get_table_options('Collection')
                );?>
            </div>
        </div>
        <p><?php echo __('You are logged in as: %s', metadata(current_user(), 'name')); ?>
        <?php fire_plugin_hook("admin_items_panel_fields", array('view'=>$this, 'record'=>$item)); ?>
