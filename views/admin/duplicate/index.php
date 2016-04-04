<?php 
    echo head(array('title' => 'Duplicate Item', 'bodyclass' => 'primary', 
        'content_class' => 'horizontal-nav'));

    echo $this->partial('Annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <h2><?php echo __('You want to duplicate the following item:'); ?></h2>
    
    <br>
    <br>
</div>
<?php 
    echo foot(); 
?>
