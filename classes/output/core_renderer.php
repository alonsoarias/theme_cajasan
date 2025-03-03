<?php

/**
 * Provides the core rendering functionality for the theme_cajasan, aligning Moodle's HTML with Bootstrap expectations.
 *
 * This core renderer class extends theme_remui's core renderer, adding specific modifications to enhance and customize
 * the user interface for theme_cajasan. Key functionalities include customized login forms, theme settings integration,
 * and dynamic handling of UI elements like carousels and notices based on theme configurations.
 *
 * @package    theme_cajasan
 * @category   output
 * @author     ...
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_cajasan\output;

use html_writer;
use custom_menu;
use action_menu_filler;
use action_menu_link_secondary;
use stdClass;
use moodle_url;
use action_menu;
use pix_icon;
use theme_config;
use core_text;
use help_icon;
use context_system;
use core_course_list_element;
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../remui/classes/output/core_renderer.php');
require_once(__DIR__ . '/../util/theme_settings.php');

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 */
class core_renderer extends \theme_remui\output\core_renderer
{


    /**
     * Cached theme config
     * @var object
     */
    protected $themeConfig = null;

/**
 * Get theme config with caching
 * @return object
 */
public function get_theme_config()
{
    if ($this->themeConfig === null) {
        $this->themeConfig = theme_config::load('cajasan');
    }
    return $this->themeConfig;
}

    /**
     * Renders the login form.
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form)
    {
        global $SITE;

        $context = $form->export_for_template($this);

        // Prepara el mensaje de error, si lo hubiera.
        $context->errorformatted = $this->error_text($context->error);

        // Añade los textos para la autorización policial.
        $context->police_autorization = get_string('police_autorization', 'theme_cajasan');
        $context->police_autorization_help = get_string('police_autorization_help', 'theme_cajasan');

        // Logo from 'remui' or from 'cajasan'? 
        // Si deseas usar el logo del tema "cajasan" en vez de "remui", cambia la línea:
        $context->logourl = $this->get_theme_logo_url('logo');

        // Nombre del sitio.
        $context->sitename = format_string($SITE->fullname, true, ['context' => \context_course::instance(SITEID)]);

        // Renderiza la plantilla 'core/loginbox' (o la que uses en tu tema).
        return $this->render_from_template('core/loginbox', $context);
    }

    /**
     * Get theme image URL.
     *
     * @param string $img Image name
     * @return string Image URL
     */
    public function get_theme_img_url($img) {
        $theme = $this->get_theme_config();
        return $theme->setting_file_url($img, $img);
    }

    /**
     * Devuelve la URL del logo. Puede cambiarse para usar 'cajasan' o 'remui' según tu preferencia.
     *
     * @param string $img
     * @return string|null
     */
    public function get_theme_logo_url($img)
    {
        $theme = theme_config::load('remui');
        return $theme->setting_file_url($img, $img);
    }

    /**
     * Returns standard footer content.
     *
     * @return string HTML footer content
     */
    public function standard_footer_html()
    {
        global $CFG, $USER;

        $output = parent::standard_footer_html();
        $theme = $this->get_theme_config();

        // Add chat widget if enabled and user is logged in
        if (!empty($this->page->theme->settings->enable_chat) && isloggedin()) {
            $output .= $this->add_chat_widget();
        }

        // Add accessibility widget only if enabled and user is logged in
        if (isloggedin() && !empty($this->page->theme->settings->accessibility_widget)) {
            $output .= '<script src="https://website-widgets.pages.dev/dist/sienna.min.js" defer></script>';
            debugging('Accessibility widget loaded for user ID: ' . $USER->id, DEBUG_DEVELOPER);
        }

        // Add copy paste prevention if enabled
        if (!empty($this->page->theme->settings->copypaste_prevention)) {
            $this->add_copy_paste_prevention();
        }

        // Check if about text should be hidden
        if (
            isset($this->page->theme->settings->hideabouttext) &&
            $this->page->theme->settings->hideabouttext == 1
        ) {
            $output .= '<style>
                body section#top-footer { 
                    display: none !important; 
                }
            </style>';
        }

        return $output;
    }

    /**
     * Sobrescribe el método full_header para mostrar avisos generales u otros estilos en el header.
     *
     * @return string
     */
    public function full_header()
    {
        global $CFG, $USER, $PAGE;

        $theme = theme_config::load('cajasan');
        $output = '';

        // Ocultar secciones front page si está configurado
        if (!empty($theme->settings->hidefrontpagesections)) {
            $output .= '<style>.frontpage-sections { display: none; }</style>';
        }

        // Aviso general (notice)
        if (!empty(trim($theme->settings->generalnotice))) {
            $mode = $theme->settings->generalnoticemode;
            // 'info' => alert-info, 'danger' => alert-danger, 'off' => sin aviso
            if ($mode === 'info') {
                $output .= '<div class="alert alert-info mt-4"><strong><i class="fa fa-info-circle"></i></strong> ' . $theme->settings->generalnotice . '</div>';
            } else if ($mode === 'danger') {
                $output .= '<div class="alert alert-danger mt-4"><strong><i class="fa fa-warning"></i></strong> ' . $theme->settings->generalnotice . '</div>';
            }
        }

        // Recordatorio para admin, si el aviso está en modo 'off'
        if (is_siteadmin() && (!empty($theme->settings->generalnoticemode) && $theme->settings->generalnoticemode === 'off')) {
            $output .= '<div class="alert mt-4"><a href="' . $CFG->wwwroot . '/admin/settings.php?section=themesettingcajasan#theme_cajasan">' .
                '<strong><i class="fa fa-edit"></i></strong> ' . get_string('generalnotice_create', 'theme_cajasan') . '</a></div>';
        }

        // Validación de URL (por ejemplo, para sitios de prueba)
        if (!$this->check_allowed_urls()) {
            $popup_id = bin2hex(random_bytes(8));
            $output .= $this->show_unauthorized_access_overlay($popup_id);
        }

        // Continúa con el header normal.
        $output .= parent::full_header();
        return $output;
    }

    /**
     * Adds chat widget if enabled.
     *
     * @return string Chat widget HTML
     */
    protected function add_chat_widget()
    {
        global $USER;

        if (empty($this->page->theme->settings->tawkto_embed_url)) {
            return '';
        }

        // Sanitize user data
        $userData = [
            'name' => clean_param($USER->firstname . " " . $USER->lastname, PARAM_TEXT),
            'email' => clean_param($USER->email, PARAM_EMAIL),
            'username' => clean_param($USER->username, PARAM_USERNAME),
            'idnumber' => clean_param($USER->idnumber, PARAM_TEXT)
        ];

        return "<!--Start of Chat Script-->
        <script type=\"text/javascript\">
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        Tawk_API.visitor = " . json_encode($userData) . ";
        Tawk_API.onLoad = function(){
            Tawk_API.setAttributes(" . json_encode($userData) . ", function(error){});
        };
        (function(){
            var s1=document.createElement(\"script\"),s0=document.getElementsByTagName(\"script\")[0];
            s1.async=true;
            s1.src='" . clean_param($this->page->theme->settings->tawkto_embed_url, PARAM_URL) . "';
            s1.charset='UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1,s0);
        })();
        </script>
        <!--End of Chat Script-->";
    }

    /**
     * Adds copy/paste prevention JavaScript for specified roles.
     */
    protected function add_copy_paste_prevention() {
        global $USER, $PAGE, $COURSE;

        try {
            // Get restricted roles from theme settings
            $restricted_roles = $this->page->theme->settings->copypaste_roles;

            // Return if no roles are restricted or user is admin
            if (empty($restricted_roles) || is_siteadmin()) {
                return;
            }

            // Get appropriate context
            $context = null;
            if (!empty($COURSE->id) && $COURSE->id > 1) {
                $context = \context_course::instance($COURSE->id);
            } else if (!empty($PAGE->context)) {
                $context = $PAGE->context;
            }

            if (!$context) {
                return;
            }

            // Convert roles to array if needed
            if (!is_array($restricted_roles)) {
                $restricted_roles = explode(',', $restricted_roles);
            }

            // Check user roles
            $has_restricted_role = false;
            $user_roles = get_user_roles($context, $USER->id);

            foreach ($user_roles as $role) {
                if (in_array($role->roleid, $restricted_roles)) {
                    $has_restricted_role = true;
                    break;
                }
            }

            // Apply restrictions if needed
            if (isloggedin() && $has_restricted_role) {
                $PAGE->requires->js_call_amd('theme_cajasan/prevent_copy_paste', 'init');
            }

        } catch (\moodle_exception $e) {
            debugging('Error in copy/paste prevention: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return;
        }
    }

    /**
     * Muestra un overlay de acceso no autorizado.
     * 
     * @param string $popup_id Un ID único para el div
     * @return string
     */
    protected function show_unauthorized_access_overlay($popup_id)
    {
        $output = '';
        $output .= '<style>
            #' . $popup_id . ' {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                background: rgba(0, 0, 0, 0.75) !important;
                z-index: 10000 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                pointer-events: auto !important;
            }
            html, body {
                overflow: hidden !important; /* Prevent scrolling on the whole page */
            }
        </style>';

        $output .= '<div id="' . $popup_id . '">';
        $output .= '<div style="padding: 20px; background: white; border: 1px solid #ccc; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
        $output .= '<h2 style="color: red;">' . get_string('unauthorized_access', 'theme_cajasan') . '</h2>';
        $output .= '<p>' . get_string('unauthorized_access_msg', 'theme_cajasan') . '</p>';
        $output .= '</div>';
        $output .= '</div>';

        // JS para bloquear devtools e interacción
        $output .= '<script type="text/javascript">
            document.addEventListener("keydown", function(event) {
                // Bloquea F12 y ctrl+shift+i / ctrl+shift+j
                if (event.keyCode == 123 || (event.ctrlKey && event.shiftKey && (event.keyCode == 73 || event.keyCode == 74))) {
                    event.preventDefault();
                    alert("' . get_string('devtools_access_disabled', 'theme_cajasan') . '");
                    return false;
                }
            });
            setInterval(function() {
                if ((window.outerHeight - window.innerHeight) > 200 || (window.outerWidth - window.innerWidth) > 200) {
                    alert("' . get_string('devtools_access_disabled', 'theme_cajasan') . '");
                }
            }, 1000);
            // Previene interacción en el resto de la página
            document.body.style.pointerEvents = "none";
            document.addEventListener("contextmenu", event => event.preventDefault());
            document.body.addEventListener("click", function(e) {
                e.stopPropagation();
                return false;
            }, true);
        </script>';

        return $output;
    }

    /**
     * Comprueba si la URL actual está en la lista de URLs permitidas.
     * @return bool True si la URL está permitida, False en caso contrario.
     */
    protected function check_allowed_urls()
    {
        global $CFG;
        $allowed_urls = [
            'https://cajasan.moodlesoporte.net',
            'http://cajasan.moodlesoporte.net',
            'https://aulavirtual.cajasan.com',
            'http://aulavirtual.cajasan.com',
            'https://moodle45.localhost.com',
            'http://moodle45.localhost.com',
            'https://demomoodle.ingeweb.co',
            'http://demomoodle.ingeweb.co'
        ];

        return in_array($CFG->wwwroot, $allowed_urls);
    }
}
