<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\Extension\EditCredit;

use DatabaseUpdater;
use DeferredUpdates;
use Exception;
use Html;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\User\UserIdentityLookup;
use Parser;
use PPFrame;
use SpecialPage;

class Hooks implements
	ParserFirstCallInitHook,
	PageSaveCompleteHook,
	GetPreferencesHook
{
	private UserIdentityLookup $userIdentityLookup;
	private EditCreditQuery $editCreditQuery;
	private LinkRenderer $linkRenderer;

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
		EditCreditQuery $editCreditQuery,
		LinkRenderer $linkRenderer
	) {
		$this->userIdentityLookup = $userIdentityLookup;
		$this->editCreditQuery = $editCreditQuery;
		$this->linkRenderer = $linkRenderer;
	}

	public function onParserFirstCallInit( $parser ) {
		$parser->setHook( 'edit-credit', [ $this,'renderTagEditCredit' ] );
		$parser->setFunctionHook( 'editcredit', [ $this,'renderEditCredit' ] );
	}
	public function renderEditCredit( Parser $parser, $username,$type='credit' ) {
		$username = $username ?? '';
		$user = $this->userIdentityLookup->getUserIdentityByName( $username );
		if ( !$user || $user->getId() === 0 ) {
			return '0';
		}
		$credit = $this->editCreditQuery->queryCredit( $user );
		switch ($type) {
			case 'css':
				return $this->editCreditQuery->calcLevelCSSClass( $credit );
			case 'level':
				return $this->editCreditQuery->calcLevel( $credit );
			default:
				return $credit;
		}
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

	public function onGetPreferences( $user, &$preferences ) {
		$link = $this->linkRenderer->makeKnownLink(
			SpecialPage::getTitleFor( 'EditCredit', $user->getName() ),
			$this->editCreditQuery->queryCredit( $user->getUser() )
		);
		$preferences['editcredit'] = [
			'type' => 'info',
			'raw' => true,
			'label-message' => 'prefs-editcredit',
			'default' => $link,
			'section' => 'personal/info',
		];
	}
}
