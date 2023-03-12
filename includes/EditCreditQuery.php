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

use Config;
use ConfigFactory;
use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\HookContainer\HookContainer;
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
		EditCountQuery $editCountQuery,
		ILoadBalancer $dbLoadBalancer,
		ConfigFactory $configFactory,
		HookContainer $hookContainer
 ) {
		$this->editCountQuery = $editCountQuery;
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
		if ( $dbCredit === false ) {
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
		return $dbCredit === true;
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
