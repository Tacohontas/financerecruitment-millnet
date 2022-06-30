<?php

namespace GPlate\Commands;

use GPlate\Console;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Inspire_Command extends Base_Command {

	/**
	 * @var string $signature
	 */
	protected $signature = 'inspire';

	/**
	 * @var string $description
	 */
	protected $description = 'When you need inspiration';

	/**
	 * @var array $quotes
	 */
	private $quotes = [
		'Your limitation is only your imagination.',
		'Time flies like an arrow, fruit flies likes banana.',
		'Push yourself, because no one else is going to do it for you.',
		'Sometimes later becomes never. Do it now.',
		'Great things never come from comfort zones.',
		'Martin gives you a pat on the shoulder.',
		'Martin gives you a high-five!',
		'Martin is cheering you up!',
		'Dream it. Wish it. Do it.',
		'The harder you work for something, the greater you\'ll feel when you achieve it.',
		'Dream bigger. Do bigger.',
		'Don\'t stop when you\'re tired. Stop when you\'re done.',
		'Experience is the name everyone gives to their mistakes.',
		'Perfection is achieved not when there is nothing more to add, but rather when there is nothing more to take away.',
		'When life gives you lemon, make lemonde!',
		'Shoot for the moon. Even if you miss, you\'ll land among the stars.',
		'If it doesn\'t challenge you, it won\'t change you.',
		'When action grows unprofitable, gather information. When information grows unprofitable, sleep.'
	];

	/**
	 * Handle this command
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function handle( $args = [] ) {
		Console::info( $this->quotes[ rand( 0, count( $this->quotes ) - 1 ) ] );
	}

}