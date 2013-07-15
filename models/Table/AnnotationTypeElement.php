<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Annotation
 * @subpackage Models
 */

/**
 * Table that links types to elements.
 *
 * @package Annotation
 * @subpackage Models
 */
class Table_AnnotationTypeElement extends Omeka_Db_Table
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
} 
