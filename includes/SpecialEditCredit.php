<?php
namespace MediaWiki\Extension\EditCredit;

use ConfigFactory;
use Html;
use HTMLForm;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\User\ActorNormalization;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserIdentityLookup;
use SpecialPage;
use Wikimedia\Rdbms\ILoadBalancer;

class SpecialEditCredit extends SpecialPage {
	private UserIdentityLookup $userIdentityLookup;
	private EditCreditQuery $editCreditQuery;

	public function __construct(
		ActorNormalization $actorNormalization,
		ILoadBalancer $dbLoadBalancer,
		UserIdentityLookup $userIdentityLookup,
		ConfigFactory $configFactory,
		HookContainer $hookContainer
	) {
		parent::__construct( 'EditCredit' );
		$this->editCreditQuery = new EditCreditQuery(
			$actorNormalization,
			$dbLoadBalancer,
			$configFactory,
			$hookContainer
		);
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
