<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/carrier/wcNativeSettings.latte */
final class Template54f7468fe2 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		$this->createTemplate('../packeta-header.latte', $this->params, 'include')->renderToContentType('html') /* line 1 */;
		echo '
<div class="packetery-options-page packeta_page packetery-wc-native-carrier-settings">
';
		$this->createTemplate('carriersUpdater.latte', $this->params, 'include')->renderToContentType('html') /* line 4 */;
		echo '
	<h2 class="packeta-countries-header">';
		echo LR\Filters::escapeHtmlText($translations['packeta']) /* line 6 */;
		echo ' - ';
		echo LR\Filters::escapeHtmlText($translations['countries']) /* line 6 */;
		echo '</h2>

	<div class="packeta_carriers_filter">
		';
		echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form = $this->global->formsStack[] = is_object($form) ? $form : $this->global->uiControl[$form], []) /* line 9 */;
		echo '
			<div class="row packeta_filter_items">
				<input placeholder="';
		echo LR\Filters::escapeHtmlAttr($translations['searchPlaceholder']) /* line 11 */;
		echo '"';
		$ʟ_input = $_input = end($this->global->formsStack)[Packetery\Module\Carrier\CountryListingPage::PARAM_CARRIER_FILTER];
		echo $ʟ_input->getControlPart()->addAttributes(['placeholder' => null])->attributes() /* line 11 */;
		echo '>
				<button class="button button-primary">';
		echo LR\Filters::escapeHtmlText($form['filter']->getCaption()) /* line 12 */;
		echo '</button>
			</div>
		';
		echo \Packetery\Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack));
		echo '
	</div>

';
		$iterations = 0;
		foreach ($countries as $country) /* line 17 */ {
			echo '	<div>
		';
			if (!empty($country['allCarriers'])) /* line 18 */ {
				echo '
			<h3>
';
				if (null !== $country['flag']) /* line 20 */ {
					echo '				<img src="';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($country['flag'])) /* line 20 */;
					echo '" alt="';
					echo LR\Filters::escapeHtmlAttr($country['name']) /* line 20 */;
					echo '">
';
				}
				echo '				';
				echo LR\Filters::escapeHtmlText($country['name']) /* line 21 */;
				echo '
			</h3>
			<table class="packeta_country_list">
				<tr>
					<th>';
				echo LR\Filters::escapeHtmlText($translations['carrier']) /* line 25 */;
				echo '</th>
					<th>';
				echo LR\Filters::escapeHtmlText($translations['status']) /* line 26 */;
				echo '</th>
					<th>';
				echo LR\Filters::escapeHtmlText($translations['action']) /* line 27 */;
				echo '</th>
				</tr>
';
				$iterations = 0;
				foreach ($country['allCarriers'] as $carrier) /* line 29 */ {
					echo '				<tr class="packeta_country_list_item">
					<td>';
					echo LR\Filters::escapeHtmlText($carrier['name']) /* line 30 */;
					echo '</td>
					<td class="packetery-carrier-status ';
					if ($carrier['isActivatedByUser']) /* line 31 */ {
						echo 'packetery-carrier-status-active';
					} else /* line 31 */ {
						echo 'packetery-carrier-status-inactive';
					}
					echo '">
						';
					echo LR\Filters::escapeHtmlText($carrier['isActivatedByUser'] ? $translations['active'] : $translations['inactive']) /* line 32 */;
					echo '</td>
					<td>
						<a class="button button-small" href="';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($carrier['detailUrl'])) /* line 34 */;
					echo '">';
					echo LR\Filters::escapeHtmlText($translations['setUp']) /* line 34 */;
					echo '</a>
					</td>
				</tr>
';
					$iterations++;
				}
				echo '			</table>
';
			}
			echo '	</div>
';
			$iterations++;
		}
		echo "\n";
		if (!$hasCarriers) /* line 41 */ {
			echo '	<h3>';
			echo LR\Filters::escapeHtmlText($translations['noCarriersFound']) /* line 41 */;
			echo '</h3>
';
		}
		echo '
</div>
';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['carrier' => '29', 'country' => '17'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
