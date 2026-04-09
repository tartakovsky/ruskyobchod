<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/dashboard/dashboard-item.latte */
final class Templatec469e8498f extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<li>
	<div>
';
		if ($item->isFinished()) /* line 4 */ {
			echo '			<svg xmlns="http://www.w3.org/2000/svg" viewBox="-4 -4 32 32" width="32" height="32" aria-hidden="true" focusable="false">
				<path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path>
			</svg>
';
		} else /* line 8 */ {
			echo '			';
			echo LR\Filters::escapeHtmlText($item->getSortOrder()) /* line 9 */;
			echo "\n";
		}
		echo '	</div>
	<div>
		<h3>
';
		if ($item->isFinished()) /* line 14 */ {
			echo '				<del>
';
			if ($item->getUrl() !== null) /* line 16 */ {
				echo '						<a href="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($item->getUrl())) /* line 17 */;
				echo '">';
				echo LR\Filters::escapeHtmlText($item->getCaption()) /* line 17 */;
				echo '</a>
';
			} else /* line 18 */ {
				echo '						';
				echo LR\Filters::escapeHtmlText($item->getCaption()) /* line 19 */;
				echo "\n";
			}
			echo '				</del>
';
		} else /* line 22 */ {
			if ($item->getUrl() !== null) /* line 23 */ {
				echo '					<a href="';
				echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($item->getUrl())) /* line 24 */;
				echo '">';
				echo LR\Filters::escapeHtmlText($item->getCaption()) /* line 24 */;
				echo '</a>
';
			} else /* line 25 */ {
				echo '					';
				echo LR\Filters::escapeHtmlText($item->getCaption()) /* line 26 */;
				echo "\n";
			}
		}
		echo '		</h3>
		<p>';
		echo LR\Filters::escapeHtmlText($item->getDescription()) /* line 30 */;
		echo '</p>
	</div>
</li>
';
		return get_defined_vars();
	}

}
