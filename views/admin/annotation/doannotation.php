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

$head = array('title' => 'Annotate',
              'bodyclass' => 'doannotation');
echo head($head);
?>

<?php
echo $this->partial('annotation-navigation.php');

?>
<div id="primary">
<?php echo flash(); ?>
    
    <h1><?php echo $head['title']; ?></h1>
    
    <p><?php link_to($this->temp_item, 'show', "Your annotated item"); ?></p>

</div>
<?php echo foot();
