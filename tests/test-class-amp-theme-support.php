<?php
/**
 * Tests for Theme Support.
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Tests for Theme Support.
 *
 * @covers AMP_Theme_Support
 */
class Test_AMP_Theme_Support extends WP_UnitTestCase {

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 *
	 * @global WP_Scripts $wp_scripts
	 */
	public function tearDown() {
		global $wp_scripts;
		$wp_scripts = null;
		parent::tearDown();
		remove_theme_support( 'amp' );
		$_REQUEST                = array(); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		$_SERVER['QUERY_STRING'] = '';
		unset( $_SERVER['REQUEST_URI'] );
		unset( $_SERVER['REQUEST_METHOD'] );
		AMP_Theme_Support::$headers_sent = array();
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Theme_Support::init()
	 * @covers AMP_Theme_Support::finish_init()
	 */
	public function test_init() {
		$this->markTestIncomplete();
	}

	/**
	 * Test redirect_canonical_amp.
	 *
	 * @covers AMP_Theme_Support::redirect_canonical_amp()
	 */
	public function test_redirect_canonical_amp() {
		$this->markTestIncomplete();
	}

	/**
	 * Test is_paired_available.
	 *
	 * @covers AMP_Theme_Support::is_paired_available()
	 */
	public function test_is_paired_available() {

		// Establish initial state.
		$post_id = $this->factory()->post->create( array( 'post_title' => 'Test' ) );
		remove_theme_support( 'amp' );
		query_posts( array( 'p' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_singular() );

		// Paired support is not available if theme support is not present or canonical.
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
		add_theme_support( 'amp' );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );

		// Paired mode is available once template_dir is supplied.
		add_theme_support( 'amp', array(
			'template_dir' => 'amp-templates',
		) );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );

		// Paired mode not available when post does not support AMP.
		add_filter( 'amp_skip_post', '__return_true' );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
		$this->assertTrue( is_singular() );
		query_posts( array( 's' => 'test' ) ); // phpcs:ignore
		$this->assertTrue( is_search() );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );
		remove_filter( 'amp_skip_post', '__return_true' );

		// Check that available_callback works.
		add_theme_support( 'amp', array(
			'template_dir'       => 'amp-templates',
			'available_callback' => 'is_singular',
		) );
		query_posts( array( 'p' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_singular() );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );

		query_posts( array( 's' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_search() );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
	}

	/**
	 * Test is_customize_preview_iframe.
	 *
	 * @covers AMP_Theme_Support::is_customize_preview_iframe()
	 */
	public function test_is_customize_preview_iframe() {
		$this->markTestIncomplete();
	}

	/**
	 * Test register_paired_hooks.
	 *
	 * @covers AMP_Theme_Support::register_paired_hooks()
	 */
	public function test_register_paired_hooks() {
		$this->markTestIncomplete();
	}

	/**
	 * Test add_hooks.
	 *
	 * @covers AMP_Theme_Support::add_hooks()
	 */
	public function test_add_hooks() {
		$this->markTestIncomplete();
	}

	/**
	 * Test purge_amp_query_vars.
	 *
	 * @covers AMP_Theme_Support::purge_amp_query_vars()
	 */
	public function test_purge_amp_query_vars() {
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification
		$bad_query_vars = array(
			'amp_latest_update_time' => '1517199956',
			'__amp_source_origin'    => home_url(),
		);
		$ok_query_vars  = array(
			'bar' => 'baz',
		);
		$all_query_vars = array_merge( $bad_query_vars, $ok_query_vars );

		$_SERVER['QUERY_STRING'] = build_query( $all_query_vars );

		remove_action( 'wp', 'amp_maybe_add_actions' );
		$this->go_to( add_query_arg( $all_query_vars, home_url( '/foo/' ) ) );
		$_REQUEST = $_GET; // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		foreach ( $all_query_vars as $key => $value ) {
			$this->assertArrayHasKey( $key, $_GET ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertArrayHasKey( $key, $_REQUEST ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertContains( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertContains( "$key=$value", $_SERVER['REQUEST_URI'] );
		}

		AMP_Theme_Support::$purged_amp_query_vars = array();
		AMP_Theme_Support::purge_amp_query_vars();
		$this->assertEqualSets( AMP_Theme_Support::$purged_amp_query_vars, $bad_query_vars );

		foreach ( $bad_query_vars as $key => $value ) {
			$this->assertArrayNotHasKey( $key, $_GET ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertArrayNotHasKey( $key, $_REQUEST ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertNotContains( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertNotContains( "$key=$value", $_SERVER['REQUEST_URI'] );
		}
		foreach ( $ok_query_vars as $key => $value ) {
			$this->assertArrayHasKey( $key, $_GET ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertArrayHasKey( $key, $_REQUEST ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$this->assertContains( "$key=$value", $_SERVER['QUERY_STRING'] );
			$this->assertContains( "$key=$value", $_SERVER['REQUEST_URI'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	/**
	 * Test send_header.
	 *
	 * @covers AMP_Theme_Support::send_header()
	 */
	public function test_send_header() {
		$this->markTestIncomplete();
	}

	/**
	 * Test handle_xhr_request().
	 *
	 * @covers AMP_Theme_Support::handle_xhr_request()
	 */
	public function test_handle_xhr_request() {
		AMP_Theme_Support::purge_amp_query_vars();
		AMP_Theme_Support::handle_xhr_request();
		$this->assertEmpty( AMP_Theme_Support::$headers_sent );

		$_GET['_wp_amp_action_xhr_converted'] = '1';

		// Try bad source origin.
		$_GET['__amp_source_origin'] = 'http://evil.example.com/';
		$_SERVER['REQUEST_METHOD']   = 'POST';
		AMP_Theme_Support::purge_amp_query_vars();
		AMP_Theme_Support::handle_xhr_request();
		$this->assertEmpty( AMP_Theme_Support::$headers_sent );

		// Try home source origin.
		$_GET['__amp_source_origin'] = home_url();
		$_SERVER['REQUEST_METHOD']   = 'POST';
		AMP_Theme_Support::purge_amp_query_vars();
		AMP_Theme_Support::handle_xhr_request();
		$this->assertCount( 1, AMP_Theme_Support::$headers_sent );
		$this->assertEquals(
			array(
				'name'        => 'AMP-Access-Control-Allow-Source-Origin',
				'value'       => home_url(),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Theme_Support::$headers_sent[0]
		);
		$this->assertEquals( PHP_INT_MAX, has_filter( 'wp_redirect', array( 'AMP_Theme_Support', 'intercept_post_request_redirect' ) ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'comment_post_redirect', array( 'AMP_Theme_Support', 'filter_comment_post_redirect' ) ) );
		$this->assertEquals(
			array( 'AMP_Theme_Support', 'handle_wp_die' ),
			apply_filters( 'wp_die_handler', '__return_true' )
		);
	}

	/**
	 * Test filter_comment_post_redirect().
	 *
	 * @covers AMP_Theme_Support::filter_comment_post_redirect()
	 */
	public function test_filter_comment_post_redirect() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', function() {
			return '__return_null';
		} );

		add_theme_support( 'amp' );
		$post    = $this->factory()->post->create_and_get();
		$comment = $this->factory()->comment->create_and_get( array(
			'comment_post_ID' => $post->ID,
		) );
		$url     = get_comment_link( $comment );

		// Test without comments_live_list.
		$filtered_url = AMP_Theme_Support::filter_comment_post_redirect( $url, $comment );
		$this->assertNotEquals(
			strtok( $url, '#' ),
			strtok( $filtered_url, '#' )
		);

		// Test with comments_live_list.
		add_theme_support( 'amp', array(
			'comments_live_list' => true,
		) );
		add_filter( 'amp_comment_posted_message', function( $message, WP_Comment $filter_comment ) {
			return sprintf( '(comment=%d,approved=%d)', $filter_comment->comment_ID, $filter_comment->comment_approved );
		}, 10, 2 );

		// Test approved comment.
		$comment->comment_approved = '1';
		ob_start();
		AMP_Theme_Support::filter_comment_post_redirect( $url, $comment );
		$response = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'message', $response );
		$this->assertEquals(
			sprintf( '(comment=%d,approved=1)', $comment->comment_ID ),
			$response['message']
		);

		// Test moderated comment.
		$comment->comment_approved = '0';
		ob_start();
		AMP_Theme_Support::filter_comment_post_redirect( $url, $comment );
		$response = json_decode( ob_get_clean(), true );
		$this->assertArrayHasKey( 'message', $response );
		$this->assertEquals(
			sprintf( '(comment=%d,approved=0)', $comment->comment_ID ),
			$response['message']
		);
	}

	/**
	 * Test handle_wp_die().
	 *
	 * @covers AMP_Theme_Support::handle_wp_die()
	 */
	public function test_handle_wp_die() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', function() {
			return '__return_null';
		} );

		ob_start();
		AMP_Theme_Support::handle_wp_die( 'string' );
		$this->assertEquals( '{"error":"string"}', ob_get_clean() );

		ob_start();
		$error = new WP_Error( 'code', 'The Message' );
		AMP_Theme_Support::handle_wp_die( $error );
		$this->assertEquals( '{"error":"The Message"}', ob_get_clean() );
	}

	/**
	 * Test intercept_post_request_redirect().
	 *
	 * @covers AMP_Theme_Support::intercept_post_request_redirect()
	 */
	public function test_intercept_post_request_redirect() {

		add_theme_support( 'amp' );
		$url = home_url( '/', 'https' );

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', function () {
			return '__return_false';
		} );

		// Test redirecting to full URL with HTTPS protocol.
		AMP_Theme_Support::$headers_sent = array();
		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( $url );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => $url,
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Theme_Support::$headers_sent
		);
		$this->assertContains(
			array(
				'name'        => 'Access-Control-Expose-Headers',
				'value'       => 'AMP-Redirect-To',
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Theme_Support::$headers_sent
		);

		// Test redirecting to non-HTTPS URL.
		AMP_Theme_Support::$headers_sent = array();
		ob_start();
		$url = home_url( '/', 'http' );
		AMP_Theme_Support::intercept_post_request_redirect( $url );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => preg_replace( '#^\w+:#', '', $url ),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Theme_Support::$headers_sent
		);
		$this->assertContains(
			array(
				'name'        => 'Access-Control-Expose-Headers',
				'value'       => 'AMP-Redirect-To',
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Theme_Support::$headers_sent
		);

		// Test redirecting to host-less location.
		AMP_Theme_Support::$headers_sent = array();
		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( '/new-location/' );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => set_url_scheme( home_url( '/new-location/' ), 'https' ),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Theme_Support::$headers_sent
		);

		// Test redirecting to scheme-less location.
		AMP_Theme_Support::$headers_sent = array();
		ob_start();
		$url = home_url( '/new-location/' );
		AMP_Theme_Support::intercept_post_request_redirect( substr( $url, strpos( $url, ':' ) + 1 ) );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => set_url_scheme( home_url( '/new-location/' ), 'https' ),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Theme_Support::$headers_sent
		);

		// Test redirecting to empty location.
		AMP_Theme_Support::$headers_sent = array();
		ob_start();
		AMP_Theme_Support::intercept_post_request_redirect( '' );
		$this->assertEquals( '{"success":true}', ob_get_clean() );
		$this->assertContains(
			array(
				'name'        => 'AMP-Redirect-To',
				'value'       => set_url_scheme( home_url(), 'https' ),
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Theme_Support::$headers_sent
		);
	}

	/**
	 * Test register_widgets().
	 *
	 * @covers AMP_Theme_Support::register_widgets()
	 * @global WP_Widget_Factory $wp_widget_factory
	 */
	public function test_register_widgets() {
		global $wp_widget_factory;
		remove_all_actions( 'widgets_init' );
		$wp_widget_factory->widgets = array();
		wp_widgets_init();
		AMP_Theme_Support::register_widgets();

		$this->assertArrayNotHasKey( 'WP_Widget_Categories', $wp_widget_factory->widgets );
		$this->assertArrayHasKey( 'AMP_Widget_Categories', $wp_widget_factory->widgets );
	}

	/**
	 * Test register_content_embed_handlers.
	 *
	 * @covers AMP_Theme_Support::register_content_embed_handlers()
	 */
	public function test_register_content_embed_handlers() {
		$this->markTestIncomplete();
	}

	/**
	 * Test set_comments_walker.
	 *
	 * @covers AMP_Theme_Support::set_comments_walker()
	 */
	public function test_set_comments_walker() {
		$this->markTestIncomplete();
	}

	/**
	 * Test amend_comment_form().
	 *
	 * @covers AMP_Theme_Support::amend_comment_form()
	 */
	public function test_amend_comment_form() {
		$post_id = $this->factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );
		$this->assertTrue( is_singular() );

		// Test native AMP.
		add_theme_support( 'amp' );
		$this->assertTrue( amp_is_canonical() );
		ob_start();
		AMP_Theme_Support::amend_comment_form();
		$output = ob_get_clean();
		$this->assertNotContains( '<input type="hidden" name="redirect_to"', $output );
		$this->assertContains( '<div submit-success>', $output );
		$this->assertContains( '<div submit-error>', $output );

		// Test paired AMP.
		add_theme_support( 'amp', array(
			'template_dir' => 'amp-templates',
		) );
		$this->assertFalse( amp_is_canonical() );
		ob_start();
		AMP_Theme_Support::amend_comment_form();
		$output = ob_get_clean();
		$this->assertContains( '<input type="hidden" name="redirect_to"', $output );
		$this->assertContains( '<div submit-success>', $output );
		$this->assertContains( '<div submit-error>', $output );
	}

	/**
	 * Test add_amp_comment_form_templates.
	 *
	 * @covers AMP_Theme_Support::add_amp_comment_form_templates()
	 */
	public function test_add_amp_comment_form_templates() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_paired_template_hierarchy.
	 *
	 * @covers AMP_Theme_Support::filter_paired_template_hierarchy()
	 */
	public function test_filter_paired_template_hierarchy() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_paired_template_include.
	 *
	 * @covers AMP_Theme_Support::filter_paired_template_include()
	 */
	public function test_filter_paired_template_include() {
		$this->markTestIncomplete();
	}

	/**
	 * Test get_current_canonical_url.
	 *
	 * @covers AMP_Theme_Support::get_current_canonical_url()
	 */
	public function test_get_current_canonical_url() {
		$this->markTestIncomplete();
	}

	/**
	 * Test get_comment_form_state_id.
	 *
	 * @covers AMP_Theme_Support::get_comment_form_state_id()
	 */
	public function test_get_comment_form_state_id() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_comment_form_defaults.
	 *
	 * @covers AMP_Theme_Support::filter_comment_form_defaults()
	 */
	public function test_filter_comment_form_defaults() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_comment_reply_link.
	 *
	 * @covers AMP_Theme_Support::filter_comment_reply_link()
	 */
	public function test_filter_comment_reply_link() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_cancel_comment_reply_link.
	 *
	 * @covers AMP_Theme_Support::filter_cancel_comment_reply_link()
	 */
	public function test_filter_cancel_comment_reply_link() {
		$this->markTestIncomplete();
	}

	/**
	 * Test print_amp_styles.
	 *
	 * @covers AMP_Theme_Support::print_amp_styles()
	 */
	public function test_print_amp_styles() {
		$this->markTestIncomplete();
	}

	/**
	 * Test ensure_required_markup().
	 *
	 * @dataProvider get_script_data
	 * @covers AMP_Theme_Support::ensure_required_markup()
	 * @param string  $script The value of the script.
	 * @param boolean $expected The expected result.
	 */
	public function test_ensure_required_markup( $script, $expected ) {
		$page = '<html><head><script type="application/ld+json">%s</script></head><body>Test</body></html>';
		$dom  = new DOMDocument();
		$dom->loadHTML( sprintf( $page, $script ) );
		AMP_Theme_Support::ensure_required_markup( $dom );
		$this->assertEquals( $expected, substr_count( $dom->saveHTML(), 'schema.org' ) );
	}

	/**
	 * Test dequeue_customize_preview_scripts.
	 *
	 * @covers AMP_Theme_Support::dequeue_customize_preview_scripts()
	 */
	public function test_dequeue_customize_preview_scripts() {
		$this->markTestIncomplete();
	}

	/**
	 * Test start_output_buffering.
	 *
	 * @covers AMP_Theme_Support::start_output_buffering()
	 */
	public function test_start_output_buffering() {
		$this->markTestIncomplete();
	}

	/**
	 * Test finish_output_buffering.
	 *
	 * @covers AMP_Theme_Support::finish_output_buffering()
	 */
	public function test_finish_output_buffering() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_customize_partial_render.
	 *
	 * @covers AMP_Theme_Support::filter_customize_partial_render()
	 */
	public function test_filter_customize_partial_render() {
		$this->markTestIncomplete();
	}

	/**
	 * Test prepare_response.
	 *
	 * @global WP_Widget_Factory $wp_widget_factory
	 * @global WP_Scripts $wp_scripts
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response() {
		global $wp_widget_factory, $wp_scripts;
		$wp_scripts = null;

		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();
		$wp_widget_factory = new WP_Widget_Factory();
		wp_widgets_init();

		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_script( 'amp-list' );
		} );
		add_action( 'wp_footer', function() {
			wp_print_scripts( 'amp-mathml' );
			?>
			<amp-mathml layout="container" data-formula="\[x = {-b \pm \sqrt{b^2-4ac} \over 2a}.\]"></amp-mathml>
			<?php
		}, 1 );

		ob_start();
		?>
		<!DOCTYPE html>
		<html amp <?php language_attributes(); ?>>
			<head>
				<?php wp_head(); ?>
				<script data-head>document.write('Illegal');</script>
			</head>
			<body>
				<img width="100" height="100" src="https://example.com/test.png">
				<audio width="400" height="300" src="https://example.com/audios/myaudio.mp3"></audio>
				<amp-ad type="a9"
					width="300"
					height="250"
					data-aax_size="300x250"
					data-aax_pubname="test123"
					data-aax_src="302"></amp-ad>
				<?php wp_footer(); ?>

				<button onclick="alert('Illegal');">no-onclick</button>

				<style>body { background: black; }</style>
			</body>
		</html>
		<?php
		$original_html  = trim( ob_get_clean() );
		$removed_nodes  = array();
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html, array(
			'validation_error_callback' => function( $removed ) use ( &$removed_nodes ) {
				$removed_nodes[ $removed['node']->nodeName ] = $removed['node'];
			},
		) );

		$this->assertContains( '<meta charset="' . get_bloginfo( 'charset' ) . '">', $sanitized_html );
		$this->assertContains( '<meta name="viewport" content="width=device-width,minimum-scale=1">', $sanitized_html );
		$this->assertContains( '<style amp-boilerplate>', $sanitized_html );
		$this->assertContains( '<style amp-custom>body { background: black; }', $sanitized_html );
		$this->assertContains( '<script type="text/javascript" src="https://cdn.ampproject.org/v0.js" async></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<script type="text/javascript" src="https://cdn.ampproject.org/v0/amp-list-latest.js" async custom-element="amp-list"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<script type="text/javascript" src="https://cdn.ampproject.org/v0/amp-mathml-latest.js" async custom-element="amp-mathml"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<meta name="generator" content="AMP Plugin', $sanitized_html );

		$this->assertNotContains( '<img', $sanitized_html );
		$this->assertContains( '<amp-img', $sanitized_html );

		$this->assertNotContains( '<audio', $sanitized_html );
		$this->assertContains( '<amp-audio', $sanitized_html );

		// Note these are single-quoted because they are injected after the DOM has been re-serialized, so the type and src attributes come from WP_Scripts::do_item().
		$this->assertContains( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0/amp-audio-latest.js\' async custom-element="amp-audio"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$this->assertContains( '<script type=\'text/javascript\' src=\'https://cdn.ampproject.org/v0/amp-ad-latest.js\' async custom-element="amp-ad"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		$this->assertContains( '<button>no-onclick</button>', $sanitized_html );
		$this->assertCount( 3, $removed_nodes );
		$this->assertInstanceOf( 'DOMElement', $removed_nodes['script'] );
		$this->assertInstanceOf( 'DOMAttr', $removed_nodes['onclick'] );
	}

	/**
	 * Test prepare_response for bad/non-HTML.
	 *
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response_bad_html() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();

		// JSON.
		$input = '{"success":true}';
		$this->assertEquals( $input, AMP_Theme_Support::prepare_response( $input ) );

		// Nothing, for redirect.
		$input = '';
		$this->assertEquals( $input, AMP_Theme_Support::prepare_response( $input ) );

		// HTML, but very stripped down.
		$input  = '<html>Hello</html>';
		$output = AMP_Theme_Support::prepare_response( $input );
		$this->assertContains( '<html amp', $output );
	}

	/**
	 * Test prepare_response to inject html[amp] attribute and ensure HTML5 doctype.
	 *
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response_to_add_html5_doctype_and_amp_attribute() {
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		ob_start();
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html><head><?php wp_head(); ?></head><body><?php wp_footer(); ?></body></html>
		<?php
		$original_html  = trim( ob_get_clean() );
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html );

		$this->assertStringStartsWith( '<!DOCTYPE html>', $sanitized_html );
		$this->assertContains( '<html amp', $sanitized_html );
	}

	/**
	 * Data provider for test_ensure_required_markup.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array(
			'schema_org_not_present'        => array(
				'',
				1,
			),
			'schema_org_present'            => array(
				wp_json_encode( array( '@context' => 'http://schema.org' ) ),
				1,
			),
			'schema_org_output_not_escaped' => array(
				'{"@context":"http://schema.org"',
				1,
			),
			'schema_org_another_key'        => array(
				wp_json_encode( array( '@anothercontext' => 'https://schema.org' ) ),
				1,
			),
		);
	}

	/**
	 * Test enqueue_assets().
	 *
	 * @covers AMP_Theme_Support::enqueue_assets()
	 */
	public function test_enqueue_assets() {
		$script_slug = 'amp-runtime';
		$style_slug  = 'amp-default';
		wp_dequeue_script( $script_slug );
		wp_dequeue_style( $style_slug );
		AMP_Theme_Support::enqueue_assets();
		$this->assertTrue( in_array( $script_slug, wp_scripts()->queue, true ) );
		$this->assertTrue( in_array( $style_slug, wp_styles()->queue, true ) );
	}
}
