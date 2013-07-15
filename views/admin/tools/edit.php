<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 */

$toolName = html_escape($annotation_tool->name);
queue_css_file('annotation-type-form');

annotation_admin_header(array(__('Tools'), __("Edit") . " &ldquo;$toolName&rdquo;"));
?>

<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <?php  include 'form.php'; ?>
</div>

<?php echo foot(); ?>
