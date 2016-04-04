<?php 
    echo head(array('title' => 'Duplicate Item', 'bodyclass' => 'primary', 
        'content_class' => 'horizontal-nav'));
?>

<div id="primary">
    <?php echo flash(); ?>
    <h2><?php echo __('You want to duplicate the following item:'); ?></h2>
    
    <?php echo "Original item: " . link_to_item(metadata($record, array('Dublin Core', 'Identifier')), array(), 'show', $record); ?>
    <br>
</div>
<?php 
    echo foot(); 
?>
