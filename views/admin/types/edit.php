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
$addTypeRequestUrl = admin_url('annotation/types/add-type-element');
$changetypeElementUrl = admin_url('annotation/types/change-type-element'); 

queue_js_file('annotation-types');

$js = "
    jQuery(document).ready(function () {
        var addNewTypeRequestUrl = '" . admin_url('annotation/types/add-new-type-element') . "'
        var addTypeRequestUrl = '" . admin_url('annotation/types/add-type-element') . "'
        var changeTypeElementUrl = '" . admin_url('annotation/types/change-type-element') . "'
        Omeka.AnnotationTypes.manageAnnotationTypes(addNewTypeRequestUrl, addTypeRequestUrl, changeTypeElementUrl);
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
    <?php include 'form.php'; ?>
</div>

<?php echo foot(); ?>
