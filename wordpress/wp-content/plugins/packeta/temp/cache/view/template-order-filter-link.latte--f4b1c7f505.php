<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/order/filter-link.latte */
final class Templatef4b1c7f505 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<a href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($link)) /* line 1 */;
		echo '"';
		if ($active) /* line 1 */ {
			echo ' class="current" aria-current="page"';
		}
		echo '>';
		echo LR\Filters::escapeHtmlText($title) /* line 1 */;
		echo '
	<span class="count">(';
		echo LR\Filters::escapeHtmlText($orderCount) /* line 2 */;
		echo ')</span></a>
';
		return get_defined_vars();
	}

}
