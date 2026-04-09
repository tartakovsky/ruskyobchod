<?php
/**
 * The template part for displaying single post content
 *
 * @package Food Grocery Store
 * @subpackage food-grocery-store
 * @since food-grocery-store 1.0
 */
?>

<?php 
    $food_grocery_store_archive_year  = get_the_time('Y'); 
    $food_grocery_store_archive_month = get_the_time('m'); 
    $food_grocery_store_archive_day   = get_the_time('d'); 
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('inner-service'); ?>>
    <h1><?php the_title(); ?></h1>
    <?php if( get_theme_mod( 'food_grocery_store_single_postdate',true) == 1 || get_theme_mod( 'food_grocery_store_single_author',true) == 1 || get_theme_mod( 'food_grocery_store_single_comments',true) == 1 || get_theme_mod( 'food_grocery_store_single_time',true) == 1) { ?>
        <div class="post-info p-2 mb-3">
          <?php if(get_theme_mod('food_grocery_store_single_postdate',true)==1){ ?>
            <i class="<?php echo esc_attr(get_theme_mod('food_grocery_store_single_postdate_icon','fas fa-calendar-alt')); ?>"></i><span class="entry-date"><a href="<?php echo esc_url( get_day_link( $food_grocery_store_archive_year, $food_grocery_store_archive_month, $food_grocery_store_archive_day)); ?>"><?php echo esc_html( get_the_date() ); ?><span class="screen-reader-text"><?php echo esc_html( get_the_date() ); ?></span></a></span><span><?php echo esc_html(get_theme_mod('food_grocery_store_single_post_meta_field_separator', '|'));?></span>
          <?php } ?>

          <?php if(get_theme_mod('food_grocery_store_single_author',true)==1){ ?>
             <i class="<?php echo esc_attr(get_theme_mod('food_grocery_store_single_author_icon','fas fa-user')); ?>"></i><span class="entry-author"><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' )) ); ?>"><?php the_author(); ?><span class="screen-reader-text"><?php the_author(); ?></span></a></span><span><?php echo esc_html(get_theme_mod('food_grocery_store_single_post_meta_field_separator', '|'));?></span>
          <?php } ?>

          <?php if(get_theme_mod('food_grocery_store_single_comments',true)==1){ ?>
             <i class="<?php echo esc_attr(get_theme_mod('food_grocery_store_single_comments_icon','fa fa-comments')); ?>" aria-hidden="true"></i><span class="entry-comments"><?php comments_number( __('0 Comment', 'food-grocery-store'), __('0 Comments', 'food-grocery-store'), __('% Comments', 'food-grocery-store') ); ?></span><span><?php echo esc_html(get_theme_mod('food_grocery_store_single_post_meta_field_separator', '|'));?></span>
          <?php } ?>

          <?php if(get_theme_mod('food_grocery_store_single_time',true)==1){ ?>
             <i class="<?php echo esc_attr(get_theme_mod('food_grocery_store_single_time_icon','fas fa-clock')); ?>"></i><span class="entry-time"><?php echo esc_html( get_the_time() ); ?></span>
          <?php } ?>
          <?php echo esc_html (food_grocery_store_edit_link()); ?>
        </div>
    <?php } ?>
    <?php if(has_post_thumbnail()) { ?>
        <div class="feature-box food-grocery-store-featured-image">
            <img class="page-image" src="<?php the_post_thumbnail_url('full'); ?>" alt="<?php the_title(); ?> post thumbnail image">
            <hr>
        </div>
    <?php } ?>
    <?php if( get_theme_mod( 'food_grocery_store_single_post_category',true) == 1) { ?>
        <div class="single-post-category mt-3">
            <span class="category"><?php esc_html_e('Categories:','food-grocery-store'); ?></span>
            <?php the_category(); ?>
        </div>
    <?php }?>
    <div class="entry-content">
        <?php the_content(); ?>
    </div>
    <?php if(get_theme_mod('food_grocery_store_toggle_tags',false)==1){ ?>
        <div class="tags-bg p-2 mt-3 mb-3">
            <?php the_tags(); ?>
        </div>
    <?php } ?>
        <?php
        // If comments are open or we have at least one comment, load up the comment template
        if ( comments_open() || '0' != get_comments_number() )
        comments_template();

        if ( is_singular( 'attachment' ) ) {
            // Parent post navigation.
            the_post_navigation( array(
                'prev_text' => _x( '<span class="meta-nav">Published in</span><span class="post-title">%title</span>', 'Parent post link', 'food-grocery-store' ),
            ) );
        } elseif ( is_singular( 'post' ) ) {
            // Previous/next post navigation.
            the_post_navigation( array(
                  'next_text' => '<span class="meta-nav" aria-hidden="true">' .esc_html(get_theme_mod('food_grocery_store_single_blog_next_navigation_text','NEXT')) . '</span> ' .
                        '<span class="screen-reader-text">' . __( 'Next post:', 'food-grocery-store' ) . '</span> ' .
                        '<span class="post-title">%title</span>',
                    'prev_text' => '<span class="meta-nav" aria-hidden="true">' .esc_html(get_theme_mod('food_grocery_store_single_blog_prev_navigation_text','PREVIOUS')) . '</span> ' .
                        '<span class="screen-reader-text">' . __( 'Previous post:', 'food-grocery-store' ) . '</span> ' .
                        '<span class="post-title">%title</span>',
            ) );
        }
    ?>
    <?php get_template_part('template-parts/related-posts'); ?>
</article>