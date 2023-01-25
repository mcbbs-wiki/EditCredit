<?php
namespace MediaWiki\Extension\EditCredit;

use Html;
use HTMLForm;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserIdentityLookup;
use SpecialPage;

class SpecialEditCredit extends SpecialPage {
	private UserIdentityLookup $userIdentityLookup;
	private EditCreditQuery $editCreditQuery;

	public function __construct(
		UserIdentityLookup $userIdentityLookup,
		EditCreditQuery $editCreditQuery
	) {
		parent::__construct( 'EditCredit' );
		$this->editCreditQuery = $editCreditQuery;
		$this->userIdentityLookup = $userIdentityLookup;
	}

	private function outputPage( UserIdentity $user = null ) {
		$formDescriptor = [
			'uid' => [
				'type' => 'user',
				'name' => 'wpUsername',
				'exists' => true,
				'label-message' => 'editcredit-form-username',
				'required' => true,
				'default' => $user ? $user->getName() : ''
			]
		];
		$form = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$form
			->setMethod( 'get' )
			->setWrapperLegendMsg( 'editcredit-form-legend' )
			->prepareForm()
			->displayForm( false );
	}

	protected function getGroupName() {
		return 'users';
	}

	public function execute( $par ) {
		$output = $this->getOutput();
		$request = $this->getRequest();
		$output->enableOOUI();
		$this->setHeaders();
		$username = $par ?? $request->getText( 'wpUsername' );
		if ( !$username ) {
			$this->outputPage();
			return;
		}

		$user = $this->userIdentityLookup
			->getUserIdentityByName( $username );
		if ( !$user || $user->getId() === 0 ) {
			$this->outputPage();
			$output->addHTML( '<br>' . Html::element(
				'strong',
				[ 'class' => 'error' ],
				$this->msg( 'editcredit-error-userdoesnotexist' )->params( $username )->text()
			) );
			return;
		}
		$this->outputPage( $user );
		$credit = $this->editCreditQuery->queryCredit( $user );
		$level = $this->editCreditQuery->calcLevel( $credit );
		$cssClass = $this->editCreditQuery->calcLevelCSSClass( $credit );
		$html = '';
		$html .= Html::element( 'h2', [], $this->msg( 'editcredit-user-heading' )->params( $user->getName() )->text() );
		$html .= Html::element( 'p', [], $this->msg( 'editcredit-user-credit' )->params( $credit )->text() );
		$html .= Html::element( 'p', [], $this->msg( 'editcredit-user-level' )->params( $level )->text() );
		$html .= Html::element( 'p', [], $this->msg( 'editcredit-user-class' )->params( $cssClass )->text() );
		$output->addHTML( $html );
	}
}
