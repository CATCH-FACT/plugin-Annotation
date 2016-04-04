<?php 
    echo head(array('title' => 'Clone Item', 'bodyclass' => 'primary', 
        'content_class' => 'horizontal-nav'));

    echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <h2><?php echo __('You want to clone the following item:'); ?></h2>
    
    <?php // echo metadata($record, array('Item Type Metadata', 'Text')); ?>
    
    <?php echo "Original item: " . link_to_item(metadata($record, array('Dublin Core', 'Identifier')), array(), 'show', $record); ?>
    <br>
    <br>
    <?php echo $this->form; ?>
</div>
<?php 
    echo foot(); 
?>
