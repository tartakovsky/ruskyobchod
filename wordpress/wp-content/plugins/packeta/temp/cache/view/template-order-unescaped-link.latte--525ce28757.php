<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/order/unescaped-link.latte */
final class Template525ce28757 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<a href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($link)) /* line 1 */;
		echo '" title="';
		echo LR\Filters::escapeHtmlAttr($title) /* line 1 */;
		echo '">';
		echo $escapedText /* line 1 */;
		echo '</a>
';
		return get_defined_vars();
	}

}
