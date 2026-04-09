<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/carrier/carriersUpdater.latte */
final class Templatecb3ca9cf74 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		$this->createTemplate('carriersUpdateNotice.latte', ['carriersUpdate' => $carriersUpdate] + $this->params, 'include')->renderToContentType('html') /* line 1 */;
		echo '
<div class="card">
	<h2>';
		echo LR\Filters::escapeHtmlText($translations['packeta']) /* line 4 */;
		echo ' - ';
		echo LR\Filters::escapeHtmlText($translations['carriersUpdate']) /* line 4 */;
		echo '</h2>

';
		if ($settingsChangedMessage) /* line 6 */ {
			echo '		<div class="notice notice-info packetery-no-side-margin">
			<p>
				';
			echo $settingsChangedMessage /* line 9 */;
			echo '
			</p>
		</div>
';
		}
		echo "\n";
		if ($isApiPasswordSet) /* line 14 */ {
			echo '		<p>
';
			if (isset($carriersUpdate['lastUpdate'])) /* line 16 */ {
				echo '				';
				echo LR\Filters::escapeHtmlText($translations['lastCarrierUpdateDatetime']) /* line 17 */;
				echo ': ';
				echo LR\Filters::escapeHtmlText($carriersUpdate['lastUpdate']) /* line 17 */;
				echo "\n";
			} else /* line 18 */ {
				echo '				';
				echo LR\Filters::escapeHtmlText($translations['carrierListNeverDownloaded']) /* line 19 */;
				echo "\n";
			}
			echo '		</p>

';
			if ($nextScheduledRun) /* line 23 */ {
				echo '			<p>
				';
				echo LR\Filters::escapeHtmlText($translations['nextScheduledRunPlannedAt']) /* line 25 */;
				echo ': ';
				echo LR\Filters::escapeHtmlText($nextScheduledRun) /* line 25 */;
				echo '
			</p>
';
			}
			echo '
		<p>
			<a href="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($carriersUpdate['link'])) /* line 30 */;
			echo '" class="button button-primary">';
			echo LR\Filters::escapeHtmlText($translations['runCarrierUpdate']) /* line 30 */;
			echo '</a>
		</p>
';
		} else /* line 32 */ {
			echo '		<p>
			';
			echo LR\Filters::escapeHtmlText($translations['pleaseCompleteSetupFirst']) /* line 34 */;
			echo '
		</p>
';
		}
		echo '</div>
';
		return get_defined_vars();
	}

}
