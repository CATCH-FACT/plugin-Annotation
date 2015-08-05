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

class AnnotationAnnotatedItem extends Omeka_Record_AbstractRecord
{
    public $id;
    public $item_id;
    public $public;
    public $finished;
    public $annotation_type_id;
    public $anonymous;
    
    protected $_related = array(
        'Item' => 'getItem',
        'Annotator' => 'getAnnotator'
        );
    
    public function getItem()
    {
        return $this->getDb()->getTable('Item')->find($this->item_id);
    }

    public function makeNotPublic()
    {
        $this->public = false;
        $item = $this->Item;
        $item->public = false;
        $item->save();
        release_object($item);
    }

    public function makeFinished()
    {
//        $this->finished = true;
        $item = $this->Item;
        $item->finished = true;
        $item->save();
        release_object($item);
    }

    
    public function getAnnotator()
    {
        $owner = $this->Item->getOwner();
        //if the user has been deleted, make a fake user called "Deleted User"
        if(!$owner) {
            $owner = new User();
            $owner->name = __('Deleted User');
            return $owner;
        }
        $user = current_user();
        if($user && $user->id == $owner->id) {
            return $owner;
        }
        //mimic an actual user, but anonymous if user doesn't have access
        if($this->anonymous == 1 && !is_allowed('Annotation_Items', 'view-anonymous')) {
            $owner = new User();
            $owner->name = __('Anonymous');
        }
        return $owner;
    }
}
