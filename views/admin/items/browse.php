<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2013
 * @package Annotation
 */

annotation_admin_header(array(__('Annotated Items')));
?>


<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">

<?php
echo flash();
?>
    <div class="pagination"><?php echo pagination_links(); ?></div>
    
    <ul class="quick-filter-wrapper">
        <li><a href="#" tabindex="0"><?php echo __('Filter by status'); ?></a>
        <ul class="dropdown">
            <li><span class="quick-filter-heading"><?php echo __('Filter by status') ?></span></li>
            <li><a href="<?php echo url('annotation/items'); ?>"><?php echo __('View All') ?></a></li>
            <li><a href="<?php echo url('annotation/items', array('status' => 'public')); ?>"><?php echo __('Public'); ?></a></li>
            <li><a href="<?php echo url('annotation/items', array('status' => 'private')); ?>"><?php echo __('Private'); ?></a></li>
            <li><a href="<?php echo url('annotation/items', array('status' => 'review')); ?>"><?php echo __('Needs review'); ?></a></li>
        </ul>
        </li>
    </ul>    
    
    <table>
        <thead id="types-table-head">
            <tr>
                <?php
                $browseHeadings[__('Annotator')] = 'annotator';
                $browseHeadings[__('Item')] = null;
                $browseHeadings[__('Publication Status')] = null;
                $browseHeadings[__('Date Added')] = 'added';
                echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => '')); 
                ?>        
            </tr>
            
        </thead>
        <tbody id="types-table-body">
        <?php foreach(loop('annotation_annotated_items') as $contribItem):?>
        
        <?php $item = $contribItem->Item; ?>
        <?php $annotator = $contribItem->Annotator; ?>
        <?php 
            if($annotator->id) {
                $annotatorUrl = url('annotation/annotators/show/id/' . $annotator->id);
            }
        
        ?>
        <tr>

            <td><?php echo metadata($annotator, 'name');?>
                 
                 <?php if(!is_null($annotator->id)): ?>
                 <?php if($contribItem->anonymous && (is_allowed('Annotation_Items', 'view-anonymous') || $annotator->id == current_user()->id)): ?>
                 <span>(<?php echo __('Anonymous'); ?>)</span>
                 <?php endif; ?>
                 <a href='<?php echo $annotatorUrl; ?>'><?php echo __("Info and annotations"); ?></a>
                 <?php endif; ?>             
            </td>
            
            <td><?php echo link_to($item, 'show', metadata($item, array('Dublin Core', 'Title'))); ?></td>
            <?php 
                if ($item->public) {
                    $status = __('Public');
                } else {
                    if($contribItem->public) {
                        $status = __('Needs review');
                    } else {
                        $status = __('Private annotation');
                    }
                }
            ?>
            <td><?php echo $status; ?></td>
            <td><?php echo format_date(metadata($item, 'added')); ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="pagination"><?php echo pagination_links(); ?></div>
<?php echo foot(); ?>