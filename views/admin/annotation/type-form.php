<?php echo js_tag('vendor/tiny_mce/tiny_mce'); ?>
<?php echo js_tag('annotation-elements'); ?>
<?php echo js_tag('tabs'); ?>
<?php echo js_tag('items'); ?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[
// TinyMCE hates document.ready.

jQuery(window).load(function () {
    
    Omeka.Tabs.initialize();

    Omeka.Items.tagDelimiter = <?php echo js_escape(get_option('tag_delimiter')); ?>;
    Omeka.Items.enableTagRemoval();
    Omeka.Items.makeFileWindow();
    Omeka.Items.enableSorting();
    Omeka.Items.tagChoices('#tags', <?php echo js_escape(url(array('controller'=>'tags', 'action'=>'autocomplete'), 'default', array(), true)); ?>);

//    Omeka.wysiwyg({
//        mode: "none",
//        forced_root_block: ""
//    });

    // Must run the element form scripts AFTER reseting textarea ids.
    jQuery(document).trigger('omeka:elementformload');

    Omeka.Items.enableAddFiles(<?php echo js_escape(__('Add Another File')); ?>);
    Omeka.Items.changeItemType(<?php echo js_escape(url("items/change-type")) ?><?php if ($id = metadata('item', 'id')) echo ', '.$id; ?>);
});

jQuery(document).bind('omeka:elementformload', function (event) { //
    //adding control events to buttons like "add input" and "autocomplete" and "datepicker selector"
    //each time an element load form even has taken place.
    
    elementFormPartialUrl = <?php echo js_escape(url('annotation/annotation/element-form')); ?>;
    autocompleteChoicesUrl = <?php echo js_escape(url('annotation/annotation/autocomplete')); ?>;
    recordType = 'Item'<?php if ($id = metadata('item', 'id')) echo ', '.$id; ?>;
    recordId = null;
    
    Omeka.Elements.makeElementControls(event.target, elementFormPartialUrl, autocompleteChoicesUrl, recordType, recordId, model);
    Omeka.Elements.makeElementInformationTooltips();
        
    //NOT adding HTML control
//    Omeka.Elements.enableWysiwyg(event.target);
});
//]]>
</script>

<?php if (!$type): ?>
<p>Please choose an annotation type to continue.</p>
<?php else: ?>
<h2>Annotate a <?php echo $type->display_name; ?></h2>

<?php 
if ($type->isFileRequired()): $required = true;?>
    <div class="field">
        <?php echo $this->formLabel('annotated_file', 'Upload a file'); ?>
        <?php echo $this->formFile('annotated_file', array('class' => 'fileinput')); ?>
    </div>
<?php endif; ?>


<?php 
############################
#actual form being generated

foreach ($type->getUniqueInputTypeElements() as $annotationTypeElement) {
    echo $this->annotationElementForm($annotationTypeElement->Element, $item, array('annotationTypeElement'=>$annotationTypeElement));
}

?>

<br>
<br>
<br>

<div>
<?php
ob_start();
require 'tag-form.php';
ob_get_contents();
echo ob_get_clean();
?>
</div>

<?php if (!isset($required) && $type->isFileAllowed()):?>
<div class="field">
        <?php echo $this->formLabel('annotated_file', __('Upload a file (Optional)')); ?>
        <br>
        <?php echo $this->formFile('annotated_file', array('class' => 'fileinput')); ?>
</div>
<?php endif; ?>

<?php if (current_user()): ?>
    
    <?php 
    //pull in the user profile form it is is set
    if( isset($profileType) ): ?>
    
    <script type="text/javascript" charset="utf-8">
    //<![CDATA[
    jQuery(document).bind('omeka:elementformload', function (event) {
         Omeka.Elements.makeElementControls(event.target, <?php echo js_escape(url('user-profiles/profiles/element-form')); ?>,'UserProfilesProfile'<?php if ($id = metadata($profile, 'id')) echo ', '.$id; ?>, ko);
         Omeka.Elements.enableWysiwyg(event.target);
    });
    //]]>
    </script>
    
        <h2 class='annotation-userprofile <?php echo $profile->exists() ? "exists" : ""  ?>'><?php echo  __('Your %s profile', $profileType->label); ?></h2>
        <p id='annotation-userprofile-visibility'>
        <?php if ($profile->exists()) :?>
            <span class='annotation-userprofile-visibility'>Show</span><span class='annotation-userprofile-visibility' style='display:none'>Hide</span>
        <?php else: ?>
            <span class='annotation-userprofile-visibility' style='display:none'>Show</span><span class='annotation-userprofile-visibility'>Hide</span>
        <?php endif; ?>
        </p>
        <div class='annotation-userprofile <?php echo $profile->exists() ? "exists" : ""  ?>'>
        <p class="user-profiles-profile-description"><?php echo $profileType->description; ?></p>
        <fieldset name="user-profiles">
        <?php 
        foreach($profileType->Elements as $element) {
            echo $this->profileElementForm($element, $profile);
        }
        ?>
        </fieldset>
        </div>
        
    <?php endif; ?>
<?php endif; ?>
<?php 
// Allow other plugins to append to the form (pass the type to allow decisions on a type-by-type basis).
fire_plugin_hook('annotation_type_form', array('type'=>$type, 'view'=>$this));
//fire_plugin_hook('contribution_type_form', array('type'=>$type, 'view'=>$this));
?>
<?php endif; ?>
<br>
<section class="three columns omega">
    <div id="save" class="panel">
        <?php echo $this->formSubmit('form-submit', __('Add Item'), array('class' => 'submit big green button')); ?>    

        <div id="public-featured">
            <?php if ( is_allowed('Items', 'makePublic') ): ?>
                <div class="public">
                    <label for="public"><?php echo __('Public'); ?>:</label> 
                    <?php echo $this->formCheckbox('annotation-public', $type->public, null, array('1', '0')); ?>
                    <label for="finished"><?php echo __('Completed'); ?>:</label> 
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
        <?php fire_plugin_hook("admin_items_panel_fields", array('view'=>$this, 'record'=>$item)); ?>
    </div>
</section>