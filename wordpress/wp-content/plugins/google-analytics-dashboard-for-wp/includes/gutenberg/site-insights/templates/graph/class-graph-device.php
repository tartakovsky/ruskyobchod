<?php
/**
 * Class that handles the output for the Device graph.
 *
 * Class ExactMetrics_SiteInsights_Template_Graph_Device
 */
class ExactMetrics_SiteInsights_Template_Graph_Device extends ExactMetrics_SiteInsights_Metric_Template {

	protected $metric = 'device';

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

		return "<div class='exactmetrics-graph-item exactmetrics-donut-chart exactmetrics-graph-{$this->metric}'>
			<script type='application/json'>{$json_data}</script>
		</div>";
	}

	/**
	 * Get AMP-compatible output for device breakdown
	 *
	 * @return string
	 */
	protected function get_amp_output() {
		if (empty($this->data['devices'])) {
			return false;
		}

		$data = $this->data['devices'];
		$title = __( 'Device Breakdown', 'google-analytics-dashboard-for-wp' );

		$html = "<div class='exactmetrics-amp-graph-item exactmetrics-amp-device-chart exactmetrics-amp-graph-{$this->metric}'>";
		$html .= "<div class='exactmetrics-amp-chart-title'>{$title}</div>";
		$html .= "<div class='exactmetrics-amp-device-container'>";
		
		foreach ($data as $device => $percentage) {
			$device_name = ucfirst($device);
			
			$html .= "<div class='exactmetrics-amp-device-item'>";
			$html .= "<div class='exactmetrics-amp-device-label'>{$device_name}</div>";
			$html .= "<div class='exactmetrics-amp-device-bar'>";
			$html .= "<div class='exactmetrics-amp-device-fill' style='width: {$percentage}%;'></div>";
			$html .= "</div>";
			$html .= "<div class='exactmetrics-amp-device-value'>{$percentage}%</div>";
			$html .= "</div>";
		}
		
		$html .= "</div>"; // Close device-container
		$html .= "</div>"; // Close graph-item

		return $html;
	}

	protected function get_options() {
		if (empty($this->data['devices'])) {
			return false;
		}

		$primaryColor = $this->attributes['primaryColor'];
		$secondaryColor = $this->attributes['secondaryColor'];
		$textColor = $this->attributes['textColor'];
		$data = $this->data['devices'];
		$labels = array();
		$series = array_values($data);

		foreach ($data as $key => $value){
			$labels[] = ucfirst($key);
		}

		$title = __( 'Device Breakdown', 'google-analytics-dashboard-for-wp' );

		$options = array(
			'series' => $series,
			'chart' => array(
				'width' => "100%",
				'height' => 'auto',
				'type' => 'donut',
			),
			'colors' => array( $primaryColor, $secondaryColor ),
			'title' => array(
				'text' => $title,
				'align' => 'left',
				'style' => array(
					'color' => $this->get_color_value($textColor),
					'fontSize' => '20px'
				)
			),
			'plotOptions' => array(
				'plotOptions' => array(
					'pie' => array(
						'donut' => array( 'size' => '75%' )
					)
				)
			),
			'legend' => array(
				'position' => 'right',
				'horizontalAlign' => 'center',
				'floating' => false,
				'fontSize' => '17px',
				'height' => '100%',
				'markers' => array(
					'width' => 30,
					'height' => 30,
					'radius' => 30
				),
				'formatter' => array(
					'args' => 'seriesName, opts',
					'body' => 'return [seriesName, "<strong> " + opts.w.globals.series[opts.seriesIndex] + "%</strong>"]'
				)
			),
			'dataLabels' => array(
				'enabled' => false
			),
			'labels' => $labels,
			'responsive' => array(
				0 => array(
					'breakpoint' => 767,
					'options' => array(
						'legend' => array(
							'show' => false
						)
					)
				)
			)
		);

		return $options;
	}
}