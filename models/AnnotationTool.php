<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 * @subpackage Models
 */

/**
 * Record that keeps track of annotations; links items to annotators.
 */

class AnnotationTool extends Omeka_Record_AbstractRecord
{
    public $id;                 //The internal ID of the tool
    public $display_name;       //The name of the tool in Omeka
    public $description;        //A description on what the application does
    public $command;            //Full web adress without arguments (http://toolshed.nl/api/analyze.xml)
    public $get_arguments;      //additional GET arguments (style=list&foo=bar) (? will be placed automatically)
    public $post_arguments;     //POST arguments to transfer data to the tool. (text=%i) (only one post value at first)
    //no arguments will send entire document as json.
    public $output_format;      // The format of the returned data (XML/JSON/RAW)
    public $jsonxml_value_node; // The name of the main node where the data resides (standard: value)
    public $jsonxml_score_node; // The score of the main node where the data resides (standard: score)
    public $jsonxml_score_sub_node; //value node can be an array. this will be the name of that score field
    public $jsonxml_value_sub_node; //value node can be an array. this will be the value of that score field
    public $jsonxml_idx_sub_node;    //value node can be an array. this will be the idx of that score field (if the data is ranked or ordered insome way)
    public $tag_or_separator;   // Separator for raw data
    public $order;              // the order of representation on the Tools form
    public $validated;          // The tool is tested properly
    
    /** alias for get tool **/
    public function getToolById($id)
    {
        return $this->_db->getTable('AnnotationTool')->find($id);
    }
    
    
    
}
