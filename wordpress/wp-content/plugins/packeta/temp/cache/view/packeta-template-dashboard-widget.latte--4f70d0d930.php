<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/dashboard-widget.latte */
final class Template4f70d0d930 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		if (false === $isOptionsFormValid) /* line 1 */ {
			echo '	<p>
		<span class="dashicons dashicons-warning"></span> ';
			echo $translations['noGlobalSettings'] /* line 3 */;
			echo '
	</p>
';
		}
		echo "\n";
		if ($isOptionsFormValid && false === $hasPacketaShipping) /* line 7 */ {
			echo '	<p>
		<span class="dashicons dashicons-warning"></span> ';
			echo $translations['noPacketaShipping'] /* line 9 */;
			echo '
	</p>
';
		}
		echo "\n";
		if ($isOptionsFormValid && false === $hasExternalCarrier) /* line 13 */ {
			echo '	<p>
		<span class="dashicons dashicons-warning"></span> ';
			echo $translations['noExternalCarrier'] /* line 15 */;
			echo '
	</p>
';
		}
		echo "\n";
		if ($isOptionsFormValid && $isCodSettingNeeded) /* line 19 */ {
			echo '	<p>
		<span class="dashicons dashicons-warning"></span> ';
			echo $translations['noCodPaymentConfigured'] /* line 21 */;
			echo '
	</p>
';
		}
		echo "\n";
		if ($isOptionsFormValid) /* line 25 */ {
			if ($activeCountries) /* line 26 */ {
				echo '		<p>
			';
				echo LR\Filters::escapeHtmlText($translations['activeCountriesNotice']) /* line 28 */;
				echo ':
';
				$iterations = 0;
				foreach ($iterator = $ʟ_it = new LR\CachingIterator($activeCountries, $ʟ_it ?? null) as $activeCountry) /* line 29 */ {
					echo '				<a href="';
					echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($activeCountry['url'])) /* line 30 */;
					echo '">';
					echo LR\Filters::escapeHtmlText($activeCountry['name']) /* line 30 */;
					echo '</a>';
					if (!$iterator->isLast()) /* line 30 */ {
						echo ', ';
					}
					echo "\n";
					$iterations++;
				}
				$iterator = $ʟ_it = $ʟ_it->getParent();
				echo '		</p>
';
			} else /* line 33 */ {
				echo '		<p>
			<span class="dashicons dashicons-warning"></span> ';
				echo $translations['noActiveCountry'] /* line 35 */;
				echo '
		</p>
';
			}
		}
		echo "\n";
		if ($survey->active) /* line 40 */ {
			echo '	<hr>
	<p><strong>';
			echo LR\Filters::escapeHtmlText($translations['surveyTitle']) /* line 42 */;
			echo '</strong></p>
	<div class="packetery-survey">
		<div>';
			echo LR\Filters::escapeHtmlText($translations['surveyDescription']) /* line 44 */;
			echo '</p>
			<p><a href="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($survey->url)) /* line 45 */;
			echo '" target="_blank"
					class="button button-primary">';
			echo LR\Filters::escapeHtmlText($translations['surveyButtonText']) /* line 46 */;
			echo '</a></p>
		</div>
		<div>
			<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($survey->image)) /* line 49 */;
			echo '" alt="';
			echo LR\Filters::escapeHtmlAttr($translations['packeta']) /* line 49 */;
			echo '">
		</div>
	</div>
';
		}
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['activeCountry' => '29'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
