<?php 
/*
`jsonxml_value_node` VARCHAR(255) NOT NULL,         #the main node with the values
`jsonxml_score_node` VARCHAR(255) NULL,
`jsonxml_value_sub_node` VARCHAR(255) NULL,         #the if the separate values also are an array
`jsonxml_score_sub_node` VARCHAR(255) NULL,
`jsonxml_idx_sub_node` VARCHAR(255) NULL,           
`tag_or_separator` VARCHAR(255) NULL,
`validated` ENUM('yes', 'no') NULL,
*/

queue_js_file('tool-settings');

$toolTypeOptions = array('' => 'Select an Item Type', 'bash' => 'Bash', 'webapp' => 'Web application');
$toolOutputTypeOptions = array('' => 'Select an Item Type', 'bash' => 'Output to screen (bash)', 'web' => 'A URL', 'file' => 'An output file (-o)');
$toolOutputOptions = array('' => 'Select an Item Type', 'raw' => 'Raw format (limited processing)', 'xml' => 'XML format', 'json' => 'JSON format');

?>
<form method='post'>  
<section class='seven columns alpha'>

    <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Name"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("The label you would like to use for this annotation type. If blank, the Item Type name will be used."); ?></p>
            <div class="input-block">
             <?php echo $this->formText('display_name', $annotation_tool->display_name, array()); ?>
            </div>
        </div>
    </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Description"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("A detailed description of the tool and what it does."); ?></p>
             <div class="input-block">
              <?php echo $this->formText('description', $annotation_tool->description, array()); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("URL without commands"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("Bare command or URL of the (web)application. Examples can be found in <a href=\"annotation/index\">Getting started</a>."); ?></p>
             <div class="input-block">
              <?php echo $this->formTextarea('command', $annotation_tool->command, array('rows' => '8')); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Extra command arguments (GET)"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("Additional GET arguments (?style=list&foo=bar) (? will be placed automatically if not given) <br>
                                                    More info: <a href=\"annotation/index\">Getting started</a>."); ?></p>
             <div class="input-block">
              <?php echo $this->formText('get_arguments', $annotation_tool->get_arguments, array()); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Extra command arguments (POST)"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("Additional POST arguments to transfer data to the tool. Will be integrated in POST JSON data structure. format: KEY:VALUE(S)<br>
                                                    More info: <a href=\"annotation/index\">Getting started</a>."); ?></p>
             <div class="input-block">
              <?php echo $this->formTextarea('post_arguments', $annotation_tool->post_arguments, array('rows' => '8')); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Tool output format"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("The output format this tool generates. Raw format means that the output has no standardized formatting. There are limited processing capabilities to this format.<br>
                                                    XML and JSON format can be processed. All values from a specified tag can be extracted, or the complete document when no tag/element name is filled in."); ?></p>
             <div class="input-block">
                <?php echo $this->formSelect('output_format', $annotation_tool->output_format, array(), $toolOutputOptions); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Name of the value node"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("The name of the node where the data resides (standard: value). This will get the data from <value>"); ?></p>
             <div class="input-block">
              <?php echo $this->formText('jsonxml_value_node', $annotation_tool->jsonxml_value_node ? $annotation_tool->jsonxml_value_node : "value" , array()); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Name of the score node"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("The name of the node where the score of the data resides (standard: score). This will get the data from <score>"); ?></p>
             <div class="input-block">
              <?php echo $this->formText('jsonxml_score_node', $annotation_tool->jsonxml_score_node ? $annotation_tool->jsonxml_score_node : "score", array()); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Name of the value SUBnode (if separate return values are in an array)"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("The name of the node where the data resides (standard: value). This will get the data from .value. Leave empty when output is normal array"); ?></p>
             <div class="input-block">
              <?php echo $this->formText('jsonxml_value_sub_node', $annotation_tool->jsonxml_value_sub_node ? $annotation_tool->jsonxml_value_sub_node : "value" , array()); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Name of the score SUBnode (if separate return values are in an array)"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("The name of the node where the score of the data resides (standard: score). This will get the data from .score. Leave empty when output is normal array"); ?></p>
             <div class="input-block">
              <?php echo $this->formText('jsonxml_score_sub_node', $annotation_tool->jsonxml_score_sub_node ? $annotation_tool->jsonxml_score_sub_node : "score", array()); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Tag / element name / separator"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("This values depends on the <b>Tool output format</b>. When the format is <b>raw</b>, a separator can be set (i.e. \\n, \\t, &).<br>
                                                    For <b>XML and JSON</b> format a tag name, or element name, can be set. If the value of interest is between <ner_value>, leave out the \"<\" and \">\"."); ?></p>
             <div class="input-block">
              <?php echo $this->formText('tag_or_separator', $annotation_tool->tag_or_separator, array()); ?>
             </div>
         </div>
      </div>

<section class='three columns omega'>
    <div id='save' class='panel'>
            <input type="submit" class="big green button" value="<?php echo __('Save Changes');?>" id="submit" name="submit">
            <?php if($annotation_tool->exists()): ?>
            <?php echo link_to($annotation_tool, 'delete-confirm', __('Delete'), array('class' => 'big red button delete-confirm')); ?>
            <?php endif; ?>
    </div>
</section>
</form>

<script type="text/javascript">
    jQuery(document).ready(function () {
        Annotation.Tools.checkTool(
            <?php echo js_escape(url(array("controller" => "settings", "action" => "check-tool"))); ?>,
            <?php echo js_escape(__('Test')); ?>
        );
    });
</script>
