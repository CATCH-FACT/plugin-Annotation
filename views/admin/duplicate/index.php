<?php 
    echo head(array('title' => 'Duplicate Item', 'bodyclass' => 'primary', 
        'content_class' => 'horizontal-nav'));

    echo $this->partial('Annotation-navigation.php');
?>

<div id="primary">

    <h2><?php echo __('You want to duplicate the following item:'); ?></h2>
    
    <br>
</div>
<?php 
    echo foot(); 
?>
