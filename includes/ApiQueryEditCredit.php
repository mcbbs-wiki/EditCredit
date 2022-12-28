<?php

namespace MediaWiki\Extension\EditCredit;

use ApiBase;
use ApiQuery;
use ApiQueryBase;
use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\ParamValidator\TypeDef\UserDef;
use MediaWiki\User\ActorNormalization;
use MediaWiki\User\UserIdentityLookup;
use MediaWiki\User\UserNameUtils;
use Wikimedia\ParamValidator\ParamValidator;
use WikiMedia\Rdbms\ILoadBalancer;

class ApiQueryEditCredit extends ApiQueryBase {
	private $editCreditQuery;

	private $userIdentityLookup;

	private $userNameUtils;

	public function __construct(
		ApiQuery $query,
		$moduleName,
		ActorNormalization $actorNormalization,
		ILoadBalancer $dbLoadBalancer,
		UserIdentityLookup $userIdentityLookup,
		UserNameUtils $userNameUtils
	) {
		parent::__construct( $query, $moduleName, 'ec' );
		$this->userIdentityLookup = $userIdentityLookup;
		$this->userNameUtils = $userNameUtils;
		$this->editCreditQuery = new EditCreditQuery( new EditCountQuery( $actorNormalization, $dbLoadBalancer ) );
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$names = [];
		foreach ( $params['user'] as $u ) {
			if ( $u === '' ) {
				$encParamName = $this->encodeParamName( 'user' );
				$this->dieWithError( [ 'apierror-paramempty', $encParamName ], "paramempty_$encParamName" );
			}
			$name = $this->userNameUtils->getCanonical( $u );
			if ( $name == false ) {
				$encParamName = $this->encodeParamName( 'user' );
				$this->dieWithError(
					[ 'apierror-baduser', $encParamName, wfEscapeWikiText( $u ) ], "baduser_$encParamName"
				);
			}
			$names[] = $name;
		}
		$userIter = $this->userIdentityLookup
			->newSelectQueryBuilder()
			->caller( __METHOD__ )
			->whereUserNames( $names )
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
				$vals['level'] = $this->editCreditQuery->queryLevel( $this->editCreditQuery->queryCredit( $user ) );
			}
			if ( in_array( 'cssClass', $params['type'] ) ) {
				$vals['cssClass'] = $this->editCreditQuery->queryLevelCSSClass( $this->editCreditQuery->queryCredit( $user ) );
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
			'action=query&list=editcredit&ecuser=Example'
				=> 'apihelp-query+editcredit-example-user',
				'action=query&list=editcredit&ecuser=Example&ectype=level'
				=> 'apihelp-query+editcredit-example-user-level'
		];
	}
}
