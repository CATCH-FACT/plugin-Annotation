<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */

queue_js_file('annotation-public-form');
$annotationPath = get_option('annotation_page_path');
if(!$annotationPath) {
    $annotationPath = 'annotation';
}
queue_css_file('form');

//load user profiles js and css if needed
if(get_option('annotation_user_profile_type') && plugin_is_active('UserProfiles') ) {
    queue_js_file('admin-globals');
    queue_js_file('tiny_mce', 'javascripts/vendor/tiny_mce');
    queue_js_file('elements');
    queue_css_string("input.add-element {display: block}");
}

$head = array('title' => 'Annotate',
              'bodyclass' => 'annotation');
echo head($head); 

echo js_escape(url($annotationPath.'/type-form'));
?>
<script type="text/javascript">
// <![CDATA[
enableAnnotationAjaxForm(<?php echo js_escape(url($annotationPath.'/type-form')); ?>);
// ]]>
</script>

<div id="primary">
<?php echo flash(); ?>
    
    <h1><?php echo $head['title']; ?></h1>

    <?php if(!get_option('annotation_simple') && !$user = current_user()) :?>
        <?php $session = new Zend_Session_Namespace;
              $session->redirect = absolute_url();
        ?>
        <p>You must <a href='<?php echo url('guest-user/user/register'); ?>'>create an account</a> or <a href='<?php echo url('guest-user/user/login'); ?>'>log in</a> before annotating. You can still leave your identity to site visitors anonymous.</p>        
    <?php else: ?>
        <form method="post" action="" enctype="multipart/form-data">
            <fieldset id="annotation-item-metadata">
                <div class="inputs">
                    <label for="annotation-type"><?php echo __("What type of item do you want to annotate?"); ?></label>
                    <?php $options = get_table_options('AnnotationType' ); ?>
                    <?php $typeId = isset($type) ? $type->id : '' ; ?>
                    <?php echo $this->formSelect( 'annotation_type', $typeId, array('multiple' => false, 'id' => 'annotation-type') , $options); ?>
                    <input type="submit" name="submit-type" id="submit-type" value="Select" />
                </div>
                <div id="annotation-type-form">
                <?php if (isset($typeForm)): echo $typeForm; endif; ?>
                </div>
            </fieldset>
            
            <fieldset id="annotation-confirm-submit" <?php if (!isset($typeForm)) { echo 'style="display: none;"'; }?>>
                <div class="inputs">
                    <?php $public = isset($_POST['annotation-public']) ? $_POST['annotation-public'] : 0; ?>
                    <?php echo $this->formCheckbox('annotation-public', $public, null, array('1', '0')); ?>
                    <?php echo $this->formLabel('annotation-public', __('Publish my annotation on the web.')); ?>
                </div>
                <div class="inputs">
                    <?php $anonymous = isset($_POST['annotation-anonymous']) ? $_POST['annotation-anonymous'] : 0; ?>
                    <?php echo $this->formCheckbox('annotation-anonymous', $anonymous, null, array(1, 0)); ?>
                    <?php echo $this->formLabel('annotation-anonymous', __("Annotate anonymously.")); ?>
                </div>
                <p><?php echo __("In order to annotate, you must read and agree to the %s",  "<a href='" . annotation_annotate_url('terms') . "' target='_blank'>" . __('Terms and Conditions') . ".</a>"); ?></p>
                <div class="inputs">
                    <?php $agree = isset( $_POST['terms-agree']) ?  $_POST['terms-agree'] : 0 ?>
                    <?php echo $this->formCheckbox('terms-agree', $agree, null, array('1', '0')); ?>
                    <?php echo $this->formLabel('terms-agree', __('I agree to the Terms and Conditions.')); ?>
                </div>
                <?php echo $this->formSubmit('form-submit', __('Annotate'), array('class' => 'submitinput')); ?>
            </fieldset>
        </form>
    <?php endif; ?>
</div>
<?php echo foot();
