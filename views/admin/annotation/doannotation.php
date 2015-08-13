<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright University of Twente, 2013
 * @package Annotation
 */

queue_js_file('annotation-admin-form');
$annotationPath = 'annotation';
queue_css_file('form');

$head = array('title' => 'Annotation result',
              'bodyclass' => 'doannotation');
echo head($head);
?>

<?php
echo $this->partial('annotation-navigation.php');

?>
<div id="primary">
<?php echo flash(); ?>
    
    <h1><?php echo $head['title']; ?></h1>
    
    <p> <?php echo __("New: ") . link_to($this->item, 'show', __("Your annotated item")); ?></p>
    
    <?php print_r($this->item); ?>
-
    <?php print_r($this->item_id); ?>  
    
</div>
<?php echo foot();
