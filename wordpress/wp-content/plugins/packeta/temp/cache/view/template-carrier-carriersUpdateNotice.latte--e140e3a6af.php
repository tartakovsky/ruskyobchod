<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/carrier/carriersUpdateNotice.latte */
final class Templatee140e3a6af extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		if (isset($carriersUpdate['result'])) /* line 1 */ {
			echo '	<div class="notice notice-';
			echo LR\Filters::escapeHtmlAttr($carriersUpdate['resultClass']) /* line 2 */;
			echo ' is-dismissible">
		<p>
			';
			echo LR\Filters::escapeHtmlText($carriersUpdate['result']) /* line 4 */;
			echo '
		</p>
	</div>
';
		}
		return get_defined_vars();
	}

}
