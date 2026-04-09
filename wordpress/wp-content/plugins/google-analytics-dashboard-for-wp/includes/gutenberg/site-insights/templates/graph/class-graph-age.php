<?php
/**
 * Class that handles the output for the Age Breakdown graph.
 *
 * Class ExactMetrics_SiteInsights_Template_Graph_Age
 */
class ExactMetrics_SiteInsights_Template_Graph_Age extends ExactMetrics_SiteInsights_Metric_Template {

	protected $metric = 'age';

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
	 * Get AMP-compatible output for age breakdown
	 *
	 * @return string
	 */
	protected function get_amp_output() {
		if (empty($this->data['age'])) {
			return false;
		}

		$data = $this->data['age'];
		$title = __( 'Age Breakdown', 'google-analytics-dashboard-for-wp' );

		$html = "<div class='exactmetrics-amp-graph-item exactmetrics-amp-age-chart exactmetrics-amp-graph-{$this->metric}'>";
		$html .= "<div class='exactmetrics-amp-chart-title'>{$title}</div>";
		$html .= "<div class='exactmetrics-amp-age-container'>";
		
		foreach ($data as $age_data) {
			$age = $age_data['age'];
			$sessions = $age_data['sessions'];
			$percent = $age_data['percent'];
			
			$html .= "<div class='exactmetrics-amp-age-item'>";
			$html .= "<div class='exactmetrics-amp-age-label'>{$age}</div>";
			$html .= "<div class='exactmetrics-amp-age-bar'>";
			$html .= "<div class='exactmetrics-amp-age-fill' style='width: {$percent}%;'></div>";
			$html .= "</div>";
			$html .= "<div class='exactmetrics-amp-age-value'>{$sessions} ({$percent}%)</div>";
			$html .= "</div>";
		}
		
		$html .= "</div>"; // Close age-container
		$html .= "</div>"; // Close graph-item

		return $html;
	}

	protected function get_options() {
		if (empty($this->data['age'])) {
			return false;
		}

		$primaryColor = $this->attributes['primaryColor'];
		$secondaryColor = $this->attributes['secondaryColor'];
		$textColor = $this->attributes['textColor'];

		$data = $this->data['age'];

		$title = __( 'Age Breakdown', 'google-analytics-dashboard-for-wp' );

		$series = array();
		$percentages = array();
		$labels = array();

		foreach ($data as $key => $country) {
			$series[$key] = (int) $country['sessions'];
			$labels[$key] = $country['age'];
			$percentages[$key] = $country['percent'];
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
			'colors' => array( $primaryColor, $secondaryColor ),
			'title' => array(
				'text' => $title,
				'align' => 'left',
				'style' => array(
					'color' => $textColor
				)
			),
			'plotOptions' => array(
				'bar' => array(
					'borderRadius' => 5,
					'borderRadiusApplication' => 'end',
					'dataLabels' => array(
						'position' => 'top'
					)
				)
			),
			'dataLabels' => array(
				'enabled' => true,
				'offsetY' => -20,
				'style' => array(
					'fontSize' => '12px',
					'colors' => array( $primaryColor )
				),
			),
			'xaxis' => array(
				'categories' => $labels
			)
		);

		return $options;
	}
}