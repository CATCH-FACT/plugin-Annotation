<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package annotation
 */
$name = html_escape($annotator->name);
queue_css_file('annotators');
annotation_admin_header(array(__('annotators'), "$name"));
?>


<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <h2><?php echo $annotator->name; ?><?php echo __("'s annotations"); ?></h2>
    
    <div id='annotation-profile-info'>
        <?php if(plugin_is_active('UserProfiles')): ?>
        <?php 
            $this->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
            echo $this->linkToOwnerProfile(array('owner'=>$annotator, 'text'=>"Profile: "));    
        ?>
        <?php endif; ?>
    </div>
    
    <div id='annotation-user-annotations'>
        <?php foreach($items as $item): ?>
        <?php set_current_record('item', $item->Item); ?>
        <section class="five columns omega annotation">
            <?php 
                if ($item->Item->public) {
                    $status = __('Public');
                } else {
                    if($item->public) {
                        $status = __('Needs review');
                    } else {
                        $status = __('Private annotation');
                    }
                }
            ?>
        
            <h2><?php echo link_to_item(null, array(), 'edit'); ?></h2>
            <p><?php echo $status;?> <?php echo (boolean) $item->anonymous ? " | " . __('Anonymous') : "";  ?></p>
            <?php
            echo item_image_gallery(
                array('linkWrapper' => array('class' => 'admin-thumb panel')),
                'square_thumbnail', true);
            ?>
            <?php echo all_element_texts('item'); ?>
        </section>   
        
        <?php endforeach; ?>

    </div>
</div>
<?php echo foot(); ?>
