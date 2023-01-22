<?php

namespace MediaWiki\Extension\EditCredit;

use Html;
use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\User\ActorNormalization;
use MediaWiki\User\UserIdentityLookup;
use Parser;
use PPFrame;
use WikiMedia\Rdbms\ILoadBalancer;

class Hooks implements ParserFirstCallInitHook {
	private $userIdentityLookup;
	private $editCreditQuery;

	public function __construct(
		ActorNormalization $actorNormalization,
		ILoadBalancer $dbLoadBalancer,
		UserIdentityLookup $userIdentityLookup
	) {
		$this->userIdentityLookup = $userIdentityLookup;
		$this->editCreditQuery = new EditCreditQuery( new EditCountQuery(
			$actorNormalization,
			$dbLoadBalancer
		) );
	}

	public function onParserFirstCallInit( $parser ) {
		$parser->setHook( 'edit-credit', [ $this,'renderTagEditCredit' ] );
	}

	public function renderTagEditCredit( $input, array $args, Parser $parser, PPFrame $frame ) {
		if ( isset( $args['username'] ) ) {
			$username = trim( $args['username'] );
			$user = $this->userIdentityLookup->getUserIdentityByName( $username );
			if ( !$user || $user->getId() === 0 ) {
				return '';
			}
			$credit = $this->editCreditQuery->queryCredit( $user );
			$level = $this->editCreditQuery->queryLevel( $credit );
			$cssClass = $this->editCreditQuery->queryLevelCSSClass( $credit );
			$type = $args['type'] ?? 'level';
			if ( $type === 'level' ) {
				$class = 'user-score-level ' . $cssClass;
				$display = $level;
			} elseif ( $type === 'credit' ) {
				$class = 'user-score-credit ' . $cssClass;
				$display = $credit;
			}
			return Html::element( 'span', [ 'class' => $class ], $display );
		} else {
			return '';
		}
	}
}
