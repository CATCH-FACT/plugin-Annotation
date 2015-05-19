<?php 
    echo head(array('title' => 'Clone Item', 'bodyclass' => 'primary', 
        'content_class' => 'horizontal-nav'));
?>
<?php echo common('annotation-nav'); ?>
<div id="primary">
    <?php echo flash(); ?>
    <h2><?php echo __('You want to clone the following item:'); ?></h2>
    <?php echo "Original item: " . link_to_item(metadata($this->session->record, array('Dublin Core', 'Identifier')), array(), 'show', $this->session->record); ?>
    
    <?php echo $this->form; ?>
</div>
<?php 
    echo foot(); 
?>
