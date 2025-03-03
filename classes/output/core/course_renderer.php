<?php
/**
 * Extends the core course renderer for theme_cajasan to customize the display of course elements.
 *
 * This class extends the default Moodle course renderer provided by the theme_remui. It allows
 * for customization specific to the needs of theme_cajasan, focusing primarily on the visual
 * presentation of course elements to align with the theme's aesthetics and functional requirements.
 *
 * @package    theme_cajasan
 * @category   output
 * @author     Pedro Alonso Arias Balcucho
 * @copyright  2025 Soporte cajasan <soporte@cajasan.co>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_cajasan\output\core;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use html_writer;
use core_course_category;
use coursecat_helper;
use stdClass;
use core_course_list_element;
use theme_remui\util\extras;

// Including the parent theme's course renderer for extension
require_once(__DIR__ . '/../../../../remui/classes/output/core/course_renderer.php');

/**
 * Class course_renderer
 *
 * Custom course renderer for theme_cajasan, extending the course renderer from theme_remui.
 * It can override methods from the parent class to alter the default rendering behavior or
 * add new methods to introduce new functionalities specific to theme_cajasan.
 */
class course_renderer extends \theme_remui\output\core\course_renderer {
    // Custom rendering methods can be defined here to override or extend the parent functionality
}
