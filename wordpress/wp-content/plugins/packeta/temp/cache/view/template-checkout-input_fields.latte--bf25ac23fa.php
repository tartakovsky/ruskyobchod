<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/checkout/input_fields.latte */
final class Templatebf25ac23fa extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		$iterations = 0;
		foreach ($fields as $fieldName) /* line 1 */ {
			echo '	<input name="';
			echo LR\Filters::escapeHtmlAttr($fieldName) /* line 2 */;
			echo '" id="';
			echo LR\Filters::escapeHtmlAttr($fieldName) /* line 2 */;
			echo '" type="hidden">
';
			$iterations++;
		}
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['fieldName' => '1'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
