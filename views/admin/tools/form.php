<?php 

queue_js_file('tool-settings');
$toolTypeOptions = array('' => 'Select an Item Type', 'bash' => 'Bash', 'webapp' => 'Web application');
$toolOutputTypeOptions = array('' => 'Select an Item Type', 'bash' => 'Output to screen (bash)', 'web' => 'A URL', 'file' => 'An output file (-o)');
$toolOutputOptions = array('' => 'Select an Item Type', 'raw' => 'Raw format (limited processing)', 'xml' => 'XML format', 'jason' => 'JASON format');

?>
<form method='post'>  
<section class='seven columns alpha'>
    <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Tool Type"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("The type of tool this will become. 'bash' for a commandline style tool. 'Web application' for a returning values from an application at another location on the web."); ?></p>
            <div class="input-block">
               <?php echo $this->formSelect('tool_type', $annotation_tool->tool_type, array(), $toolTypeOptions); ?>
            </div>
        </div>
    </div>
    
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
             <label><?php echo __("Command / Url"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("Bare command or URL of the (web)application. In case of a python/php/java program include the interpreter command (python your_tool.py).
                                                    This is the most <b> complicated </b> part.<br>
                                                    Very important are the input (%i) and output (%o) parameters. One input and output parameter per tool. 
                                                    Examples can be found in <a href=\"annotation/index\">Getting started</a>."); ?></p>
             <div class="input-block">
              <?php echo $this->formTextArea('command', $annotation_tool->command, array('rows' => '8')); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Extra command arguments"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("Put the arguments necessary to run the application. <br>
                                                    More info: <a href=\"annotation/index\">Getting started</a>."); ?></p>
             <div class="input-block">
              <?php echo $this->formText('arguments', $annotation_tool->arguments, array()); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Tool output type"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("The type of output this tool generates. The output can be directed through different channels. The options that are available: From screen (bash), from a URL, or output file (-o)"); ?></p>
             <div class="input-block">
                <?php echo $this->formSelect('output_type', $annotation_tool->output_type, array(), $toolOutputTypeOptions); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Tool output format"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("The output format this tool generates. Raw format means that the output has no standardized formatting. There are limited processing capabilities to this format.<br>
                                                    XML and JASON format can be processed. All values from a specified tag can be extracted, of the complete document when no tag/element name is filled in."); ?></p>
             <div class="input-block">
                <?php echo $this->formSelect('output_format', $annotation_tool->output_format, array(), $toolOutputOptions); ?>
             </div>
         </div>
     </div>

     <div class="field">
         <div class="two columns alpha">
             <label><?php echo __("Tag / element name / separator"); ?></label>
         </div>
         <div class="inputs five columns omega">
             <p class="explanation"><?php echo __("This values depends on the <b>Tool output format</b>. When the format is <b>raw</b>, a separator can be set (i.e. \\n, \\t, &).<br>
                                                    For <b>XML and JASON</b> format a tag name, or element name, can be set. If the value of interest is between <ner_value>, leave out the \"<\" and \">\"."); ?></p>
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
