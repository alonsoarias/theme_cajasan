<?php

/**
 * cajasan.
 *
 * @package    theme_cajasan
 * @copyright  Creado por Ing Pablo A Pico - @pabloapico exclusivamente para plataformas Moodle creadas y soportadas por cajasan - Sistemas y Publicidad
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../remui/lib.php');

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_cajasan_get_extra_scss($theme) {
    $scss = '';
    // Cargando SCSS existente de variables y estilos personalizados
    $scss .= file_get_contents(__DIR__ . '/scss/_variables.scss');
    $scss .= file_get_contents(__DIR__ . '/scss/custom_variables.scss');
    $scss .= file_get_contents(__DIR__ . '/scss/cajasan.scss');

    // Añadiendo el contenido de custom.css
    $customCss = file_get_contents(__DIR__ . '/style/custom.css');
    $scss .= $customCss;

    return $scss;
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_cajasan_get_pre_scss($theme) {
    $scss = theme_remui_get_extra_scss($theme);
    return $scss;
}

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_cajasan_get_main_scss_content($theme) {
    global $CFG;

    // Primero, cargar el SCSS del tema padre (RemUI) directamente, ya que no podemos 
    // confiar en method_exists en este caso específico. Utilizamos la lógica original
    // de theme_remui_get_main_scss_content.
    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();
    $context = context_system::instance();

    if ($filename == 'default.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/remui/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/remui/scss/preset/plain.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_remui', 'preset', 0, '/', $filename))) {
        $scss .= $presetfile->get_content();
    } else {
        // Fallback de seguridad a default.scss si no se encuentra el preset especificado.
        $scss .= file_get_contents($CFG->dirroot . '/theme/remui/scss/preset/default.scss');
    }

    // Luego, cargar las personalizaciones SCSS de cajasan.
    $cajasanVariables = file_get_contents($CFG->dirroot . '/theme/cajasan/scss/_variables.scss');
    $customVariables = file_get_contents($CFG->dirroot . '/theme/cajasan/scss/custom_variables.scss');
    $cajasanScss = file_get_contents($CFG->dirroot . '/theme/cajasan/scss/cajasan.scss');

    // Cargar cualquier CSS personalizado desde 'custom.css'.
    $customCss = file_get_contents($CFG->dirroot . '/theme/cajasan/style/custom.css');

    // Combinar todos los estilos en el orden correcto.
    $combinedScssContent = $scss . "\n" . $cajasanVariables . "\n" . $customVariables . "\n" . $cajasanScss . "\n" . $customCss;

    return $combinedScssContent;
}

/**
 * Adds the footer image to CSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_cajasan_set_extra_img($theme) {
    global $OUTPUT;

    $css = '';
    $topfooterimg = $theme->setting_file_url('topfooterimg', 'topfooterimg');

    if (is_null($topfooterimg)) {
        $css =  "#top-footer {background-image: none;}";
    } else {
        $css = "#top-footer {background-image: url('$topfooterimg');}";
    }

    $content = '';

    // Sets the login background image.
    $loginimage = $theme->setting_file_url('loginimage', 'loginimage');
    if (!empty($loginimage)) {
        $content .= 'body.pagelayout-login #page { ';
        $content .= "background-image: url('$loginimage'); background-size: cover;";
        $content .= ' }';
    }

    // Always return the background image with the scss when we have it.
    return !empty($theme->settings->scss) ? $theme->settings->scss . ' ' . $content : $content;
    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return mixed
 */
function theme_cajasan_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    $theme = theme_config::load('cajasan');

    if ($context->contextlevel == CONTEXT_SYSTEM) {
        // Áreas válidas fijas.
        $validfileareas = [
            'loginimage',
            'personalareaheader',
            'mycoursesheader'
        ];
        
        // Si el área es una de las válidas, se sirve el archivo.
        if (in_array($filearea, $validfileareas)) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        }
        
        // Si el área corresponde a una imagen del slider (usando el prefijo "loging_"),
        // se procesa para extraer el número y servir el archivo.
        if (strpos($filearea, 'loging_slideimage') === 0) {
            $slide_number = substr($filearea, strlen('loging_slideimage'));
            return $theme->setting_file_serve("loging_slideimage{$slide_number}", $args, $forcedownload, $options);
        }
    }

    return theme_remui_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options);
}
