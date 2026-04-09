<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/product_category/form-fields.latte */
final class Templatee96be1b5a2 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		$form = $this->global->formsStack[] = is_object($form) ? $form : $this->global->uiControl[$form] /* line 1 */;
		echo '	<tr class="form-field">
		<th scope="row"><p><strong>';
		echo LR\Filters::escapeHtmlText($translations['disallowedShippingRatesHeading']) /* line 3 */;
		echo '</strong></p></th>
		<td>
			<table>
';
		$iterations = 0;
		foreach ($form[Packetery\Module\ProductCategory\Entity::META_DISALLOWED_SHIPPING_RATES]->getControls() as $control) /* line 6 */ {
			echo '					<tr class="packetery-product-category-carrier-item">
						<td><input';
			$ʟ_input = $_input = is_object($control) ? $control : end($this->global->formsStack)[$control];
			echo $ʟ_input->getControlPart()->attributes() /* line 8 */;
			echo '></td>
						<td><label';
			$ʟ_input = $_input = is_object($control) ? $control : end($this->global->formsStack)[$control];
			echo $ʟ_input->getLabelPart()->attributes() /* line 9 */;
			echo '>';
			echo $ʟ_input->getLabelPart()->getHtml() /* line 9 */;
			echo '</label></td>
					</tr>
';
			$iterations++;
		}
		echo '			</table>
		</td>
	</tr>
';
		array_pop($this->global->formsStack);
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['control' => '6'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
