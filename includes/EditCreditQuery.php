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

class EditCreditQuery extends EditCreditCalc{
	private ILoadBalancer $dbLoadBalancer;
	private Config $config;
	private HookContainer $hooks;

	public function __construct( ActorNormalization $actorNormalization,
	ILoadBalancer $dbLoadBalancer,ConfigFactory $configFactory,HookContainer $hookContainer ) {
		parent::__construct(new EditCountQuery($actorNormalization,$dbLoadBalancer),$configFactory,$hookContainer);
		$this->dbLoadBalancer = $dbLoadBalancer;
		$this->config = $configFactory->makeConfig( 'EditCredit' );
		$this->hooks = $hookContainer;
	}

	public function queryCredit(UserIdentity $user) : int
	{
		return $this->calcEditCredit($user);
	}
	private function queryFromCache(UserIdentity $user)
	{
		
	}
	private function queryFromTable(UserIdentity $user)
	{
		
	}
}
