<?php
namespace MediaWiki\Extension\EditCredit;

use DeferrableUpdate;
use MediaWiki\User\UserIdentity;

class UpdateCredit implements DeferrableUpdate {
	private $user;
	private $editCreditQuery;

	public function __construct( UserIdentity $user, EditCreditQuery $editCreditQuery ) {
		$this->user = $user;
		$this->editCreditQuery = $editCreditQuery;
	}

	public function doUpdate() {
		$credit = $this->editCreditQuery->calcEditcredit( $this->user );
		$this->editCreditQuery->setUserCredit( $this->user, $credit );
	}
}
