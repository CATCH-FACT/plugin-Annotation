<form id="csvimport" method="post" action="">
<?php
    $colNames = $this->columnNames;
    $colExamples = $this->columnExamples;
?>
    <table id="clone-fields" class="simple" cellspacing="0" cellpadding="0">
    <thead>
    <tr>
        <th><?php echo __('Column'); ?></th>
        <th><?php echo __('Data'); ?></th>
        <th><?php echo __('Clone'); ?></th>
    </tr>
    </thead>
    <tbody>
<?php //for($i = 0; $i < count($colExamples); $i++): ?>
<?php foreach($colExamples as $colExampleId => $colExampleValue): ?>
    <?php foreach($colExampleValue as $aId => $subColExampleValue): ?>
        <tr>
        <?php $nameField = $colNames[$colExampleId]; ?>
        <td><strong><?php echo html_escape($nameField); ?></strong></td>

        <?php $exampleString = $colExampleValue[$aId]->text; ?>
        <td>&quot;<?php echo html_escape(substr($exampleString, 0, 67)); ?>&quot;<?php if (strlen($exampleString) > 47) { echo '...';} ?></td>
        <?php echo $this->form->getSubForm("row$colExampleId id$aId"); ?>
        </tr>
    <?php endforeach; ?>
<?php endforeach; ?>
    </tbody>
    </table>
    <fieldset>
    <?php echo $this->form->submit; ?>
    </fieldset>
</form>
