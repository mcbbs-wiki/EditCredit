<?php
namespace MediaWiki\Extension\EditCredit;

use Html;
use Linker;
use MediaWiki\Languages\LanguageConverterFactory;
use QueryPage;
use Title;

class SpecialMostCreditUser extends QueryPage {
	private LanguageConverterFactory $lcf;

	public function __construct( LanguageConverterFactory $lcf ) {
		parent::__construct( 'MostCreditUser' );
		$this->lcf = $lcf;
	}

	public function isExpensive() {
		return false;
	}

	public function isSyndicated() {
		return false;
	}

	public function getQueryInfo() {
		return [
			'tables' => [
				'e' => 'user_editcredit',
				'u' => 'user'
			],
			'fields' => [
				'username' => 'u.user_name',
				'value' => 'e.ue_credit'
			],
			'join_conds' => [
				'e' => [
					'INNER JOIN',
					'e.ue_id = u.user_id'
				]
			]
		];
	}

	public function formatResult( $skin, $result ) {
		$lc = $this->lcf->getLanguageConverter( $this->getContentLanguage() );
		$title = Title::makeTitleSafe( NS_USER, $result->username );
		if ( !$title ) {
			return Html::element(
				'span',
				[ 'class' => 'mw-invalidtitle' ],
				Linker::getInvalidTitleDescription(
					$this->getContext(),
					NS_USER,
					$result->username )
			);
		}
		$link = $this->getLinkRenderer()->makeLink(
			$title,
			$lc->convert( $title->getPrefixedText() )
		);
		return $this->getLanguage()->specialList(
			$link,
			$this->msg( 'editcredit-mostcredit-suffix' )
				->numParams( $result->value )
				->text()
			);
	}

	protected function getGroupName() {
		return 'users';
	}
}
