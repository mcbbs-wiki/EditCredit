<?php
namespace MediaWiki\Extension\EditCredit;

use QueryPage;
use Title;
use Html;
use Linker;

class SpecialMostCreditUser extends QueryPage
{   
    public function __construct() {
        parent::__construct( 'MostCreditUser' );
    }
    public function isExpensive() {
        return false;
    }
    public function isSyndicated() {
        return false;
    }
    public function getQueryInfo() {
        return [
            'tables'=>[
                'e'=>'user_editcredit',
                'u'=>'user'
            ],
            'fields' => [
                'username'=>'u.user_name',
                'value'=>'e.ue_credit'
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
			$this->getContentLanguage()->convert( $title->getPrefixedText() )
		);
        return $this->getLanguage()->specialList($link,$this->msg( 'editcredit-mostcredit-suffix' )->numParams($result->value)->text());
    }
    protected function getGroupName() {
		return 'users';
	}
}
