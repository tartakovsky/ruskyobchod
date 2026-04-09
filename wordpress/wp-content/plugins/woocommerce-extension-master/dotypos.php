<?php

/**
 * Plugin Name: Dotypos
 * Plugin URI: https://www.dotykacka.cz/woocommerce/
 * Description: The plugin enables connection and synchronization: categories, products and stock status between the e-shop and the Dotypos cash register system.
 * Version: 0.2.25
 * Author: Jagu s.r.o.
 * Author URI: https://jagu.cz/
 * Developer: DTK
 * Developer URI: https://dotykacka.cz/
 * Text Domain: dotypos
 * Domain Path: /dotypos
 *
 * Woo: 12345:342928dfsfhsf8429842374wdf4234sfd
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WooCommerce\Admin
 */

use App\Service\LicenceService;
use Automattic\WooCommerce\Admin\Features\Features;
use Automattic\WooCommerce\Admin\Features\Navigation\Menu;
use Automattic\WooCommerce\Admin\Features\Navigation\Screen;

use App\Service\DotyposService;
use Automattic\WooCommerce\Admin\Notes\Note;
use Automattic\WooCommerce\Admin\Notes\Notes;

require_once( plugin_dir_path( __FILE__ ) . '/libraries/action-scheduler/action-scheduler.php' );

require __DIR__.'/libraries/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	 'https://plugins.dotykacka.cz/?action=get_metadata&slug=dotypos',
	__FILE__,
	'dotypos'
);

//TODO due to webhooks refactor
$WC_Dotypos = null;

/**
 * Activation and deactivation hooks for WordPress
 */
function dotypos_extension_activate() {
	global $wpdb;
	global $dotypos_db_version;

	$table_name = $wpdb->prefix . Dotypos::DOTYPOS_WEBHOOK_TABLE;
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id varchar(100) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	update_option( 'dotypos_db_version', $dotypos_db_version );
	//Create default settings
    if(get_option(Dotypos::$keys['settings']) === false) {
	    update_option( Dotypos::$keys['settings'], Dotypos::DEFAULT_SETTINGS );
    }
}

register_activation_hook( __FILE__, 'dotypos_extension_activate' );

function dotypos_extension_deactivate() {
	global $wpdb;

	$table_name = $wpdb->prefix . Dotypos::DOTYPOS_WEBHOOK_TABLE;
	$sql = "DROP TABLE IF EXISTS $table_name;";

	//TODO delete webhooks functions are already created

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	delete_option( 'dotypos_db_version');
	Dotypos::remove_activity_panel_inbox_job_notes();
}

register_deactivation_hook( __FILE__, 'dotypos_extension_deactivate' );

if ( ! class_exists( 'Dotypos' ) ) :
	/**
	 * Dotypos core class
	 */
	class Dotypos {

		const ADMIN_JOB_NOTE_NAME = 'dotypos_job_note';

		const IMPORTED_CATEGORY_NAME = 'Imported from Woocommerce';

		const DOTYPOS_WEBHOOK_TABLE = 'dotypos_webhook_log';

        const LICENCE_SERVER = false;

	const DEFAULT_SETTINGS = [
	'debug'    => false,
	'licence' => [
	'registered' => false,
	'verified' => false,
	'expired' => false,
	],
	'dotypos'  => [
	'apiKey'      => null,
	'cloudId'     => null,
	'warehouseId' => null,
	'licenceKey' => null,
	'webhook' => [
	'movement' => [
	'id' => null
	],
	'product' => [
	'id' => null,
	'disabled' => false,
	]
	]
	],
	'category' => [
	'enabled'   => false,
	'syncTitle' => false,
	],
	'product'  => [
	'enabled'      => false,
	'syncTitle'    => false,
	'syncPrice'    => false,
	'syncVat'      => false,
	'syncCategory' => false,
	'syncEAN'      => false,
	'syncNote'     => false,
	'movement'     => [
	'syncFromDotypos'      => false,
	'syncToDotypos'        => false,
	'wcPairAttribute'      => null,
	'dotyposPairAttribute' => null
	],
	],
	];

		static $keys = [
			'settings' => 'dotypos_settings',
			'category' => [
				'field-id' => 'dotypos_product_cat_id',
			],
			'product' => [
				'field-id' => 'dotypos_product_id',
			],
            'cache' => [
	            'import_products_wizard' => 'import_products_wizard222211',
                'import_products_wizard_selected' => 'import_products_wizard_selected'
            ]
		];

		/**
		 * The single instance of the class.
		 */
		protected static $_instance = null;

		/**
		 * @var DotyposService
		 */
		public $dotyposService;

		/**
		 * @var LicenceService
		 */
		public $licenceService;

		/**
		 * Constructor.
		 */
		protected function __construct() {
			$this->includes();
			$this->init();
		}


		/**
		 * Main Extension Instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
				//TODO remove this bullshit
				global $WC_Dotypos;
				$WC_Dotypos = self::$_instance;
			}

			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			// Override this PHP function to prevent unwanted copies of your instance.
			//   Implement your own error or use `wc_doing_it_wrong()`
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			// Override this PHP function to prevent unwanted copies of your instance.
			//   Implement your own error or use `wc_doing_it_wrong()`
		}

		/**
		 * Function for loading dependencies.
		 */
		private function includes() {
			$loader = include __DIR__ . '/' . 'vendor/autoload.php';

			if ( ! $loader ) {
				throw new Exception( 'vendor/autoload.php missing please run `composer install`' );
			}
			Sentry\init([
                    'dsn' => 'https://6a5b977badb543b0845c02181bb0c298@sentry.jagu.cz/34' ,
					'max_request_body_size' => 'medium',
                    'before_send' => function (Sentry\Event $event) {
                        $isDotyposException = false;
                        foreach($event->getExceptions() as $exception) {
                            $stacktrace = $exception->getStacktrace();
                            if($stacktrace !== null) {
	                            foreach ( $stacktrace->getFrames() as $frame ) {
		                            if( strpos( $frame->getFile(), "/woocommerce-extension") !== false) {
			                            $isDotyposException = true;
                                    }
	                            }
                            }
                        }
	                    if($isDotyposException) {
		                    return $event;
	                    }
	                    return null;
                    }
            ]);
		}

		/**
		 * Function for getting everything set up and ready to run.
		 */
		private function init() {
            //Adds global js variables
            add_action( 'admin_head', 'dotypos_add_js_vars');
			//Categories
			$this->initCategories();
			//Init translations
            $this->initLocalizations();
            //Init products
            $this->initProducts();
            //Init movements
            $this->initMovements();
			//Services
			$settings             = get_option( Dotypos::$keys['settings'] );
			if (isset($settings['dotypos'])) {
				$this->dotyposService = new DotyposService( $settings['dotypos']['apiKey'], $settings['dotypos']['cloudId'] );
				$this->licenceService = new LicenceService();
				//Verify licence
				$cacheKey = 'dotypos_licence_verified';
				$verified = get_transient( $cacheKey );
				//TODO handle 500 from licence server
				if ( ! $verified ) {
                    if (self::LICENCE_SERVER) {
                        $response = $this->licenceService->verify($settings['dotypos']['licenceKey']);
                        if (!is_wp_error($response) && isset($response['body'])) {
                            $data = json_decode($response['body'], true);
                            if (!$data) {
                                exit;
                            }
                            if ($data['result']['valid']) {
                                $verified = true;
                            } else {
                                wc_get_logger()->debug('[Licence] Not valid ' . $data['result']['code'] . ': ' . $data['result']['message'], array('source' => 'dotypos_integration'));
                            }
                        }
                    }
                    else {
                        $verified = true;
                    }
					$settings['licence']['expired'] = ! $verified;
					update_option( Dotypos::$keys['settings'], $settings );
					set_transient( $cacheKey, $verified, 60 * 60 );
				}
			}

			// Set up cache management.
			// new My_Extension_Cache();

			// Initialize REST API.
			// new My_Extension_REST_API();

			// Set up email management.
			// new My_Extension_Email_Manager();

			// Register with some-action hook
			// add_action('some-action', 'my-extension-function');
		}

		private function initCategories() {
			add_action( 'product_cat_add_form_fields', 'dotypos_add_product_cat_term_fields' );
			add_action( 'product_cat_edit_form_fields', 'dotypos_edit_product_cat_term_fields', 10, 2 );
			add_action( 'created_product_cat', 'dotypos_save_product_cat_term_fields' );
			add_action( 'edited_product_cat', 'dotypos_save_product_cat_term_fields' );
			add_action( 'manage_edit-product_cat_columns', 'filter_cpt_columns' );
			add_action( 'manage_product_cat_custom_column', 'action_custom_columns_content', 10, 3 );
			add_action( 'quick_edit_custom_box', 'dotypos_quick_edit_product_cat_term_fields', 10, 3 );
			add_action( 'edited_product_cat', 'dotypos_quick_edit_save_category_field' );
			add_action( 'admin_print_footer_scripts-edit-tags.php', 'dotypos_quick_edit_product_cat_term_javascript' );

			add_action( 'init', [ $this, 'registerDotyposCategoryTaxonomy' ] );
		}

		private function initProducts() {
			add_action( 'manage_edit-product_columns', 'filter_cpt_product_columns' );
			add_action( 'manage_product_posts_custom_column', 'action_custom_product_columns_content', 10, 2 );
			add_action( 'woocommerce_update_product', array( $this, 'handle_product_updated'), 10, 1);

			//Webhooks
			add_action( 'parse_request', function( $wp ){
				if ( preg_match( '/^\/dotypos-webhook-product/', $_SERVER['REQUEST_URI'], $matches ) ) {
                    send_nosniff_header();
                    header('Cache-Control: no-cache');
                    header('Pragma: no-cache');
                    $this->processWebhookProduct();
					exit; // and exit
				}
			});
		}

		public function dotypos_set_script_translations() {
			$loaded = wp_set_script_translations( 'dotypos', 'dotypos', plugin_dir_path( __FILE__ ) . '/languages' );
			//wc_get_logger()->debug('Dotypos translations loaded: '.json_encode($loaded), array('source' => 'dotypos_integration'));
		}

		private function initLocalizations() {
            add_action( 'admin_enqueue_scripts', [$this, 'dotypos_set_script_translations'] );
		}

        public function initMovements() {
	        add_action( 'woocommerce_reduce_order_stock', array( $this, 'handle_reduce_order_stock' ), 10, 1 );
	        add_action( 'woocommerce_restore_order_stock', array( $this, 'handle_restore_order_stock' ), 10, 1 );

	        //Webhooks
	        add_action( 'parse_request', function( $wp ){
		        if ( preg_match( '/^\/dotypos-webhook-movement/', $_SERVER['REQUEST_URI'], $matches ) ) {
                    send_nosniff_header();
                    header('Cache-Control: no-cache');
                    header('Pragma: no-cache');
			        $this->processWebhookMovement();
			        exit; // and exit
		        }
	        });

        }

		/**
		 * Handle product stock change
		 * @param WC_Product_Simple $product
		 */
		public function handle_product_set_stock($product) {
			// Generic stock hooks are intentionally disabled for Dotypos writes.
			// Outbound sync must be driven only by WooCommerce order stock reduction/restoration.
			return;
		}

		/**
		 * Handle variation stock change
         * TODO not working anymore
		 * @param WC_Product_Simple $product
		 */
		public function handle_variation_set_stock($product) {
			$this->handle_product_set_stock($product);
		}

		/**
		 * Handle product stock change
		 */
		public function handle_update_product_stock_query($sql, $product_id_with_stock, $stock_quantity) {
			return $sql;
		}

		private function sync_order_stock_to_dotypos( $order, bool $restore = false ): void {
			$settings = get_option( Dotypos::$keys['settings'] );
			if ( empty( $settings['product']['movement']['syncToDotypos'] ) ) {
				return;
			}

			if ( is_numeric( $order ) ) {
				$order = wc_get_order( $order );
			}

			if ( ! $order instanceof WC_Order ) {
				return;
			}

			$meta_key = '_dotypos_stock_synced';
			$is_synced = $order->get_meta( $meta_key, true );
			if ( ! $restore && $is_synced ) {
				return;
			}
			if ( $restore && ! $is_synced ) {
				return;
			}

			$changes = [];
			$id_key  = Dotypos::$keys['product']['field-id'];
			foreach ( $order->get_items( 'line_item' ) as $item ) {
				$product = $item->get_product();
				if ( ! $product ) {
					continue;
				}

				$dotypos_id = $product->get_meta( $id_key );
				if ( empty( $dotypos_id ) ) {
					continue;
				}

				$resolved_qty = null;
				if ( function_exists( 'gastronom_resolve_dotypos_order_sync_quantity' ) ) {
					$resolved_qty = gastronom_resolve_dotypos_order_sync_quantity( $order, $item, $restore );
					if ( $resolved_qty === false ) {
						continue;
					}
				}

				$qty = $resolved_qty === null ? (float) $item->get_quantity() : (float) $resolved_qty;
				if ( $qty <= 0 ) {
					continue;
				}

				if ( ! isset( $changes[ $dotypos_id ] ) ) {
					$changes[ $dotypos_id ] = 0.0;
				}
				$changes[ $dotypos_id ] += $restore ? $qty : -$qty;
			}

			if ( empty( $changes ) ) {
				return;
			}

			$invoice_number = 'WC-ORDER-' . $order->get_id() . ( $restore ? '-RESTORE' : '-REDUCE' );
			foreach ( $changes as $dotypos_id => $change ) {
				if ( abs( $change ) > 0.000001 ) {
					$this->dotyposService->updateProductStock( $settings['dotypos']['warehouseId'], $dotypos_id, $change, $invoice_number );
				}
			}

			if ( $restore ) {
				$order->delete_meta_data( $meta_key );
			} else {
				$order->update_meta_data( $meta_key, 1 );
			}
			$order->save();
		}

		public function handle_reduce_order_stock( $order ): void {
			$this->sync_order_stock_to_dotypos( $order, false );
		}

		public function handle_restore_order_stock( $order ): void {
			$this->sync_order_stock_to_dotypos( $order, true );
		}

		//Synchronize product with Dotypos
		public function handle_product_updated( $product_id ) {
			$productId                = Dotypos::$keys['product']['field-id'];
			$categoryId                = Dotypos::$keys['category']['field-id'];
			$product = wc_get_product( $product_id );
			$settings          = get_option( Dotypos::$keys['settings'] );
			$payload = [];
			$dotyposId = get_post_meta( $product->get_id(), $productId, true );
			wc_get_logger()->debug('Updated product with WC ID: '.$product->get_id().' and DTK ID: '.$dotyposId, array('source' => 'dotypos_integration'));
			if ( ! empty( $dotyposId ) ) {
				//Title
				if ( $settings['product']['syncTitle'] ) {
					$payload['name'] = $product->get_title();
				}
				//Price
				if ( $settings['product']['syncPrice'] ) {
					$price = $product->get_price();
					//TODO check if not taxable
					$taxes = WC_Tax::get_rates_for_tax_class( get_post_meta( $product->get_id(), '_tax_class', true ) );
					if ( count( $taxes ) > 0 ) {
						$vat = floatval(reset($taxes)->tax_rate) / 100 + 1;
					} else {
						$vat = 1;
					}
					if ( wc_prices_include_tax() === true ) {
						$payload['priceWithoutVat'] = $price / $vat;
						$payload['priceWithVat'] = $price;
					} else {
						$payload['priceWithoutVat'] = $price;
						$payload['priceWithVat'] = $price / $vat;
					}
				}
				if ( $settings['product']['syncVat'] ) {
					$taxes = WC_Tax::get_rates_for_tax_class( get_post_meta( $product->get_id(), '_tax_class', true ) );
					if ( count( $taxes ) > 0 ) {
						$payload['vat'] = floatval(reset($taxes)->tax_rate) / 100 + 1;
					}
					else {
						$payload['vat'] = 1;
                    }
				}
				//Category
				if ( $settings['product']['syncCategory'] ) {
					$categories = $product->get_category_ids();
					if ( count( $categories ) > 0 ) {
						//Takes only first category
						$category  = $categories[0];
						$dotyposCategoryId = get_term_meta( $category, $categoryId, true );
						if ( $dotyposCategoryId ) {
							$payload['_categoryId'] = $dotyposCategoryId;
						}
					}
				}
				//EAN
				if ( $settings['product']['syncEAN'] ) {
					//TODO supports only one combination of pairing. Needs to be updated when new pairing options added.
					if ($settings['product']['movement']['dotyposPairAttribute'] == 'ean' ) {
						$payload['ean'] = [ get_post_meta( $product->get_id(), $settings['product']['movement']['wcPairAttribute'], true ) ];
					}
				}
				//Note
				if ( $settings['product']['syncNote'] ) {
					$note = get_post_meta( $product->get_id(), '_purchase_note', true );
					if($note) {
						$payload['notes'] = [ $note ];
					}
					else {
						$payload['notes'] = null;
					}

				}
				wc_get_logger()->debug('Payload for update product: '.json_encode($payload), array('source' => 'dotypos_integration'));
				if(!empty($payload)) {
					Dotypos::instance()->dotyposService->patchProduct($dotyposId, $payload );
				}
			}
		}

		public static function add_activity_panel_inbox_note( string $title, string $content, array $actions ) {
			if ( ! class_exists( 'Automattic\WooCommerce\Admin\Notes\Notes' ) ) {
				return;
			}
			if ( ! class_exists( 'WC_Data_Store' ) ) {
				return;
			}

			$note = new Note();
			$note->set_title( $title );
			$note->set_content( $content );
			$note->set_type( Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
			$note->set_layout( 'plain' );
			$note->set_image( '' );
			$note->set_source( 'dotypos' );
			$note->set_name( self::ADMIN_JOB_NOTE_NAME );
			//Support up to 2 actions
			$actions = array_slice( $actions, 0, 2 );
			foreach ( $actions as $action ) {
				$note->add_action(
					$action[0], $action[1], $action[2]
				);
			}

			// Save the note to lock in our changes.
			$note->save();
		}

		public static function remove_activity_panel_inbox_job_notes() {
			if ( ! class_exists( 'Automattic\WooCommerce\Admin\Notes\Notes' ) ) {
				return;
			}
			Notes::delete_notes_with_name( self::ADMIN_JOB_NOTE_NAME );
		}

		public function registerDotyposCategoryTaxonomy() {
			$labels = array(
				'name'          => _x( 'Dotypos Category', 'taxonomy general name', 'dotypos' ),
				'singular_name' => _x( 'Dotypos Category', 'taxonomy singular name', 'dotypos' ),
				'search_items'  => __( 'Search Dotypos Category', 'dotypos' ),
				'all_items'     => __( 'All Dotypos Categories', 'dotypos' ),
				'edit_item'     => __( 'Edit Dotypos Category', 'dotypos' ),
				'update_item'   => __( 'Update Dotypos Category', 'dotypos' ),
				'add_new_item'  => __( 'Add New Dotypos Category', 'dotypos' ),
				'new_item_name' => __( 'New Dotypos Category Name', 'dotypos' ),
				'menu_name'     => __( 'Dotypos Category', 'dotypos' ),
			);
			$args   = array(
				'hierarchical'      => false,
				'labels'            => $labels,
				'show_ui'           => false,
				'show_admin_column' => false,
				'query_var'         => true,
				//'rewrite'           => [ 'slug' => 'dotypos-category' ],
				'public'            => false,
			);
			register_taxonomy( 'dotypos_product_cat', [ 'product' ], $args );
		}

        public function createDotyposWebhookMovement() {
	        $settings          = get_option( Dotypos::$keys['settings'] );
	        if($settings['dotypos']['webhook']['movement']['id'] !== null) {
		        $this->deleteDotyposWebhookMovement();
	        }
	            $payload = [
	                    '_cloudId' => $settings['dotypos']['cloudId'],
                    '_warehouseId' =>  $settings['dotypos']['warehouseId'],
                    'method' => 'POST',
                    'url' => get_site_url().'/dotypos-webhook-movement',
                    'payloadEntity' => 'STOCKLOG',
                    'payloadVersion' => 'V1'
                ];
	            //wc_get_logger()->debug('Creating webhook movement with payload: '.json_encode($payload), array('source' => 'dotypos_integration'));
	            $webhook = $this->dotyposService->postWebhook($payload);
	            $id = $webhook['id'];
		        $settings['dotypos']['webhook']['movement']['id'] = $id;
		        update_option( Dotypos::$keys['settings'], $settings );
		        return $id;
        }

		public function deleteDotyposWebhookMovement() {
			$settings          = get_option( Dotypos::$keys['settings'] );
			if($settings['dotypos']['webhook']['movement']['id'] !== null) {
				$webhook = $this->dotyposService->deleteWebhook($settings['dotypos']['webhook']['movement']['id']);
				$settings['dotypos']['webhook']['movement']['id'] = null;
				update_option( Dotypos::$keys['settings'], $settings );
			}
		}

		public function createDotyposWebhookProduct() {
			$settings          = get_option( Dotypos::$keys['settings'] );
			if($settings['dotypos']['webhook']['product']['id'] !== null) {
			    $this->deleteDotyposWebhookProduct();
			}
				$payload = [
					'_cloudId' => $settings['dotypos']['cloudId'],
					'method' => 'POST',
					'url' => get_site_url().'/dotypos-webhook-product',
					'payloadEntity' => 'PRODUCT',
					'payloadVersion' => 'V1'
				];
				$webhook = $this->dotyposService->postWebhook($payload);
				$id = $webhook['id'];
				$settings['dotypos']['webhook']['product']['id'] = $id;
				update_option( Dotypos::$keys['settings'], $settings );
				return $id;
		}

		public function deleteDotyposWebhookProduct() {
			$settings          = get_option( Dotypos::$keys['settings'] );
			if($settings['dotypos']['webhook']['product']['id'] !== null) {
				$webhook = $this->dotyposService->deleteWebhook($settings['dotypos']['webhook']['product']['id']);
				$settings['dotypos']['webhook']['product']['id'] = null;
				update_option( Dotypos::$keys['settings'], $settings );
			}
		}

		public static function getTaxRates() {
			$all_tax_rates = [];
			$tax_classes = WC_Tax::get_tax_classes(); // Retrieve all tax classes.
			if ( !in_array( '', $tax_classes ) ) { // Make sure "Standard rate" (empty class name) is present.
				array_unshift( $tax_classes, '' );
			}
			foreach ( $tax_classes as $tax_class ) { // For each tax class, get all rates.
				$taxes = WC_Tax::get_rates_for_tax_class( $tax_class );
				$all_tax_rates = array_merge( $all_tax_rates, $taxes );
			}
			return $all_tax_rates;
        }

        private function apply_stock_visibility_threshold( WC_Product $product ): void
        {
            $qty = (float) $product->get_stock_quantity();
            $isWeighted = get_post_meta( $product->get_id(), '_gls_weighted', true ) === 'yes';
            $threshold = $isWeighted ? 0.1 : 0.0;

            if ( $qty <= $threshold ) {
                $product->set_stock_status( 'outofstock' );
                $product->save();

                if ( get_post_status( $product->get_id() ) !== 'draft' ) {
                    wp_update_post( [ 'ID' => $product->get_id(), 'post_status' => 'draft' ], false, false );
                }
                return;
            }

            $product->set_stock_status( 'instock' );
            $product->save();

            if ( get_post_status( $product->get_id() ) !== 'publish' ) {
                wp_update_post( [ 'ID' => $product->get_id(), 'post_status' => 'publish' ], false, false );
            }
        }

        private function processWebhookMovement(): void
        {
            global $wpdb;
            $log_table_name = $wpdb->prefix . Dotypos::DOTYPOS_WEBHOOK_TABLE;
            $settings             = get_option( Dotypos::$keys['settings'] );
            if($settings['product']['movement']['syncFromDotypos']) {
                if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

                    wc_get_logger()->debug( 'Dotypos movement webhook arrived...', array( 'source' => 'dotypos_integration' ) );

                    //disable dtk sync
                    global $WC_Dotypos;
                    remove_action( 'woocommerce_update_product_stock_query', [ $WC_Dotypos, 'handle_update_product_stock_query' ] );
                    remove_action( 'woocommerce_product_set_stock', [ $WC_Dotypos, 'handle_product_set_stock' ] );
                    remove_action( 'woocommerce_variation_set_stock', [ $WC_Dotypos, 'handle_variation_set_stock' ] );


                    $content = file_get_contents( 'php://input' );

                    if ( empty( $content ) ) {
                        $payloads = [];
                    } else {
                        $payloads = json_decode( $content, true );

                        if ( count( $payloads ) === 0 ) {
                            $payloads = [];
                        }
                    }
                    foreach ( $payloads as $stockmovement ) {
                        if ( ! isset( $stockmovement['stocklogid'], $stockmovement['warehouseid'], $stockmovement['quantity'] ) ) {
                            wc_get_logger()->debug( 'Webhook requested with missing required fields [stocklogid,warehouseid,quantity]', array( 'source' => 'dotypos_integration' ) );
                        }

                        //wc_get_logger()->debug( 'Webhook payload: ' . json_encode( $stockmovement ), array( 'source' => 'dotypos_integration' ) );


                        //TODO to constant
                        if ( isset( $stockmovement['invoicenumber'] )
                            && strpos( (string) $stockmovement['invoicenumber'], 'WC' ) === 0 ) { // skip loop
                            continue;
                        }

                        $dotykackaWarehouseId = (int) $stockmovement['warehouseid'];
                        $quantity             = (float) $stockmovement['quantity'];
                        $stocklogid           = (string) $stockmovement['stocklogid'];
                        $product_id           = (int) $stockmovement['product_id']; // in docs is float waat?

                        //Skip wrong warehouse id
                        if ( $dotykackaWarehouseId != $settings['dotypos']['warehouseId'] ) {
                            continue;
                        }

                        //Check for in db exists
                        $logCnt = $wpdb->get_var(
                            $wpdb->prepare(
                                "
            SELECT COUNT(*)
            FROM $log_table_name
            WHERE id = %s
        ",
                                $stocklogid
                            )
                        );
                        if ( $logCnt != 0 ) {
                            continue; // skip processed
                        }

                        $wpdb->query(
                            $wpdb->prepare(
                                "
      INSERT INTO $log_table_name
      ( id )
      VALUES ( %s )
      ",
                                $stocklogid
                            )
                        );

                        //Process
                        $product = $wpdb->get_var(
                            $wpdb->prepare(
                                "
            SELECT COUNT(*)
            FROM $log_table_name
            WHERE id = %s
        ",
                                $stocklogid
                            )
                        );

                        if ( ! empty( $product_id ) ) {

                            $query = new WP_Query( array(
                                'post_type'      => 'product',
                                'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                                'posts_per_page' => -1,
                                'meta_query' => array(
                                    array(
                                        'key'     => 'dotypos_product_id',
                                        'value'   => $product_id,
                                        'compare' => '=',
                                    )
                                )
                            ) );

                            $products = $query->get_posts();
                            if ( count( $products ) === 1 ) {
                                $product = wc_get_product( $products[0]->ID );
                                $exactQuantity = null;

                                try {
                                    $productDetail = Dotypos::instance()->dotyposService->getProductOnWarehouse(
                                        $settings['dotypos']['warehouseId'],
                                        (string) $product_id
                                    );

                                    if ( is_array( $productDetail ) && isset( $productDetail['stockQuantityStatus'] ) ) {
                                        $exactQuantity = (float) $productDetail['stockQuantityStatus'];
                                    }
                                } catch ( \Throwable $e ) {
                                    wc_get_logger()->debug(
                                        '[Webhook] Exact stock fetch failed for ID ' . $product_id . ': ' . $e->getMessage(),
                                        array( 'source' => 'dotypos_integration' )
                                    );
                                }

                                if ( $exactQuantity === null ) {
                                    $exactQuantity = (float) $product->get_stock_quantity() + $quantity;
                                }

                                if ( ! function_exists( 'gastronom_apply_dotypos_stock_to_wc_product' ) || ! gastronom_apply_dotypos_stock_to_wc_product( $product, $exactQuantity ) ) {
                                    $product->set_stock_quantity( $exactQuantity );
                                    $product->save();
                                    $this->apply_stock_visibility_threshold( $product );
                                }
                            } else {
                                wc_get_logger()->debug( '[Webhook] More than exactly one paired product for ID: ' . $product_id, array( 'source' => 'dotypos_integration' ) );
                            }
                        }

                    }
                }
            }
        }

        private function processWebhookProduct(): void {
            global $wpdb;
            $log_table_name = $wpdb->prefix . Dotypos::DOTYPOS_WEBHOOK_TABLE;
            $settings       = get_option( Dotypos::$keys['settings'] );

            if ( $settings['dotypos']['webhook']['product']['disabled'] === false && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

                wc_get_logger()->debug( 'Dotypos product webhook arrived...', array( 'source' => 'dotypos_integration' ) );

                //disable dtk sync
                global $WC_Dotypos;
                remove_action( 'woocommerce_update_product', [ $WC_Dotypos, 'handle_product_updated' ] );


                $content = file_get_contents( 'php://input' );

                if ( empty( $content ) ) {
                    $payloads = [];
                } else {
                    $payloads = json_decode( $content, true );

                    if ( count( $payloads ) === 0 ) {
                        $payloads = [];
                    }
                }
                foreach ( $payloads as $product ) {

                    //wc_get_logger()->debug( 'Webhook product payload: ' . json_encode( $product ), array( 'source' => 'dotypos_integration' ) );

                    $productId  = Dotypos::$keys['product']['field-id'];
                    $categoryId = Dotypos::$keys['category']['field-id'];
                    $settings   = get_option( Dotypos::$keys['settings'] );
                    $query      = new WP_Query( array(
                        'post_type'      => 'product',
                        'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'trash' ),
                        'posts_per_page' => -1,
                        'meta_query'     => array(
                            array(
                                'key'     => $productId,
                                'value'   => $product['productid'],
                                'compare' => '=',
                            )
                        )
                    ) );

                    $products = $query->get_posts();
                    $primaryProduct = null;
                    if ( count( $products ) >= 1 ) {
                        foreach ( $products as $candidate ) {
                            if ( $candidate->post_status === 'publish' ) {
                                $primaryProduct = $candidate;
                                break;
                            }
                        }
                        if ( $primaryProduct === null ) {
                            foreach ( $products as $candidate ) {
                                if ( $candidate->post_status !== 'trash' ) {
                                    $primaryProduct = $candidate;
                                    break;
                                }
                            }
                        }
                        if ( $primaryProduct === null ) {
                            $primaryProduct = $products[0];
                        }
                    }
                    //wc_get_logger()->debug( 'Webhook updated found WC products: ' . json_encode( $products ), array( 'source' => 'dotypos_integration' ) );
                    if ( count( $products ) >= 1 && $primaryProduct !== null ) {
                        //wc_get_logger()->debug( 'Webhook updated WC ' . $products[0]->ID . ' product.', array( 'source' => 'dotypos_integration' ) );
                        //Title
                        // Handle deletion: if product deleted in Dotypos, hide on site
                        if ( isset( $product['deleted'] ) && $product['deleted'] === true ) {
                            foreach ( $products as $matchedProduct ) {
                                if ( $matchedProduct->post_status !== 'trash' ) {
                                    wp_update_post( [ 'ID' => $matchedProduct->ID, 'post_status' => 'draft' ], false, false );
                                    update_post_meta( $matchedProduct->ID, '_stock', '0' );
                                    update_post_meta( $matchedProduct->ID, '_stock_status', 'outofstock' );
                                }
                            }
                            wc_get_logger()->debug( '[Webhook] Product deleted in Dotypos, hidden matched WC products for Dotypos ID ' . $product['productid'], array( 'source' => 'dotypos_integration' ) );
                            continue;
                        }
                        if ( $primaryProduct->post_status !== 'publish' ) {
                            wp_update_post( [ 'ID' => $primaryProduct->ID, 'post_status' => 'publish' ], false, false );
                        }
                        if ( count( $products ) > 1 ) {
                            foreach ( $products as $matchedProduct ) {
                                if ( $matchedProduct->ID !== $primaryProduct->ID && $matchedProduct->post_status !== 'trash' ) {
                                    wp_update_post( [ 'ID' => $matchedProduct->ID, 'post_status' => 'draft' ], false, false );
                                }
                            }
                            wc_get_logger()->debug( '[Webhook product] Resolved duplicate WC products for ID: ' . $product['productid'] . ', primary WC ID ' . $primaryProduct->ID, array( 'source' => 'dotypos_integration' ) );
                        }
                        if ( $settings['product']['syncTitle'] ) {
                            $error = wp_update_post( [
                                'ID'    => $primaryProduct->ID,
                                'post_title' => $product['name']
                            ], true, false );
                            //wc_get_logger()->debug( 'Updated product '.json_encode($error), array( 'source' => 'dotypos_integration' ) );
                        }
                        //Price
                        if ( $settings['product']['syncPrice'] ) {
                            if ( wc_prices_include_tax() === true ) {
                                $price = $product['pricewithvat'];
                            } else {
                                $price = $product['pricewithoutvat'];
                            }
                            update_post_meta($primaryProduct->ID, '_price', $price);
                            update_post_meta($primaryProduct->ID, '_regular_price', $price);
                            //wc_get_logger()->debug( 'Updating price of product ID: '.$products[0]->ID.' Price: '.$price, array( 'source' => 'dotypos_integration' ) );
                        }
                        if ( $settings['product']['syncVat'] ) {
                            $all_tax_rates = Dotypos::getTaxRates();
                            foreach ($all_tax_rates AS $rate) {
                                //wc_get_logger()->debug(round($rate->tax_rate, 2).' == '.round((($dotyposProduct['vat']-1)*100), array('source' => 'dotypos_integration'), 2));
                                if(round($rate->tax_rate, 2) == round((($product['vat']-1)*100), 2)) {
                                    update_post_meta($primaryProduct->ID, '_tax_status', 'taxable');
                                    update_post_meta($primaryProduct->ID, '_tax_class', $rate->tax_rate_class);
                                }
                            }
                        }
                        //Category
                        $dotyposCategories = [];
                        $categories        = get_terms( array(
                            'taxonomy'   => 'product_cat',
                            'hide_empty' => false
                        ) );
                        foreach ( $categories as $category ) {
                            $dotyposId = get_term_meta( $category->term_id, $categoryId, true );
                            if ( ! empty( $dotyposId ) ) {
                                $dotyposCategories[ $dotyposId ] = $category->term_id;
                            }
                        }
                        if ( $settings['product']['syncCategory'] ) {
                            if ( isset( $dotyposCategories[ $product['categoryid'] ] ) ) {
                                wp_set_object_terms( $primaryProduct->ID, $dotyposCategories[ $product['categoryid'] ], 'product_cat' );
                            }
                        }
                        //EAN
                        if ( $settings['product']['syncEAN'] ) {
                            //wc_get_logger()->debug( 'Updating EAN ' . $product['ean'], array( 'source' => 'dotypos_integration' ) );
                            //TODO supports only one combination of pairing. Needs to be updated when new pairing options added.
                            if ( $settings['product']['movement']['wcPairAttribute'] == 'sku' && $settings['product']['movement']['dotyposPairAttribute'] == 'ean' ) {
                                if ( is_array( $product['ean'] ) ) {
                                    $ean = $product['ean'][0];
                                } else {
                                    $ean = explode(PHP_EOL, $product['ean'])[0];
                                }
                                //wc_get_logger()->debug( 'Updating EAN ' .$ean, array( 'source' => 'dotypos_integration' ) );
                                update_post_meta($primaryProduct->ID, '_sku', $ean);
                            }
                        }
                        //Note
                        if ( $settings['product']['syncNote'] ) {
                            if ( is_array( $product['noteslist'] ) ) {
                                $note = $product['noteslist'][0];
                            } else {
                                $note = $product['noteslist'];
                            }
                            update_post_meta($primaryProduct->ID, '_purchase_note', $note);
                        }
                    } else if (count( $products ) === 0) {
                        // AUTO-CREATE: new product from Dotypos not yet on site
                        if ( isset( $product['deleted'] ) && $product['deleted'] === true ) {
                            wc_get_logger()->debug( '[Webhook] Skipping deleted new product: ' . $product['name'] . ' (Dotypos ID: ' . $product['productid'] . ')', array( 'source' => 'dotypos_integration' ) );
                            continue;
                        }
                        $newProductId = wp_insert_post( array(
                            'post_title'   => $product['name'],
                            'post_content' => '',
                            'post_status'  => 'publish',
                            'post_type'    => 'product'
                        ), true );
                        if ( ! is_wp_error( $newProductId ) ) {
                            wp_set_object_terms( $newProductId, 'simple', 'product_type' );
                            // Pair with Dotypos
                            update_post_meta( $newProductId, $productId, $product['productid'] );
                            // Category
                            $dotyposCategoriesNew = [];
                            $categoriesNew = get_terms( array(
                                'taxonomy'   => 'product_cat',
                                'hide_empty' => false
                            ) );
                            foreach ( $categoriesNew as $category ) {
                                $dotyposId = get_term_meta( $category->term_id, $categoryId, true );
                                if ( ! empty( $dotyposId ) ) {
                                    $dotyposCategoriesNew[ $dotyposId ] = $category->term_id;
                                }
                            }
                            if ( isset( $dotyposCategoriesNew[ $product['categoryid'] ] ) ) {
                                wp_set_object_terms( $newProductId, $dotyposCategoriesNew[ $product['categoryid'] ], 'product_cat' );
                            } else {
                                wp_set_object_terms( $newProductId, 1, 'product_cat' );
                            }
                            // Price
                            if ( wc_prices_include_tax() === true ) {
                                $price = $product['pricewithvat'];
                            } else {
                                $price = $product['pricewithoutvat'];
                            }
                            update_post_meta( $newProductId, '_price', $price );
                            update_post_meta( $newProductId, '_regular_price', $price );
                            // VAT
                            $all_tax_rates_new = Dotypos::getTaxRates();
                            foreach ( $all_tax_rates_new as $rate ) {
                                if ( round( $rate->tax_rate, 2 ) == round( ( ( $product['vat'] - 1 ) * 100 ), 2 ) ) {
                                    update_post_meta( $newProductId, '_tax_status', 'taxable' );
                                    update_post_meta( $newProductId, '_tax_class', $rate->tax_rate_class );
                                }
                            }
                            // EAN/SKU
                            if ( isset( $product['ean'] ) ) {
                                if ( is_array( $product['ean'] ) ) {
                                    $ean = $product['ean'][0];
                                } else {
                                    $ean = explode( PHP_EOL, $product['ean'] )[0];
                                }
                                update_post_meta( $newProductId, '_sku', $ean );
                            }
                            // Stock management
                            update_post_meta( $newProductId, '_manage_stock', 'yes' );
                            update_post_meta( $newProductId, '_backorders', 'yes' );
                            update_post_meta( $newProductId, '_stock', 0 );
				wc_update_product_stock_status( $newProductId, 'outofstock' );
                            update_post_meta( $newProductId, '_visibility', 'visible' );
                            update_post_meta( $newProductId, 'total_sales', '0' );
                            // Note
                            if ( isset( $product['noteslist'] ) ) {
                                if ( is_array( $product['noteslist'] ) ) {
                                    $note = $product['noteslist'][0];
                                } else {
                                    $note = $product['noteslist'];
                                }
                                update_post_meta( $newProductId, '_purchase_note', $note );
                            }
                            wc_get_logger()->debug( '[Webhook] Auto-created product: ' . $product['name'] . ' (Dotypos ID: ' . $product['productid'] . ', WC ID: ' . $newProductId . ')', array( 'source' => 'dotypos_integration' ) );
                        } else {
                            wc_get_logger()->debug( '[Webhook] Error creating product: ' . $product['name'] . ' - ' . json_encode( $newProductId ), array( 'source' => 'dotypos_integration' ) );
                        }
                    }
                }
            }
        }

	}
endif;

/**
 * Function for delaying initialization of the extension until after WooComerce is loaded.
 */
function dotypos_initialize() {

	// This is also a great place to check for the existence of the WooCommerce class
	if ( ! class_exists( 'WooCommerce' ) ) {
		// You can handle this situation in a variety of ways,
		//   but adding a WordPress admin notice is often a good tactic.
		return;
	}

	load_plugin_textdomain( 'dotypos', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	$GLOBALS['dotypos'] = Dotypos::instance();
}

add_action( 'plugins_loaded', 'dotypos_initialize', 10 );

/**
 * Register menu
 */
function dotypos_register_menu_items() {

	if (
		! method_exists( Screen::class, 'register_post_type' ) ||
		! method_exists( Menu::class, 'add_plugin_item' ) ||
		! method_exists( Menu::class, 'add_plugin_category' ) ||
		! Features::is_enabled( 'navigation' )
	) {
		/*
		function page_content(){
			echo '<div class="wrap"><h2>Testing</h2></div>';
		}

		add_menu_page( 'Dotypos', 'Dotypos', 'manage_woocommerce', 'dotypos', 'page_content' );
		*/
	} else {
		//TODO when new WC Naviation released
		/*
		Menu::add_plugin_item(
			array(
				'id'         => 'dotypos',
				'title'      => 'Dotypos',
				'capability' => 'manage_woocommerce',
				'url'        => 'dotypos'
			)
		);
		*/
	}
}

/**
 * Register the JS.
 */
function add_dotypos_extension_register_script() {
    if ( ! class_exists( 'Automattic\WooCommerce\Admin\PageController' ) || ! \Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page() ) {
        return;
    }

	$script_path       = '/build/index.js';
	$script_asset_path = __DIR__ . '/build/index.asset.php';
	$script_asset      = file_exists( $script_asset_path )
		? require( $script_asset_path )
		: array( 'dependencies' => array(), 'version' => filemtime( $script_path ) );
	$script_url        = plugins_url( $script_path, __FILE__ );

	wp_register_script(
		'dotypos',
		$script_url,
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_register_style(
		'dotypos',
		plugins_url( '/build/index.css', __FILE__ ),
		// Add any dependencies styles may have, such as wp-components.
		array(),
		filemtime( __DIR__ . '/build/index.css' )
	);

	wp_enqueue_script( 'dotypos' );
	wp_enqueue_style( 'dotypos' );
}

add_action( 'admin_enqueue_scripts', 'add_dotypos_extension_register_script' );

/**
 * Register settings page
 */

function add_dotypos_extension_pages() {
	if ( ! function_exists( 'wc_admin_register_page' ) ) {
		return;
	}

	wc_admin_register_page( array(
		'id'       => 'dotypos-settings',
		'title'    => __('Dotypos', 'dotypos'),
		//'parent'   => 'woocommerce',
		'path'     => '/dotypos-settings',
		'icon'     => 'data:image/svg+xml;base64,' . base64_encode( '<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   height="46.67625"
   width="35.73"
   xml:space="preserve"
   viewBox="0 0 43 57.625002"
   y="0px"
   x="0px"
   fill="black"
   id="Layer_1"
   version="1.1"><metadata
   id="metadata862"><rdf:RDF><cc:Work
       rdf:about=""><dc:format>image/svg+xml</dc:format><dc:type
         rdf:resource="http://purl.org/dc/dcmitype/StillImage" /><dc:title></dc:title></cc:Work></rdf:RDF></metadata><defs
   id="defs860" />
<style
   id="style833"
   type="text/css">
	.st0{fill:#50B25D;}
</style>

<path
   id="path837"
   d="m 0,21.525 c 0,-28.7 43,-28.7 43,0 0,28.6 -43,28.5 -43,0 m 31.2,0 c 0,-13.7 -19.4,-13.6 -19.4,0 0,13.4 19.4,13.5 19.4,0" />







<path
   id="path853"
   d="m 40.7,52.125 -3.9,-6.2 c -4.4,2.8 -9.7,4.4 -15.3,4.4 -5.6,0 -10.9,-1.6 -15.3,-4.4 l -3.9,6.2 c 5.6,3.5 12.1,5.5 19.2,5.5 7.1,0 13.6,-2 19.2,-5.5"
   class="st0" />

</svg>' ),
		'nav_args' => array(
			'order' => 1000,
			//'parent' => 'woocommerce',
		),
        'position' => 120
	) );

	wc_admin_register_page( array(
		'id'       => 'dotypos-settings-connect',
		'title'    => __('Connect to Dotypos', 'dotypos'),
		//'parent'   => 'woocommerce',
		'path'     => '/dotypos-settings-connect',
		//'nav_args' => array(
		//	'order' => 1,
		//	'parent' => 'woocommerce',
		//),
	) );

	remove_menu_page( 'wc-admin&path=/dotypos-settings-connect' );
}

add_action( 'admin_menu', 'add_dotypos_extension_pages' );

/**
 * Add Dotypos category id to product category
 */
function dotypos_add_product_cat_term_fields( $taxonomy ) {
	$id = Dotypos::$keys['category']['field-id'];

	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false
	) );
	$usedCategories = [];
	foreach ( $categories as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		if(!empty($dotyposId)) {
			$usedCategories[] = $dotyposId;
		}
	}

	echo '<div class="form-field">
	<label for="' . $id . '">'.__('Dotypos ID', 'dotypos').'</label>
	<select type="text" name="' . $id . '" id="' . $id . '" />
	<option value="">'.__('None', 'dotypos').'</option>
	';

	$terms = get_terms( array(
		'taxonomy'   => 'dotypos_product_cat',
		'hide_empty' => false
	) );
	foreach ( $terms as $term ) {
		$dotyposId = get_term_meta( $term->term_id, $id, true );
		if(!in_array($dotyposId, $usedCategories)) {
			echo '<option value="' . $dotyposId . '"';
			echo '>' . $term->name . ' ( ' . $dotyposId . ' )</option>';
		}
	}
	echo '</select>
	<p>'.__('Category will be synchronized with selected Dotypos category.', 'dotypos').'</p>
	</div>';

}

function dotypos_edit_product_cat_term_fields( $term, $taxonomy ) {
	$id    = Dotypos::$keys['category']['field-id'];
	$value = get_term_meta( $term->term_id, $id, true );

	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false
	) );
	$usedCategories = [];
	foreach ( $categories as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		if(!empty($dotyposId)) {
			$usedCategories[] = $dotyposId;
		}
	}

	echo '<tr class="form-field">
	<th>
		<label for="' . $id . '">'.__('Dotypos ID', 'dotypos').'</label>
	</th>
	<td>
	<select type="text" name="' . $id . '" id="' . $id . '" />
	<option value="">'.__('None', 'dotypos').'</option>';
	$terms = get_terms( array(
		'taxonomy'   => 'dotypos_product_cat',
		'hide_empty' => false
	) );
	/** @var WP_Term $category */
	foreach ( $terms as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		if(!in_array($dotyposId, $usedCategories) || $dotyposId == $value) {
			echo '<option value="' . $dotyposId . '"';
			if ( $dotyposId == $value ) {
				echo ' selected="selected"';
			}
			echo '>' . $category->name . ' ( ' . $dotyposId . ' )</option>';
		}
	}
	echo '</select><p>'.__('Category will be synchronized with selected Dotypos category.', 'dotypos').'</p>
	</td>
	</tr>';
}

function dotypos_quick_edit_product_cat_term_fields( $column_name, $post_type, $taxonomy ) {
	//TODO render only not used categories
	$id = Dotypos::$keys['category']['field-id'];
	if ( $post_type != 'product_cat' && $column_name != $id ) {
		return false;
	}
	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false
	) );
	$usedCategories = [];
	foreach ( $categories as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		if(!empty($dotyposId)) {
			$usedCategories[] = $dotyposId;
		}
	}
	echo '
		<fieldset>
        <div class="inline-edit-col">
            <label>
                <span class="title">'.__('Dotypos ID', 'dotypos').'</span>
                <span class="input-text-wrap"><select type="text" name="' . $id . '" id="' . $id . '" />
	<option value="">'.__('None', 'dotypos').'</option>';
	$terms = get_terms( array(
		'taxonomy'   => 'dotypos_product_cat',
		'hide_empty' => false
	) );
	foreach ( $terms as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		if(!in_array($dotyposId, $usedCategories)) {
			echo '<option value="' . $dotyposId . '"';
			echo '>' . $category->name . ' ( ' . $dotyposId . ' )</option>';
		}
	}
	echo '</select></span>
            </label>
        </div>
    </fieldset>
	';
}

/**
 * Sets up value in quick edit od product category
 */
function dotypos_quick_edit_product_cat_term_javascript() {
	$current_screen = get_current_screen();
	$id             = Dotypos::$keys['category']['field-id'];
	if ( $current_screen->id != 'edit-product_cat' && $current_screen->taxonomy != 'product_cat' ) {
		return false;
	}

	$terms = get_terms( array(
		'taxonomy'   => 'dotypos_product_cat',
		'hide_empty' => false
	) );
	$categories = [];
	foreach ( $terms as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		$categories[$dotyposId] = $category->name;
	}

	wp_enqueue_script( 'jquery' );
	?>
    <script type="text/javascript">
        <?php
            echo 'var dotyposCategories = [];';
            foreach ($categories as $catId => $name) {
                echo 'dotyposCategories[\''.$catId.'\'] = \''.$name.'\';';
            }
        ?>
        /*global jQuery*/
        jQuery(function ($) {
            $('#the-list').on('click', 'button.editinline', function (e) {
                e.preventDefault();
                var $tr = $(this).closest('tr');
                var val = $tr.find('td.<?php echo $id; ?>').text();
                const select = $('tr.inline-edit-row select[name="<?php echo $id; ?>"]')[0];
                const actualCategoryText = dotyposCategories[val] + ' (' + val + ')';
                var option = new Option(actualCategoryText, val);
                $(option).html(actualCategoryText);
                $(select).append(option);
                $(select).val(val ? val : '');
                //TODO has to be called dont know why :(
                $('tr.inline-edit-row select[name="<?php echo $id; ?>"] option[value=' + val + ']').attr('selected', 'selected');

            });
        });
    </script>
	<?php
}

/**
 * Callback runs when category is updated
 */
function dotypos_quick_edit_save_category_field( $term_id ) {
	$id = Dotypos::$keys['category']['field-id'];
	if ( isset( $_POST[ $id ] ) ) {
		update_term_meta( $term_id, $id, $_POST[ $id ] );
	}
	//Sync with Dotypos
	$settings = get_option( Dotypos::$keys['settings'] );
	if ($settings['category']['enabled'] && $settings['category']['sync_title'] ) {
		$category  = get_term( $term_id );
		$dotyposId = get_term_meta( $term_id, $id, true );
		if ( ! empty( $dotyposId ) ) {
			$dotyposService = Dotypos::instance()->dotyposService;
			$dotyposService->patchCategory( $dotyposId, [ 'name' => $category->name, 'deleted' => false ] );
		}
	}
}

function dotypos_save_product_cat_term_fields( $term_id ) {
	$id = Dotypos::$keys['category']['field-id'];
	update_term_meta(
		$term_id,
		$id,
		sanitize_text_field( $_POST[ $id ] )
	);
	//Sync with Dotypos
	$settings = get_option( Dotypos::$keys['settings'] );
	if ($settings['category']['enabled'] && $settings['category']['sync_title'] ) {
		$category  = get_term( $term_id );
		$dotyposId = get_term_meta( $term_id, $id, true );
		if ( ! empty( $dotyposId ) ) {
			$dotyposService = Dotypos::instance()->dotyposService;
			$dotyposService->patchCategory( $dotyposId, [ 'name' => $category->name, 'deleted' => false ] );
		}
	}
}

function filter_cpt_columns( $columns ) {
	$id             = Dotypos::$keys['category']['field-id'];
	$columns[ $id ] = __('Dotypos ID', 'dotypos');

	return $columns;
}

function action_custom_columns_content( $columns, $column, $id ) {
	$term = Dotypos::$keys['category']['field-id'];
	switch ( $column ) {
		case $term:
		    $name = get_term($id)->name;
		    $id = get_term_meta( $id, $term, true );
			echo ( $id ) ? $name.' ('.$id.')' : '—';
			break;
	}

	return $columns;
}

//Products dotypos columns

function filter_cpt_product_columns( $columns ) {
	$id             = Dotypos::$keys['product']['field-id'];
	$columns[ $id ] = __('Dotypos ID', 'dotypos');

	return $columns;
}

function action_custom_product_columns_content( $column, $postid ) {
	$id = Dotypos::$keys['product']['field-id'];
	switch ( $column ) {
		case $id:
			$dotyposId = get_post_meta( $postid, $id, true );
			echo ( $dotyposId ) ? $dotyposId : '—';
			break;
	}

	//return $columns;
}

function dotypos_add_js_vars() {
    echo '<script>
const dotyposGlobals = {
    rootUrl: "'.get_admin_url().'", 
    restRootUrl: "'.get_rest_url().'" 
};
</script>';
}


/**
 * REST API
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'dotypos/v1', '/settings', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'dotypos_get_rest_settings',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/pairingKeys', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'dotypos_post_rest_get_pairing_attributes',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/settings', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'dotypos_post_rest_settings',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/connect', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'dotypos_post_rest_connect',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/register', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'dotypos_post_rest_register',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/webhooks/movement', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'dotypos_post_rest_webhook_movement',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/webhooks/product', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'dotypos_post_rest_webhook_product',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/jobs', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'dotypos_post_rest_jobs',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/dotypos/warehouses', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'dotypos_get_rest_dotypos_warehouses',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/dotypos/products', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'dotypos_get_rest_dotypos_products_import',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
	register_rest_route( 'dotypos/v1', '/dotypos/products/import', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'dotypos_get_rest_dotypos_products_import_selected',
		//'permission_callback' => function () {
		//  return current_user_can( 'administrator' );
		//}
		'permission_callback' => function () {
			return true;
		}
	) );
} );

function dotypos_get_rest_settings( $data ) {
	$settings = get_option( Dotypos::$keys['settings'] );
	if ( $settings === false ) {
		return Dotypos::DEFAULT_SETTINGS;
	}

	return $settings;
}

function dotypos_post_rest_settings( $request ) {
    //Handle backend settings
	$originSettings   = get_option( Dotypos::$keys['settings']);
	$settings = json_decode( $request->get_body(), true );
	//TODO not saving with this enabled
	$settings['dotypos']['webhook'] = $originSettings['dotypos']['webhook'];
	$settings['licence'] = $originSettings['licence'];
	$settings['dotypos']['warehouseId'] = $originSettings['dotypos']['warehouseId'];
	$result   = update_option( Dotypos::$keys['settings'], $settings );
	if ( $result ) {
		return new WP_REST_Response( 'OK', 200 );
	} else {
		return new WP_REST_Response( 'Settings not saved.', 500 );
	}
}

function dotypos_post_rest_get_pairing_attributes( $request ) {
    $keys = [];
	$meta_keys = [];
	$posts     = get_posts( array( 'post_type' => 'product', 'limit' => 5 ) );
    $fallBackKeys = ['_sku'];

	foreach ( $posts as $post ) {
		$post_meta_keys = get_post_custom_keys( $post->ID );
		$meta_keys      = array_merge( $meta_keys, $post_meta_keys );
	}
	$keys = array_values( array_unique( $meta_keys ) );
    $disabledKeys = array_unique([
            'dotypos_product_id',
            'total_sales',
            '_edit_lock',
            '_edit_last',
            '_tax_status',
            '_tax_class',
            '_sold_individually',
            '_virtual',
            '_downloadable',
            '_download_limit',
            '_download_expiry',
            '_stock',
            '_stock_status',
            '_wc_average_rating',
            '_wc_review_count',
            '_product_version',
            '_total_sales',
            '_visibility',
            '_manage_stock',
            '_backorders',
            '_price',
            '_regular_price',
            '_purchase_note'
    ]);
    $filteredKeys = array_diff( $keys, $disabledKeys );
    $returnedKeys = array_unique(array_merge($fallBackKeys, $filteredKeys));
    return new WP_REST_Response( json_encode(array_values($returnedKeys)), 200 );
}

function dotypos_post_rest_register( $request ) {
	$settings   = get_option( Dotypos::$keys['settings']);
	if($settings['licence']['registered']) {
        if(Dotypos::LICENCE_SERVER) {
            $response = Dotypos::instance()->licenceService->verify($settings['dotypos']['licenceKey']);
            //wc_get_logger()->debug('Register: '.json_encode($response), array('source' => 'dotypos_integration'));
            if (!is_wp_error($response) && isset($response['body'])) {
                $data = json_decode($response['body'], true);
                if ($data['result']['valid']) {
                    $settings['licence']['registered'] = true;
                    $settings['licence']['verified'] = true;
                    $settings['licence']['expired'] = false;
                    update_option(Dotypos::$keys['settings'], $settings);
                    return new WP_REST_Response('OK', 200);
                }
            }
        }
        else {
            $settings['licence']['registered'] = true;
            $settings['licence']['verified'] = true;
            $settings['licence']['expired'] = false;
            update_option(Dotypos::$keys['settings'], $settings);
            return new WP_REST_Response('OK', 200);
        }
	}
	$result = Dotypos::instance()->licenceService->register($settings['dotypos']['licenceKey']);
	//wc_get_logger()->debug(json_encode($result), array('source' => 'dotypos_integration'));
	if(is_wp_error($result)) {
		return new WP_REST_Response( 'CANNOT_CONNECT', 200 );
	}
    if(Dotypos::LICENCE_SERVER) {
        $data = json_decode($result['body'], true);
        if ($data['result']['success']) {
            $settings['licence']['registered'] = true;
            $settings['licence']['verified'] = true;
            $settings['licence']['expired'] = false;
            update_option(Dotypos::$keys['settings'], $settings);
            return new WP_REST_Response('OK', 200);
        } else {
            //Renew licence if domain match
            $parse = parse_url(get_site_url());
            $domain = $parse['host'];
            if ($data['result']['code'] === 'ALREADY_IN_USE' && $data['result']['domain'] === $domain) {
                $settings['licence']['registered'] = true;
                $settings['licence']['verified'] = true;
                $settings['licence']['expired'] = false;
                update_option(Dotypos::$keys['settings'], $settings);
                return new WP_REST_Response('OK', 200);
            }
            return new WP_REST_Response($data['result']['code'], 200);
        }
    }
    else {
        $settings['licence']['registered'] = true;
        $settings['licence']['verified'] = true;
        $settings['licence']['expired'] = false;
        update_option(Dotypos::$keys['settings'], $settings);
        return new WP_REST_Response('OK', 200);
    }
}

function dotypos_post_rest_connect( $request ) {
	$settings = dotypos_get_rest_settings(null);
	$data = json_decode( $request->get_body(), true );
	$settings['dotypos']['apiKey'] = $data['token'];
	$settings['dotypos']['cloudId'] = $data['cloudId'];
	$result = update_option( Dotypos::$keys['settings'], $settings );
	if ( $result ) {
		return new WP_REST_Response( 'OK', 200 );
	} else {
		return new WP_REST_Response( 'Settings not saved.', 500 );
	}
}

function dotypos_post_rest_webhook_movement( $request ) {
	if ( Dotypos::instance()->createDotyposWebhookMovement() ) {
		return new WP_REST_Response( 'OK', 200 );
	} else {
		return new WP_REST_Response( 'Webhook not created.', 500 );
	}
}

function dotypos_post_rest_webhook_product( $request ) {
	if ( Dotypos::instance()->createDotyposWebhookProduct() ) {
		return new WP_REST_Response( 'OK', 200 );
	} else {
		return new WP_REST_Response( 'Webhook not created.', 500 );
	}
}

function dotypos_post_rest_jobs( $request ) {
	//TODO make Job class
	$job       = json_decode( $request->get_body(), true );
	$job['id'] = as_enqueue_async_action( $job['hook'], $job['args'], $job['group'] );

	return new WP_REST_Response( 'Created job with ID: ' . $job['id'], 200 );
}

function dotypos_get_rest_dotypos_warehouses( $request ) {
	$warehouses = [];
	foreach ( Dotypos::instance()->dotyposService->getWarehouses() as $warehouse ) {
		$warehouses[] = $warehouse;
	}

	return new WP_REST_Response( $warehouses, 200 );
}

function dotypos_get_rest_dotypos_products_import( $request ) {
	$products = get_transient( Dotypos::$keys['cache']['import_products_wizard'] );
    if($products !== false) {
	    delete_transient( Dotypos::$keys['cache']['import_products_wizard'] );
	    return new WP_REST_Response( json_encode($products), 200 );
    }
    else {
	    return new WP_REST_Response( 'LOCKED', 423 );
    }
}

function dotypos_get_rest_dotypos_products_import_selected( $request ) {
	$products       = json_decode( $request->get_body(), true );
	$selectedProducts = [];
	foreach($products as $product) {
	    if($product['import'] === true) {
		    $selectedProducts[$product['id']] = ['pair' => $product['pair']];
        }
    }
	set_transient( Dotypos::$keys['cache']['import_products_wizard_selected'], $selectedProducts );
	as_enqueue_async_action( 'dotypos_job_import_products_from_wizard_import', ['data' => []], 'dotypos_jobs' );
    return new WP_REST_Response( 'OK', 200 );
}

/**
 * Jobs consumers
 */

add_action( 'dotypos_job_import_categories', 'dotypos_job_import_categories_consume' );
function dotypos_job_import_categories_consume( array $data ) {
	wc_get_logger()->debug('Importing categories from dotypos started...', array('source' => 'dotypos_integration'));
	$id                = Dotypos::$keys['category']['field-id'];
	$dotyposCategories = [];
	foreach ( Dotypos::instance()->dotyposService->getCategories() as $category ) {
		$dotyposCategories[] = $category;
	}
	$categories = [];
	/** @var WP_Term $category */
	foreach ( get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false ] ) as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		if ( ! empty( $dotyposId ) ) {
			$categories[ $dotyposId ] = [ 'term' => $category, $id => $dotyposId ];
		} else {
			$categories[] = [ 'term' => $category, $id => $dotyposId ];
		}
	}
	$usedNames = [];
	foreach ($categories as $categoryItem) {
		/** @var WP_Term $category */
	    $category = $categoryItem['term'];
		$usedNames[] = ['canonical_name' => strtolower($category->name), 'counter' => 1];
    }
	foreach ( $dotyposCategories as $dotyposCategory ) {
		//TODO maybe update title?
		if ( isset( $categories[ $dotyposCategory['id'] ] ) ) {
			wc_get_logger()->debug('Category '.$dotyposCategory['name'].' not imported already exists in Woocommerce', array('source' => 'dotypos_integration'));
		} else {
			if ( $dotyposCategory['deleted'] !== true ) {
			    $categoryName = $dotyposCategory['name'];
			    //Check for name variants WC is case insensitive for category name
                $canonicalName = strtolower($categoryName);
                $key = array_search($canonicalName, array_column($usedNames, 'canonical_name'));
                if($key !== false) {
                    $categoryName = $categoryName.' '.$usedNames[$key]['counter'];
                    $usedNames[$key]['counter']++;
                }
                else {
                    $usedNames[] = ['canonical_name' => $canonicalName, 'counter' => 1];
                }
				/** @var WP_Term $newCategory */
				$newCategory = wp_insert_term( $categoryName, 'product_cat', array() );
				if ( ! is_wp_error( $newCategory ) ) {
					update_term_meta(
						$newCategory['term_id'],
						$id,
						sanitize_text_field( $dotyposCategory['id'] )
					);
					wc_get_logger()->debug('Category '.$dotyposCategory['name'].' imported from Dotypos', array('source' => 'dotypos_integration'));
				}
				else {
					wc_get_logger()->debug('Cannot insert category from Dotypos '.json_encode($newCategory), array('source' => 'dotypos_integration'));
				}
			}
		}
	}
	dotypos_job_fetch_categories_consume( [], false );
	Dotypos::add_activity_panel_inbox_note( __('Categories from Dotypos imported', 'dotypos'), __('Categories from Dotypos were successfully imported.', 'dotypos'), [
		[
			'open_categories',
			__('List categories', 'dotypos'),
			get_site_url().'/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product'
		],
		[ 'open_dotypos_settings', __('Settings', 'dotypos'), get_site_url().'/wp-admin/?page=wc-admin&path=%2Fdotypos-settings' ]
	] );
}

add_action( 'dotypos_job_export_categories', 'dotypos_job_export_categories_consume' );
function dotypos_job_export_categories_consume( array $data ) {
	$id                = Dotypos::$keys['category']['field-id'];
	$settings          = get_option( Dotypos::$keys['settings'] );
	$dotyposCategories = [];
	foreach ( Dotypos::instance()->dotyposService->getCategories() as $category ) {
		$dotyposCategories[ $category['id'] ] = $category;
	}
	/** @var WP_Term $category */
	foreach ( get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false ] ) as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		//TODO maybe update title?
		if ( isset( $dotyposCategories[ $dotyposId ] ) ) {
		} else {
			$payload         = [
				[
					'_cloudId' => $settings['dotypos']['cloudId'],
					'deleted'  => false,
					'display'  => true,
					'flags'    => 0,
					'hexColor' => '#ffffff',
					'name'     => $category->name,
				]
			];
			$dotyposCategory = Dotypos::instance()->dotyposService->postCategory( $payload );
			wc_get_logger()->debug('[Import categories to Dotypos] Category '.$dotyposCategory[0]['name'].'('.$dotyposCategory[0]['id'].') exported to Dotypos', array('source' => 'dotypos_integration'));
			if ( ! empty( $dotyposCategory[0]['id'] ) ) {
				update_term_meta(
					$category->term_id,
					$id,
					sanitize_text_field( $dotyposCategory[0]['id'] )
				);
			}
		}
	}
	dotypos_job_fetch_categories_consume( [], false );
	Dotypos::add_activity_panel_inbox_note( __('Categories exported to Dotypos', 'dotypos'), __('Categories were successfully exported to Dotypos.', 'dotypos'), [
		[
			'open_categories',
			__('List categories', 'dotypos'),
			get_site_url().'/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product'
		],
		[ 'open_dotypos_settings', __('Settings', 'dotypos'), get_site_url().'/wp-admin/?page=wc-admin&path=%2Fdotypos-settings' ]
	] );
}

add_action( 'dotypos_job_fetch_categories', 'dotypos_job_fetch_categories_consume' );
function dotypos_job_fetch_categories_consume( array $data , bool $notify = true) {
	//Delete all dotypos_product_cat terms
	$terms = get_terms( array(
		'taxonomy'   => 'dotypos_product_cat',
		'hide_empty' => false
	) );
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'dotypos_product_cat' );
	}
	foreach ( Dotypos::instance()->dotyposService->getCategories() as $category ) {
		$id          = Dotypos::$keys['category']['field-id'];
		$newCategory = wp_insert_term( $category['name'], 'dotypos_product_cat', array() );
		if ( ! is_wp_error( $newCategory ) ) {
		    //Dotypos ID
			update_term_meta(
				$newCategory['term_id'],
				$id,
				sanitize_text_field( $category['id'] )
			);
		}
	}
	if($notify) {
		Dotypos::add_activity_panel_inbox_note( __('Fetched Dotypos categories', 'dotypos'), __('Categories from Dotypos were successfully fetched. Now you can pair your categories.', 'dotypos'), [
			[
				'open_categories',
				__('List categories', 'dotypos'),
				get_site_url().'/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product'
			],
			[ 'open_dotypos_settings', __('Settings', 'dotypos'), get_site_url().'/wp-admin/?page=wc-admin&path=%2Fdotypos-settings' ]
		] );
	}
}

add_action( 'dotypos_job_import_products', 'dotypos_job_import_products_consume' );
function dotypos_job_import_products_consume( array $data ) {
	$id                = Dotypos::$keys['category']['field-id'];
	$productId                = Dotypos::$keys['product']['field-id'];
	$settings = get_option( Dotypos::$keys['settings'] );
	$dotyposCategories = [];
	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false
	) );
	foreach ( $categories as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		if(!empty($dotyposId)) {
			$dotyposCategories[$dotyposId] = $category->term_id;
		}
	}
	$all_tax_rates = Dotypos::getTaxRates();
	$products = [];
	/** @var WP_Post $product */
	wc_get_logger()->debug('Import products from Dotypos started...', array('source' => 'dotypos_integration'));
	foreach ( get_posts( [ 'numberposts' => -1, 'post_type' => 'product' ] ) as $product ) {
		$dotyposId = get_post_meta( $product->ID, $productId, true );
		if ( ! empty( $dotyposId ) ) {
			$products[ $dotyposId ] = [ 'product' => $product, $productId => $dotyposId ];
		} else {
			$products[] = [ 'product' => $product, $productId => $dotyposId ];
		}
	}
    $i = 1;
	foreach ( Dotypos::instance()->dotyposService->getProducts() as $dotyposProduct ) {
		//TODO maybe update attributes?
		if ( isset( $products[ $dotyposProduct['id'] ] ) ) {
			wc_get_logger()->debug($i.'. Not imported '.$dotyposProduct['name'].' with Dotypos ID: '.$dotyposProduct['id'].', because this product already exists with name '.$products[ $dotyposProduct['id'] ]['product']->post_title, array('source' => 'dotypos_integration'));
		} else {
			if ( $dotyposProduct['deleted'] !== true ) {
				/** @var WP_Post $newProduct */
				$newProductId = wp_insert_post( array(
					'post_title' => $dotyposProduct['name'],
					'post_content' => $dotyposProduct['description'] === null ? '' : $dotyposProduct['description'],
					'post_status' => 'publish',
					'post_type' => "product"), true);
				if ( ! is_wp_error( $newProductId ) ) {
					wp_set_object_terms( $newProductId, 'simple', 'product_type' ); // set product is simple/variable/grouped
					update_post_meta( $newProductId, $productId, $dotyposProduct['id'] );
					if(isset($dotyposCategories[$dotyposProduct['_categoryId']])) {
						wp_set_object_terms($newProductId, $dotyposCategories[$dotyposProduct['_categoryId']], 'product_cat');
                    }
					//Any paired Dotypos category found
					else {
						wp_set_object_terms($newProductId, 1, 'product_cat');
                    }
					$dotyposPairingAttribute = $dotyposProduct[$settings['product']['movement']['dotyposPairAttribute']];
					if(is_array($dotyposPairingAttribute) && !empty($dotyposPairingAttribute)) {
						$dotyposPairingAttribute = $dotyposPairingAttribute[0];
                    }
					else {
						$dotyposPairingAttribute = '';
                    }
					foreach ($all_tax_rates AS $rate) {
						//wc_get_logger()->debug(round($rate->tax_rate, 2).' == '.round((($dotyposProduct['vat']-1)*100), array('source' => 'dotypos_integration'), 2));
					    if(round($rate->tax_rate, 2) == round((($dotyposProduct['vat']-1)*100), 2)) {
						    update_post_meta($newProductId, '_tax_status', 'taxable');
						    update_post_meta($newProductId, '_tax_class', $rate->tax_rate_class);
                        }
                    }

					update_post_meta($newProductId, $settings['product']['movement']['wcPairAttribute'], $dotyposPairingAttribute);
					update_post_meta($newProductId, 'total_sales', '0');
					update_post_meta($newProductId, '_visibility', 'visible');
					//update_post_meta($newProductId, '_stock_status', 'instock');
					update_post_meta($newProductId, '_manage_stock', "yes");
					update_post_meta($newProductId, '_backorders', "yes");
					//Quantity
                    //TODO make UI for this in product importer
                    /*
                    $productDetail = Dotypos::instance()->dotyposService->getProductOnWarehouse($settings['dotypos']['warehouseId'], $dotyposProduct['id']);
					//wc_get_logger()->debug(json_encode($productDetail), array('source' => 'dotypos_integration'));
					//wc_get_logger()->debug(count($productDetail), array('source' => 'dotypos_integration'));
                    if(count($productDetail) === 0) {
                        $quantity = 0;
                    }
                    else {
	                    //wc_get_logger()->debug("Quantity: ".$productDetail['stockQuantityStatus'], array('source' => 'dotypos_integration'));
                        $quantity = $productDetail['stockQuantityStatus'];
                    }
					update_post_meta($newProductId, '_stock', $quantity);
                    */
					//wc_get_logger()->debug('Settings: '.wc_prices_include_tax(), array('source' => 'dotypos_integration'));
					wc_get_logger()->debug('Imported '.$dotyposProduct['name'].' with Dotypos ID: '.$dotyposProduct['id'], array('source' => 'dotypos_integration'));
					if(wc_prices_include_tax() === true) {
					    if($dotyposProduct['vat'] == 1) {
						    $price = $dotyposProduct['priceWithoutVat'];
                        }
					    else {
						    $price = $dotyposProduct['priceWithVat'];
					    }
                    }
					else {
						$price = $dotyposProduct['priceWithoutVat'];
					}
					update_post_meta($newProductId, '_price', $price);
					update_post_meta($newProductId, '_regular_price', $price);
					$note = $dotyposProduct['notes'];
					if(is_array($note) && !empty($note)) {
						$note = $note[0];
					}
					else {
					    $note = '';
                    }
					//Supports only one note
                    update_post_meta($newProductId, '_purchase_note', $note);
					//Another unused attributes
					//update_post_meta($newProductId, '_downloadable', 'yes');
					//update_post_meta($newProductId, '_virtual', 'yes');
					//update_post_meta($newProductId, '_featured', "no");
					//update_post_meta($newProductId, '_weight', "");
					//update_post_meta($newProductId, '_length', "");
					//update_post_meta($newProductId, '_width', "");
					//update_post_meta($newProductId, '_height', "");
					//update_post_meta($newProductId, '_sale_price_dates_from', "");
					//update_post_meta($newProductId, '_sale_price_dates_to', "");
					//update_post_meta($newProductId, '_price', "1");
					//update_post_meta($newProductId, '_sold_individually', "");
				}
				else {
					wc_get_logger()->debug($i.'. Importing Dotypos product error: '.json_encode($newProductId), array('source' => 'dotypos_integration'));
				}
			}
		}
		$i++;
	}
	wc_get_logger()->debug('Import products from Dotypos ended...', array('source' => 'dotypos_integration'));
	Dotypos::add_activity_panel_inbox_note( __('Products from Dotypos imported', 'dotypos'), __('Products from Dotypos were successfully imported.', 'dotypos'), [
		[
			'open_products',
			__('List products', 'dotypos'),
			get_site_url().'/wp-admin/edit.php?post_type=product'
		],
		[ 'open_dotypos_settings', __('Settings', 'dotypos'), get_site_url().'/wp-admin/?page=wc-admin&path=%2Fdotypos-settings' ]
	] );
}

add_action( 'dotypos_job_import_products_from_wizard', 'dotypos_job_import_products_from_wizard_consume' );
function dotypos_job_import_products_from_wizard_consume( array $data ) {
	delete_transient( Dotypos::$keys['cache']['import_products_wizard']);
	$settings = get_option( Dotypos::$keys['settings'] );
	$productIdKey                = Dotypos::$keys['product']['field-id'];
	$products = [];
	/** @var WP_Post $product */
	wc_get_logger()->debug('Import products wizard from Dotypos started...', array('source' => 'dotypos_integration'));
	foreach ( get_posts( [ 'numberposts' => -1, 'post_type' => 'product', 'cache_results'  => false, 'fields' => 'ids' ] ) as $productId ) {
		$dotyposId = get_post_meta( $productId, $productIdKey, true );
		if ( ! empty( $dotyposId ) ) {
			$products[ $dotyposId ] = [ $productIdKey => $dotyposId ];
		} else {
			$products[] = [ $productIdKey => $dotyposId ];
		}
	}
	$i = 1;
	$dotyposProducts = [];
	foreach ( Dotypos::instance()->dotyposService->getProducts() as $dotyposProduct ) {
		$dotyposPairingAttribute = $dotyposProduct[$settings['product']['movement']['dotyposPairAttribute']];
		if(is_array($dotyposPairingAttribute) && !empty($dotyposPairingAttribute)) {
			$dotyposPairingAttribute = $dotyposPairingAttribute[0];
		}
		else {
			$dotyposPairingAttribute = '';
		}
		//TODO maybe update attributes?
		if (isset( $products[ $dotyposProduct['id'] ] ) ) {
			//wc_get_logger()->debug($i.'. Not imported '.$dotyposProduct['name'].' with Dotypos ID: '.$dotyposProduct['id'].', because this product already exists, array('source' => 'dotypos_integration'));
		} else {
			if ( $dotyposProduct['deleted'] !== true ) {
				$dotyposProducts[] = ['id' => $dotyposProduct['id'], 'name' => $dotyposProduct['name'], 'pairing_attribute' => $dotyposPairingAttribute, 'import' => true, 'pair' => true];
			}
		}
		$i++;
	}
	set_transient( Dotypos::$keys['cache']['import_products_wizard'], $dotyposProducts );
	wc_get_logger()->debug('Import products wizard from Dotypos ended. Returned '.count($dotyposProducts).' products.', array('source' => 'dotypos_integration'));
}

add_action( 'dotypos_job_import_products_from_wizard_import', 'dotypos_job_import_products_from_wizard_import_consume' );
function dotypos_job_import_products_from_wizard_import_consume( array $data ) {
    $selectedProducts = get_transient(Dotypos::$keys['cache']['import_products_wizard_selected']);
	$id                = Dotypos::$keys['category']['field-id'];
	$productId                = Dotypos::$keys['product']['field-id'];
	$settings = get_option( Dotypos::$keys['settings'] );
	$dotyposCategories = [];
	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false
	) );
	foreach ( $categories as $category ) {
		$dotyposId = get_term_meta( $category->term_id, $id, true );
		if(!empty($dotyposId)) {
			$dotyposCategories[$dotyposId] = $category->term_id;
		}
	}
	$all_tax_rates = Dotypos::getTaxRates();
	$products = [];
	/** @var WP_Post $product */
	wc_get_logger()->debug('Import products from wizard Dotypos started...', array('source' => 'dotypos_integration'));
	foreach ( get_posts( [ 'numberposts' => -1, 'post_type' => 'product' ] ) as $product ) {
		$dotyposId = get_post_meta( $product->ID, $productId, true );
		if ( ! empty( $dotyposId ) ) {
			$products[ $dotyposId ] = [ 'product' => $product, $productId => $dotyposId ];
		} else {
			$products[] = [ 'product' => $product, $productId => $dotyposId ];
		}
	}
	$i = 1;
	foreach ( Dotypos::instance()->dotyposService->getProducts() as $dotyposProduct ) {
		//TODO maybe update attributes?
        if($selectedProducts[ $dotyposProduct['id'] ]) {
		if ( isset( $products[ $dotyposProduct['id'] ] ) ) {
			wc_get_logger()->debug($i.'. Not imported '.$dotyposProduct['name'].' with Dotypos ID: '.$dotyposProduct['id'].', because this product already exists with name '.$products[ $dotyposProduct['id'] ]['product']->post_title, array('source' => 'dotypos_integration'));
		} else {
			if ( $dotyposProduct['deleted'] !== true ) {
				/** @var WP_Post $newProduct */
				$newProductId = wp_insert_post( array(
					'post_title'   => $dotyposProduct['name'],
					'post_content' => $dotyposProduct['description'] === null ? '' : $dotyposProduct['description'],
					'post_status'  => 'publish',
					'post_type'    => "product"
				), true );
				if ( ! is_wp_error( $newProductId ) ) {
					wp_set_object_terms( $newProductId, 'simple', 'product_type' ); // set product is simple/variable/grouped
					if($selectedProducts[ $dotyposProduct['id']]['pair'] === true) {
						update_post_meta( $newProductId, $productId, $dotyposProduct['id'] );
					}
					if ( isset( $dotyposCategories[ $dotyposProduct['_categoryId'] ] ) ) {
						wp_set_object_terms( $newProductId, $dotyposCategories[ $dotyposProduct['_categoryId'] ], 'product_cat' );
					} //Any paired Dotypos category found
					else {
						wp_set_object_terms( $newProductId, 1, 'product_cat' );
					}
					$dotyposPairingAttribute = $dotyposProduct[ $settings['product']['movement']['dotyposPairAttribute'] ];
					if ( is_array( $dotyposPairingAttribute ) && ! empty( $dotyposPairingAttribute ) ) {
						$dotyposPairingAttribute = $dotyposPairingAttribute[0];
					} else {
						$dotyposPairingAttribute = '';
					}
					foreach ( $all_tax_rates as $rate ) {
						if ( round( $rate->tax_rate, 2 ) == round( ( ( $dotyposProduct['vat'] - 1 ) * 100 ), 2 ) ) {
							update_post_meta( $newProductId, '_tax_status', 'taxable' );
							update_post_meta( $newProductId, '_tax_class', $rate->tax_rate_class );
						}
					}
					update_post_meta( $newProductId, $settings['product']['movement']['wcPairAttribute'], $dotyposPairingAttribute );
					update_post_meta( $newProductId, 'total_sales', '0' );
					update_post_meta( $newProductId, '_visibility', 'visible' );
					update_post_meta( $newProductId, '_manage_stock', "yes" );
					update_post_meta( $newProductId, '_backorders', "yes" );
					//Quantity
					//TODO make UI for this in product importer
					/*
					$productDetail = Dotypos::instance()->dotyposService->getProductOnWarehouse($settings['dotypos']['warehouseId'], $dotyposProduct['id']);
					//wc_get_logger()->debug(json_encode($productDetail), array('source' => 'dotypos_integration'));
					//wc_get_logger()->debug(count($productDetail), array('source' => 'dotypos_integration'));
					if(count($productDetail) === 0) {
						$quantity = 0;
					}
					else {
						//wc_get_logger()->debug("Quantity: ".$productDetail['stockQuantityStatus'], array('source' => 'dotypos_integration'));
						$quantity = $productDetail['stockQuantityStatus'];
					}
					update_post_meta($newProductId, '_stock', $quantity);
					*/
					//wc_get_logger()->debug('Settings: '.wc_prices_include_tax(), array('source' => 'dotypos_integration'));
					wc_get_logger()->debug( 'Imported ' . $dotyposProduct['name'] . ' with Dotypos ID: ' . $dotyposProduct['id'], array( 'source' => 'dotypos_integration' ) );
					if ( wc_prices_include_tax() === true ) {
						if ( $dotyposProduct['vat'] == 1 ) {
							$price = $dotyposProduct['priceWithoutVat'];
						} else {
							$price = $dotyposProduct['priceWithVat'];
						}
					} else {
						$price = $dotyposProduct['priceWithoutVat'];
					}
					update_post_meta( $newProductId, '_price', $price );
					update_post_meta( $newProductId, '_regular_price', $price );
					$note = $dotyposProduct['notes'];
					if ( is_array( $note ) && ! empty( $note ) ) {
						$note = $note[0];
					} else {
						$note = '';
					}
					//Supports only one note
					update_post_meta( $newProductId, '_purchase_note', $note );
				} else {
					wc_get_logger()->debug( $i . '. Importing Dotypos product error: ' . json_encode( $newProductId ), array( 'source' => 'dotypos_integration' ) );
				}
			}
		}
		}
		$i++;
	}
	delete_transient( Dotypos::$keys['cache']['import_products_wizard_selected']);
	wc_get_logger()->debug('Import products from Dotypos ended...', array('source' => 'dotypos_integration'));
	Dotypos::add_activity_panel_inbox_note( __('Products from Dotypos imported', 'dotypos'), __('Products from Dotypos were successfully imported.', 'dotypos'), [
		[
			'open_products',
			__('List products', 'dotypos'),
			get_site_url().'/wp-admin/edit.php?post_type=product'
		],
		[ 'open_dotypos_settings', __('Settings', 'dotypos'), get_site_url().'/wp-admin/?page=wc-admin&path=%2Fdotypos-settings' ]
	] );
}

add_action( 'dotypos_job_export_products', 'dotypos_job_export_products_consume' );
function dotypos_job_export_products_consume( array $data ) {
	$id       = Dotypos::$keys['product']['field-id'];
	$categoryId = Dotypos::$keys['category']['field-id'];
	$settings = get_option( Dotypos::$keys['settings'] );
	$settings['dotypos']['webhook']['product']['disabled'] = true;
	update_option( Dotypos::$keys['settings'],  $settings);

	static $dictUnits = [
		'ks' => 'Piece',
	];
	$fallbackUnit = 'Piece';
	$dotykackaUnit = $fallbackUnit;

	//Create import category if not exists
	$importCategoryId = null;
	$dotyposCategories = [];
	foreach (Dotypos::instance()->dotyposService->getCategories() as $category) {
		$dotyposCategories[$category['id']] = $category['id'];
		if ($category['name'] === Dotypos::IMPORTED_CATEGORY_NAME) {
			$importCategoryId = $category['id'];
		}
	}
	if (!$importCategoryId) {
		$payloadCategory         = [
			[
				'_cloudId' => $settings['dotypos']['cloudId'],
				'deleted'  => false,
				'display'  => true,
				'flags'    => 0,
				'hexColor' => '#ffffff',
				'name'     => Dotypos::IMPORTED_CATEGORY_NAME,
			]
		];
		$importCategoryId = Dotypos::instance()->dotyposService->postCategory($payloadCategory)['id'];
	}

	$products = [];
	$cachedCategories = [];
	wc_get_logger()->debug('Export products to Dotypos started...', array('source' => 'dotypos_integration'));
	$count = wp_count_posts( $type = 'product');
	wc_get_logger()->debug('Trying to export '.$count->publish.' products', array('source' => 'dotypos_integration'));
	/** @var WP_Post $product */
    $i = 1;
	foreach ( get_posts( [ 'numberposts' => -1, 'post_type' => 'product' ] ) as $product ) {
		$productMeta = get_post_meta( $product->ID);
		//wc_get_logger()->debug($productMeta[$id][0], array('source' => 'dotypos_integration'));
		//Only unpaired products
		if ( empty( $productMeta[$id][0] ) ) {
		    //Get tax rate
            //TODO check if not taxable
			$taxes = WC_Tax::get_rates_for_tax_class( $productMeta['_tax_class'][0] );
			if(count($taxes) > 0) {
				$vat = (float) reset( $taxes )->tax_rate / 100 + 1;
            }
			else {
				$vat = 1;
            }
			//Check if product has dotypos paired category
            $categoryTerms = wp_get_post_terms($product->ID, 'product_cat');
			//Takes only first. DTK has no support for multiple categories
            if(isset($cachedCategories[$categoryTerms[0]->term_id])) {
                $dotyposCategoryIdFromCache = $cachedCategories[$categoryTerms[0]->term_id];
            }
            else {
	            $dotyposCategoryIdFromCache = get_term_meta( $categoryTerms[0]->term_id, $categoryId, true );
	            $cachedCategories[$categoryTerms[0]->term_id] = $dotyposCategoryIdFromCache;
            }
			$productCategoryDotyposId = isset($categoryTerms[0]) ? $dotyposCategoryIdFromCache : '';
			if(isset($dotyposCategories[$productCategoryDotyposId])) {
			    $dotyposCategoryId = $productCategoryDotyposId;
            }
			else {
				$dotyposCategoryId = (int) $importCategoryId;
            }
			//wc_get_logger()->debug('Product category ID: '.json_encode($dotyposCategoryId), array('source' => 'dotypos_integration'));
			$payload = [
				'_cloudId' => (int) $settings['dotypos']['cloudId'],
				'_categoryId' => $dotyposCategoryId,
				'name' => $product->post_title,
                'description' => mb_substr($product->post_content, 0, 1000),
				'unit' => $dotykackaUnit,
				'priceWithoutVat' => wc_prices_include_tax() ? (float) ( $productMeta['_price'][0] / $vat ) : (float) $productMeta['_price'][0],
				'vat' => (float) $vat,
				'hexColor' => '#EED5D2',
				'display' => true,
				'deleted' => 0,
				'packaging' => 1,
				'discountPermitted' => 0,
				'discountPercent' => 0,
				'requiresPriceEntry' => 0,
				'stockOverdraft' => 'ALLOW',
				'onSale' => 0,
				'flags' => 0,
				'stockDeduct' => 1,
				'points' => 0.0,
				'versionDate' => null
            ];
            wc_get_logger()->debug('Trying to export  '.$i.' of '.$count->publish.' products with WC ID: '. $product->ID, array('source' => 'dotypos_integration'));
            wc_get_logger()->debug(print_r($payload, true), array('source' => 'dotypos_integration'));
			//TODO supports only one combination of pairing. Needs to be updated when new pairing options added.
			if($settings['product']['movement']['dotyposPairAttribute']=='ean') {
                if (isset($productMeta[$settings['product']['movement']['wcPairAttribute']]) &&  count($productMeta[$settings['product']['movement']['wcPairAttribute']]) > 0 ) {
                    $payload['ean'] = [$productMeta[$settings['product']['movement']['wcPairAttribute']][0]];
                }
            }
			//wc_get_logger()->debug(json_encode($payload), array('source' => 'dotypos_integration'));
			$newProduct = Dotypos::instance()->dotyposService->postProduct([$payload]);
			//wc_get_logger()->debug(json_encode($newProduct), array('source' => 'dotypos_integration'));
			$updatedMeta = update_post_meta( $product->ID , $id, $newProduct[0]['id'] );
			wc_get_logger()->debug('Exported unpaired '.$i.' of '.$count->publish.' products', array('source' => 'dotypos_integration'));
			$i++;
			//wc_get_logger()->debug('Update dotypos ID after export to Dotypos. WC ID: '.$product->ID.' Meta ID: '.$id.' DTK ID:'.$newProduct['id'], array('source' => 'dotypos_integration'));
		}
		else {
			$i++;
			wc_get_logger()->debug('Not exported because paired '.$i.' of '.$count->publish.' products', array('source' => 'dotypos_integration'));
        }
	}

	$settings['dotypos']['webhook']['product']['disabled'] = false;
	update_option( Dotypos::$keys['settings'],  $settings);

	Dotypos::add_activity_panel_inbox_note( __('Products exported to Dotypos', 'dotypos'), __('Products were successfully exported to Dotypos.', 'dotypos'), [
		[
			'open_products',
			__('List products', 'dotypos'),
			get_site_url().'/wp-admin/edit.php?post_type=product'
		],
		[ 'open_dotypos_settings', __('Settings', 'dotypos'), get_site_url().'/wp-admin/?page=wc-admin&path=%2Fdotypos-settings' ]
	] );
}

add_action( 'dotypos_job_pair_products', 'dotypos_job_pair_products_consume' );
function dotypos_job_pair_products_consume( array $data ) {
	$id       = Dotypos::$keys['product']['field-id'];
	$settings = get_option( Dotypos::$keys['settings'] );

	$products = wc_get_products([
		'posts_per_page' => -1,
		'status' => 'publish'
	]);
	$wcProducts = [];
	foreach ($products as $product) {
	    //Reset pairing
		update_post_meta($product->get_id(), $id, '');
		//TODO supports only one combination of pairing. Needs to be updated when new pairing options added.
        $meta = get_post_meta( $product->get_id(), $settings['product']['movement']['wcPairAttribute'], true );
		if($settings['product']['movement']['dotyposPairAttribute']=='ean') {
			$wcProducts[ $product->get_id() ] = $meta;
		}
	}

	foreach (Dotypos::instance()->dotyposService->getProducts() as $dtkProduct) {
		//TODO supports only one combination of pairing. Needs to be updated when new pairing options added.
		if (isset($dtkProduct['ean'])) {
			foreach ($dtkProduct['ean'] as $ean) {
				$key = array_search($ean, $wcProducts);
				if ($key !== false) {
					update_post_meta($key, $id, $dtkProduct['id']);
					break;
				}
			}
		}
	}


	Dotypos::add_activity_panel_inbox_note( __('Products paired to Dotypos', 'dotypos'), __('Products were successfully paired with Dotypos.', 'dotypos'), [
		[
			'open_products',
			__('List products', 'dotypos'),
			get_site_url().'/wp-admin/edit.php?post_type=product'
		],
		[ 'open_dotypos_settings', __('Settings', 'dotypos'), get_site_url().'/wp-admin/?page=wc-admin&path=%2Fdotypos-settings' ]
	] );
}

add_action( 'dotypos_job_overwrite_from_dotypos', 'dotypos_job_overwrite_from_dotypos_consume' );
function dotypos_job_overwrite_from_dotypos_consume( array $data ) {

	global $WC_Dotypos;
    remove_action( 'woocommerce_update_product_stock_query', [ $WC_Dotypos, 'handle_update_product_stock_query' ] );
	remove_action( 'woocommerce_product_set_stock', [ $WC_Dotypos, 'handle_product_set_stock' ] );
	remove_action( 'woocommerce_variation_set_stock', [ $WC_Dotypos, 'handle_variation_set_stock' ] );
    remove_action( 'woocommerce_update_product', [ $WC_Dotypos, 'handle_product_updated' ] );

	$id       = Dotypos::$keys['product']['field-id'];
	$settings = get_option( Dotypos::$keys['settings'] );

	wc_get_logger()->debug('Overwrite from dotypos', array('source' => 'dotypos_integration'));

	$products = wc_get_products([
		'limit' => -1,
		'status' => 'publish'
	]);
    $wcProducts = [];
	foreach ($products as $product) {
	    $dotyposId = get_post_meta($product->get_id(), $id, true);
	    $wcProducts[$dotyposId] = $product->get_id();
	}

	foreach(Dotypos::instance()->dotyposService->getProductsOnWarehouse($settings['dotypos']['warehouseId']) as $product) {
	    if(isset($wcProducts[$product['id']])) {
		    // Decimal stock: write float directly, bypass intval in wc_stock_amount
							$wc_id = $wcProducts[$product['id']];
							$qty = floatval($product['stockQuantityStatus']);
							if ( ! function_exists( 'gastronom_apply_dotypos_stock_to_wc_product' ) || ! gastronom_apply_dotypos_stock_to_wc_product( wc_get_product( $wc_id ), $qty ) ) {
								update_post_meta($wc_id, '_stock', $qty);
								wc_update_product_stock_status($wc_id, $qty > 0 ? 'instock' : 'outofstock');
								wc_delete_product_transients($wc_id);
							}
        }
    }

	Dotypos::add_activity_panel_inbox_note( __('Products stocks overwrite from Dotypos', 'dotypos'), __('Products stocks were successfully overwrite from Dotypos.', 'dotypos'), [
		[
			'open_products',
			__('List products', 'dotypos'),
			get_site_url().'/wp-admin/edit.php?post_type=product'
		],
		[ 'open_dotypos_settings', __('Settings', 'dotypos'), get_site_url().'/wp-admin/?page=wc-admin&path=%2Fdotypos-settings' ]
	] );
}

add_action( 'dotypos_job_overwrite_from_woocommerce', 'dotypos_job_overwrite_from_woocommerce_consume' );
function dotypos_job_overwrite_from_woocommerce_consume( array $data ) {

	$id       = Dotypos::$keys['product']['field-id'];
	$settings = get_option( Dotypos::$keys['settings'] );

	wc_get_logger()->debug('Overwrite from dotypos', array('source' => 'dotypos_integration'));

	$products = wc_get_products([
		'limit' => -1,
		'status' => 'publish'
	]);
	$wcProducts = [];
	/** @var WC_Product_Simple $product */
	foreach ($products as $product) {
		$dotyposId = get_post_meta($product->get_id(), $id, true);
		$wcProducts[$dotyposId] = ['id' => $product->get_id(), 'stock_quantity' => $product->get_stock_quantity()];
	}

	foreach(Dotypos::instance()->dotyposService->getProductsOnWarehouse($settings['dotypos']['warehouseId']) as $product) {
		if(isset($wcProducts[$product['id']])) {
		    $change = -$product['stockQuantityStatus'] + $wcProducts[$product['id']]['stock_quantity'];
			Dotypos::instance()->dotyposService->updateProductStock( $settings['dotypos']['warehouseId'], $product['id'], $change );
			unset($wcProducts[$product['id']]);
		}
	}

    //Process product which are not on warehouse (any stock status)
    foreach ($wcProducts AS $dotyposId => $product) {
	    $change = $product['stock_quantity'];
	    Dotypos::instance()->dotyposService->updateProductStock( $settings['dotypos']['warehouseId'], $dotyposId, $change );
    }

	Dotypos::add_activity_panel_inbox_note( __('Products stocks overwrite to Dotypos', 'dotypos'), __('Products stocks were successfully overwrite to Dotypos.', 'dotypos'), [
		[
			'open_products',
			__('List products', 'dotypos'),
			get_site_url().'/wp-admin/edit.php?post_type=product'
		],
		[ 'open_dotypos_settings', __('Settings', 'dotypos'), get_site_url().'/wp-admin/?page=wc-admin&path=%2Fdotypos-settings' ]
	] );
}

function eg_increase_time_limit( $time_limit ) {
	return 360000;
}
add_filter( 'action_scheduler_queue_runner_time_limit', 'eg_increase_time_limit' );

function dotypos_load_my_own_textdomain( $mofile, $domain ) {
	wc_get_logger()->debug('Localize '.$mofile.' '.$domain, array('source' => 'dotypos_integration'));
	if ( 'dotypos' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
		$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
		$mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
		wc_get_logger()->debug('Mofile'.$mofile, array('source' => 'dotypos_integration'));
	}
	return $mofile;
}
//add_filter( 'load_textdomain_mofile', 'dotypos_load_my_own_textdomain', 10, 2 );
