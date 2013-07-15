<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */

/*$annotationType = $annotation_type;
$annotationTypeElements = $annotation_type->AnnotationTypeElements;
$itemType = $annotation_type->ItemType;
if($itemType) {
    $elements = $itemType->Elements;    
} else {
    $elements = array();
}
*/
annotation_admin_header(array(__('Tools'), __('Add a new tool')));
?>

<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <?php include 'form.php'; ?>
</div>
<?php echo foot(); ?>
