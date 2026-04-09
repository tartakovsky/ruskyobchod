<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/order/type-select.latte */
final class Templatec415a1b31a extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<select name="packetery_order_type js-wizard-packetery-order-type">
	<option value="">';
		echo LR\Filters::escapeHtmlText($translations['packetaMethodType']) /* line 2 */;
		echo '</option>
	<option value="carriers"';
		if ($packeteryOrderType === 'carriers') /* line 3 */ {
			echo ' selected="selected"';
		}
		echo '>';
		echo LR\Filters::escapeHtmlText($translations['carrierPackets']) /* line 3 */;
		echo '</option>
	<option value="packeta"';
		if ($packeteryOrderType === Packetery\Core\Entity\Carrier::INTERNAL_PICKUP_POINTS_ID) /* line 4 */ {
			echo '
		selected="selected"';
		}
		echo '>';
		echo LR\Filters::escapeHtmlText($translations['packetaPickupPointPackets']) /* line 5 */;
		echo '</option>
</select>
';
		$iterations = 0;
		foreach ($linkFilters as $name => $value) /* line 7 */ {
			echo '	<input type="hidden" name="';
			echo LR\Filters::escapeHtmlAttr($name) /* line 8 */;
			echo '" value="';
			echo LR\Filters::escapeHtmlAttr($value) /* line 8 */;
			echo '">
';
			$iterations++;
		}
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['name' => '7', 'value' => '7'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
