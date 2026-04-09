<?php

use \Packetery\Latte\Runtime as LR;

/** source: /home/u595644545/domains/ruskyobchod.sk/public_html/wp-content/plugins/packeta/template/packeta-header.latte */
final class Templateea315be638 extends \Packetery\Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<div class="packeta_header">
	<div';
		echo ($ʟ_tmp = array_filter(['packeta_logo', $isCzechLocale ? 'packeta_logo-cs' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 2 */;
		echo '>
';
		if (true === $isCzechLocale) /* line 3 */ {
			echo '			<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($logoZasilkovna)) /* line 4 */;
			echo '" alt="';
			echo LR\Filters::escapeHtmlAttr($translations['packeta']) /* line 4 */;
			echo '">
';
		} else /* line 5 */ {
			echo '			<img src="';
			echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($logoPacketa)) /* line 6 */;
			echo '" alt="';
			echo LR\Filters::escapeHtmlAttr($translations['packeta']) /* line 6 */;
			echo '">
';
		}
		echo '	</div>
';
		ob_start(function () {});
		try {
			echo '	<h1>';
			ob_start();
			try {
				echo LR\Filters::escapeHtmlText($translations['title']) /* line 9 */;
			} finally {
				$ʟ_ifc[1] = rtrim(ob_get_flush()) === '';
			}
			echo '</h1>
';
		} finally {
			if ($ʟ_ifc[1] ?? null) {
				ob_end_clean();
			} else {
				echo ob_get_clean();
			}
		}
		echo '</div>
';
		return get_defined_vars();
	}

}
