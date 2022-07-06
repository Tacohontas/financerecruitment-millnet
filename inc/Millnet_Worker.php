<?php

namespace Financerecruitment_Millnet;

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
		
	}

}
