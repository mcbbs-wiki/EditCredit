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

use ApiBase;
use ApiQuery;
use ApiQueryBase;
use MediaWiki\ParamValidator\TypeDef\UserDef;
use MediaWiki\User\UserIdentityLookup;
use MediaWiki\User\UserNameUtils;
use Wikimedia\ParamValidator\ParamValidator;

class ApiQueryEditCredit extends ApiQueryBase {
	private EditCreditQuery $editCreditQuery;
	private UserIdentityLookup $userIdentityLookup;
	private UserNameUtils $userNameUtils;

	public function __construct(
		ApiQuery $query,
		$moduleName,
		UserIdentityLookup $userIdentityLookup,
		UserNameUtils $userNameUtils,
		EditCreditQuery $editCreditQuery
	) {
		parent::__construct( $query, $moduleName, 'ecr' );
		$this->userIdentityLookup = $userIdentityLookup;
		$this->userNameUtils = $userNameUtils;
		$this->editCreditQuery = $editCreditQuery;
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$userIter = $this->userIdentityLookup
			->newSelectQueryBuilder()
			->caller( __METHOD__ )
			->whereUserNames( $params['user'] )
			->orderByName()
			->fetchUserIdentities();
		$result = $this->getResult();
		$result->addIndexedTagName( [ 'query', $this->getModuleName() ], '' );
		foreach ( $userIter as $user ) {
			$vals = [
				'user' => $user->getName(),
				'userid' => $user->getId(), ];
			if ( in_array( 'credit', $params['type'] ) ) {
				$vals['credit'] = $this->editCreditQuery->queryCredit( $user );
			}
			if ( in_array( 'level', $params['type'] ) ) {
				$vals['level'] = $this->editCreditQuery->calcLevel( $this->editCreditQuery->queryCredit( $user ) );
			}
			if ( in_array( 'cssClass', $params['type'] ) ) {
				$vals['cssClass'] = $this->editCreditQuery->calcLevelCSSClass(
					$this->editCreditQuery->queryCredit( $user )
				);
			}
			$result->addValue( [ 'query', $this->getModuleName() ], null, $vals );
		}
	}

	protected function getAllowedParams() {
		return [
			'user' => [
				ParamValidator::PARAM_TYPE => 'user',
				UserDef::PARAM_ALLOWED_USER_TYPES => [ 'name', 'id' ],
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_REQUIRED => true
			],
			'type' => [
				ParamValidator::PARAM_DEFAULT => 'credit|level',
				ParamValidator::PARAM_TYPE => [ 'credit', 'level', 'cssClass' ],
				ParamValidator::PARAM_ISMULTI => true,
				ApiBase::PARAM_HELP_MSG_PER_VALUE => [],
			]
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=query&list=editcredit&ecruser=Example'
				=> 'apihelp-query+editcredit-example-user',
				'action=query&list=editcredit&ecruser=Example&ecrtype=level'
				=> 'apihelp-query+editcredit-example-user-level'
		];
	}
}
