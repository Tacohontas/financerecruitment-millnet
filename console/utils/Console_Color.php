<?php

namespace GPlate\Utils;

if ( ! defined( 'GPLATE_CONSOLE' ) ) exit; // Exit if accessed directly

class Console_Color {

	const BLACK = "\033[0;30m";
	const DARK_GREY = "\033[1;30m";
	const LIGHT_RED = "\033[1;31m";
	const LIGHT_GREEN = "\033[1;32m";
	const YELLOW = "\033[1;33m";
	const LIGHT_CYAN = "\033[1;36m";
	const WHITE = "\033[1;37m";

	const RESET = "\033[0m";

}