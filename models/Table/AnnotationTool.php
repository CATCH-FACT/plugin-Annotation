<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Meertens Institute 2015
 * @package Annotation
 * @subpackage Models
 */

/**
 * Table that holds info on tools.
 *
 * @package Annotation
 * @subpackage Models
 */
class Table_AnnotationTool extends Omeka_Db_Table
{
    
    /**
     * Retrieves AnnotationTypeElements associated with the given type.
     *
     * @param AnnotationType|int $type AnnotationType to search for
     * @return array Array of AnnotationTypeElements
     */
    public function findByType($type)
    {
        if (is_int($type)) {
            $typeId = $type;
        } else {
            $typeId = $type->id;
        }
        
        return $this->findBySql('type_id = ?', array($typeId));
    }
    
    public function getSelect()
    {
        $select = parent::getSelect();
        $select->order('order ASC');
        return $select;
    }
    
    /**
     * Find all elements for use by select form element.
     * 
     * @return array
     */
    public function findElementsForSelect()
    {
        $db = $this->getDb();
        $select = $db->select()
                     ->from(array('annotation_tools' => $db->AnnotationTool), 
                             array('id', 'display_name'))
                     ->order(array($db->AnnotationTool => 'order'));
        $elements = $db->fetchAll($select);
        $options = array('' => __('Select Below'));
        foreach ($elements as $element) {
            $options[$element['id']] = $element['display_name'];
        }
        return $options;
    }
} 
