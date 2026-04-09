<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/confirm-modal-template.latte */
final class Template2e5e592614 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<script type="text/template" id="tmpl-wc-packetery-confirm-modal">
	<div class="wc-backbone-modal" data-packetery-confirm-modal>
		<div class="wc-backbone-modal-content">
			<section class="wc-backbone-modal-main" role="main">
				<header class="wc-backbone-modal-header">
					<h1>{{ data.heading }}</h1>
					<div class="modal-close modal-close-link dashicons dashicons-no-alt">
						<span class="screen-reader-text">';
		echo LR\Filters::escapeHtmlText($translations['closeModalPanel']) /* line 8 */;
		echo '</span>
					</div>
				</header>
				<article>
					<p>{{ data.text }}</p>
				</article>
				<footer>
					<div class="inner">
						<a class="button button-sencondary button-large modal-close modal-close-link">';
		echo LR\Filters::escapeHtmlText($translations['no']) /* line 17 */;
		echo '</a>
						<a data-packetery-confirm-yes class="button button-primary button-large">';
		echo LR\Filters::escapeHtmlText($translations['yes']) /* line 18 */;
		echo '</a>
					</div>
				</footer>
			</section>
		</div>
	</div>
	<div class="wc-backbone-modal-backdrop modal-close"></div>
</script>
';
		return get_defined_vars();
	}

}
