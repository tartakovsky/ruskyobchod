<?php
/**
 * Class that handles the output for the pageviews graph.
 *
 * Class ExactMetrics_SiteInsights_Template_Graph_Pageviews
 */
class ExactMetrics_SiteInsights_Template_Graph_Pageviews extends ExactMetrics_SiteInsights_Metric_Template {

	protected $metric = 'pageviews';

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
	 * Get AMP-compatible output for pageviews graph.
	 *
	 * @return string
	 */
	protected function get_amp_output() {
		if ( empty( $this->data['overviewgraph'] ) ) {
			return $this->get_amp_placeholder();
		}

		$data = $this->data['overviewgraph'];
		$pageviews_data = $data['pageviews']['datapoints'];
		$labels = $data['labels'];

		// Get the latest pageviews count
		$latest_pageviews = end( $pageviews_data );
		$previous_pageviews = prev( $pageviews_data );

		// Calculate change percentage
		$change_percentage = 0;
		if ( $previous_pageviews > 0 ) {
			$change_percentage = ( ( $latest_pageviews - $previous_pageviews ) / $previous_pageviews ) * 100;
		}

		$change_class = $change_percentage >= 0 ? 'positive' : 'negative';
		$change_icon = $change_percentage >= 0 ? '↗' : '↘';

		$output = '<div class="exactmetrics-amp-pageviews-block">';
		$output .= '<div class="exactmetrics-amp-header">';
		$output .= '<h3>' . esc_html__( 'Pageviews', 'google-analytics-dashboard-for-wp' ) . '</h3>';
		$output .= '<div class="exactmetrics-amp-metric">';
		$output .= '<span class="exactmetrics-amp-value">' . number_format( $latest_pageviews ) . '</span>';
		$output .= '<span class="exactmetrics-amp-change ' . esc_attr( $change_class ) . '">';
		$output .= '<span class="exactmetrics-amp-icon">' . $change_icon . '</span>';
		$output .= '<span class="exactmetrics-amp-percentage">' . number_format( abs( $change_percentage ), 1 ) . '%</span>';
		$output .= '</span>';
		$output .= '</div>';
		$output .= '</div>';

		// Show last 7 data points as a simple list
		$output .= '<div class="exactmetrics-amp-data-points">';
		$output .= '<h4>' . esc_html__( 'Last 7 days', 'google-analytics-dashboard-for-wp' ) . '</h4>';
		$output .= '<ul>';
		
		$recent_data = array_slice( array_combine( $labels, $pageviews_data ), -7 );
		foreach ( $recent_data as $label => $value ) {
			$output .= '<li>';
			$output .= '<span class="exactmetrics-amp-date">' . esc_html( $label ) . '</span>';
			$output .= '<span class="exactmetrics-amp-pageviews">' . number_format( $value ) . '</span>';
			$output .= '</li>';
		}
		
		$output .= '</ul>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get AMP placeholder when no data is available.
	 *
	 * @return string
	 */
	private function get_amp_placeholder() {
		return sprintf(
			'<div class="exactmetrics-amp-placeholder exactmetrics-pageviews-block">
				<p>%s</p>
			</div>',
			esc_html__( 'No pageviews data available', 'google-analytics-dashboard-for-wp' )
		);
	}

	protected function get_options() {
		if (empty($this->data['overviewgraph'])) {
			return false;
		}

		$primaryColor = $this->attributes['primaryColor'];
		$textColor = $this->attributes['textColor'];
		$secondaryColor = $this->attributes['secondaryColor'];

		$data = $this->data['overviewgraph'];

		$title = __( 'Page views', 'google-analytics-dashboard-for-wp' );

		$options = array(
			'series' => array(
				array(
					'name' => $title,
					'data' => $data['pageviews']['datapoints']
				)
			),
			'chart' => array(
				'height' => 450,
				'type' => 'area',
				'zoom' => array( 'enabled' => false ),
				'toolbar' => array( 'show' => false )
			),
			'dataLabels' => array( 'enabled' => false ),
			'stroke' => array(
				'curve' => 'straight',
				'width' => 4,
				'colors' => array( $primaryColor )
			),
			'fill' => array( 'type' => 'solid' ),
			'colors' => array( $secondaryColor ),
			'markers' => array(
				'style' => 'hollow',
				'size' => 5,
				'colors' => array( '#ffffff' ),
				'strokeColors' => $primaryColor
			),
			'title' => array(
				'text' => $title,
				'align' => 'left',
				'style' => array(
					'color' => $textColor,
					'fontSize' => '17px',
					'fontWeight' => 'bold',
				)
			),
			'grid' => array(
				'show' => true,
				'borderColor' => 'rgba(58, 147, 221, 0.15)',
				'xaxis' => array(
					'lines' => array( 'show' => true )
				),
				'yaxis' => array(
					'lines' => array( 'show' => true )
				),
				'padding' => array(
					'top' => 10,
					'right' => 10,
					'bottom' => 10,
					'left' => 10,
				)
			),
			'xaxis' => array( 'categories' => $data['labels'] ),
			'yaxis' => array( 'type' => 'numeric' ),
		);

		return $options;
	}


}