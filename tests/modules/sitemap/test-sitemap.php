<?php
/**
 * Class Test_Sitemap
 *
 * @package 
 */

/**
 * Sitemap test case.
 */

require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-sitemap-test-base.php';

class Test_Sitemap extends Sitemap_Test_Base {

	public function setUp(){
		parent::init();
		parent::setUp();
	}

	public function tearDown(){
		parent::init();
		parent::tearDown();
	}

	/**
	 * Creates posts and pages and tests whether only pages are being shown in the sitemap.
	 */
	public function test_only_pages() {
		$posts = $this->setup_posts( 2 );
		$pages = $this->setup_posts( 2, 0, 'page' );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'page' );

		$this->_setup_options( 'sitemap', $custom_options );

		$this->validate_sitemap(
			array(
					$pages['without'][0] => true,
					$pages['without'][1] => true,
					$posts['without'][0] => false,
					$posts['without'][1] => false,					
			)
		);
	}

	/**
	 * Creates posts with and without featured images and tests whether the sitemap
	 * 1) contains the image tag in the posts that have images attached.
	 * 2) does not contain the image tag in the posts that do not have images attached.
	 */
	public function test_featured_image() {
		$posts = $this->setup_posts( 2, 2 );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$with = $posts['with'];
		$without = $posts['without'];
		$this->validate_sitemap(
			array(
					$with[0] => array(
						'image'	=> true,
					),
					$with[1] => array(
						'image'	=> true,
					),
					$without[0] => array(
						'image'	=> false,
					),
					$without[1] => array(
						'image'	=> false,
					),
			)
		);
	}

	/**
	 * Creates posts with and without featured images and switches OFF the images from the sitemap. Tests that the sitemap does not contain the image tag for any post.
	 */
	public function test_exclude_images() {
		$posts = $this->setup_posts( 2, 2 );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = 'on';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$with = $posts['with'];
		$without = $posts['without'];
		$this->validate_sitemap(
			array(
					$with[0] => array(
						'image'	=> false,
					),
					$with[1] => array(
						'image'	=> false,
					),
					$without[0] => array(
						'image'	=> false,
					),
					$without[1] => array(
						'image'	=> false,
					),
			)
		);
	}

	/**
	 * Add WooCommerce product gallery images to XML sitemap.
	 *
	 * @ticket 366 Add WooCommerce product gallery images to XML sitemap
	 */
	public function test_woocommerce_gallery() {
		$woo = 'woocommerce/woocommerce.php';
		$file = dirname( dirname( AIOSEOP_UNIT_TESTING_DIR ) ) . '/';
		
		if ( ! file_exists( $file . $woo ) ) {
			$this->markTestSkipped( 'WooCommerce not installed. Skipping.' );
		}

		tests_add_filter( 'muplugins_loaded', function(){
			require $file . $woo;
		} );

		activate_plugin( $woo );

		if ( ! is_plugin_active( $woo ) ) {
			$this->markTestSkipped( 'WooCommerce not activated. Skipping.' );
		}

		// create 4 attachments.
		$attachments = array();
		for ( $x = 0; $x < 4; $x++ ) {
			$attachments[] = $this->upload_image_and_maybe_attach( str_replace( '\\', '/', AIOSEOP_UNIT_TESTING_DIR . '/resources/images/footer-logo.png' ) );
		}

		$id = $this->factory->post->create( array( 'post_type' => 'product' ) );
		update_post_meta( $id, '_product_image_gallery', implode( ',', $attachments ) );
		$url = get_permalink( $id );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'product' );

		$this->_setup_options( 'sitemap', $custom_options );

		$this->validate_sitemap(
			array(
					$url => array(
						'image'	=> true,
					),
			)
		);
	}
}


