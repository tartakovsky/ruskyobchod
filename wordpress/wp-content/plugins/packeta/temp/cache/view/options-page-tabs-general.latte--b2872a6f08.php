<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/options/page-tabs/general.latte */
final class Templateb2872a6f08 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		settings_errors() /* line 1 */;
		echo "\n";
		$form = $this->global->formsStack[] = is_object($form) ? $form : $this->global->uiControl[$form] /* line 3 */;
		echo '<form';
		echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false);
		echo '>
	';
		settings_fields('packetery') /* line 4 */;
		echo '

	<table class="form-table packetery_settings" role="presentation">
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-api_password"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 9 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 9 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-general-password"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-api_password"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 12 */;
		echo '>
				<div class="packetery-help-block">
					<p>
						';
		echo sprintf( $translations['apiPasswordCanBeFoundAt%sUrl'], $apiPasswordLink ) /* line 15 */;
		echo '
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-sender"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 22 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 22 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-general-sender"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-sender"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 25 */;
		echo '>
				<div class="packetery-help-block">
					<p>
						';
		echo $translations['senderDescription'] /* line 28 */;
		echo '
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-packeta_label_format"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 35 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 35 */;
		echo '</label>
			</th>
			<td>
				<select class="packetery-js-wizard-general-packeta-label-format"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-packeta_label_format"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 38 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 38 */;
		echo '</select>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-carrier_label_format"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 43 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 43 */;
		echo '</label>
			</th>
			<td>
				<select class="packetery-js-wizard-general-carrier-label-format"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-carrier_label_format"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 46 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 46 */;
		echo '</select>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-cod_payment_methods"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 51 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 51 */;
		echo '</label>
			</th>
			<td>
				<div class="custom-select2-wrapper packetery-js-wizard-general-cod">
					<select data-packetery-select2 ';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-cod_payment_methods"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-packetery-select2' => null])->attributes() /* line 55 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 55 */;
		echo '</select>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-packaging_weight"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 61 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 61 */;
		echo '</label>
			</th>
			<td>
				<input type="number" step="0.001" class="packetery-js-wizard-general-packaging-weight"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-packaging_weight"];
		echo $ʟ_input->getControlPart()->addAttributes(['type' => null, 'step' => null, 'class' => null])->attributes() /* line 64 */;
		echo '>
				<div class="packetery-help-block">
					<p>
						';
		echo LR\Filters::escapeHtmlText($translations['packagingWeightDescription']) /* line 67 */;
		echo '
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_weight_enabled"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 74 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 74 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-general-default-weight-enabled"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_weight_enabled"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 77 */;
		echo '>
			</td>
		</tr>
		<tr id="packetery-default-weight-value">
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_weight"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 82 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 82 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-general-default-weight"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_weight"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 85 */;
		echo '>
				<div class="packetery-help-block">
					<p>
						';
		echo LR\Filters::escapeHtmlText($translations['defaultWeightDescription']) /* line 88 */;
		echo '
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-dimensions_unit"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 95 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 95 */;
		echo '</label>
			</th>
			<td>
				<select class="packetery-js-wizard-general-dimensions-unit"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-dimensions_unit"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 98 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 98 */;
		echo '</select>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_dimensions_enabled"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 103 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 103 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-general-default-dimensions-enabled"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_dimensions_enabled"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 106 */;
		echo '>
			</td>
		</tr>
		<tr id="packetery-default-dimensions-value">
			<th scope="row">
				<label>';
		echo LR\Filters::escapeHtmlText($translations['dimensionsLabel']) /* line 111 */;
		echo '</label>
			</th>
			<td class="packetery-dimensions-block">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_length"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 114 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 114 */;
		echo '</label>
				<input class="packetery-js-wizard-general-default-length"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_length"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 115 */;
		echo '>
				<br>
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_width"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 117 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 117 */;
		echo '</label>
				<input class="packetery-js-wizard-general-default-width"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_width"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 118 */;
		echo '>
				<br>
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_height"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 120 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 120 */;
		echo '</label>
				<input class="packetery-js-wizard-general-default-height"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-default_height"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 121 */;
		echo '>
				<br>
				<div class="packetery-help-block">
					<p>';
		echo LR\Filters::escapeHtmlText($translations['defaultDimensionsDescription']) /* line 124 */;
		echo '</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-replace_shipping_address_with_pickup_point_address"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 130 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 130 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-general-pickup-point-address"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-replace_shipping_address_with_pickup_point_address"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 133 */;
		echo '>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-checkout_detection"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 138 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 138 */;
		echo '</label>
			</th>
			<td>
				<select class="packetery-js-wizard-general-checkout-detection"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-checkout_detection"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 141 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 141 */;
		echo '</select>
				<div class="packetery-help-block" >
					<p>
						';
		echo LR\Filters::escapeHtmlText($translations['setCheckoutDetectionDescription']) /* line 144 */;
		echo '
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-checkout_widget_button_location"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 151 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 151 */;
		echo '</label>
			</th>
			<td>
				<select class="packetery-js-wizard-general-widget-button-location"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-checkout_widget_button_location"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 154 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 154 */;
		echo '</select>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-hide_checkout_logo"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 159 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 159 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-general-hide-logo"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-hide_checkout_logo"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 162 */;
		echo '>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-auto_email_info_insertion"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 167 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 167 */;
		echo '</label>
			</th>
			<td>
				<input';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-auto_email_info_insertion"];
		echo $ʟ_input->getControlPart()->attributes() /* line 170 */;
		echo '>
				<br>
				<div class="packetery-help-block">
					<p>';
		echo LR\Filters::escapeHtmlText($translations['autoEmailInfoInsertionDescription']) /* line 173 */;
		echo '</p>
				</div>
			</td>
		</tr>
		<tr id="packetery-email-hook">
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-email_hook"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 179 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 179 */;
		echo '</label>
			</th>
			<td>
				<select class="packetery-js-wizard-general-email-hook"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-email_hook"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 182 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 182 */;
		echo '</select>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-force_packet_cancel"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 187 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 187 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-force-packet-cancel"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-force_packet_cancel"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 190 */;
		echo '>
				<div class="packetery-help-block">
					<p>
						';
		echo LR\Filters::escapeHtmlText($forcePacketCancelDescription) /* line 193 */;
		echo '
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-widget_auto_open"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 200 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 200 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-widget-auto-open"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-widget_auto_open"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 203 */;
		echo '>
				<div class="packetery-help-block">
					<p>
						';
		echo LR\Filters::escapeHtmlText($translations['widgetAutoOpenDescription']) /* line 206 */;
		echo '
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-free_shipping_shown"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 213 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 213 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-free-shipping-shown"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-free_shipping_shown"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 216 */;
		echo '>
				<div class="packetery-help-block">
					<p>
						';
		echo LR\Filters::escapeHtmlText($translations['freeShippingTextDescription']) /* line 219 */;
		echo '
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-prices_include_tax"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 226 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 226 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-prices-include-tax"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-prices_include_tax"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 229 */;
		echo '>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-pickup_point_validation_enabled"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 234 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 234 */;
		echo '</label>
			</th>
			<td>
				<input class="packetery-js-wizard-pickup-point-validation-enabled"';
		$ʟ_input = $_input = end($this->global->formsStack)["packetery-pickup_point_validation_enabled"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 237 */;
		echo '>
			</td>
		</tr>
	</table>
	<p class="submit">
		<button class="button button-primary packetery-js-wizard-settings-save-button"';
		$ʟ_input = $_input = end($this->global->formsStack)["save"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 242 */;
		echo '>';
		echo LR\Filters::escapeHtmlText($translations['saveChanges']) /* line 242 */;
		echo '</button>
';
		if ($canValidateSender) /* line 243 */ {
			echo '			<a href="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($senderValidationLink)) /* line 244 */;
			echo '" class="button">';
			echo LR\Filters::escapeHtmlText($translations['validateSender']) /* line 244 */;
			echo '</a>
';
		}
		echo '	</p>
';
		echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line 3 */;
		echo '</form>
';
		return get_defined_vars();
	}

}
