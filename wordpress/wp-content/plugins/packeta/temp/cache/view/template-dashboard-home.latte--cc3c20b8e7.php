<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/dashboard/home.latte */
final class Templatecc3c20b8e7 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		$this->createTemplate('../packeta-header.latte', $this->params, 'include')->renderToContentType('html') /* line 1 */;
		echo '
<div class="packetery_page">

';
		$this->createTemplate('../carrier/carriersUpdateNotice.latte', ['carriersUpdate' => $carriersUpdate] + $this->params, 'include')->renderToContentType('html') /* line 5 */;
		echo '
	<div class="packeta-dashboard">
		<h2>';
		echo LR\Filters::escapeHtmlText($translations['getTheMost']) /* line 8 */;
		echo '</h2>

		<ul>
';
		$iterations = 0;
		foreach ($items as $item) /* line 11 */ {
			$this->createTemplate('dashboard-item.latte', ['item' => $item] + $this->params, 'include')->renderToContentType('html') /* line 12 */;
			$iterations++;
		}
		echo '		</ul>
	</div>
</div>
';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['item' => '11'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
