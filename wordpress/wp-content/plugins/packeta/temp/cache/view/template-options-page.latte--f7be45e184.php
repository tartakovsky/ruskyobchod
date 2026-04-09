<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/options/page.latte */
final class Templatef7be45e184 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		$this->createTemplate('../packeta-header.latte', $this->params, 'include')->renderToContentType('html') /* line 1 */;
		echo '
<div id="packetery-options-page" class="packeta_page">

	<nav class="nav-tab-wrapper">
		<a
				href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($generalTabLink)) /* line 7 */;
		echo '"
				class="nav-tab';
		if ($activeTab === Packetery\Module\Options\Page::TAB_GENERAL) /* line 8 */ {
			echo ' nav-tab-active';
		}
		echo '"
		>
			';
		echo LR\Filters::escapeHtmlText($translations['general']) /* line 10 */;
		echo '
		</a>
		<a
				href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($packetStatusSyncTabLink)) /* line 13 */;
		echo '"
				class="nav-tab';
		if ($activeTab === Packetery\Module\Options\Page::TAB_PACKET_STATUS_SYNC) /* line 14 */ {
			echo ' nav-tab-active';
		}
		echo '"
		>
			';
		echo LR\Filters::escapeHtmlText($translations['packetStatusSyncTabLinkLabel']) /* line 16 */;
		echo '
		</a>
		<a
				href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($autoSubmissionTabLink)) /* line 19 */;
		echo '"
				class="nav-tab';
		if ($activeTab === Packetery\Module\Options\Page::TAB_AUTO_SUBMISSION) /* line 20 */ {
			echo ' nav-tab-active';
		}
		echo '"
		>
			';
		echo LR\Filters::escapeHtmlText($translations['packetAutoSubmission']) /* line 22 */;
		echo '
		</a>
		<a
				href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($advancedTabLink)) /* line 25 */;
		echo '"
				class="nav-tab';
		if ($activeTab === Packetery\Module\Options\Page::TAB_ADVANCED) /* line 26 */ {
			echo ' nav-tab-active';
		}
		echo '"
		>
			';
		echo LR\Filters::escapeHtmlText($translations['advanced']) /* line 28 */;
		echo '
		</a>
		<a
				href="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($supportTabLink)) /* line 31 */;
		echo '"
				class="nav-tab';
		if ($activeTab === Packetery\Module\Options\Page::TAB_SUPPORT) /* line 32 */ {
			echo ' nav-tab-active';
		}
		echo '"
		>
			';
		echo LR\Filters::escapeHtmlText($translations['support']) /* line 34 */;
		echo '
		</a>
	</nav>

	<div class="packeta_tab_content">
';
		if (isset($error)) /* line 39 */ {
			echo '			<div class="notice notice-error">
				<p><strong>';
			echo LR\Filters::escapeHtmlText($error) /* line 41 */;
			echo '</strong></p>
			</div>
';
		}
		echo '
		';
		echo $messages /* line 45 */;
		echo '

';
		$this->createTemplate(('page-tabs/' . $activeTab . '.latte'), $this->params, 'include')->renderToContentType('html') /* line 47 */;
		echo '	</div>
</div>
';
		return get_defined_vars();
	}

}
