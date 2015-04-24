<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */

queue_js_file('annotation-admin-form');

//initiate knockout
queue_js_file('knockout-3.3.0');
queue_js_string('console.log("knockout loaded");');

//initiate moment and daterangepicker
queue_js_file('moment');
queue_js_file('jquery.daterangepicker');
queue_css_file('daterangepicker');

//initiate annotation model
queue_js_file('annotation-model');
queue_js_string('console.log("annotation knockout model loaded");');

$annotationPath = 'annotation';
queue_css_file('form');

$pageTitle = __('Annotate');
$head = array('title' => $pageTitle, 'bodyclass' => 'annotation');
echo head($head);
echo flash();
?>

<script type="text/javascript">

var model = new DocumentModel();
//first destroy bindings before applying them
ko.applyBindings(model);

// <![CDATA[
enableAnnotationAjaxForm(<?php echo js_escape(url($annotationPath.'/annotation/type-form')); ?>);
// ]]>
</script>

<?php
echo $this->partial('annotation-navigation.php');

?>
<div id="primary">
<?php $user = current_user(); ?>
<?php echo flash(); ?>
    
    <h1><?php echo $head['title']; ?></h1>

        <form method="post" action="" id="annotation-form"enctype="multipart/form-data">
        
            <fieldset id="annotation-item-metadata">
                <div class="inputs">
                    <label for="annotation-type"><?php echo __("What type of item do you want to annotate?"); ?></label>
                    <?php $options = get_table_options('AnnotationType'); ?>
                    <?php $typeId = isset($type) ? $type->id : '' ; ?>
                    <?php echo $this->formSelect( 'annotation_type', $typeId, array('multiple' => false, 'id' => 'annotation-type') , $options); ?>
                    <input type="submit" name="submit-type" id="submit-type" value="Select" />
                </div>
                <div id="annotation-type-form">

                <?php echo $id; ?>

                <?php if (isset($typeForm)): echo $typeForm; endif; ?>
                </div>
            </fieldset>
           
        </form>
</div>

<?php echo foot();?>
