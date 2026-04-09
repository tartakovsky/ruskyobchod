<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/order/stored-until-modal.latte */
final class Templatedc8a811be5 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<script type="text/template" id="tmpl-';
		echo LR\Filters::escapeHtmlAttr($id) /* line 1 */;
		echo '">
	<div class="wc-backbone-modal" data-packetery-stored-until-modal>
		';
		echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form = $this->global->formsStack[] = is_object($form) ? $form : $this->global->uiControl[$form], ['id' => 'order-stored-until-form', 'data-order-id' => "{{ data.order.id }}", 'data-nonce' => $nonce, 'data-stored-until-save-url' => $storedUntilSaveUrl]) /* line 3 */;
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
									<td>';
		if ($ʟ_label = end($this->global->formsStack)["packetery_stored_until"]->getLabel()) echo $ʟ_label;
		echo '</td>
									<td>';
		echo end($this->global->formsStack)["packetery_stored_until"]->getControl()->addAttributes(['value' => '{{ data.order.packetery_stored_until }}']) /* line 24 */;
		echo '</td>
									';
		echo end($this->global->formsStack)["packet_id"]->getControl()->addAttributes(['value' => '{{ data.order.id }}']) /* line 25 */;
		echo '
								</tr>
								</tbody>
							</table>
						</div>
					</article>
					<footer>
						<div class="inner">
							<div id="publishing-action">
								<span class="spinner"></span>
								';
		echo end($this->global->formsStack)["cancel"]->getControl()->addAttributes(['class' => 'button button-large modal-close modal-close-link']) /* line 35 */;
		echo '
								';
		echo end($this->global->formsStack)["submit"]->getControl()->addAttributes(['class' => 'button button-primary button-large']) /* line 36 */;
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
