<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 * @subpackage Models
 */

/**
 * Record that keeps track of annotations; links items to annotators.
 */

class AnnotationTool extends Omeka_Record_AbstractRecord
{
    public $id;
    public $tool_type;
    public $display_name;
    public $description;
    public $command;
    public $arguments;
    public $output_type;
    public $output_format;
    public $tag_or_separator;
    public $order;
    public $validated;
    
}
