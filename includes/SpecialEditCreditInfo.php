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
use Html;
use SpecialPage;

class SpecialEditCreditInfo extends SpecialPage {
	private Config $config;

	public function __construct( ConfigFactory $configFactory ) {
		parent::__construct( 'EditCreditInfo' );
		$this->config = $configFactory->makeConfig( 'EditCredit' );
	}

	private function outputLevelTable() {
		$level = $this->config->get( 'CreditLevels' );
		$html = Html::openElement( 'table', [ 'class' => 'wikitable' ] );
		$html .= Html::openElement( 'thead' );
		$html .= Html::openElement( 'tr' );
		$html .= Html::element( 'th', [], $this->msg( 'editcredit-info-level' )->text() );
		$html .= Html::element( 'th', [], $this->msg( 'editcredit-info-max' )->text() );
		$html .= Html::closeElement( 'tr' );
		$html .= Html::closeElement( 'thead' );
		$html .= Html::openElement( 'tbody' );
		foreach ( $level as $least => $level ) {
			$html .= Html::openElement( 'tr' );
			$html .= Html::element( 'td', [], $least );
			$html .= Html::element( 'td', [], $level );
			$html .= Html::closeElement( 'tr' );
		}
		$html .= Html::closeElement( 'tbody' );
		$html .= Html::closeElement( 'table' );
		return $html;
	}

	private function outputClassTable() {
		$levelCSS = $this->config->get( 'CreditCSSClass' );
		$html = Html::openElement( 'table', [ 'class' => 'wikitable' ] );
		$html .= Html::openElement( 'thead' );
		$html .= Html::openElement( 'tr' );
		$html .= Html::element( 'th', [], $this->msg( 'editcredit-info-class' )->text() );
		$html .= Html::element( 'th', [], $this->msg( 'editcredit-info-max' )->text() );
		$html .= Html::closeElement( 'tr' );
		$html .= Html::closeElement( 'thead' );
		$html .= Html::openElement( 'tbody' );
		foreach ( $levelCSS as $least => $level ) {
			$html .= Html::openElement( 'tr' );
			$html .= Html::element( 'td', [], $level );
			$html .= Html::element( 'td', [], $least );
			$html .= Html::closeElement( 'tr' );
		}
		$html .= Html::closeElement( 'tbody' );
		$html .= Html::closeElement( 'table' );
		return $html;
	}

	private function outputPage() {
		$html = '';
		$html .= Html::element( 'p', [], $this->msg( 'editcredit-info-heading' )->text() );
		$html .= Html::element( 'h2', [], $this->msg( 'editcredit-info-level' )->text() );
		$html .= $this->outputLevelTable();
		$html .= Html::element( 'h2', [], $this->msg( 'editcredit-info-class' )->text() );
		$html .= $this->outputClassTable();
		return $html;
	}

	protected function getGroupName() {
		return 'wiki';
	}

	public function execute( $par ) {
		$output = $this->getOutput();
		$request = $this->getRequest();
		$this->setHeaders();

		$output->addHTML( $this->outputPage() );
	}
}
