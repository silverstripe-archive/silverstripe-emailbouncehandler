<?php
/**
 * Base class that email bounce handlers extend
 */
class Email_BounceHandler extends Controller {
	
	static $allowed_actions = array( 
		'index'
	);
	
	public function init() {
		BasicAuth::protect_entire_site(false);
		parent::init();
	}
	
	public function index() {
		$subclasses = ClassInfo::subclassesFor( $this->class );
		unset($subclasses[$this->class]);
		
		if( $subclasses ) {	
			$subclass = array_pop( $subclasses ); 
			$task = new $subclass();
			$task->index();
			return;
		}	 
				
		// Check if access key exists
		if( !isset($_REQUEST['Key']) ) {
			echo 'Error: Access validation failed. No "Key" specified.';
			return;
		}

		// Check against access key defined in _config.php
		if( $_REQUEST['Key'] != EMAIL_BOUNCEHANDLER_KEY) {
			echo 'Error: Access validation failed. Invalid "Key" specified.';
			return;
		}

		if( !$_REQUEST['Email'] ) {
			echo "No email address";
			return;		
		}
		
		$this->recordBounce( $_REQUEST['Email'], $_REQUEST['Date'], $_REQUEST['Time'], $_REQUEST['Message'] );	 
	}
		
	private function recordBounce( $email, $date = null, $time = null, $error = null ) {
		if(preg_match('/<(.*)>/', $email, $parts)) $email = $parts[1];
		
		$SQL_email = Convert::raw2sql($email);
		$SQL_bounceTime = Convert::raw2sql("$date $time");

		$duplicateBounce = DataObject::get_one("Email_BounceRecord",
			"\"BounceEmail\" = '$SQL_email' AND (\"BounceTime\"+INTERVAL 1 MINUTE) > '$SQL_bounceTime'");
		
		if(!$duplicateBounce) {
			$record = new Email_BounceRecord();
			
			$member = DataObject::get_one( 'Member', "\"Email\"='$SQL_email'" );
			
			if( $member ) {
				$record->MemberID = $member->ID;

				// If the SilverStripeMessageID (taken from the X-SilverStripeMessageID header embedded in the email)
				// is sent, then log this bounce in a Newsletter_SentRecipient record so it will show up on the 'Sent
				// Status Report' tab of the Newsletter
				if( isset($_REQUEST['SilverStripeMessageID'])) {
					// Note: was sent out with: $project . '.' . $messageID;
					$message_id_parts = explode('.', $_REQUEST['SilverStripeMessageID']);
		
					// Escape just in case
					$SQL_memberID = Convert::raw2sql($member->ID);
					
					// Log the bounce
					if(class_exists('Newsletter_SentRecipient')) {
						// Note: was encoded with: base64_encode( $newsletter->ID . '_' . date( 'd-m-Y H:i:s' ) );
						$newsletter_id_date_parts = explode ('_', base64_decode($message_id_parts[1]) );
						$SQL_newsletterID = Convert::raw2sql($newsletter_id_date_parts[0]);
						$oldNewsletterSentRecipient = DataObject::get_one("Newsletter_SentRecipient",
							"\"MemberID\" = '$SQL_memberID' AND \"ParentID\" = '$SQL_newsletterID'"
							. " AND \"Email\" = '$SQL_email'");
						
						// Update the Newsletter_SentRecipient record if it exists
						if($oldNewsletterSentRecipient) {			
							$oldNewsletterSentRecipient->Result = 'Bounced';
							$oldNewsletterSentRecipient->write();
						} else {
							// For some reason it didn't exist, create a new record
							$newNewsletterSentRecipient = new Newsletter_SentRecipient();
							$newNewsletterSentRecipient->Email = $SQL_email;
							$newNewsletterSentRecipient->MemberID = $member->ID;
							$newNewsletterSentRecipient->Result = 'Bounced';
							$newNewsletterSentRecipient->ParentID = $newsletter_id_date_parts[0];
							$newNewsletterSentRecipient->write();
						}

						// Now we are going to Blacklist this member so that email will not be sent to them in the future.
						// Note: Sending can be re-enabled by going to 'Mailing List' 'Bounced' tab and unchecking the box
						// under 'Blacklisted'
						$member->setBlacklistedEmail(TRUE);
						echo '<p><b>Member: '.$member->FirstName.' '.$member->Surname
							.' <'.$member->Email.'> was added to the Email Blacklist!</b></p>';
					}
				}
			} 
						
			if(!$date) $date = date( 'd-m-Y' );
					
			if(!$time) $time = date( 'H:i:s' );
					
			$record->BounceEmail = $email;
			$record->BounceTime = $date . ' ' . $time;
			$record->BounceMessage = $error;
			$record->write();
			
			echo "Handled bounced email to address: $email";	
		} else {
			echo 'Sorry, this bounce report has already been logged, not logging this duplicate bounce.';
		}
	}	
		
}