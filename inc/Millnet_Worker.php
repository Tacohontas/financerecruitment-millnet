<?php

namespace Financerecruitment_Millnet;

use Financerecruitment_Millnet\Soap\Millnet;
use Financerecruitment_Millnet\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Millnet_Worker
 */
class Millnet_Worker {
	use Singleton;

	/**
	 * Form CSS class
	 */
	const FORM_CSS_CLASS = 'financerecruitment-millnet-form';
	
	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		// add_action('init', [$this, 'test']);
		add_filter( 'gform_admin_pre_render', [ $this, 'handle_form_pre_render' ] );
	}

		
	}

}
