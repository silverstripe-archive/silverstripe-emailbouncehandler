<?php
/**
 * Database record for recording a bounced email
 */
class Email_BounceRecord extends DataObject
{
    public static $db = array(
            'BounceEmail' => 'Varchar',
            'BounceTime' => 'SS_Datetime',
            'BounceMessage' => 'Varchar'
    );
    
    public static $has_one = array(
            'Member' => 'Member'
    );

    public static $has_many = array();
    
    public static $many_many = array();
    
    public static $defaults = array();
    
    public static $singular_name = 'Email Bounce Record';
    
    
    /** 
    * a record of Email_BounceRecord can't be created manually. Instead, it should be	
    * created though system. 
    */
    public function canCreate($member = null)
    {
        return false;
    }
}
