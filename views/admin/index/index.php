<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */

annotation_admin_header(array(__('Getting Started')));
?>

<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <div id="getting-started">
    <h2><?php echo __("Getting Started"); ?></h2>
    <p><?php echo __("A basic annotation form is installed and ready to ask annotators the minimum amount of information, and to include their name."); ?></p>
    <p><?php echo __("Based on the annotation tools installed and the coupling between input values and target fields, annotation values will be automatically designated."); ?></p>  
    <dl>
        <dt><?php echo __("1. Set up Tools to automatically annotate with:"); ?></dt>
        <dd>
            <p><?php echo __("Setting up the tools is the most complicated part of the settings.") ?></p>
            <ul>            
            <li><?php echo __("[INFORMATION ON SETTING UP TOOLS!]"); ?></li>
            </ul>
        </dd>
    
        <dt><?php echo __("2. Modify the annotation form:"); ?></dt>
        <dd>
            <ul>
                <li><?php echo __("Choose item types you wish visitors to share, and customize the fields they should use, in %s", "<a href='" . url('annotation/types') . "'>" . __("Annotation Types") . ".</a>"); ?></li>
                <?php if(plugin_is_active('UserProfiles')):?>
                <li><?php echo __("Set up profile information you would like from your annotators by setting up a %s ", "<a href='" . url('user-profiles') . "'>" . __('user profiles type') . "</a>"); ?> </li>
                <?php else:?>
                <li><?php echo __("The optional User Profiles plugin lets you set up additional information you would like to ask from your annotators. To use those features, please install that, then return here for additional guidance.");?></li>
                <?php endif; ?>
            </ul>
        </dd>
        <dt><?php echo __("3. Configure the %s for annotations:", "<a href='" . url('annotation/settings') . "'>" . __('submission settings') . "</a>"); ?></dt>
        <dd>
            <ul>
                <li><?php echo __("Set the terms of service for annotating to the site."); ?></li>
                <li><?php echo __("Set up an auto-generated email to send to all annotators after they submit their annotation."); ?></li>
                <li><?php echo __("Decide whether to use the 'Simple' options. This requires only that annotators provide an email address."); ?></li>
                <li><?php echo __("Specify a collection for new annotated items."); ?></li>
            </ul>
        </dd>
        <dt><?php echo __("4. Browse annotations and their status, with links to more annotator information, in %s", "<a href='" . url('annotation/items'). "'>" . __('Annotations') . "</a>"); ?></dt>
    </dl>
    </div>
</div>
<?php echo foot(); ?>
