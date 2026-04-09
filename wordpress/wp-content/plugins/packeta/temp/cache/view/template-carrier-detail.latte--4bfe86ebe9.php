<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/carrier/detail.latte */
final class Template4bfe86ebe9 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		$this->createTemplate('../packeta-header.latte', $this->params, 'include')->renderToContentType('html') /* line 1 */;
		echo '
<div class="packetery-options-page packeta_page">
	';
		echo $flashMessages /* line 4 */;
		echo "\n";
		settings_errors() /* line 5 */;
		echo '
	<div class="packeta_carrier_list">
';
		$this->createTemplate('carrier.latte', ['carrier_data' => $carrierTemplateData, 'displayCarrierTitle' => false] + $this->params, 'include')->renderToContentType('html') /* line 8 */;
		echo '	</div>
</div>
';
		return get_defined_vars();
	}

}
