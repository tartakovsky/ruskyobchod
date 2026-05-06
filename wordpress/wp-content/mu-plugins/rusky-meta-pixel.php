<?php
/**
 * Plugin Name: Rusky Meta Pixel
 * Description: Adds Meta Pixel tracking for core WooCommerce storefront events.
 */

defined( 'ABSPATH' ) || exit;

const RUSKY_META_PIXEL_ID = '988003717119707';

function rusky_meta_pixel_enabled(): bool {
	return ! is_admin() && RUSKY_META_PIXEL_ID !== '';
}

function rusky_meta_pixel_print_base(): void {
	if ( ! rusky_meta_pixel_enabled() ) {
		return;
	}
	?>
	<!-- Meta Pixel Code -->
	<script>
	!function(f,n){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
	n.callMethod.apply(n,arguments):n.queue.push(arguments)};
	if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[]}(window);
	(function(f,d,s,u){var loaded=false;function l(){if(loaded||f.fbq.loadedScript)return;loaded=true;f.fbq.loadedScript=true;
	var e=d.createElement(s);e.async=true;e.src=u;d.getElementsByTagName(s)[0].parentNode.insertBefore(e,d.getElementsByTagName(s)[0])}
	['pointerdown','keydown','touchstart','scroll'].forEach(function(eventName){f.addEventListener(eventName,l,{once:true,passive:true})});
	f.addEventListener('load',function(){setTimeout(l,7000)});
	})(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '<?php echo esc_js( RUSKY_META_PIXEL_ID ); ?>');
	fbq('track', 'PageView');
	</script>
	<noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id=<?php echo esc_attr( RUSKY_META_PIXEL_ID ); ?>&ev=PageView&noscript=1" /></noscript>
	<!-- End Meta Pixel Code -->
	<?php
}
add_action( 'wp_head', 'rusky_meta_pixel_print_base', 1 );

function rusky_meta_pixel_product_payload( WC_Product $product ): array {
	$product_id = $product->get_id();
	$price      = wc_get_price_to_display( $product );

	return array(
		'content_ids'  => array( (string) $product_id ),
		'content_type' => 'product',
		'content_name' => wp_strip_all_tags( $product->get_name() ),
		'value'        => (float) $price,
		'currency'     => get_woocommerce_currency(),
	);
}

function rusky_meta_pixel_print_event( string $event, array $payload ): void {
	if ( ! rusky_meta_pixel_enabled() ) {
		return;
	}

	printf(
		"<script>window.fbq && fbq('track', %s, %s);</script>\n",
		wp_json_encode( $event ),
		wp_json_encode( $payload )
	);
}

function rusky_meta_pixel_view_content(): void {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) {
		return;
	}

	rusky_meta_pixel_print_event( 'ViewContent', rusky_meta_pixel_product_payload( $product ) );
}
add_action( 'wp_footer', 'rusky_meta_pixel_view_content', 20 );

function rusky_meta_pixel_initiate_checkout(): void {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() || ! WC()->cart ) {
		return;
	}

	$ids = array();
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		if ( ! empty( $cart_item['product_id'] ) ) {
			$ids[] = (string) $cart_item['product_id'];
		}
	}

	rusky_meta_pixel_print_event(
		'InitiateCheckout',
		array(
			'content_ids'  => array_values( array_unique( $ids ) ),
			'content_type' => 'product',
			'value'        => (float) WC()->cart->get_total( 'edit' ),
			'currency'     => get_woocommerce_currency(),
		)
	);
}
add_action( 'wp_footer', 'rusky_meta_pixel_initiate_checkout', 20 );

function rusky_meta_pixel_purchase( int $order_id ): void {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$ids = array();
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( $product ) {
			$ids[] = (string) $product->get_id();
		}
	}

	rusky_meta_pixel_print_event(
		'Purchase',
		array(
			'content_ids'  => array_values( array_unique( $ids ) ),
			'content_type' => 'product',
			'value'        => (float) $order->get_total(),
			'currency'     => $order->get_currency(),
		)
	);
}
add_action( 'woocommerce_thankyou', 'rusky_meta_pixel_purchase', 20 );

function rusky_meta_pixel_add_to_cart_script(): void {
	if ( ! function_exists( 'is_woocommerce' ) || ! ( is_shop() || is_product_category() || is_product_tag() || is_product() ) ) {
		return;
	}
	?>
	<script>
	document.addEventListener('click', function(event) {
		var button = event.target.closest('.add_to_cart_button[data-product_id], button.single_add_to_cart_button');
		if (!button || !window.fbq) {
			return;
		}
		var productId = button.getAttribute('data-product_id');
		if (!productId) {
			var form = button.closest('form.cart');
			var productInput = form && form.querySelector('[name="add-to-cart"]');
			productId = productInput && productInput.value;
		}
		if (productId) {
			fbq('track', 'AddToCart', {
				content_ids: [String(productId)],
				content_type: 'product',
				currency: '<?php echo esc_js( get_woocommerce_currency() ); ?>'
			});
		}
	}, true);
	</script>
	<?php
}
add_action( 'wp_footer', 'rusky_meta_pixel_add_to_cart_script', 30 );
