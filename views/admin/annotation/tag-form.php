<?php 

echo annotation_tag_form($item, array('annotationType' => $type)); 

fire_plugin_hook('admin_items_form_tags', array('item' => $item, 'view' => $this)); 

?>
