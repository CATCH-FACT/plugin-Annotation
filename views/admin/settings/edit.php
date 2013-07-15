<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */


queue_js_file('annotation');
queue_js_file('tiny_mce', 'javascripts/vendor/tiny_mce');
queue_js_string('setUpSettingsWysiwyg();');
annotation_admin_header(array(__('Submission Settings')));

?>

<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <?php echo $form; ?>
</div>

<?php echo foot(); ?>
