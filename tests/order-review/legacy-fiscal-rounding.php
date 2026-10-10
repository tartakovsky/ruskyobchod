<?php
declare(strict_types=1);
define('ABSPATH', '/isolated/');
function add_action(...$args) {}
function add_filter(...$args) {}
function wc_get_price_decimals() { return 2; }
class WP_Error { public function __construct(public string $code, public string $message) {} }
class Dotypos { public static $keys = ['product'=>['field-id'=>'_dotypos_id']]; }
class FiscalProduct { public function get_meta($key) { return '12345'; } }
class WC_Order_Item_Product {
    public function __construct(private float $quantity, private float $total, private float $weight = 0) {}
    public function get_product() { return new FiscalProduct(); }
    public function get_meta($key, $single=true) { return $key === '_gastronom_weight_preorder' ? ($this->weight > 0 ? 'yes' : '') : ($key === '_gastronom_actual_weight_kg' ? $this->weight : ''); }
    public function get_quantity() { return $this->quantity; }
    public function get_total() { return $this->total; }
    public function get_total_tax() { return 0; }
    public function get_id() { return 1; }
}
class WC_Order {
    public function __construct(private array $items, private float $total) {}
    public function get_items($type) { return $this->items; }
    public function get_total() { return $this->total; }
    public function get_id() { return 11612; }
}
require __DIR__.'/../../wordpress/wp-content/mu-plugins/rusky-dotypos-fiscalization.php';
function fiscal_check(bool $yes, string $label): void {
    if (!$yes) { throw new RuntimeException('FAIL '.$label); }
    echo 'PASS '.$label."\n";
}
foreach ([36.00, 45.40, 45.41, 45.42, 45.43, 45.44, 45.45, 45.46, 45.47, 45.48, 45.49, 45.50, 45.51] as $total) {
    $items = rdf_action_items(new WC_Order([new WC_Order_Item_Product(6, 36)], $total));
    fiscal_check(is_array($items), 'legacy six-unit receipt representable '.$total);
    $cents=0; $quantity=0;
    foreach ($items as $item) {
        $cents+=(int)round(round($item['manual-price'], 2)*$item['qty']*100);
        $quantity+=$item['qty'];
        if ($item['id']!==12345) { throw new RuntimeException('Product mapping changed'); }
    }
    fiscal_check($cents===(int)round($total*100) && (float)$quantity===6.0, 'exact cents and original stock quantity '.$total);
    if ($total===45.43) {
        fiscal_check(count($items)===2 && $items[0]['qty']===5 && $items[0]['manual-price']===7.57 && $items[1]['qty']===1 && $items[1]['manual-price']===7.58, 'September 11612 rounding fix retained');
    }
}
$items=rdf_action_items(new WC_Order([new WC_Order_Item_Product(1, 7.5, 0.375)], 7.5));
fiscal_check(is_array($items) && $items[0]['qty']===0.375 && $items[0]['manual-price']===20.0, 'legacy fractional weight preserved');
