<?php
/**
 * Core: Tab_Base class
 *
 * @package WP_Chimp\Core
 * @since 0.3.0
 */

namespace WP_Chimp\Admin\Partials;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use WP_Chimp\Core\Hooks_Trait;
use WP_Chimp\Core\Lists\Query_Trait;

/**
 * The Tab base class.
 *
 * @since 0.6.0
 *
 * @property WP_Chimp\Core\Lists\Query $lists_query
 * @property string $title
 * @property string $slug
 */
abstract class Tab_Base implements Tab_Interface {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait, Query_Trait;

	/**
	 * The Tab title
	 *
	 * @since 0.6.0
	 * @var string
	 */
	protected $title;

	/**
	 * The Tab slug
	 *
	 * @since 0.6.0
	 * @var string
	 */
	protected $slug;

	/**
	 * Retrieve the Tab title.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Retrieve the Tab slug.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}
}
