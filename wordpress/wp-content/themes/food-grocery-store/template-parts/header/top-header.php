<?php
/**
 * The template part for top header
 *
 * @package Food Grocery Store 
 * @subpackage food-grocery-store
 * @since food-grocery-store 1.0
 */
?>

<?php if(get_theme_mod('food_grocery_store_topbar_arrows', true) == 1 || get_theme_mod( 'food_grocery_store_resp_topbar_hide_show', true) == 1){ ?>
  <div class="top-bar pt-lg-2 pb-lg-4 pt-md-2 pb-md-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-4 col-md-3 col-12">
          <div class="row">
            <div class="col-lg-6 col-md-6 border-right text-center">
              <?php if( get_theme_mod( 'food_grocery_store_daily_deals_link') != '' || get_theme_mod( 'food_grocery_store_daily_deals_text') != ''){ ?>
                <a href="<?php echo esc_html(get_theme_mod('food_grocery_store_daily_deals_link',''));?>" ><i class="<?php echo esc_attr(get_theme_mod('food_grocery_store_dailydeals_icon','fas fa-rss')); ?> me-2"></i><?php echo esc_html(get_theme_mod('food_grocery_store_daily_deals_text',''));?></a>
              <?php } ?>
            </div>
            <div class="col-lg-6 col-md-6 text-center">
              <?php if( get_theme_mod( 'food_grocery_store_contact_link') != '' || get_theme_mod( 'food_grocery_store_contact_text') != '') { ?>
                <a href="<?php echo esc_html(get_theme_mod('food_grocery_store_contact_link',''));?>" ><i class="<?php echo esc_attr(get_theme_mod('food_grocery_store_helpdesk_icon','fas fa-headphones')); ?> me-2"></i><?php echo esc_html(get_theme_mod('food_grocery_store_contact_text',''));?></a>
              <?php } ?>
            </div>
          </div>
        </div>

        <div class="col-lg-2 col-md-1 col-12 top-none"> </div>

        <div class="col-lg-6 col-md-8 col-12">
          <div class="row">
            <div class="col-lg-4 col-md-4 border-right">
              <?php if( get_theme_mod( 'food_grocery_store_order_tracking',true) == 1) { ?>
                <?php if(class_exists('woocommerce')){ ?>
                  <div class="order-track position-relative">
                    <span><i class="<?php echo esc_attr(get_theme_mod('food_grocery_store_order_icon','fas fa-map-marker-alt')); ?> me-2"></i><?php esc_html_e('Order Tracking','food-grocery-store'); ?></span>
                    <div class="order-track-hover text-left">
                      <?php echo do_shortcode('[woocommerce_order_tracking]','food-grocery-store'); ?>
                    </div>
                  </div>
                <?php }?>
              <?php }?>
            </div>
            <div class="col-lg-5 col-md-4 text-center border-right">
              <?php if( get_theme_mod( 'food_grocery_store_currency_switcher', true) == 1) { ?>
                <?php if(class_exists('woocommerce')){ ?>
                  <div class="currency-box">
                    <?php echo do_shortcode( '[woocommerce_currency_switcher_drop_down_box]','food-grocery-store');?>
                  </div>
                <?php } ?>
              <?php }?>
            </div>
            <div class="col-lg-3 col-md-4">
              <?php if( get_theme_mod( 'food_grocery_store_google_translator', true) == 1) { ?>
                <div class="translate-lang">
                  <?php echo do_shortcode( '[gtranslate]','food-grocery-store');?>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php }?>