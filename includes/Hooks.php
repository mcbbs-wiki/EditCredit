<?php

namespace MediaWiki\Extension\EditCredit;

use DatabaseUpdater;
use DeferredUpdates;
use Exception;
use Html;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\User\UserIdentityLookup;
use Parser;
use PPFrame;

class Hooks implements ParserFirstCallInitHook, PageSaveCompleteHook {
	private UserIdentityLookup $userIdentityLookup;
	private EditCreditQuery $editCreditQuery;

	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$dir = __DIR__ . '/../sql';
		$dbType = $updater->getDB()->getType();
		if ( !in_array( $dbType, [ 'mysql' ] ) ) {
				throw new Exception( 'Database type not currently supported' );
		}
		$updater->addExtensionTable( 'user_editcredit', "{$dir}/tables-generated.sql" );
		return true;
	}

	public function __construct(
		UserIdentityLookup $userIdentityLookup,
		EditCreditQuery $editCreditQuery
	) {
		$this->userIdentityLookup = $userIdentityLookup;
		$this->editCreditQuery = $editCreditQuery;
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

	public function onPageSaveComplete(
		$wikiPage,
		$user,
		$summary,
		$flags,
		$revisionRecord,
		$editResult
	) {
		$update = new UpdateCredit( $user, $this->editCreditQuery );
		DeferredUpdates::addUpdate( $update );
		// $update->doUpdate();
	}
}
