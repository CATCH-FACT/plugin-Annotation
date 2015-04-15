<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright University of Twente
 * @package Annotation
 */

$annotationTypeElements = $annotation_type->AnnotationTypeElements;

$typeName = html_escape($annotation_type->display_name);
queue_css_file('annotation-type-form');

$addNewTypeRequestUrl = admin_url('annotation/types/add-new-type-element');
$addExistingTypeRequestUrl = admin_url('annotation/types/add-existing-type-element');
$changeExistingtypeElementUrl = admin_url('annotation/types/change-existing-type-element'); 

queue_js_file('annotation-types');

$js = "
    jQuery(document).ready(function () {
        var addNewTypeRequestUrl = '" . admin_url('annotation/types/add-new-type-element') . "'
        var addExistingTypeRequestUrl = '" . admin_url('annotation/types/add-existing-type-element') . "'
        var changeExistingTypeElementUrl = '" . admin_url('annotation/types/change-existing-type-element') . "'
        Omeka.AnnotationTypes.manageAnnotationTypes(addNewTypeRequestUrl, addExistingTypeRequestUrl, changeExistingTypeElementUrl);
        Omeka.AnnotationTypes.enableSorting();
    });                
    ";
queue_js_string($js);
queue_css_file('annotation-type-form');
annotation_admin_header(array(__('Types'), __("Edit") . " &ldquo;$typeName&rdquo;"));
?>

<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <?php  include 'form.php'; ?>
</div>

<?php echo foot(); ?>
