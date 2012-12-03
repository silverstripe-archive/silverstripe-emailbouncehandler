# SilverStripe Email Bounce Handling Module

## Overview

Email bounce handling for SilverStripe CMS, implemented through callbacks
in the mail server for incoming mail.

*Caution: This functionality has been migrated from SilverStripe core in 2012,
but hasn't been actively used or maintained in a while.
It should be regarded as a starting point rather than a complete solution.
In general, we recommend using third party email SaaS solutions
if you care about bounce tracking and management.*

If the [newsletter module](https://github.com/silverstripe-labs/silverstripe-newsletter)
is installed, the bounce tracking can also identify the `Member`
record and newsletter which this email related to, and track the data more specifically.

## Installation

First of all, define a unique `EMAIL_BOUNCEHANDLER_KEY` constant,
in order to secure the tracking against unverified use.
Change the value in `_config.php`, or define your own one earlier on.

You need to let your mailserver know where to forward bounced emails to.
In the *Exim* mailserver, this is called the "[pipe transport](http://www.exim.org/exim-html-3.20/doc/html/spec_18.html)".
The configuration setting will look roughly like the following example:

	| php -q /your/path/framework/cli-script.php /Email_BounceHandler

Please ensure that the `From:` or `Reply-To:` address in the emails you
send matches the one address being configured in the mailserver.

## Usage

You can send an email through SilverStripe as usual, no special flags are needed.

	:::php
	$email = new Email();
	$email
		->setTo('test@test.com')
		->setFrom('mailer@mydomain.com')
		->setSubject('Test Email')
		->send();

Bounces will be recorded as new `Email_BounceRecord` database entries,
as well as tracked in the `Member->Bounced` property.

Alternatively, you can define a `BOUNCE_EMAIL` constant to set up
a global bounce address for all emails sent through SilverStripe's `Email` class.

## Related

 * [Jeremy's bouncehandler module](https://github.com/burnbright/silverstripe-bouncehandler)