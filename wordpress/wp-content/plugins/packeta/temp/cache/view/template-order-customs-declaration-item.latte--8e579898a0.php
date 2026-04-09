<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/order/customs-declaration-item.latte */
final class Template8e579898a0 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo "\n";
		$this->global->formsStack[] = $formContainer = is_object($container[$id]) ? $container[$id] : end($this->global->formsStack)[$container[$id]] /* line 5 */;
		echo '	<div data-replication-item>
		<div class="packetery-flex-container packetery-flex-container-no-margin">
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<label';
		$ʟ_input = $_input = end($this->global->formsStack)["customs_code"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 10 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 10 */;
		echo '</label>
					<input data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['customs_code']->getHtmlId() . '_message') /* line 11 */;
		echo '" class="js-packetery-customs-code"';
		$ʟ_input = $_input = end($this->global->formsStack)["customs_code"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null, 'class' => null])->attributes() /* line 11 */;
		echo '>
					<p class="packetery-input-validation-message help-block text-danger"
						id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['customs_code']->getHtmlId() . '_message') /* line 13 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["customs_code"]->getError()) /* line 13 */;
		echo '</p>
				</div>
			</div>
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<label';
		$ʟ_input = $_input = end($this->global->formsStack)["value"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 18 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 18 */;
		echo '</label>
					<input data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['value']->getHtmlId() . '_message') /* line 19 */;
		echo '" class="js-packetery-value"';
		$ʟ_input = $_input = end($this->global->formsStack)["value"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null, 'class' => null])->attributes() /* line 19 */;
		echo '>
					<p class="packetery-input-validation-message help-block text-danger"
						id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['value']->getHtmlId() . '_message') /* line 21 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["value"]->getError()) /* line 21 */;
		echo '</p>
				</div>
			</div>
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<label';
		$ʟ_input = $_input = end($this->global->formsStack)["product_name_en"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 26 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 26 */;
		echo '</label>
					<input data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['product_name_en']->getHtmlId() . '_message') /* line 27 */;
		echo '" class="js-packetery-product-name-en"';
		$ʟ_input = $_input = end($this->global->formsStack)["product_name_en"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null, 'class' => null])->attributes() /* line 27 */;
		echo '>
					<p class="packetery-input-validation-message help-block text-danger"
						id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['product_name_en']->getHtmlId() . '_message') /* line 29 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["product_name_en"]->getError()) /* line 29 */;
		echo '</p>
				</div>
			</div>
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<label';
		$ʟ_input = $_input = end($this->global->formsStack)["product_name"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 34 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 34 */;
		echo '</label>
					<input data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['product_name']->getHtmlId() . '_message') /* line 35 */;
		echo '" class="js-packetery-product-name"';
		$ʟ_input = $_input = end($this->global->formsStack)["product_name"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null, 'class' => null])->attributes() /* line 35 */;
		echo '>
					<p class="packetery-input-validation-message help-block text-danger"
						id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['product_name']->getHtmlId() . '_message') /* line 37 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["product_name"]->getError()) /* line 37 */;
		echo '</p>
				</div>
			</div>
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<label';
		$ʟ_input = $_input = end($this->global->formsStack)["units_count"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 42 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 42 */;
		echo '</label>
					<input data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['units_count']->getHtmlId() . '_message') /* line 43 */;
		echo '" class="js-packetery-units-count"';
		$ʟ_input = $_input = end($this->global->formsStack)["units_count"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null, 'class' => null])->attributes() /* line 43 */;
		echo '>
					<p class="packetery-input-validation-message help-block text-danger"
						id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['units_count']->getHtmlId() . '_message') /* line 45 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["units_count"]->getError()) /* line 45 */;
		echo '</p>
				</div>
			</div>
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<label';
		$ʟ_input = $_input = end($this->global->formsStack)["country_of_origin"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 50 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 50 */;
		echo '</label>
					<input data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['country_of_origin']->getHtmlId() . '_message') /* line 51 */;
		echo '" class="js-packetery-country-of-origin"';
		$ʟ_input = $_input = end($this->global->formsStack)["country_of_origin"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null, 'class' => null])->attributes() /* line 51 */;
		echo '>
					<p class="packetery-input-validation-message help-block text-danger"
						id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['country_of_origin']->getHtmlId() . '_message') /* line 53 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["country_of_origin"]->getError()) /* line 53 */;
		echo '</p>
				</div>
			</div>
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<label';
		$ʟ_input = $_input = end($this->global->formsStack)["weight"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 58 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 58 */;
		echo '</label>
					<input data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['weight']->getHtmlId() . '_message') /* line 59 */;
		echo '" class="js-packetery-weight"';
		$ʟ_input = $_input = end($this->global->formsStack)["weight"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null, 'class' => null])->attributes() /* line 59 */;
		echo '>
					<p class="packetery-input-validation-message help-block text-danger"
						id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['weight']->getHtmlId() . '_message') /* line 61 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["weight"]->getError()) /* line 61 */;
		echo '</p>
				</div>
			</div>
			<div class="packetery-flex-break-row"></div>
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<label';
		$ʟ_input = $_input = end($this->global->formsStack)["is_food_or_book"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 67 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 67 */;
		echo '</label>
					<input data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['is_food_or_book']->getHtmlId() . '_message') /* line 68 */;
		echo '" class="js-packetery-is-food-or-book"';
		$ʟ_input = $_input = end($this->global->formsStack)["is_food_or_book"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null, 'class' => null])->attributes() /* line 68 */;
		echo '>
					<p class="packetery-input-validation-message help-block text-danger"
						id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['is_food_or_book']->getHtmlId() . '_message') /* line 70 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["is_food_or_book"]->getError()) /* line 70 */;
		echo '</p>
				</div>
			</div>
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<label';
		$ʟ_input = $_input = end($this->global->formsStack)["is_voc"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 75 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 75 */;
		echo '</label>
					<input data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['is_voc']->getHtmlId() . '_message') /* line 76 */;
		echo '" class="js-packetery-is-voc"';
		$ʟ_input = $_input = end($this->global->formsStack)["is_voc"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null, 'class' => null])->attributes() /* line 76 */;
		echo '>
					<p class="packetery-input-validation-message help-block text-danger"
						id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['is_voc']->getHtmlId() . '_message') /* line 78 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["is_voc"]->getError()) /* line 78 */;
		echo '</p>
				</div>
			</div>
		</div>
		<div class="packetery-flex-container">
			<div class="packetery-customs-declaration-flex-item">
				<div class="packetery-customs-declaration-flex-item-content">
					<button type="button" data-replication-delete class="button button-small">';
		echo LR\Filters::escapeHtmlText($translations['delete']) /* line 85 */;
		echo '</button>
				</div>
			</div>
		</div>
	</div>
';
		array_pop($this->global->formsStack);
		$formContainer = end($this->global->formsStack);
		return get_defined_vars();
	}

}
