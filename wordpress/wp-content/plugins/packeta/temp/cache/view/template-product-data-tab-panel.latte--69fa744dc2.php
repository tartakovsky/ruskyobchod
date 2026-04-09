<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/product/data-tab-panel.latte */
final class Template69fa744dc2 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		$form = $this->global->formsStack[] = is_object($form) ? $form : $this->global->uiControl[$form] /* line 1 */;
		echo '	<div id="';
		echo LR\Filters::escapeHtmlAttr(Packetery\Module\Product\DataTab::NAME) /* line 2 */;
		echo '" class="panel woocommerce_options_panel">
		<p class="form-field">
			<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery_age_verification_18_plus"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 4 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 4 */;
		echo '</label>
			<input';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery_age_verification_18_plus"];
		echo $ʟ_input->getControlPart()->attributes() /* line 5 */;
		echo '>
		</p>
		<hr>
		<p><strong>';
		echo LR\Filters::escapeHtmlText($translations['disallowedShippingRatesHeading']) /* line 8 */;
		echo '</strong></p>
		<div class="form-field">
			<table>
';
		$iterations = 0;
		foreach ($form[Packetery\Module\Product\Entity::META_DISALLOWED_SHIPPING_RATES]->getControls() as $control) /* line 11 */ {
			echo '					<tr class="packetery-shipping-rate">
						<td><input';
			$ʟ_input = $_input = is_object($control) ? $control : end($this->global->formsStack)[$control];
			echo $ʟ_input->getControlPart()->attributes() /* line 13 */;
			echo '></td>
						<td><label';
			$ʟ_input = $_input = is_object($control) ? $control : end($this->global->formsStack)[$control];
			echo $ʟ_input->getLabelPart()->attributes() /* line 14 */;
			echo '>';
			echo $ʟ_input->getLabelPart()->getHtml() /* line 14 */;
			echo '</label></td>
					</tr>
';
			$iterations++;
		}
		echo '			</table>
		</div>
	</div>
';
		array_pop($this->global->formsStack);
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['control' => '11'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
