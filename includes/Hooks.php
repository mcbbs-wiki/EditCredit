<?php

namespace MediaWiki\Extension\EditCredit;

use ConfigFactory;
use Html;
use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\User\ActorNormalization;
use MediaWiki\User\UserIdentityLookup;
use DatabaseUpdater;
use Parser;
use Exception;
use MediaWiki\HookContainer\HookContainer;
use PPFrame;
use WikiMedia\Rdbms\ILoadBalancer;

class Hooks implements ParserFirstCallInitHook {
	private UserIdentityLookup $userIdentityLookup;
	private EditCreditQuery $editCreditQuery;
	public static function onLoadExtensionSchemaUpdates(DatabaseUpdater $updater) {
		$dir = __DIR__ . '/../sql';
		$dbType = $updater->getDB()->getType();
		if (!in_array($dbType, array('mysql'))) {
				throw new Exception('Database type not currently supported');
		}
		$updater->addExtensionTable('user_editcredit', "{$dir}/tables-generated.sql");
		return true;
	}
	public function __construct(
		ActorNormalization $actorNormalization,
		ILoadBalancer $dbLoadBalancer,
		UserIdentityLookup $userIdentityLookup,
		ConfigFactory $configFactory,
		HookContainer $hookContainer
	) {
		$this->userIdentityLookup = $userIdentityLookup;
		$this->editCreditQuery = new EditCreditQuery( 
			$actorNormalization,
			$dbLoadBalancer,
			$configFactory,
			$hookContainer
		 );
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
			$level = $this->editCreditQuery->calcLevel( $credit );
			$cssClass = $this->editCreditQuery->calcLevelCSSClass( $credit );
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
