<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */

Annotation_admin_header(array(__('Annotators')));
?>


<?php 
echo $this->partial('Annotation-navigation.php');
?>

<div id="primary">
<?php
echo flash();
if (!has_loop_records('Annotation_Annotators')):
    echo '<p>No one has Annotated to the site yet.</p>';
else:
?>
    <div class="pagination"><?php echo pagination_links(); ?></div>
    <table>
        <thead id="types-table-head">
            <tr>
                <th><?php echo __('ID'); ?></th>
                <th><?php echo __('Name'); ?></th>
                <th><?php echo __('Email'); ?></th>
                <th><?php echo __('Annotated Items'); ?></th>
            </tr>
        </thead>
        <tbody id="types-table-body">
<?php 
foreach (loop('Annotation_Annotators') as $Annotator):
    $id = $Annotator->id;
?>
    <tr>
        <td><?php echo html_escape($Annotator->id); ?></td>
        <td><a href="<?php echo url(array('action' => 'show', 'id' => $id)); ?>"><?php echo html_escape($Annotator->name); ?></a></td>
        <td><?php echo html_escape($Annotator->email); ?></td>
        <td><a href="<?php echo url("items/browse/Annotator_id/$id") ?>">View</a></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination"><?php echo pagination_links(); ?></div>
<?php endif; ?>
</div>
<?php echo foot(); ?>
