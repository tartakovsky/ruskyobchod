<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/order/customs-declaration-form-template.latte */
final class Template7f46baf1d7 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo "\n";
		$formContainer = $formTemplate[Packetery\Module\Order\CustomsDeclarationMetabox::FORM_CONTAINER_NAME] /* line 3 */;
		$form = $this->global->formsStack[] = is_object($formTemplate) ? $formTemplate : $this->global->uiControl[$formTemplate] /* line 4 */;
		echo '<form id="';
		echo LR\Filters::escapeHtmlAttr(Packetery\Module\Order\CustomsDeclarationMetabox::FORM_ID) /* line 4 */;
		echo '_template" hidden';
		echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['id' => null, 'hidden' => null], false);
		echo '>
	<div data-packetery-customs-declaration-item>
';
		$iterations = 0;
		foreach ($formContainer['items']->getComponents() as $id => $item) /* line 6 */ {
			$this->createTemplate('customs-declaration-item.latte', ['container' => $formContainer['items'], 'id' => $id] + $this->params, 'include')->renderToContentType('html') /* line 7 */;
			$iterations++;
		}
		echo '	</div>
';
		echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line 4 */;
		echo '</form>
';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['id' => '6', 'item' => '6'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
