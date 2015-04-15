<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright 
 * @package Annotation
 */
queue_css_file('annotation-type-form');
annotation_admin_header(array(__('Tools')));
?>
<a id="add-type" class="small green button" href="<?php echo url(array('action' => 'add')); ?>">Add a Tool</a>
    
<?php 
echo $this->partial('annotation-navigation.php');
?>


<div id="primary">
    <?php echo flash(); ?>

    <table>
        <thead id="types-table-head">
            <tr>
                <th><?php echo __("Name"); ?></th>
                <th><?php echo __("Description"); ?></th>
                <th><?php echo __("Edit"); ?></th>
                <th><?php echo __("Validated"); ?></th>
            </tr>
        </thead>
        <tbody id="types-table-body">
<?php foreach ($annotation_tools as $tool): ?>
    <tr>
        <td><strong><?php echo metadata($tool, 'display_name'); ?></strong></td>
        <td><?php  echo __(metadata($tool, 'description')); ?></td>
        <td><a href="<?php echo url(array('action' => 'edit', 'id' => $tool->id)); ?>" class="edit"><?php echo __("Edit"); ?></a></td>
        <td><?php  echo __(metadata($tool, 'validated')); ?></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php echo foot(); ?>
