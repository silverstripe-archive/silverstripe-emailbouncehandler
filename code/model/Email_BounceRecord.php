<?php
/**
 * Database record for recording a bounced email
 */
class Email_BounceRecord extends DataObject {
	static $db = array(
			'BounceEmail' => 'Varchar',
			'BounceTime' => 'SS_Datetime',
			'BounceMessage' => 'Varchar'
	);
	
	static $has_one = array(
			'Member' => 'Member'
	);	 

	static $has_many = array();
	
	static $many_many = array();
	
	static $defaults = array();
	
	static $singular_name = 'Email Bounce Record';
	
	
	/** 
	* a record of Email_BounceRecord can't be created manually. Instead, it should be	
	* created though system. 
	*/ 
	public function canCreate($member = null) {
		return false; 
	}
}


