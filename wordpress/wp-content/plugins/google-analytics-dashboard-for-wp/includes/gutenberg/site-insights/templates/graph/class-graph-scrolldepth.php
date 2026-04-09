<?php
/**
 * Class that handles the output for the Scroll Depth graph.
 *
 * Class ExactMetrics_SiteInsights_Template_Graph_Scrolldepth
 */
class ExactMetrics_SiteInsights_Template_Graph_Scrolldepth extends ExactMetrics_SiteInsights_Metric_Template {

	protected $metric = 'scrolldepth';

	protected $type = 'graph';

	public function output(){
		// If we're in AMP, return AMP-compatible output
		if ( $this->is_amp() ) {
			return $this->get_amp_output();
		}

		$json_data = $this->get_json_data();

		if (empty($json_data)) {
			return false;
		}

		return "<div class='exactmetrics-graph-item exactmetrics-graph-{$this->metric}'>
			<script type='application/json'>{$json_data}</script>
		</div>";
	}

	/**
	 * Get AMP-compatible output for scroll depth
	 *
	 * @return string
	 */
	protected function get_amp_output() {
		if (!isset($this->data['scroll']) || empty($this->data['scroll']['average'])) {
			return false;
		}

		$value = $this->data['scroll']['average'];
		$title = __( 'Average Scroll Depth', 'google-analytics-dashboard-for-wp' );

		$html = "<div class='exactmetrics-amp-graph-item exactmetrics-amp-scroll-chart exactmetrics-amp-graph-{$this->metric}'>";
		$html .= "<div class='exactmetrics-amp-chart-title'>{$title}</div>";
		$html .= "<div class='exactmetrics-amp-scroll-container'>";
		$html .= "<div class='exactmetrics-amp-scroll-circle'>";
		$html .= "<div class='exactmetrics-amp-scroll-value'>{$value}%</div>";
		$html .= "</div>";
		$html .= "<div class='exactmetrics-amp-scroll-label'>" . __( 'Average Scroll Depth', 'google-analytics-dashboard-for-wp' ) . "</div>";
		$html .= "</div>"; // Close scroll-container
		$html .= "</div>"; // Close graph-item

		return $html;
	}

	protected function get_options() {
		if (!isset($this->data['scroll']) || empty($this->data['scroll']['average'])) {
			return false;
		}

		$primaryColor = $this->attributes['primaryColor'];
		$secondaryColor = $this->attributes['secondaryColor'];

		$value = $this->data['scroll']['average'];

		$title = __( 'Average Scroll Depth', 'google-analytics-dashboard-for-wp' );

		$options = array(
			'series' => array( $value ),
			'chart' => array(
				'height' => 350,
				'type' => 'radialBar',
			),
			'plotOptions' => array(
				'radialBar' => array(
					'size' => $value . '%',
				)
			),
			'colors' => array( $primaryColor, $secondaryColor ),
			'labels' => array( $title ),
		);

		return $options;
	}
}