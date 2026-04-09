<?php
/**
 * Class that handles the output for the Top Interests graph.
 *
 * Class ExactMetrics_SiteInsights_Template_Graph_Topinterests
 */
class ExactMetrics_SiteInsights_Template_Graph_Topinterests extends ExactMetrics_SiteInsights_Metric_Template {

	protected $metric = 'topinterests';

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
	 * Get AMP-compatible output for top interests
	 *
	 * @return string
	 */
	protected function get_amp_output() {
		if (empty($this->data['interest'])) {
			return false;
		}

		$data = $this->data['interest'];
		$title = __( 'Top Interests', 'google-analytics-dashboard-for-wp' );

		$html = "<div class='exactmetrics-amp-graph-item exactmetrics-amp-interests-chart exactmetrics-amp-graph-{$this->metric}'>";
		$html .= "<div class='exactmetrics-amp-chart-title'>{$title}</div>";
		$html .= "<div class='exactmetrics-amp-interests-container'>";
		
		foreach ($data as $interest) {
			$interest_name = $interest['interest'];
			$sessions = $interest['sessions'];
			$percent = $interest['percent'];
			
			$html .= "<div class='exactmetrics-amp-interest-item'>";
			$html .= "<div class='exactmetrics-amp-interest-label'>{$interest_name}</div>";
			$html .= "<div class='exactmetrics-amp-interest-bar'>";
			$html .= "<div class='exactmetrics-amp-interest-fill' style='width: {$percent}%;'></div>";
			$html .= "</div>";
			$html .= "<div class='exactmetrics-amp-interest-value'>{$sessions} ({$percent}%)</div>";
			$html .= "</div>";
		}
		
		$html .= "</div>"; // Close interests-container
		$html .= "</div>"; // Close graph-item

		return $html;
	}

	protected function get_options() {
		if (empty($this->data['interest'])) {
			return false;
		}

		$primaryColor = $this->attributes['primaryColor'];
		$secondaryColor = $this->attributes['secondaryColor'];
		$textColor = $this->attributes['textColor'];

		$data = $this->data['interest'];

		$title = __( 'Top Interests', 'google-analytics-dashboard-for-wp' );

		$series = array();
		$percentages = array();
		$labels = array_column($data, 'interest');

		foreach ($data as $key => $country) {
			$series[$key] = (int) $country['sessions'];
			$percentages[$key] = (int) $country['percent'];
		}

		$options = array(
			'series' => array(
				array(
					'name' => $title,
					'data' => $series,
				)
			),
			'chart' => array(
				'height' => 430,
				'type' => 'bar',
				'zoom' => array( 'enabled' => false ),
				'toolbar' => array( 'show' => false )
			),
			'dataLabels' => array(
				'enabled' => true,
				'style' => array(
					'fontSize' => '12px',
					'colors' => array( $textColor )
				)
			),
			'colors' => array( $primaryColor, $secondaryColor ),
			'title' => array(
				'text' => $title,
				'align' => 'left',
				'style' => array(
					'color' => $textColor,
					'fontSize' => '20px'
				)
			),
			'plotOptions' => array(
				'bar' => array(
					'horizontal' => true,
					'borderRadius' => 5,
					'borderRadiusApplication' => 'end',
					'dataLabels' => array(
						'position' => 'center'
					)
				)
			),
			'xaxis' => array(
				'categories' => $labels
			)
		);

		return $options;
	}
}