<?php global $wp_query, $post;
 ?>
<?php $skip_min_height = false; ?><section class="u-clearfix u-section-1" id="sec-aede">
  <div class="u-clearfix u-sheet u-sheet-1">
    <?php get_template_search_form(); ?><!--blog--><!--blog_options_json--><!--{"type":"Recent","source":"","tags":"","count":"3"}--><!--/blog_options_json-->
    <div class="u-blog u-expanded-width u-layout-grid u-blog-1" data-items-per-page="1" data-max-items="3">
      <div class="u-list-control"></div>
      <div class="u-repeater u-repeater-1"><?php
                                    $countItems = 3;
                                    while (have_posts()) { the_post();
                                    $templateOrder = $wp_query->current_post % $countItems;
                                ?><?php if ($templateOrder == 0) { ?><!--blog_post-->
        <?php $postItemInvisible = !has_post_thumbnail() ? true : false; ?><div class="u-align-left u-blog-post u-container-align-left u-container-style u-repeater-item">
          <div class="u-container-layout u-similar-container u-container-layout-1"><!--blog_post_header-->
            <h3 class="u-blog-control u-text u-text-1">
              <?php if (!is_singular()): ?><a class="u-post-header-link" href="<?php the_permalink(); ?>"><?php endif; ?><?php the_title(); ?><?php if (!is_singular()): ?></a><?php endif; ?>
            </h3><!--/blog_post_header--><!--blog_post_content-->
            <div class="u-blog-control u-post-content u-text u-text-2"><?php echo !is_search() && (is_singular() || $post->post_type !== 'post') ? theme_get_content() : theme_get_excerpt(); ?></div><!--/blog_post_content--><!--blog_post_readmore-->
            <a href="<?php the_permalink(); ?>" class="u-blog-control u-btn u-button-style u-btn-1"><!--blog_post_readmore_content--><!--options_json--><!--{"content":"","defaultValue":"Read More"}--><!--/options_json--><?php _e(sprintf(__('Read More', 'kakiteollisuus'))); ?><!--/blog_post_readmore_content--></a><!--/blog_post_readmore-->
          </div>
        </div><!--/blog_post--><?php } ?><?php if ($templateOrder == 1) { ?><!--blog_post-->
        <?php $postItemInvisible = !has_post_thumbnail() ? true : false; ?><div class="u-align-left u-blog-post u-container-align-left u-container-style u-repeater-item">
          <div class="u-container-layout u-similar-container u-container-layout-2"><!--blog_post_header-->
            <h3 class="u-blog-control u-text u-text-3">
              <?php if (!is_singular()): ?><a class="u-post-header-link" href="<?php the_permalink(); ?>"><?php endif; ?><?php the_title(); ?><?php if (!is_singular()): ?></a><?php endif; ?>
            </h3><!--/blog_post_header--><!--blog_post_content-->
            <div class="u-blog-control u-post-content u-text u-text-4"><?php echo !is_search() && (is_singular() || $post->post_type !== 'post') ? theme_get_content() : theme_get_excerpt(); ?></div><!--/blog_post_content--><!--blog_post_readmore-->
            <a href="<?php the_permalink(); ?>" class="u-blog-control u-btn u-button-style u-btn-2"><!--blog_post_readmore_content--><!--options_json--><!--{"content":"","defaultValue":"Read More"}--><!--/options_json--><?php _e(sprintf(__('Read More', 'kakiteollisuus'))); ?><!--/blog_post_readmore_content--></a><!--/blog_post_readmore-->
          </div>
        </div><!--/blog_post--><?php } ?><?php if ($templateOrder == 2) { ?><!--blog_post-->
        <?php $postItemInvisible = !has_post_thumbnail() ? true : false; ?><div class="u-align-left u-blog-post u-container-align-left u-container-style u-repeater-item">
          <div class="u-container-layout u-similar-container u-container-layout-3"><!--blog_post_header-->
            <h3 class="u-blog-control u-text u-text-5">
              <?php if (!is_singular()): ?><a class="u-post-header-link" href="<?php the_permalink(); ?>"><?php endif; ?><?php the_title(); ?><?php if (!is_singular()): ?></a><?php endif; ?>
            </h3><!--/blog_post_header--><!--blog_post_content-->
            <div class="u-blog-control u-post-content u-text u-text-6"><?php echo !is_search() && (is_singular() || $post->post_type !== 'post') ? theme_get_content() : theme_get_excerpt(); ?></div><!--/blog_post_content--><!--blog_post_readmore-->
            <a href="<?php the_permalink(); ?>" class="u-blog-control u-btn u-button-style u-btn-3"><!--blog_post_readmore_content--><!--options_json--><!--{"content":"","defaultValue":"Read More"}--><!--/options_json--><?php _e(sprintf(__('Read More', 'kakiteollisuus'))); ?><!--/blog_post_readmore_content--></a><!--/blog_post_readmore-->
          </div>
        </div><!--/blog_post--><?php } ?><?php } if (!have_posts()) { ?>
          <div class="u-search-not-found-results u-text u-align-center">No search result found</div>
    <?php } ?>
      </div>
      <div class="u-list-control"></div>
    </div><!--/blog-->
  </div>
</section><?php if ($skip_min_height) { echo "<style> .u-section-1, .u-section-1 .u-sheet {min-height: auto;}</style>"; } ?>