<?php

namespace MediaWiki\Extension\EditCredit;

use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserIdentity;

class EditCreditQuery {
	private $editCountQuery;

	public function __construct( EditCountQuery $editCountQuery ) {
		$this->editCountQuery = $editCountQuery;
	}

	public function queryCredit( UserIdentity $user ) {
		$credit = 0;
		$ns = $this->editCountQuery->queryAllNamespaces( $user );
		$hooks = MediaWikiServices::getInstance()->getHookContainer();
		@$hooks->run( "CalcUserCredit", [ $ns, &$credit ] );
		return $credit;
	}

	public function queryLevel( int $credit ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'EditCredit' );
		$scores = $config->get( 'CreditLevels' );
		$levelCSS = $config->get( 'CreditCSSClass' );
		$lvl = 0;
		foreach ( $scores as $index => $score ) {
			if ( $credit < $score ) {
				$lvl = $index;
				break;
			}
		}
		$class = '';
		foreach ( $levelCSS as $least => $level ) {
			if ( $credit < $least ) {
				$class = $level;
				break;
			}
		}
		return [ 'level' => $lvl, 'cssClass' => $class ];
	}
}
