<?php 
$head = array('title' => __('Annotation Terms of Service'));
echo head($head);
?>

<div id="primary">
<h1><?php echo $head['title']; ?></h1>
<?php echo get_option('annotation_consent_text'); ?>
</div>
<?php echo foot(); ?>