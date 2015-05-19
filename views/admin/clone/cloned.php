<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute, 2015
 * @package Annotation
 */

annotation_admin_header(array(__('Cloned')));
?>

<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <div id="clone">
        
        <h2><?php echo __("Clone results:"); ?></h2>

        <?php echo "Original item: " . link_to_item(metadata($record, array('Dublin Core', 'Identifier')), array(), 'show', $record); ?>
        <br>
        <br>
        <?php echo "New item: " . link_to_item(metadata($new_record, array('Dublin Core', 'Identifier')), array(), 'show', $new_record); ?>

    </div>
</div>
<?php echo foot(); ?>
