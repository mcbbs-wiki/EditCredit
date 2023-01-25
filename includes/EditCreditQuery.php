<?php

namespace MediaWiki\Extension\EditCredit;

use Config;
use ConfigFactory;
use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\User\ActorNormalization;
use MediaWiki\User\UserIdentity;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\ILoadBalancer;

class EditCreditQuery {
	private EditCountQuery $editCountQuery;
	private IDatabase $dbr;
	private IDatabase $dbw;
	private Config $config;
	private HookContainer $hooks;

	public function __construct(
		ActorNormalization $actorNormalization,
		ILoadBalancer $dbLoadBalancer,
		ConfigFactory $configFactory,
		HookContainer $hookContainer
 ) {
		$this->editCountQuery = new EditCountQuery( $actorNormalization, $dbLoadBalancer );
		$this->dbr = $dbLoadBalancer->getConnection( DB_REPLICA );
		$this->dbw = $dbLoadBalancer->getConnection( DB_PRIMARY );
		$this->config = $configFactory->makeConfig( 'EditCredit' );
		$this->hooks = $hookContainer;
	}

	public function queryCredit( UserIdentity $user ): int {
		$userId = $user->getId();
		$dbCredit = $this->dbr->newSelectQueryBuilder()
			->select( 'ue_credit' )
			->from( 'user_editcredit' )
			->where( "ue_id = $userId" )
			->fetchField();
		if ( !$dbCredit ) {
			$calcCredit = $this->calcEditcredit( $user );
			$this->insertUserCredit( $user, $calcCredit );
			return $calcCredit;
		} else {
			return $dbCredit;
		}
	}

	private function isUserCreditExists( UserIdentity $user ): bool {
		$userId = $user->getId();
		$dbCredit = $this->dbr->newSelectQueryBuilder()
			->select( 'ue_credit' )
			->from( 'user_editcredit' )
			->where( "ue_id = $userId" )
			->fetchField();
		return $dbCredit ? true : false;
	}

	public function setUserCredit( UserIdentity $user, int $credit ) {
		if ( $this->isUserCreditExists( $user ) ) {
			$this->updateUserCredit( $user, $credit );
		} else {
			$this->insertUserCredit( $user, $credit );
		}
	}

	private function updateUserCredit( UserIdentity $user, int $credit ) {
		$userId = $user->getId();
		$this->dbw->update( 'user_editcredit', [ 'ue_credit' => $credit ], "ue_id = $userId" );
	}

	private function insertUserCredit( UserIdentity $user, int $credit ) {
		$this->dbw->insert( 'user_editcredit', [ 'ue_id' => $user->getId(),'ue_credit' => $credit ] );
	}

	public function calcEditcredit( UserIdentity $user ) {
		$credit = 0;
		$ns = $this->editCountQuery->queryAllNamespaces( $user );
		@$this->hooks->run( "CalcUserCredit", [ $ns, &$credit ] );
		return $credit;
	}

	public function calcLevelCSSClass( int $credit ) {
		$levelCSS = $this->config->get( 'CreditCSSClass' );
		$class = '';
		foreach ( $levelCSS as $least => $level ) {
			if ( $credit < $least ) {
				$class = $level;
				break;
			}
		}
		return $class;
	}

	public function calcLevel( int $credit ) {
		$scores = $this->config->get( 'CreditLevels' );
		$lvl = 0;
		foreach ( $scores as $index => $score ) {
			if ( $credit < $score ) {
				$lvl = $index;
				break;
			}
		}
		return $lvl;
	}
}
