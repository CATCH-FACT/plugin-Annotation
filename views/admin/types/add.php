<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */

$annotationType = $annotation_type;
$annotationTypeElements = $annotation_type->AnnotationTypeElements;
$itemType = $annotation_type->ItemType;

if($itemType) {
    $elements = $itemType->Elements;    
} else {
    $elements = array();
}

$addNewRequestUrl = admin_url('annotation/types/add-type-element');
$addRequestUrl = admin_url('annotation/types/add-type-element');
$changeElementUrl = admin_url('annotation/types/change-element');

queue_js_file('annotation-types');

$js = "
    jQuery(document).ready(function () {
        var addNewRequestUrl = '" . admin_url('annotation/types/add-type-element') . "'
        var addRequestUrl = '" . admin_url('annotation/types/add-type-element') . "'
        var changeElementUrl = '" . admin_url('annotation/types/change-element') . "'
        Omeka.AnnotationTypes.manageAnnotationTypes(addNewRequestUrl, addRequestUrl, changeElementUrl);
        Omeka.AnnotationTypes.enableSorting();
    });
";
queue_js_string($js);
queue_css_file('annotation-type-form');
annotation_admin_header(array(__('Types'), __('Add a new type')));
?>

<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <?php include 'form.php'; ?>
</div>

<?php echo foot(); ?>
