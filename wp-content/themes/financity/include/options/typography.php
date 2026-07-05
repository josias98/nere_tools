<?php
	/*	
	*	Goodlayers Option
	*	---------------------------------------------------------------------
	*	This file store an array of theme options
	*	---------------------------------------------------------------------
	*/	

	$financity_admin_option->add_element(array(
	
		// typography head section
		'title' => esc_html__('Typography', 'financity'),
		'slug' => 'financity_typography',
		'icon' => get_template_directory_uri() . '/include/options/images/typography.png',
		'options' => array(
		
			// starting the subnav
			'font-family' => array(
				'title' => esc_html__('Font Family', 'financity'),
				'options' => array(
					'heading-font' => array(
						'title' => esc_html__('Heading Font', 'financity'),
						'type' => 'font',
						'data-type' => 'font',
						'default' => 'Open Sans',
						'selector' => '.financity-body h1, .financity-body h2, .financity-body h3, ' . 
							'.financity-body h4, .financity-body h5, .financity-body h6, .financity-body .financity-title-font,' .
							'.financity-body .gdlr-core-title-font{ font-family: #gdlr#; }' . 
							'.financity-body .gdlr-core-blockquote-item .gdlr-core-blockquote-item-content{ font-family: #gdlr#; }' . 
							'.financity-body .gdlr-core-counter-item .gdlr-core-counter-item-number, .financity-body .gdlr-core-counter-item .gdlr-core-counter-item-bottom-text{ font-family: #gdlr#; }' .
							'.woocommerce-breadcrumb, .woocommerce span.onsale, ' .
							'.single-product.woocommerce div.product p.price .woocommerce-Price-amount, .single-product.woocommerce #review_form #respond label{ font-family: #gdlr#; }'
					),
					'navigation-font' => array(
						'title' => esc_html__('Navigation Font', 'financity'),
						'type' => 'font',
						'data-type' => 'font',
						'default' => 'Open Sans',
						'selector' => '.financity-navigation .sf-menu > li > a, .financity-navigation .sf-vertical > li > a, .financity-navigation-font{ font-family: #gdlr#; }'
					),	
					'content-font' => array(
						'title' => esc_html__('Content Font', 'financity'),
						'type' => 'font',
						'data-type' => 'font',
						'default' => 'Open Sans',
						'selector' => '.financity-body, .financity-body .gdlr-core-content-font, ' . 
							'.financity-body input, .financity-body textarea, .financity-body button, .financity-body select, ' . 
							'.financity-body .financity-content-font, .gdlr-core-audio .mejs-container *{ font-family: #gdlr#; }'
					),
					'info-font' => array(
						'title' => esc_html__('Info Font', 'financity'),
						'type' => 'font',
						'data-type' => 'font',
						'default' => 'Open Sans',
						'selector' => '.financity-body .gdlr-core-info-font, .financity-body .financity-info-font{ font-family: #gdlr#; }'
					),
					'blog-info-font' => array(
						'title' => esc_html__('Blog Info Font', 'financity'),
						'type' => 'font',
						'data-type' => 'font',
						'default' => 'Open Sans',
						'selector' => '.financity-body .gdlr-core-blog-info-font, .financity-body .financity-blog-info-font{ font-family: #gdlr#; }'
					),
					'quote-font' => array(
						'title' => esc_html__('Quote Font', 'financity'),
						'type' => 'font',
						'data-type' => 'font',
						'default' => 'Open Sans',
						'selector' => '.financity-body .gdlr-core-quote-font, blockquote{ font-family: #gdlr#; }'
					),
					'testimonial-font' => array(
						'title' => esc_html__('Testimonial Font', 'financity'),
						'type' => 'font',
						'data-type' => 'font',
						'default' => 'Open Sans',
						'selector' => '.financity-body .gdlr-core-testimonial-content{ font-family: #gdlr#; }'
					),
					'additional-font' => array(
						'title' => esc_html__('Additional Font', 'financity'),
						'type' => 'font',
						'data-type' => 'font',
						'customizer' => false,
						'default' => 'Georgia, serif',
						'description' => esc_html__('Additional font you want to include for custom css.', 'financity')
					),
					'additional-font2' => array(
						'title' => esc_html__('Additional Font2', 'financity'),
						'type' => 'font',
						'data-type' => 'font',
						'customizer' => false,
						'default' => 'Georgia, serif',
						'description' => esc_html__('Additional font you want to include for custom css.', 'financity')
					),
					
				) // font-family-options
			), // font-family-nav
			
			'font-size' => array(
				'title' => esc_html__('Font Size', 'financity'),
				'options' => array(
				
					'h1-font-size' => array(
						'title' => esc_html__('H1 Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '52px',
						'selector' => '.financity-body h1{ font-size: #gdlr#; }' 
					),					
					'h2-font-size' => array(
						'title' => esc_html__('H2 Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '48px',
						'selector' => '.financity-body h2, #poststuff .gdlr-core-page-builder-body h2{ font-size: #gdlr#; }' 
					),					
					'h3-font-size' => array(
						'title' => esc_html__('H3 Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '36px',
						'selector' => '.financity-body h3{ font-size: #gdlr#; }' 
					),					
					'h4-font-size' => array(
						'title' => esc_html__('H4 Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '28px',
						'selector' => '.financity-body h4{ font-size: #gdlr#; }' 
					),					
					'h5-font-size' => array(
						'title' => esc_html__('H5 Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '22px',
						'selector' => '.financity-body h5{ font-size: #gdlr#; }' 
					),					
					'h6-font-size' => array(
						'title' => esc_html__('H6 Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '18px',
						'selector' => '.financity-body h6{ font-size: #gdlr#; }' 
					),					
					'navigation-font-size' => array(
						'title' => esc_html__('Navigation Font Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '14px',
						'selector' => '.financity-navigation .sf-menu > li > a, .financity-navigation .sf-vertical > li > a{ font-size: #gdlr#; }' 
					),	
					'navigation-font-weight' => array(
						'title' => esc_html__('Navigation Font Weight', 'financity'),
						'type' => 'text',
						'data-type' => 'text',
						'default' => '800',
						'selector' => '.financity-navigation .sf-menu > li > a, .financity-navigation .sf-vertical > li > a{ font-weight: #gdlr#; }',
						'description' => esc_html__('Eg. lighter, bold, normal, 300, 400, 600, 700, 800', 'financity')
					),	
					'navigation-font-letter-spacing' => array(
						'title' => esc_html__('Navigation Font Letter Spacing', 'financity'),
						'type' => 'text',
						'data-type' => 'pixel',
						'data-input-type' => 'pixel',
						'selector' => '.financity-navigation .sf-menu > li > a, .financity-navigation .sf-vertical > li > a{ letter-spacing: #gdlr#; }'
					),
					'navigation-text-transform' => array(
						'title' => esc_html__('Navigation Text Transform', 'financity'),
						'type' => 'combobox',
						'data-type' => 'text',
						'options' => array(
							'uppercase' => esc_html__('Uppercase', 'financity'),
							'lowercase' => esc_html__('Lowercase', 'financity'),
							'capitalize' => esc_html__('Capitalize', 'financity'),
							'none' => esc_html__('None', 'financity'),
						),
						'default' => 'uppercase',
						'selector' => '.financity-navigation .sf-menu > li > a, .financity-navigation .sf-vertical > li > a{ text-transform: #gdlr#; }',
					),
					'content-font-size' => array(
						'title' => esc_html__('Content Size', 'financity'),
						'type' => 'fontslider',
						'data-type' => 'pixel',
						'default' => '15px',
						'selector' => '.financity-body{ font-size: #gdlr#; }' 
					),
					'content-line-height' => array(
						'title' => esc_html__('Content Line Height', 'financity'),
						'type' => 'text',
						'data-type' => 'text',
						'default' => '1.7',
						'selector' => '.financity-body, .financity-body p, .financity-line-height, .gdlr-core-line-height{ line-height: #gdlr#; }'
					),
					
				) // font-size-options
			), // font-size-nav			
			
			'font-upload' => array(
				'title' => esc_html__('Font Uploader', 'financity'),
				'reload-after' => true,
				'customizer' => false,
				'options' => array(
				
					'font-upload' => array(
						'title' => esc_html__('Upload Fonts', 'financity'),
						'type' => 'custom',
						'item-type' => 'fontupload',
						'wrapper-class' => 'gdlr-core-fullsize',
					),
					
				) // fontupload-options
			), // fontupload-nav
		
		) // typography-options
		
	), 4);