<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/options/help-block-link.latte */
final class Template9a2e3d1266 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<a target="_blank" href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($href)) /* line 1 */;
		echo '">';
		echo LR\Filters::escapeHtmlText($href) /* line 1 */;
		echo '</a>
';
		return get_defined_vars();
	}

}
