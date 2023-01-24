<?php

namespace MediaWiki\Extension\EditCredit;

use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\ActorNormalization;
use ConfigFactory;
use Config;
use MediaWiki\HookContainer\HookContainer;
use Wikimedia\Rdbms\ILoadBalancer;

class EditCreditQuery {
	private EditCountQuery $editCountQuery;
	private ILoadBalancer $dbLoadBalancer;
	private Config $config;
	private HookContainer $hooks;

	public function __construct( ActorNormalization $actorNormalization,
	ILoadBalancer $dbLoadBalancer,ConfigFactory $configFactory,HookContainer $hookContainer ) {
		$this->dbLoadBalancer = $dbLoadBalancer;
		$this->editCountQuery = new EditCountQuery($actorNormalization,$dbLoadBalancer);
		$this->config = $configFactory->makeConfig( 'EditCredit' );
		$this->hooks = $hookContainer;
	}

	public function queryCredit(UserIdentity $user)
	{
		return $this->queryFromUserEdit($user);
	}
	private function queryFromCache(UserIdentity $user)
	{
		
	}
	private function queryFromTable(UserIdentity $user)
	{
		
	}
	private function queryFromUserEdit( UserIdentity $user ) {
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
