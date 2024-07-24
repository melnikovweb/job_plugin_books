<?php

defined('ABSPATH') || exit;

class ParserLauncher
{
	protected static $pageName = 'Parsing Manager';
	protected static $pageSlug = 'parsing-manager';

	public static function setup()
	{
		add_action('admin_menu', [__CLASS__, 'menuPage'], 999, 1);

		if ( ! wp_next_scheduled( 'run_books_parser' ) ) {
			wp_schedule_event( strtotime( 'midnight' ), 'daily', 'run_books_parser' );
		}
		
		add_action( 'run_books_parser', 'run_books_parser' );
		
	}

	function run_books_parser() {
		self::runSetMainPost();
	}

	public static function menuPage()
	{
		add_menu_page(
			self::$pageName,
			self::$pageName,
			'administrator',
			self::$pageSlug,
			[self::class, 'menuPageCallback'],
			'dashicons-schedule',
			3
		);
	}

	public static function menuPageCallback()
	{
		$args = [
			'posts_per_page' => -1,
			'post_type'      => 'signatory-report',
			'meta_query'     => [
				[
					'key'     => 'report_manager',
					'compare' => 'NOT EXISTS'
				]
			]
		];

?>


		<form method="POST" action="">
			<?php submit_button('Run parsing books'); ?>
		</form>


<?php
		if (isset($_POST['submit']) && $_POST['submit'] === 'Run parsing books') {
			self::runSetMainPost();
		}
	}

	private static function runSetMainPost()
	{
		require 'parser.php';
	}
}

ParserLauncher::setup();