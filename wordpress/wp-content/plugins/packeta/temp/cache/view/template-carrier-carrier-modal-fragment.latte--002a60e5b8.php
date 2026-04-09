<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/carrier/carrier-modal-fragment.latte */
final class Template002a60e5b8 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<p>
	<a href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($carrierSettingsLink)) /* line 2 */;
		echo '" target="_blank">
		';
		echo LR\Filters::escapeHtmlText($translations['carrierSettingsLinkText']) /* line 3 */;
		echo '
	</a>
</p>
';
		return get_defined_vars();
	}

}
