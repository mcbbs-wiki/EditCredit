<?php

namespace MediaWiki\Extension\EditCredit;

use Config;
use ConfigFactory;
use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\User\UserIdentity;

class EditCreditCalc {
	private EditCountQuery $editCountQuery;
	private Config $config;
	private HookContainer $hooks;

	public function __construct(
		EditCountQuery $editCountQuery,
		ConfigFactory $configFactory,
		HookContainer $hookContainer
 ) {
		$this->editCountQuery = $editCountQuery;
		$this->config = $configFactory->makeConfig( 'EditCredit' );
		$this->hooks = $hookContainer;
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
