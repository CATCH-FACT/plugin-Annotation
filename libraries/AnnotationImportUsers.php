<?php
class AnnotationImportUsers extends Omeka_Job_AbstractJob
{
    public function perform()
    {
        $db = get_db();
        //import the annotators to Guest Users
        $sql = "SELECT * FROM $db->AnnotationAnnotators";
        $res = $db->query($sql);
        $data = $res->fetchAll();
        //key: annotator id; value user id
        $annotatorUserMap = array();
        $usernameFiller = 11111; //count up for non-unique usernames, and ones too short
        $emailFiller = 1;
        $validatorOptions = array(
                'table'   => $db->getTable('User')->getTableName(),
                'field'   => 'username',
                'adapter' => $db->getAdapter()
        );
        $uniqueUsernameValidator = new Zend_Validate_Db_NoRecordExists($validatorOptions);
        $emailValidator = new Zend_Validate_EmailAddress();
        foreach($data as $annotator) {
            if($user = $db->getTable('User')->findByEmail($annotator['email'])) {
                $userAnnotatorMap[$user->id][] = $annotator['id'];
            } else {
                $user = new User();
                //create username from email
                $usernameParts = explode('@', $annotator['email']);
                $username = preg_replace("/[^a-zA-Z0-9\s]/", "", $usernameParts[0]);
        
                if( (strlen($username) < $user::USERNAME_MIN_LENGTH) || !$uniqueUsernameValidator->isValid($username)) {
                    $username = $username . $usernameFiller;
                    $usernameFiller++;
                }
                $email = $annotator['email'];
                if(!$emailValidator->isValid($email)) {
                    //can't save as a new user w/o valid unique email, so either create
                    //a fake one, or shove all invalid-email annotators onto one real user (superuser)
                    $email = "fake$emailFiller@example.com";
                    $emailFiller++;
                }
                $name = trim($annotator['name']);
                $user->username = $username;
                $user->name = empty($name) ? "user" : $name;
                $user->email = $email;
                $user->role = "guest";
                $user->active = true;
        
                try {
                    $user->save();
                } catch (Exception $e) {
                    _log($e->getMessage());
                    $user = $db->getTable('User')->find(1);
                }
                $userAnnotatorMap[$user->id] = array($annotator['id']);
            }
            release_object($user);
        }
        $this->_mapUsersToItems($userAnnotatorMap);
        //we need to keep track of which annotators got mapped to which users
        //so that the UserProfiles import of annotator info can match people up
        $serialized = serialize($userAnnotatorMap);
        $putResult = file_put_contents(CONTRIBUTION_PLUGIN_DIR . '/upgrade_files/user_annotator_map.txt', $serialized);
    }
    
    private function _mapUsersToItems($userAnnotatorMap)
    {
        $db=get_db();        
        foreach($userAnnotatorMap as $userId=>$annotatorIds) {
            $contribIds = implode(',' , $annotatorIds);
            //dig up the items annotated and set the owner
            $sql = "SELECT `item_id` FROM $db->AnnotationAnnotatedItems WHERE `annotator_id` IN ($contribIds) ";
            $res = $db->query($sql);
            $annotatedItemIds =  $res->fetchAll();
            $itemTable = $db->getTable('Item');
            $ids = array();
            foreach($annotatedItemIds as $row) {
                $ids[] = $row['item_id'];
            }
            $idsString = implode(',', $ids);
            if(!empty($idsString)) {
                $sql = "UPDATE `omeka_items` SET `owner_id`=$userId WHERE `id` IN ($idsString)";
                $res = $db->query($sql);
            }            
        }
    }
}