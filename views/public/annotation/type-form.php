
<?php if (!$type): ?>
<p>You must choose a annotation type to continue.</p>
<?php else: ?>
<h2>Annotate a <?php echo $type->display_name; ?></h2>

<?php 
if ($type->isFileRequired()):
    $required = true;
?>



<div class="field">
        <?php echo $this->formLabel('annotated_file', 'Upload a file'); ?>
        <?php echo $this->formFile('annotated_file', array('class' => 'fileinput')); ?>
</div>

<?php endif; ?>

<?php 
foreach ($type->getTypeElements() as $annotationTypeElement) {
    echo $this->elementForm($annotationTypeElement->Element, $item, array('annotationTypeElement'=>$annotationTypeElement));
}
?>

<?php 
if (!isset($required) && $type->isFileAllowed()):
?>
<div class="field">
        <?php echo $this->formLabel('annotated_file', __('Upload a file (Optional)')); ?>
        <?php echo $this->formFile('annotated_file', array('class' => 'fileinput')); ?>
</div>
<?php endif; ?>

<?php $user = current_user(); ?>
<?php if(get_option('annotation_simple') && !current_user()) : ?>
<div class="field">
    <?php echo $this->formLabel('annotation_simple_email', __('Email (Required)')); ?>
    <?php echo $this->formText('annotation_simple_email'); ?>
</div>

<?php else: ?>
    <p><?php echo __('You are logged in as: %s', metadata($user, 'name')); ?>
    
    <?php 
    //pull in the user profile form it is is set
    if( isset($profileType) ): ?>
    
    <script type="text/javascript" charset="utf-8">
    //<![CDATA[
    jQuery(document).bind('omeka:elementformload', function (event) {
         Omeka.Elements.makeElementControls(event.target, <?php echo js_escape(url('user-profiles/profiles/element-form')); ?>,'UserProfilesProfile'<?php if ($id = metadata($profile, 'id')) echo ', '.$id; ?>);
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
// Allow other plugins to append to the form (pass the type to allow decisions
// on a type-by-type basis).
fire_plugin_hook('annotation_type_form', array('type'=>$type, 'view'=>$this));
?>
<?php endif; ?>
