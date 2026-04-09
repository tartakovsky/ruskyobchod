<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/carrier/carrier.latte */
final class Template97c4a0555f extends \Packetery\Latte\Runtime\Template
{
	protected const BLOCKS = [
		['pricingRule' => 'blockPricingRule', 'weightRulesSection' => 'blockWeightRulesSection', 'productValueRulesSection' => 'blockProductValueRulesSection', 'surchargeRules' => 'blockSurchargeRules', 'surchargeRulesSection' => 'blockSurchargeRulesSection'],
	];


	public function main(): array
	{
		extract($this->params);
		$carrier = $carrier_data['carrier'] /* line 1 */;
		echo '
<div class="packetery-carrier-options-page packeta_carrier_detail_wrapper">
	<div class="card packeta packeta_carrier_detail">
';
		if ($displayCarrierTitle ?? true) /* line 6 */ {
			echo '		<div>
			<h2 class="packeta_carrier_title">';
			echo LR\Filters::escapeHtmlText($carrier->getName()) /* line 7 */;
			echo '</h2>
		</div>
';
		}
		echo "\n";
		if (isset($carrier_data['form'])) /* line 10 */ {
			echo "\n";
			if ($carrier_data['isAvailableVendorsCountLow']) /* line 12 */ {
				echo '				<div class="notice notice-info">
					<p>';
				echo LR\Filters::escapeHtmlText($translations['lowAvailableVendorsCount']) /* line 14 */;
				echo '</p>
				</div>
';
			}
			echo "\n";
			if (!$carrier_data['carrier']->isAvailable()) /* line 18 */ {
				echo '				<div class="notice notice-info">
					<p>';
				echo LR\Filters::escapeHtmlText($translations['carrierUnavailable']) /* line 20 */;
				echo '</p>
				</div>
';
			}
			echo '





';
			$form = $carrier_data['formTemplate'] /* line 155 */;
			$form = $this->global->formsStack[] = is_object($form) ? $form : $this->global->uiControl[$form] /* line 156 */;
			echo '			<form class="packetery-hidden"';
			echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['class' => null], false);
			echo '>
				<table class="form-table" role="presentation">
';
			$this->renderBlock('weightRulesSection', ['form' => $form] + get_defined_vars(), 'html') /* line 158 */;
			$this->renderBlock('productValueRulesSection', ['form' => $form] + get_defined_vars(), 'html') /* line 159 */;
			$this->renderBlock('surchargeRulesSection', ['form' => $form] + get_defined_vars(), 'html') /* line 160 */;
			echo '				</table>
';
			echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line 156 */;
			echo '			</form>

';
			$form = $carrier_data['form'] /* line 164 */;
			$form = $this->global->formsStack[] = is_object($form) ? $form : $this->global->uiControl[$form] /* line 165 */;
			echo '			<form';
			echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false);
			echo '>
				<table class="form-table" role="presentation">
';
			if (isset($form['active'])) /* line 167 */ {
				if ($carrier_data['carrier']->isAvailable()) /* line 167 */ {
					echo '					<tr>
						<th scope="row">
							<label';
					$ʟ_input = $_input = end($this->global->formsStack)["active"];
					echo $ʟ_input->getLabelPart()->attributes() /* line 169 */;
					echo '>';
					echo $ʟ_input->getLabelPart()->getHtml() /* line 169 */;
					echo '</label>
						</th>
						<td>
							<input';
					$ʟ_input = $_input = end($this->global->formsStack)["active"];
					echo $ʟ_input->getControlPart()->attributes() /* line 172 */;
					echo '>
						</td>
					</tr>
';
				}
			}
			echo '					<tr>
						<th scope="row">
							<label';
			$ʟ_input = $_input = end($this->global->formsStack)["name"];
			echo $ʟ_input->getLabelPart()->attributes() /* line 177 */;
			echo '>';
			echo $ʟ_input->getLabelPart()->getHtml() /* line 177 */;
			echo '</label>
						</th>
						<td>
							<input class="packetery-carrier-name"
												data-lfv-message-id="';
			echo LR\Filters::escapeHtmlAttr($form['name']->getHtmlId() . '_message') /* line 181 */;
			echo '"';
			$ʟ_input = $_input = end($this->global->formsStack)["name"];
			echo $ʟ_input->getControlPart()->addAttributes(['class' => null, 'data-lfv-message-id' => null])->attributes() /* line 180 */;
			echo '>
							<p class="packetery-input-validation-message help-block text-danger"
								id="';
			echo LR\Filters::escapeHtmlAttr($form['name']->getHtmlId() . '_message') /* line 183 */;
			echo '">';
			echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["name"]->getError()) /* line 183 */;
			echo '</p>
						</td>
					</tr>

';
			if (isset($form['vendor_groups'])) /* line 187 */ {
				$vendorCheckboxes = $form['vendor_groups']->getComponents() /* line 188 */;
				echo '						<tr>
							<th scope="row">
								<label>';
				echo LR\Filters::escapeHtmlText($translations['allowedPickupPointTypes']) /* line 191 */;
				echo ':</label>
							</th>
							<td>
								<table>
';
				$iterations = 0;
				foreach ($vendorCheckboxes as $component) /* line 195 */ {
					echo '										<tr>
											<th scope="row">
												<label';
					$ʟ_input = $_input = is_object($component) ? $component : end($this->global->formsStack)[$component];
					echo $ʟ_input->getLabelPart()->attributes() /* line 198 */;
					echo '>';
					echo $ʟ_input->getLabelPart()->getHtml() /* line 198 */;
					echo '</label>
											</th>
											<td>
												<input';
					$ʟ_input = $_input = is_object($component) ? $component : end($this->global->formsStack)[$component];
					echo $ʟ_input->getControlPart()->attributes() /* line 201 */;
					echo '>
											</td>
										</tr>
';
					$iterations++;
				}
				echo '								</table>
';
				if (count($vendorCheckboxes) > \Packetery\Module\Carrier\OptionsPage::MINIMUM_CHECKED_VENDORS) /* line 206 */ {
					echo '									<p>
										';
					echo LR\Filters::escapeHtmlText($translations['checkAtLeastTwo']) /* line 208 */;
					echo '
									</p>
';
				}
				echo '							</td>
						</tr>
';
			}
			echo '
					<tr>
						<th scope="row">
							<label';
			$ʟ_input = $_input = end($this->global->formsStack)["pricing_type"];
			echo $ʟ_input->getLabelPart()->attributes() /* line 217 */;
			echo '>';
			echo $ʟ_input->getLabelPart()->getHtml() /* line 217 */;
			echo '</label>
						</th>
						<td>
							<div>
								<select';
			$ʟ_input = $_input = end($this->global->formsStack)["pricing_type"];
			echo $ʟ_input->getControlPart()->attributes() /* line 221 */;
			echo '>';
			echo $ʟ_input->getControl()->getHtml() /* line 221 */;
			echo '</select>
							</div>
							<p class="packetery-input-validation-message help-block text-danger"
								id="';
			echo LR\Filters::escapeHtmlAttr($form['pricing_type']->getHtmlId() . '_message') /* line 224 */;
			echo '">';
			echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["pricing_type"]->getError()) /* line 224 */;
			echo '</p>
						</td>
					</tr>

';
			$this->renderBlock('weightRulesSection', ['form' => $form] + get_defined_vars(), 'html') /* line 228 */;
			$this->renderBlock('productValueRulesSection', ['form' => $form] + get_defined_vars(), 'html') /* line 229 */;
			echo "\n";
			if ($carrier->supportsCod()) /* line 231 */ {
				echo '						<tr>
							<th scope="row">
								<label';
				$ʟ_input = $_input = end($this->global->formsStack)["default_COD_surcharge"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 234 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 234 */;
				echo '</label>
							</th>
							<td>
								<div>
									<input
											data-lfv-message-id="';
				echo LR\Filters::escapeHtmlAttr($form['default_COD_surcharge']->getHtmlId() . '_message') /* line 239 */;
				echo '"';
				$ʟ_input = $_input = end($this->global->formsStack)["default_COD_surcharge"];
				echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null])->attributes() /* line 238 */;
				echo '>
									<span> ';
				echo $globalCurrency /* line 240 */;
				echo '</span>
								</div>
								<p class="packetery-input-validation-message help-block text-danger"
									id="';
				echo LR\Filters::escapeHtmlAttr($form['default_COD_surcharge']->getHtmlId() . '_message') /* line 243 */;
				echo '">';
				echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["default_COD_surcharge"]->getError()) /* line 243 */;
				echo '</p>
							</td>
						</tr>
';
				$this->renderBlock('surchargeRulesSection', ['form' => $form] + get_defined_vars(), 'html') /* line 246 */;
			} else /* line 247 */ {
				echo '						<tr>
							<th></th>
							<td>
								<p>';
				echo LR\Filters::escapeHtmlText($translations['carrierDoesNotSupportCod']) /* line 251 */;
				echo '</p>
							</td>
						</tr>
';
			}
			echo '
					<tr>
						<th scope="row">
							<label';
			$ʟ_input = $_input = end($this->global->formsStack)["free_shipping_limit"];
			echo $ʟ_input->getLabelPart()->attributes() /* line 258 */;
			echo '>';
			echo $ʟ_input->getLabelPart()->getHtml() /* line 258 */;
			echo '</label>
						</th>
						<td>
							<div>
								<input
										data-lfv-message-id="';
			echo LR\Filters::escapeHtmlAttr($form['free_shipping_limit']->getHtmlId() . '_message') /* line 263 */;
			echo '"';
			$ʟ_input = $_input = end($this->global->formsStack)["free_shipping_limit"];
			echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null])->attributes() /* line 262 */;
			echo '>
								<span> ';
			echo $globalCurrency /* line 264 */;
			echo '</span>
							</div>
							<p class="packetery-input-validation-message help-block text-danger"
								id="';
			echo LR\Filters::escapeHtmlAttr($form['free_shipping_limit']->getHtmlId() . '_message') /* line 267 */;
			echo '">';
			echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["free_shipping_limit"]->getError()) /* line 267 */;
			echo '</p>
							<p>
								';
			echo LR\Filters::escapeHtmlText($translations['afterExceedingThisAmountShippingIsFree']) /* line 269 */;
			echo '
							</p>
						</td>
					</tr>

';
			if ($carrier->isCarDelivery()) /* line 274 */ {
				echo '						<tr>
							<th scope="row">
								<label';
				$ʟ_input = $_input = end($this->global->formsStack)["days_until_shipping"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 277 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 277 */;
				echo '</label>
							</th>
							<td>
								<div>
									<input data-lfv-message-id="';
				echo LR\Filters::escapeHtmlAttr($form['days_until_shipping']->getHtmlId() . '_message') /* line 281 */;
				echo '"';
				$ʟ_input = $_input = end($this->global->formsStack)["days_until_shipping"];
				echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null])->attributes() /* line 281 */;
				echo '>
								</div>
								<p class="packetery-input-validation-message help-block text-danger"
									id="';
				echo LR\Filters::escapeHtmlAttr($form['days_until_shipping']->getHtmlId() . '_message') /* line 284 */;
				echo '">';
				echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["days_until_shipping"]->getError()) /* line 284 */;
				echo '</p>
								<p>
									';
				echo LR\Filters::escapeHtmlText($translations['daysUntilShipping']) /* line 286 */;
				echo '
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label';
				$ʟ_input = $_input = end($this->global->formsStack)["shipping_time_cut_off"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 293 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 293 */;
				echo '</label>
							</th>
							<td>
								<div>
									<input data-lfv-message-id="';
				echo LR\Filters::escapeHtmlAttr($form['shipping_time_cut_off']->getHtmlId() . '_message') /* line 297 */;
				echo '"';
				$ʟ_input = $_input = end($this->global->formsStack)["shipping_time_cut_off"];
				echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null])->attributes() /* line 297 */;
				echo '>
								</div>
								<p class="packetery-input-validation-message help-block text-danger"
									id="';
				echo LR\Filters::escapeHtmlAttr($form['shipping_time_cut_off']->getHtmlId() . '_message') /* line 300 */;
				echo '">';
				echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["shipping_time_cut_off"]->getError()) /* line 300 */;
				echo '</p>
								<p>
									';
				echo LR\Filters::escapeHtmlText($translations['shippingTimeCutOff']) /* line 302 */;
				echo '
								</p>
							</td>
						</tr>
';
			}
			echo "\n";
			if (isset($form['address_validation'])) /* line 308 */ {
				echo '						<tr>
							<th scope="row">
								<label';
				$ʟ_input = $_input = end($this->global->formsStack)["address_validation"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 311 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 311 */;
				echo '</label>
							</th>
							<td>
								<select';
				$ʟ_input = $_input = end($this->global->formsStack)["address_validation"];
				echo $ʟ_input->getControlPart()->attributes() /* line 314 */;
				echo '>';
				echo $ʟ_input->getControl()->getHtml() /* line 314 */;
				echo '</select>
								<p>
									';
				echo LR\Filters::escapeHtmlText($translations['addressValidationDescription']) /* line 316 */;
				echo '
								</p>
							</td>
						</tr>
';
			}
			if (isset($form['age_verification_fee'])) /* line 321 */ {
				echo '						<tr>
							<th scope="row">
								<label';
				$ʟ_input = $_input = end($this->global->formsStack)["age_verification_fee"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 324 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 324 */;
				echo '</label>
							</th>
							<td>
								<div>
									<input
											data-lfv-message-id="';
				echo LR\Filters::escapeHtmlAttr($form['age_verification_fee']->getHtmlId() . '_message') /* line 329 */;
				echo '"';
				$ʟ_input = $_input = end($this->global->formsStack)["age_verification_fee"];
				echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null])->attributes() /* line 328 */;
				echo '>
									<span> ';
				echo $globalCurrency /* line 330 */;
				echo '</span>
								</div>
								<p class="packetery-input-validation-message help-block text-danger"
									id="';
				echo LR\Filters::escapeHtmlAttr($form['age_verification_fee']->getHtmlId() . '_message') /* line 333 */;
				echo '">';
				echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["age_verification_fee"]->getError()) /* line 333 */;
				echo '
								</p>
								<p>';
				echo LR\Filters::escapeHtmlText($translations['ageVerificationSupportedNotification']) /* line 335 */;
				echo '</p>
							</td>
						</tr>
';
			}
			if ($carrier->supportsCod()) /* line 339 */ {
				echo '						<tr>
							<th scope="row">
								<label';
				$ʟ_input = $_input = end($this->global->formsStack)["cod_rounding"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 342 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 342 */;
				echo '</label>
							</th>
							<td>
								<select';
				$ʟ_input = $_input = end($this->global->formsStack)["cod_rounding"];
				echo $ʟ_input->getControlPart()->attributes() /* line 345 */;
				echo '>';
				echo $ʟ_input->getControl()->getHtml() /* line 345 */;
				echo '</select>
								<p>
									';
				echo LR\Filters::escapeHtmlText($translations['roundingDescription']) /* line 347 */;
				echo '
								</p>
							</td>
						</tr>
';
			}
			echo '					<tr>
						<th scope="row">
							<label';
			$ʟ_input = $_input = end($this->global->formsStack)["coupon_free_shipping-active"];
			echo $ʟ_input->getLabelPart()->attributes() /* line 354 */;
			echo '>';
			echo $ʟ_input->getLabelPart()->getHtml() /* line 354 */;
			echo '</label>
						</th>
						<td>
							<input';
			$ʟ_input = $_input = end($this->global->formsStack)["coupon_free_shipping-active"];
			echo $ʟ_input->getControlPart()->attributes() /* line 357 */;
			echo '>
						</td>
					</tr>
					<tr id="';
			echo LR\Filters::escapeHtmlAttr($carrier_data['couponFreeShippingForFeesContainerId']) /* line 360 */;
			echo '">
						<th scope="row">
							<label';
			$ʟ_input = $_input = end($this->global->formsStack)["coupon_free_shipping-allow_for_fees"];
			echo $ʟ_input->getLabelPart()->attributes() /* line 362 */;
			echo '>';
			echo $ʟ_input->getLabelPart()->getHtml() /* line 362 */;
			echo '</label>
						</th>
						<td>
							<input';
			$ʟ_input = $_input = end($this->global->formsStack)["coupon_free_shipping-allow_for_fees"];
			echo $ʟ_input->getControlPart()->attributes() /* line 365 */;
			echo '>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label';
			$ʟ_input = $_input = end($this->global->formsStack)["disallowed_checkout_payment_methods"];
			echo $ʟ_input->getLabelPart()->attributes() /* line 370 */;
			echo '>';
			echo $ʟ_input->getLabelPart()->getHtml() /* line 370 */;
			echo '</label>
						</th>
						<td>
							<select data-packetery-select2';
			$ʟ_input = $_input = end($this->global->formsStack)["disallowed_checkout_payment_methods"];
			echo $ʟ_input->getControlPart()->addAttributes(['data-packetery-select2' => null])->attributes() /* line 373 */;
			echo '>';
			echo $ʟ_input->getControl()->getHtml() /* line 373 */;
			echo '</select>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label';
			$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-active"];
			echo $ʟ_input->getLabelPart()->attributes() /* line 379 */;
			echo '>';
			echo $ʟ_input->getLabelPart()->getHtml() /* line 379 */;
			echo '</label>
						</th>
						<td>
							<input';
			$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-active"];
			echo $ʟ_input->getControlPart()->attributes() /* line 382 */;
			echo '>
						</td>
					</tr>
					<tr id="';
			echo LR\Filters::escapeHtmlAttr($carrier_data['dimensionRestrictionContainerId']) /* line 385 */;
			echo '">
						<td colspan="2">
							<table class="packetery-table">
';
			if (isset($form['dimensions_restrictions-length'])) /* line 388 */ {
				echo '									<tr>
										<th><label';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-length"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 390 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 390 */;
				echo '</label></th>
										<td><input';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-length"];
				echo $ʟ_input->getControlPart()->attributes() /* line 391 */;
				echo '></td>
									</tr>
									<tr>
										<th><label';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-width"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 394 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 394 */;
				echo '</label></th>
										<td><input';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-width"];
				echo $ʟ_input->getControlPart()->attributes() /* line 395 */;
				echo '></td>
									</tr>
									<tr>
										<th><label';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-height"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 398 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 398 */;
				echo '</label></th>
										<td><input';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-height"];
				echo $ʟ_input->getControlPart()->attributes() /* line 399 */;
				echo '></td>
									</tr>
';
			}
			if (isset($form['dimensions_restrictions-maximum_length'])) /* line 402 */ {
				echo '									<tr>
										<th><label';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-maximum_length"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 404 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 404 */;
				echo '</label></th>
										<td><input';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-maximum_length"];
				echo $ʟ_input->getControlPart()->attributes() /* line 405 */;
				echo '></td>
									</tr>
									<tr>
										<th><label';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-dimensions_sum"];
				echo $ʟ_input->getLabelPart()->attributes() /* line 408 */;
				echo '>';
				echo $ʟ_input->getLabelPart()->getHtml() /* line 408 */;
				echo '</label></th>
										<td><input';
				$ʟ_input = $_input = end($this->global->formsStack)["dimensions_restrictions-dimensions_sum"];
				echo $ʟ_input->getControlPart()->attributes() /* line 409 */;
				echo '></td>
									</tr>
';
			}
			echo '							</table>
						</td>
					</tr>

				</table>

				<p class="submit">
					<button class="button button-primary"';
			$ʟ_input = $_input = end($this->global->formsStack)["save"];
			echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 419 */;
			echo '>';
			echo LR\Filters::escapeHtmlText($translations['saveChanges']) /* line 419 */;
			echo '</button>
				</p>

';
			echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line 165 */;
			echo '			</form>
';
		}
		echo '	</div>
</div>
';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['weightId' => '67', 'tmp' => '67, 143', 'valueId' => '88', 'component' => '88, 195', 'surchargeId' => '143'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}


	/** {define pricingRule} on line 24 */
	public function blockPricingRule(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$this->global->formsStack[] = $formContainer = is_object($container[$id]) ? $container[$id] : end($this->global->formsStack)[$container[$id]] /* line 25 */;
		echo '				<tr data-replication-item';
		echo ($ʟ_tmp = array_filter([$class ?? ''])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 25 */;
		echo '>
					<td>
						<div class="packetery-rule">
							<div class="packetery-label">
								<label';
		$ʟ_input = $_input = is_object($fieldName) ? $fieldName : end($this->global->formsStack)[$fieldName];
		echo $ʟ_input->getLabelPart()->attributes() /* line 29 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 29 */;
		echo '</label>
							</div>
							<div class="packetery-input">
								<div class="packetery-input-with-unit">
									<input
											data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id][$fieldName]->getHtmlId() . '_message') /* line 34 */;
		echo '"';
		$ʟ_input = $_input = is_object($fieldName) ? $fieldName : end($this->global->formsStack)[$fieldName];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null])->attributes() /* line 33 */;
		echo '>
									<span> ';
		echo $fieldUnit /* line 35 */;
		echo '</span>
								</div>
								<p class="packetery-input-validation-message help-block text-danger"
									id="';
		echo LR\Filters::escapeHtmlAttr($container[$id][$fieldName]->getHtmlId() . '_message') /* line 38 */;
		echo '">';
		$ʟ_input = is_object($fieldName) ? $fieldName : end($this->global->formsStack)[$fieldName];
		echo LR\Filters::escapeHtmlText($ʟ_input->getError()) /* line 38 */;
		echo '</p>
							</div>
							<div class="packetery-label">
								<label';
		$ʟ_input = $_input = end($this->global->formsStack)["price"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 41 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 41 */;
		echo '</label>
							</div>
							<div class="packetery-input">
								<div class="packetery-input-with-unit">
									<input
											data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['price']->getHtmlId() . '_message') /* line 46 */;
		echo '"';
		$ʟ_input = $_input = end($this->global->formsStack)["price"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null])->attributes() /* line 45 */;
		echo '>
									<span> ';
		echo $globalCurrency /* line 47 */;
		echo '</span>
								</div>
								<p class="packetery-input-validation-message help-block text-danger"
									id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['price']->getHtmlId() . '_message') /* line 50 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["price"]->getError()) /* line 50 */;
		echo '</p>
							</div>
						</div>
						<button type="button" data-replication-delete class="button button-small">';
		echo LR\Filters::escapeHtmlText($translations['delete']) /* line 53 */;
		echo '</button>
					</td>
				</tr>
';
		array_pop($this->global->formsStack);
		$formContainer = end($this->global->formsStack);
	}


	/** {define weightRulesSection} on line 58 */
	public function blockWeightRulesSection(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$container = $form['weight_limits'] /* line 59 */;
		echo '				<tr id="';
		echo LR\Filters::escapeHtmlAttr($carrier_data['weightLimitsContainerId']) /* line 60 */;
		echo '">
					<th scope="row">
						<label>';
		echo LR\Filters::escapeHtmlText($translations['weightRules']) /* line 62 */;
		echo ':</label>
					</th>
					<td class="js-weight-rules">
						<table>
							<tbody data-replication-item-container data-replication-min-items="1">
';
		$iterations = 0;
		foreach ($iterator = $ʟ_it = new LR\CachingIterator($container->getComponents(), $ʟ_it ?? null) as $weightId => $tmp) /* line 67 */ {
			$this->renderBlock('pricingRule', ['fieldName' => 'weight', 'fieldUnit' => 'kg', 'id' => $weightId, 'container' => $container] + get_defined_vars(), 'html') /* line 68 */;
			$iterations++;
		}
		$iterator = $ʟ_it = $ʟ_it->getParent();
		echo '							</tbody>
						</table>

						<button type="button" data-replication-add
								class="button button-small">';
		echo LR\Filters::escapeHtmlText($translations['addWeightRule']) /* line 74 */;
		echo '</button>
					</td>
				</tr>
';
	}


	/** {define productValueRulesSection} on line 79 */
	public function blockProductValueRulesSection(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$container = $form['product_value_limits'] /* line 80 */;
		echo '				<tr id="';
		echo LR\Filters::escapeHtmlAttr($carrier_data['productValueLimitsContainerId']) /* line 81 */;
		echo '">
					<th scope="row">
						<label>';
		echo LR\Filters::escapeHtmlText($translations['productValueRules']) /* line 83 */;
		echo ':</label>
					</th>
					<td class="js-product-value-rules">
						<table>
							<tbody data-replication-item-container data-replication-min-items="1">
';
		$iterations = 0;
		foreach ($iterator = $ʟ_it = new LR\CachingIterator($container->getComponents(), $ʟ_it ?? null) as $valueId => $component) /* line 88 */ {
			$this->renderBlock('pricingRule', ['fieldName' => 'value', 'fieldUnit' => $globalCurrency, 'id' => $valueId, 'container' => $container] + get_defined_vars(), 'html') /* line 89 */;
			$iterations++;
		}
		$iterator = $ʟ_it = $ʟ_it->getParent();
		echo '							</tbody>
						</table>

						<button type="button" data-replication-add
								class="button button-small">';
		echo LR\Filters::escapeHtmlText($translations['addProductValueRule']) /* line 95 */;
		echo '</button>
					</td>
				</tr>
';
	}


	/** {define surchargeRules} on line 100 */
	public function blockSurchargeRules(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$this->global->formsStack[] = $formContainer = is_object($container[$id]) ? $container[$id] : end($this->global->formsStack)[$container[$id]] /* line 101 */;
		echo '				<tr data-replication-item';
		echo ($ʟ_tmp = array_filter([$class ?? ''])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 101 */;
		echo '>
					<td>
						<div class="packetery-rule">
							<div class="packetery-label">
								<label';
		$ʟ_input = $_input = end($this->global->formsStack)["order_price"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 105 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 105 */;
		echo '</label>
							</div>
							<div class="packetery-input">
								<div class="packetery-input-with-unit">
									<input
											data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['order_price']->getHtmlId() . '_message') /* line 110 */;
		echo '"';
		$ʟ_input = $_input = end($this->global->formsStack)["order_price"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null])->attributes() /* line 109 */;
		echo '>
									<span> ';
		echo $globalCurrency /* line 111 */;
		echo '</span>
								</div>
								<p class="packetery-input-validation-message help-block text-danger"
									id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['order_price']->getHtmlId() . '_message') /* line 114 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["order_price"]->getError()) /* line 114 */;
		echo '</p>
							</div>
							<div class="packetery-label">
								<label';
		$ʟ_input = $_input = end($this->global->formsStack)["surcharge"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 117 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 117 */;
		echo '</label>
							</div>
							<div class="packetery-input">
								<div class="packetery-input-with-unit">
									<input
											data-lfv-message-id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['surcharge']->getHtmlId() . '_message') /* line 122 */;
		echo '"';
		$ʟ_input = $_input = end($this->global->formsStack)["surcharge"];
		echo $ʟ_input->getControlPart()->addAttributes(['data-lfv-message-id' => null])->attributes() /* line 121 */;
		echo '>
									<span> ';
		echo $globalCurrency /* line 123 */;
		echo '</span>
								</div>
								<p class="packetery-input-validation-message help-block text-danger"
									id="';
		echo LR\Filters::escapeHtmlAttr($container[$id]['surcharge']->getHtmlId() . '_message') /* line 126 */;
		echo '">';
		echo LR\Filters::escapeHtmlText(end($this->global->formsStack)["surcharge"]->getError()) /* line 126 */;
		echo '</p>
							</div>
						</div>
						<button type="button" data-replication-delete class="button button-small">';
		echo LR\Filters::escapeHtmlText($translations['delete']) /* line 129 */;
		echo '</button>
					</td>
				</tr>
';
		array_pop($this->global->formsStack);
		$formContainer = end($this->global->formsStack);
	}


	/** {define surchargeRulesSection} on line 134 */
	public function blockSurchargeRulesSection(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);
		$container = $form['surcharge_limits'] /* line 135 */;
		echo '				<tr>
					<th scope="row">
						<label>';
		echo LR\Filters::escapeHtmlText($translations['codSurchargeRules']) /* line 138 */;
		echo ':</label>
					</th>
					<td class="js-surcharge-rules">
						<table>
							<tbody data-replication-item-container data-replication-min-items="0">
';
		$iterations = 0;
		foreach ($iterator = $ʟ_it = new LR\CachingIterator($container->getComponents(), $ʟ_it ?? null) as $surchargeId => $tmp) /* line 143 */ {
			$this->renderBlock('surchargeRules', ['id' => $surchargeId, 'container' => $container] + get_defined_vars(), 'html') /* line 144 */;
			$iterations++;
		}
		$iterator = $ʟ_it = $ʟ_it->getParent();
		echo '							</tbody>
						</table>

						<button type="button" data-replication-add
								class="button button-small">';
		echo LR\Filters::escapeHtmlText($translations['addCodSurchargeRule']) /* line 150 */;
		echo '</button>
					</td>
				</tr>
';
	}

}
