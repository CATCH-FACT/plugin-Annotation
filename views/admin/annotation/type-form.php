<?php echo js_tag('vendor/tiny_mce/tiny_mce'); ?>
<?php echo js_tag('annotation-elements'); ?>
<?php echo js_tag('annotation-items'); ?>
<?php echo js_tag('tabs'); ?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[
// TinyMCE hates document.ready.

loadImageURL = <?php echo js_escape(img("ajax-loader.gif")); ?>;

jQuery(window).load(function () {
    
//    Omeka.Tabs.initialize();

    elementFormTagToolUrl = <?php echo js_escape(url('annotation/annotation/element-form-tagtool')); ?>;

    Omeka.Items.tagDelimiter = <?php echo js_escape(get_option('tag_delimiter')); ?>;
    //tags other functions
    Omeka.Items.enableTagRemoval(elementFormTagToolUrl);
    
    Omeka.Items.makeFileWindow();
    Omeka.Items.enableSorting();
    //tags autocomplete
    Omeka.Items.tagChoices('#tags', <?php echo js_escape(url(array('controller'=>'tags', 'action'=>'autocomplete'), 'default', array(), true)); ?>);

    //html
    Omeka.wysiwyg({
        mode: "none",
        forced_root_block: ""
    });

    // Must run the element form scripts AFTER reseting textarea ids.
    jQuery(document).trigger('omeka:elementformload');

//    Omeka.Items.enableAddFiles(<?php echo js_escape(__('Add Another File')); ?>);
    Omeka.Items.changeItemType(<?php echo js_escape(url("items/change-type")); ?><?php if ($id = metadata('item', 'id')) echo ', '.$id; ?>);
    
});

jQuery(document).bind('omeka:elementformload', function (event) { //
    //adding control events to buttons like "add input" and "autocomplete" and "datepicker selector"
    //each time an element load form even has taken place.

    elementFormPartialUrl = <?php echo js_escape(url('annotation/annotation/element-form')); ?>;
    autocompleteChoicesUrl = <?php echo js_escape(url('annotation/annotation/autocomplete')); ?>;
    annotationId = <?php echo $type->id; ?>;
    recordType = 'Item'<?php if ($id = metadata('item', 'id')) echo ', ' . $id; ?>;
    recordId = null;
    
    Omeka.Elements.makeElementControls(event.target, elementFormPartialUrl, autocompleteChoicesUrl, loadImageURL, recordType, recordId, annotationId, model);
    Omeka.Elements.makeElementInformationTooltips();
    
    Omeka.Items.enableAddFiles(<?php echo js_escape(__('Add Another File')); ?>);

    //NOT adding HTML control (should I add it with a setting?)
//    Omeka.Elements.enableWysiwyg(event.target);
});
//]]>
</script>

<?php if (!$type): ?>
<p><?php echo __("Please choose an annotation type to continue.")?></p>
<?php else: ?>
<h2><?php echo __("Annotate a %s", $type->display_name); ?></h2>

<?php 
############################
#actual form being generated

foreach ($type->getUniqueInputTypeElements() as $annotationTypeElement) {
    echo $this->annotationElementForm($annotationTypeElement->Element, $item, array('annotationTypeElement'=>$annotationTypeElement));
}

?>

<br>
<br>
<h2><?php echo __("Tags"); ?></h2>

<div id="tags-metadata">
<?php
ob_start();
require 'tag-form.php';
ob_get_contents();
echo ob_get_clean();
?>
</div>

<h2><?php echo __("Files"); ?></h2>

<div id="files-metadata">
<?php 
if (!isset($required) && $type->isFileAllowed()){
    ob_start();
    require 'files-form.php';
    ob_get_contents();
    echo ob_get_clean();
}
?>
</div>

<br>
<hr>
<br>

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
fire_plugin_hook('annotation_type_form', array('type'=>$type, 'view'=>$this, 'item'=>$item));
//fire_plugin_hook('contribution_type_form', array('type'=>$type, 'view'=>$this));
?>
<?php endif; ?>
