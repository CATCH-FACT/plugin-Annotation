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
    
    <p><b> <?php echo __("New: ") . link_to($this->item, 'show', __("Your annotated item")); ?></b></p>

    <br><hr>

    <p class="folktale-description" style="border:0; color:grey">Een <?php echo metadata('item', array('Item Type Metadata', 'Subgenre'), array('no_filter' => true)); ?> (<?php echo metadata('item', array('Dublin Core', 'Type'), array('no_filter' => true)); ?>), <?php echo metadata('item', array('Dublin Core', 'Date')); ?></p>

    <div id="primary">

        <?php fire_plugin_hook('public_items_show_top', array('view' => $this, 'item' => $item)); ?>
        
        <?php echo all_element_texts('item'); ?>

    </div><!-- end primary -->


    
</div>
<?php echo foot();
