<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/checkout/widget-button.latte */
final class Template8450e6bd21 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="packetery-widget-button-wrapper">
	<div class="form-row packeta-widget packetery-hidden ';
		echo LR\Filters::escapeHtmlAttr($renderer) /* line 2 */;
		echo '">
		<div class="packetery-widget-button-row packeta-widget-button">
';
		if ($showLogo) /* line 4 */ {
			echo '			<img class="packetery-widget-button-logo" src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($logo)) /* line 4 */;
			echo '" alt="';
			echo LR\Filters::escapeHtmlAttr($translations['packeta']) /* line 4 */;
			echo '">
';
		}
		echo '			<button class="button alt"> . . .</button>
		</div>
		<p class="packeta-widget-selected-address"></p>
		<p class="packeta-widget-place"></p>
		<p class="packeta-widget-info"></p>
	</div>
</div>
';
		return get_defined_vars();
	}

}
