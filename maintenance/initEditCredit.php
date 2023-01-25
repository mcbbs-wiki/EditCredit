<?php

namespace MediaWiki\Extension\EditCredit\Maintenance;

use Maintenance;
use MediaWiki\MediaWikiServices;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Brief oneline description of Hello world.
 */
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
		$editCreditCalc = $mws->getService( 'EditCredit.Query' );
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
			$credit = $editCreditCalc->calcEditcredit( $uil->getUserIdentityByUserId( $userId ) );
			$this->output( "$credit ... " );
			if ( in_array( $userId, $userCreditIds ) ) {
				$dbw->update( 'user_editcredit', [ 'ue_credit' => $credit ], "ue_id = $userId" );
				$this->output( "Update Success.\n" );
			} else {
				$dbw->insert( 'user_editcredit', [ 'ue_id' => $userId,'ue_credit' => $credit ] );
				$this->output( "Create Success.\n" );
			}
		}
	}
}

$maintClass = InitEditCredit::class;
require_once RUN_MAINTENANCE_IF_MAIN;
