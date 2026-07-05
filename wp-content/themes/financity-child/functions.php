<?php

function handleAjaxWd() {

    $args = array('post_type' => 'portfolio', 'post_status' => 'publish', 'suppress_filters' => false, "numberposts" => -1, "post_per_page" => -1);
    $catid = intval($_POST['catid']);
    $tagid = intval($_POST['tagid']);
    $financementid = intval($_POST['financementid']);

    if ($tagid || $catid || $financementid) {
        $args['tax_query'] = array(
            'relation' => "AND",
        );

        if ($catid) {
            array_push($args['tax_query'], array('terms' => $catid, 'taxonomy' => 'portfolio_category', 'field' => 'term_id'));
        }
        if ($tagid) {
            array_push($args['tax_query'], array('terms' => $tagid, 'taxonomy' => 'portfolio_tag', 'field' => 'term_id'));
        }
        if ($financementid) {
            array_push($args['tax_query'], array('terms' => $financementid, 'taxonomy' => 'type_financement', 'field' => 'term_id'));
        }
    }
    $q = new WP_Query($args);
    $posts = $q->get_posts();

    $data = [];
    $data['content'] = "";
    if (count($posts)) {
        $i = 0;
        foreach ($posts as $p) {
            $i++;
            $imageid = get_post_thumbnail_id($p->ID);
            $alt_text = get_post_meta($imageid, '_wp_attachment_image_alt', true);
            $size = "";
            $image_src = wp_get_attachment_image_src($imageid, "Blog Column Thumbnail");
            $data['content'] .= '<div class="gdlr-core-item-list  gdlr-core-item-pdlr gdlr-core-column-20 ' . ($i === 1 ? "gdlr-core-column-first" : "") . '" >
    <div class="gdlr-core-portfolio-grid  gdlr-core-center-align gdlr-core-style-with-frame gdlr-core-js" data-sync-height="portfolio-item-1"  >
        <div class="gdlr-core-portfolio-grid-frame gdlr-core-skin-e-background" style="opacity: 1 ;"  >
        </div>
        <div class="gdlr-core-portfolio-thumbnail gdlr-core-media-image  gdlr-core-style-title"  >
            <div class="gdlr-core-portfolio-thumbnail-image-wrap " >
                <a href="' . get_permalink($p->ID) . '" >
                    <img src="' . $image_src[0] . '" alt="" width="700" height="430" title="' . $alt_text . '" />
                    <span class="gdlr-core-image-overlay  gdlr-core-portfolio-overlay gdlr-core-image-overlay-center-icon gdlr-core-js"  >
                        <span class="gdlr-core-image-overlay-content" >
                            <span class="gdlr-core-portfolio-title gdlr-core-title-font " style="font-size: 15px ;font-weight: 400 ;letter-spacing: 1px ;text-transform: none ;"  >' . $p->post_title . '</span>
                        </span>
                    </span>
                </a>
            </div>
        </div>
        <div class="gdlr-core-portfolio-content-wrap gdlr-core-skin-divider"  >
            <h3 class="gdlr-core-portfolio-title gdlr-core-skin-title" style="font-size: 16px ;font-weight: 500 ;letter-spacing: 0px ;text-transform: none ;"  >
                <a href="' . get_permalink($p->ID) . '" >' . $p->post_title . '</a>
            </h3>
            <div class="gdlr-core-portfolio-content" >' . ($p->post_excerpt) . '</div>
            <div class="gdlr-core-portfolio-read-more-wrap" >
                <a class="gdlr-core-portfolio-read-more" href="' . get_permalink($p->ID) . '" >' . esc_html__("Read More", "goodlayers-core") . '</a>
            </div>
        </div>
    </div>
</div>  
    ';
        }
    }
    $data['load_more'] = "none";
    $data['status'] = "success";

    die(json_encode($data));
}

add_action('wp_ajax_gdlr_core_portfolio_wd_ajax', 'handleAjaxWd');
add_action('wp_ajax_nopriv_gdlr_core_portfolio_wd_ajax', 'handleAjaxWd');

add_shortcode("wd_portfolio", function($atts) {
    /**
     * On récupère les datas qu'il faut
     */
    $categories = get_terms([
        "taxonomy" => "portfolio_category",
        'hide_empty' => false,
    ]);
    $types_financements = get_terms([
        "taxonomy" => "type_financement",
        'hide_empty' => false,
    ]);
    $tags = get_tags([
        "taxonomy" => "portfolio_tag",
        'hide_empty' => false,
    ]);

    ob_start();
    ?>
    <div class="gdlr-core-pbf-background-wrap" style="background-color: #f5f5f5;"></div>
    <div id="gdlr-core-portfolio-item-wd" class="gdlr-core-portfolio-item gdlr-core-item-pdb clearfix gdlr-core-portfolio-item-style-grid" style="padding-bottom: 10px ;">
        <div id="gdlr-core-filterer-wrap-id" class="gdlr-core-filterer-wrap gdlr-core-js  gdlr-core-style-text gdlr-core-item-pdlr gdlr-core-center-align" data-target="gdlr-core-portfolio-item-holder" data-target-action="replace"><span class="filter-cat"><?php echo esc_html__('Sector :', 'goodlayers-core'); ?> </span>
            <a href="#" id="gdlr-core-filterer-d" class="gdlr-core-filterer-links-wd gdlr-core-filterer gdlr-core-button-color gdlr-core-active"><?php echo esc_html__('All', 'goodlayers-core'); ?></a>
            <?php foreach ($categories as $c) : ?>
                <a href="#" data-cat-id="<?php echo $c->term_id; ?>" class="gdlr-core-filterer-links-wd gdlr-core-filterer gdlr-core-button-color"><?php echo $c->name; ?></a>
            <?php endforeach; ?>
        </div>
        <div id="gdlr-core-filterer-wrap-id3" class="gdlr-core-filterer-wrap gdlr-core-js  gdlr-core-style-text gdlr-core-item-pdlr gdlr-core-center-align" data-target="gdlr-core-portfolio-item-holder" data-target-action="replace"><span class="filter-cat"><?php echo esc_html__('Financing :', 'goodlayers-core'); ?> </span>
            <a href="#" id="gdlr-core-filterer-d3" class="gdlr-core-filterer-links-wd3 gdlr-core-filterer gdlr-core-button-color gdlr-core-active"><?php echo esc_html__('All', 'goodlayers-core'); ?></a>
            <?php foreach ($types_financements as $type_financement) : ?>
                <a href="#" data-typefinancement-id="<?php echo $type_financement->term_id; ?>" class="gdlr-core-filterer-links-wd3 gdlr-core-filterer gdlr-core-button-color"><?php echo $type_financement->name; ?></a>
            <?php endforeach; ?>
        </div>
        <div id="gdlr-core-filterer-wrap-id2" class="gdlr-core-filterer-wrap gdlr-core-js  gdlr-core-style-text gdlr-core-item-pdlr gdlr-core-center-align" data-target="gdlr-core-portfolio-item-holder" data-target-action="replace"><span class="filter-cat"><?php echo esc_html__('Status :', 'goodlayers-core'); ?> </span>
            <a href="#" id="gdlr-core-filterer-d2" class="gdlr-core-filterer-links-wd2 gdlr-core-filterer gdlr-core-button-color gdlr-core-active"><?php echo esc_html__('All', 'goodlayers-core'); ?></a>
            <?php foreach ($tags as $t) : ?>
                <a href="#" data-tag-id="<?php echo $t->term_id; ?>" class="gdlr-core-filterer-links-wd2 gdlr-core-filterer gdlr-core-button-color"><?php echo $t->name; ?></a>
            <?php endforeach; ?>
        </div>

        <div class="gdlr-core-portfolio-item-holder gdlr-core-js-2 clearfix" data-layout="fitrows" style="opacity: 1;">

        </div>
    </div>

    <?php
    return ob_get_clean();
});


//function gdlr_core_portfolio_ajax() {
//    if (!empty($_POST['settings'])) {
//        $settings = $_POST['settings'];
//        if (!empty($_POST['option']['name']) && !empty($_POST['option']['value'])) {
//            if (in_array($_POST['option']['name'], array('paged', 'category'))) {
//                $settings[$_POST['option']['name']] = $_POST['option']['value'];
//
//                if ($_POST['option']['name'] == 'category') {
//                    $settings['paged'] = 1;
//                    unset($settings['tag']);
//                }
//            }
//        } else {
//            $settings['paged'] = 1;
//        }
//
//        $portfolio_item = new gdlr_core_portfolio_item($settings);
//        $query = $portfolio_item->get_portfolio_query();
//
//        if ($settings['portfolio-style'] == 'fixed-metro') {
//            $content = $portfolio_item->get_portfolio_fixed_metro($query);
//        } else {
//            $content = $portfolio_item->get_portfolio_grid_content($query);
//        }
//
//        $ret = array(
//            'status' => 'success',
//            'content' => $content
//        );
//        if (!empty($settings['pagination']) && $settings['pagination'] != 'none') {
//            $extra_class = ($settings['no-space'] == 'yes') ? '' : 'gdlr-core-item-pdlr';
//
//            // always change the load more button
//            if ($settings['pagination'] == 'load-more') {
//                $paged = empty($query->query['paged']) ? 2 : intval($query->query['paged']) + 1;
//                $ret['load_more'] = gdlr_core_get_ajax_load_more('portfolio', $settings, $paged, $query->max_num_pages, 'gdlr-core-portfolio-item-holder', $extra_class);
//                $ret['load_more'] = empty($ret['load_more']) ? 'none' : 2 . $ret['load_more'];
//
//                // change pagination on category filter
//            } else if (empty($_POST['option']['name']) || $_POST['option']['name'] == 'category') {
//                $ret['pagination'] = gdlr_core_get_ajax_pagination('portfolio', $settings, $query->max_num_pages, 'gdlr-core-portfolio-item-holder', $extra_class);
//                $ret['pagination'] = empty($ret['pagination']) ? 'none' : $ret['pagination'];
//            }
//        }
//
//        die(json_encode($ret));
//    } else {
//        die(json_encode(array(
//            'status' => 'failed',
//            'message' => esc_html__('Settings variable is not defined.', 'goodlayers-core-portfolio')
//        )));
//    }
//}

add_action('init', function() {
    register_taxonomy('type_financement', 'portfolio', array(
        'label' => 'Type de financements',
        'show_in_rest' => true,
        'hierarchical' => true, // important, si false alors on est en mode TAG, avec TRUE, on est en mode CATEGORIE.
        'labels' => array(
            'name' => 'Type de financements',
            'singular_name' => 'Type de financement',
            'all_items' => 'Toutes les types de financement',
            'edit_item' => 'Éditer le type de financement',
            'view_item' => 'Voir le type de financement',
            'update_item' => 'Mettre à jour le type de financement',
            'add_new_item' => 'Ajouter un type de financement',
            'new_item_name' => 'Nouveau type de financement',
            'search_items' => 'Rechercher parmi lestypes de financements',
            'popular_items' => 'Type de financement les plus utilisés'
    )));
});

// Disable core update emails
add_filter( 'auto_core_update_send_email', '__return_false' );

// Disable plugin update emails
add_filter( 'auto_plugin_update_send_email', '__return_false' );

// Disable theme update emails
add_filter( 'auto_theme_update_send_email', '__return_false' );

