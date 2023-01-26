<?php

namespace MediaWiki\Extension\EditCredit\Maintenance;

use Maintenance;
use MediaWiki\Extension\EditCredit\EditCreditQuery;
use MediaWiki\MediaWikiServices;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

class InitEditCredit extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'EditCredit' );
	}

	public function execute() {
		$mws = MediaWikiServices::getInstance();
		$dblb = $mws->getDBLoadBalancer();
		$dbr = $dblb->getConnection( DB_REPLICA );
		$dbw = $dblb->getConnection( DB_PRIMARY );
		$uil = $mws->getUserIdentityLookup();
		/** @var EditCreditQuery $editCreditQuery */
		$editCreditQuery = $mws->getService( 'EditCredit.EditCreditQuery' );
		$userCreditIds = $dbr->newSelectQueryBuilder()
				->select( 'ue_id' )
				->from( 'user_editcredit' )
				->caller( __METHOD__ )
				->fetchFieldValues();
		$userIds = $dbr->newSelectQueryBuilder()
				->select( 'user_id' )
				->from( 'user' )
				->caller( __METHOD__ )
				->fetchFieldValues();
		foreach ( $userIds as $userId ) {
			$this->output( "Processing user $userId ... " );
			$user = $uil->getUserIdentityByUserId( $userId );
			$credit = $editCreditQuery->calcEditcredit( $user );
			$this->output( "$credit " );
			$editCreditQuery->setUserCredit( $user, $credit );
			$this->output( "Success.\n" );
		}
	}
}

$maintClass = InitEditCredit::class;
require_once RUN_MAINTENANCE_IF_MAIN;
