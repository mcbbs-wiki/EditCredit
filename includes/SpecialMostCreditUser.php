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
