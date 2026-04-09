<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/order/modal-template.latte */
final class Template079ce8e846 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<script type="text/template" id="tmpl-wc-packetery-modal-view-order">
	<div class="wc-backbone-modal" data-packetery-modal>
		';
		echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form = $this->global->formsStack[] = is_object($form) ? $form : $this->global->uiControl[$form], ['id' => 'order-modal-edit-form', 'data-order-id' => "{{ data.order.id }}", 'data-nonce' => $nonce, 'data-order-save-url' => $orderSaveUrl]) /* line 3 */;
		echo '
			<div class="wc-backbone-modal-content">
				<section class="wc-backbone-modal-main" role="main">
					<header class="wc-backbone-modal-header">
						<h1>';
		esc_html_e( sprintf( $translations['order#%s'], '{{ data.order.custom_number }}' ) ) /* line 7 */;
		echo '</h1>
						<div class="modal-close modal-close-link dashicons dashicons-no-alt">
							<span class="screen-reader-text">';
		echo LR\Filters::escapeHtmlText($translations['closeModalPanel']) /* line 9 */;
		echo '</span>
						</div>
					</header>
					<div class="notice is-dismissible hidden">
						<p></p>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text"></span>
						</button>
					</div>
					<article>
						<div class="wc-order-preview-table-wrapper">
							<table>
								<tbody>
								<tr>
									<td>
										';
		if ($ʟ_label = end($this->global->formsStack)["packeteryWeight"]->getLabel()) echo $ʟ_label;
		echo '
										<span title="';
		echo LR\Filters::escapeHtmlText($translations['weightIsManual']) /* line 25 */;
		echo '" class="{{ data.order.manualWeightIconExtraClass }}dashicons dashicons-lock"></span>
									</td>
									<td>
										';
		echo end($this->global->formsStack)["packeteryWeight"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryWeight }}', 'class' => 'packetery-js-wizard-modal-weight']) /* line 28 */;
		echo '
										';
		echo end($this->global->formsStack)["packeteryOriginalWeight"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryOriginalWeight }}']) /* line 29 */;
		echo '
									</td>
								</tr>
								<# if (data.order.requiresSizeDimensions) { #>
								<tr>
									<td>';
		if ($ʟ_label = end($this->global->formsStack)["packeteryLength"]->getLabel()) echo $ʟ_label;
		echo '</td>
									<td>';
		echo end($this->global->formsStack)["packeteryLength"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryLength }}', 'class' => 'packetery-js-wizard-modal-length']) /* line 35 */;
		echo '</td>
								</tr>
								<tr>
									<td>';
		if ($ʟ_label = end($this->global->formsStack)["packeteryWidth"]->getLabel()) echo $ʟ_label;
		echo '</td>
									<td>';
		echo end($this->global->formsStack)["packeteryWidth"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryWidth }}', 'class' => 'packetery-js-wizard-modal-width']) /* line 39 */;
		echo '</td>
								</tr>
								<tr>
									<td>';
		if ($ʟ_label = end($this->global->formsStack)["packeteryHeight"]->getLabel()) echo $ʟ_label;
		echo '</td>
									<td>';
		echo end($this->global->formsStack)["packeteryHeight"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryHeight }}', 'class' => 'packetery-js-wizard-modal-height']) /* line 43 */;
		echo '</td>
								</tr>
								<# } #>
								<# if ( data.order.allowsAdultContent ) { #>
								<tr>
									<td>';
		if ($ʟ_label = end($this->global->formsStack)["packeteryAdultContent"]->getLabelPart("")) echo $ʟ_label;
		echo '</td>
									<td>
										<input type="checkbox" name="packeteryAdultContent" class="packetery-js-wizard-modal-adult-content"
										<# if ( data.order.packeteryAdultContent ) { #>
										checked
										<# } #>
										>
									</td>
								</tr>
								<# } #>
								<# if ( data.order.hasCod ) { #>
								<tr>
									<td>
										';
		if ($ʟ_label = end($this->global->formsStack)["packeteryCOD"]->getLabel()) echo $ʟ_label;
		echo '
										<span title="';
		echo LR\Filters::escapeHtmlText($translations['codIsManual']) /* line 62 */;
		echo '" class="{{ data.order.manualCodIconExtraClass }}dashicons dashicons-lock"></span>
									</td>
									<td>
										';
		echo end($this->global->formsStack)["packeteryCOD"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryCOD }}', 'class' => 'packetery-js-wizard-modal-cod']) /* line 65 */;
		echo '
										';
		echo end($this->global->formsStack)["packeteryCalculatedCod"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryCalculatedCod }}']) /* line 66 */;
		echo '
									</td>
								</tr>
								<# } #>
								<tr>
									<td>
										';
		if ($ʟ_label = end($this->global->formsStack)["packeteryValue"]->getLabel()) echo $ʟ_label;
		echo '
										<span title="';
		echo LR\Filters::escapeHtmlText($translations['valueIsManual']) /* line 73 */;
		echo '" class="{{ data.order.manualValueIconExtraClass }}dashicons dashicons-lock"></span>
									</td>
									<td>
										';
		echo end($this->global->formsStack)["packeteryValue"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryValue }}', 'class' => 'packetery-js-wizard-modal-value']) /* line 76 */;
		echo '
										';
		echo end($this->global->formsStack)["packeteryCalculatedValue"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryCalculatedValue }}']) /* line 77 */;
		echo '
									</td>
								</tr>
								<# if ( data.order.hasDeliverOn ) { #>
								<tr class="{{ data.order.packeteryDeliverOnClass }}">
									<td>';
		if ($ʟ_label = end($this->global->formsStack)["packeteryDeliverOn"]->getLabel()) echo $ʟ_label;
		echo '</td>
									<td>';
		echo end($this->global->formsStack)["packeteryDeliverOn"]->getControl()->addAttributes(['value' =>'{{ data.order.packeteryDeliverOn }}', 'class' => 'date-picker packetery-js-wizard-modal-deliver-on']) /* line 83 */;
		echo '</td>
								</tr>
								<# } #>
								</tbody>
							</table>
						</div>
					</article>
					<footer>
						<div class="inner">
							<div id="publishing-action">
								<span class="spinner"></span>
								';
		echo end($this->global->formsStack)["cancel"]->getControl()->addAttributes(['class' => 'button button-sencondary button-large modal-close modal-close-link']) /* line 95 */;
		echo '
								';
		echo end($this->global->formsStack)["submit"]->getControl()->addAttributes(['class' => 'button button-primary button-large']) /* line 96 */;
		echo '
							</div>
						</div>
					</footer>
				</section>
			</div>
		';
		echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack));
		echo '
	</div>
	<div class="wc-backbone-modal-backdrop modal-close"></div>
</script>
';
		return get_defined_vars();
	}

}
