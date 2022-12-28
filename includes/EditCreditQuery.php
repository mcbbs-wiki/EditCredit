<?php

namespace MediaWiki\Extension\EditCredit;

use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserIdentity;

class EditCreditQuery {
	private $editCountQuery;
	private $config;

	public function __construct( EditCountQuery $editCountQuery ) {
		$this->editCountQuery = $editCountQuery;
		$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'EditCredit' );
	}

	public function queryCredit( UserIdentity $user ) {
		$credit = 0;
		$ns = $this->editCountQuery->queryAllNamespaces( $user );
		$hooks = MediaWikiServices::getInstance()->getHookContainer();
		@$hooks->run( "CalcUserCredit", [ $ns, &$credit ] );
		return $credit;
	}

	public function queryLevelCSSClass( int $credit ) {
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

	public function queryLevel( int $credit ) {
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
