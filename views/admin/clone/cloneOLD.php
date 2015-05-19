<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute, 2015
 * @package Annotation
 */

annotation_admin_header(array(__('Clone')));
?>

<?php 
echo $this->partial('annotation-navigation.php');
?>

<div id="primary">
    <?php echo flash(); ?>
    <div id="clone">
        
        <h2><?php echo __("You want to clone the following item:"); ?></h2>

        <?php echo "Original item: " . link_to_item(metadata($record, array('Dublin Core', 'Identifier')), array(), 'show', $record); ?>

        <br>
        <form id="select_metadata" method="post" action="">
        
        <table id="column-mappings" class="simple" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th>Select</th>
                <th>Field</th>
                <th>Values</th>
            </tr>
            </thead>
            <tbody>

        <?php foreach($elementsTexts as $elementsTextId => $elementsText):?>
            <?php $sel = true;
                if (strlen($elementsText->text) >= 50){ $sel = false; }?>
                <tr>
                    <td><?php echo $this->formCheckbox('clone[' . $elementsText->element_id . ']', $sel, null, array('1', '0')); ?></td>
                    <td><strong><?php echo html_escape($allElements[$elementsText->element_id]); ?></strong></td>
                    <td>&quot;<?php echo snippet($elementsText->text, 0, 100); ?>&quot;</td>
                </tr>
        <?php endforeach; ?>
        
            <?php if (metadata($record, 'has tags')): ?>
            <tr>
                <td><?php echo $this->formCheckbox('clonetag[' . $elementsText->element_id . ']', $sel, null, array('1', '0')); ?></td>
                <td><strong><?php echo __('Tags'); ?></strong></td>
                <td><?php echo implode($record->getTags(), ", "); ?></td>
                </div>
             </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <pre>
        <?php //echo print_r($record, true); ?>
        </pre>
        
        <?php echo $this->formButton('submit', __('Clone Item'),
            array('type' => 'submit',
                  'class' => 'submit submit-medium'));?>
        </form>
    </div>
</div>
<?php echo foot(); ?>
