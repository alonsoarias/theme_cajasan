<?php
// This file is part of Moodle - http://moodle.org/
// 
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// 
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require(__DIR__ . '/../remui/settings.php');
require_once(__DIR__ . '/classes/admin_settingspage_tabs.php');
require_once($CFG->libdir . '/accesslib.php');
require_once(__DIR__ . '/lib.php');

// Capturar pestañas del tema padre (si existen)
$parent_tabs = null;
if (isset($settings) && method_exists($settings, 'get_tabs')) {
    $parent_tabs = $settings->get_tabs();
}

unset($settings);
$settings = null;

// Crear categoría en "Apariencia"
$ADMIN->add('appearance', new admin_category('theme_cajasan', get_string('pluginname', 'theme_cajasan')));

// Crear objeto de configuraciones con pestañas
$asettings = new theme_cajasan_admin_settingspage_tabs(
    'themesettingcajasan',
    get_string('themesettings', 'theme_cajasan'),
    'moodle/site:config'
);

if ($ADMIN->fulltree) {
    // Variables comunes
    $a = new stdClass();
    $a->example_banner = (string)$OUTPUT->image_url('example_banner', 'theme_cajasan');
    $a->cover_remui = (string)$OUTPUT->image_url('cover_remui', 'theme');
    $a->example_cover1 = (string)$OUTPUT->image_url('login_bg_corp', 'theme');
    $a->example_cover2 = (string)$OUTPUT->image_url('login_bg', 'theme');

    /* =========================================================================
       TAB 1: General Settings
       ========================================================================= */
    $page = new admin_settingpage('theme_cajasan_generals', get_string('generalsettings', 'theme_cajasan'));

    // --- Notificaciones Generales ---
    $page->add(new admin_setting_heading(
        'theme_cajasan/generalnoticeheading',
        get_string('generalnoticeheading', 'theme_cajasan'),
        ''
    ));

    $name = 'theme_cajasan/generalnoticemode';
    $title = get_string('generalnoticemode', 'theme_cajasan');
    $description = get_string('generalnoticemodedesc', 'theme_cajasan');
    $default = 'off';
    $choices = [
        'off' => get_string('generalnoticemode_off', 'theme_cajasan'),
        'info' => get_string('generalnoticemode_info', 'theme_cajasan'),
        'danger' => get_string('generalnoticemode_danger', 'theme_cajasan')
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_cajasan/generalnotice';
    $title = get_string('generalnotice', 'theme_cajasan');
    $description = get_string('generalnoticedesc', 'theme_cajasan');
    $default = '<strong>Estamos trabajando</strong> para mejorar...';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // --- Chat Settings ---
    $page->add(new admin_setting_heading(
        'theme_cajasan/chatheading',
        get_string('chatheading', 'theme_cajasan'),
        ''
    ));

    $name = 'theme_cajasan/enable_chat';
    $title = get_string('enable_chat', 'theme_cajasan');
    $description = get_string('enable_chatdesc', 'theme_cajasan');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_cajasan/tawkto_embed_url';
    $title = get_string('tawkto_embed_url', 'theme_cajasan');
    $description = get_string('tawkto_embed_urldesc', 'theme_cajasan');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // --- Accessibility Settings ---
    $page->add(new admin_setting_heading(
        'theme_cajasan/accessibilityheading',
        get_string('accessibilityheading', 'theme_cajasan'),
        ''
    ));

    $name = 'theme_cajasan/accessibility_widget';
    $title = get_string('accessibility_widget', 'theme_cajasan');
    $description = get_string('accessibility_widgetdesc', 'theme_cajasan');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // --- Content Protection Settings ---
    $page->add(new admin_setting_heading(
        'theme_cajasan/contentprotectionheading',
        get_string('contentprotectionheading', 'theme_cajasan'),
        ''
    ));

    $name = 'theme_cajasan/copypaste_prevention';
    $title = get_string('copypaste_prevention', 'theme_cajasan');
    $description = get_string('copypaste_preventiondesc', 'theme_cajasan');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Roles para protección
    $roles = role_get_names(null, ROLENAME_ORIGINAL);
    $roles_array = [];
    foreach ($roles as $role) {
        $roles_array[$role->id] = $role->localname;
    }

    $name = 'theme_cajasan/copypaste_roles';
    $title = get_string('copypaste_roles', 'theme_cajasan');
    $description = get_string('copypaste_rolesdesc', 'theme_cajasan');
    $default = [5]; // Rol de estudiante por defecto
    $setting = new admin_setting_configmultiselect($name, $title, $description, $default, $roles_array);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $asettings->add_tab($page);

    /* =========================================================================
       TAB 2: Login Page Settings
       ========================================================================= */
    $page = new admin_settingpage('theme_cajasan_login', get_string('loginsettings', 'theme_cajasan'));

    // Imagen de Login
    $name = 'theme_cajasan/loginimage';
    $title = get_string('loginimage', 'theme_cajasan');
    $description = get_string('loginimagedesc', 'theme_cajasan', $a);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginimage', 0, [
        'subdirs' => 0,
        'accepted_types' => 'web_image'
    ]);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Color de Fondo del Login
    $name = 'theme_cajasan/loginbg_color';
    $title = get_string('loginbg_color', 'theme_cajasan');
    $description = get_string('loginbg_colordesc', 'theme_cajasan');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '#b2cdea');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Carousel Settings
    $page->add(new admin_setting_heading(
        'theme_cajasan_carousel',
        get_string('carouselsettings', 'theme_cajasan'),
        ''
    ));

    // Número de slides (se utiliza el prefijo "loging_" para evitar duplicidad)
    $name = 'theme_cajasan/loging_numberofslides';
    $title = get_string('numberofslides', 'theme_cajasan');
    $description = get_string('numberofslides_desc', 'theme_cajasan');
    $choices = range(1, 5);
    $page->add(new admin_setting_configselect($name, $title, $description, 1, array_combine($choices, $choices)));

    // Settings para cada slide
    $numslides = get_config('theme_cajasan', 'loging_numberofslides') ?: 1;
    for ($i = 1; $i <= $numslides; $i++) {
        // Título del slide
        $name = 'theme_cajasan/loging_slidetitle' . $i;
        $title = get_string('slidetitle', 'theme_cajasan', $i);
        $description = get_string('slidetitle_desc', 'theme_cajasan', $i);
        $page->add(new admin_setting_configtext($name, $title, $description, ''));

        // Imagen del slide
        $name = 'theme_cajasan/loging_slideimage' . $i;
        $title = get_string('slideimage', 'theme_cajasan', $i);
        $description = get_string('slideimage_desc', 'theme_cajasan', $i);
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'loging_slideimage' . $i, 0, [
            'subdirs' => 0,
            'accepted_types' => ['web_image']
        ]);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // URL del slide
        $name = 'theme_cajasan/loging_slideurl' . $i;
        $title = get_string('slideurl', 'theme_cajasan', $i);
        $description = get_string('slideurldesc', 'theme_cajasan', $i);
        $page->add(new admin_setting_configtext($name, $title, $description, ''));
    }

    // Intervalo del carrusel
    $name = 'theme_cajasan/carouselinterval';
    $title = get_string('carouselinterval', 'theme_cajasan');
    $description = get_string('carouselintervaldesc', 'theme_cajasan');
    $setting = new admin_setting_configtext($name, $title, $description, '5000');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $asettings->add_tab($page);

    /* =========================================================================
       TAB 3: Dashboard Settings
       ========================================================================= */
    $page = new admin_settingpage('theme_cajasan_dashboard', get_string('dashboardsettings', 'theme_cajasan'));

    // Personal Area Header Settings
    $page->add(new admin_setting_heading(
        'theme_cajasan/personalareaheading',
        get_string('personalareaheading', 'theme_cajasan'),
        ''
    ));

    // Toggle de visibilidad del Personal Area Header
    $name = 'theme_cajasan/show_personalareaheader';
    $title = get_string('show_personalareaheader', 'theme_cajasan');
    $description = get_string('show_personalareaheaderdesc', 'theme_cajasan');
    $default = 1;
    $choices = [
        0 => get_string('hide', 'theme_cajasan'),
        1 => get_string('show', 'theme_cajasan')
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Imagen del Personal Area Header
    $name = 'theme_cajasan/personalareaheader';
    $title = get_string('personalareaheader', 'theme_cajasan');
    $description = get_string('personalareaheaderdesc', 'theme_cajasan', $a);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'personalareaheader', 0, [
        'subdirs' => 0,
        'accepted_types' => 'web_image'
    ]);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // My Courses Header Settings
    $page->add(new admin_setting_heading(
        'theme_cajasan/mycoursesheading',
        get_string('mycoursesheading', 'theme_cajasan'),
        ''
    ));

    // Toggle de visibilidad del My Courses Header
    $name = 'theme_cajasan/show_mycoursesheader';
    $title = get_string('show_mycoursesheader', 'theme_cajasan');
    $description = get_string('show_mycoursesheaderdesc', 'theme_cajasan');
    $default = 1;
    $choices = [
        0 => get_string('hide', 'theme_cajasan'),
        1 => get_string('show', 'theme_cajasan')
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Imagen del My Courses Header
    $name = 'theme_cajasan/mycoursesheader';
    $title = get_string('mycoursesheader', 'theme_cajasan');
    $description = get_string('mycoursesheaderdesc', 'theme_cajasan', $a);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'mycoursesheader', 0, [
        'subdirs' => 0,
        'accepted_types' => 'web_image'
    ]);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $asettings->add_tab($page);

    /* =========================================================================
       TAB 4: Footer Settings
       ========================================================================= */
    $page = new admin_settingpage('theme_cajasan_footer', get_string('footersettings', 'theme_cajasan'));

    // Visibilidad del Footer
    $name = 'theme_cajasan/hidefootersections';
    $title = get_string('hidefootersections', 'theme_cajasan');
    $description = get_string('hidefootersections_desc', 'theme_cajasan');
    $default = 0;
    $choices = [
        0 => get_string('show', 'theme_cajasan'),
        1 => get_string('hide', 'theme_cajasan')
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    // About Section
    $page->add(new admin_setting_heading(
        'theme_cajasan/footeraboutheading',
        get_string('footeraboutheading', 'theme_cajasan'),
        ''
    ));

    $name = 'theme_cajasan/abouttitle';
    $title = get_string('abouttitle', 'theme_cajasan');
    $description = get_string('abouttitledesc', 'theme_cajasan');
    $default = get_string('abouttitle_default', 'theme_cajasan');
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_cajasan/abouttext';
    $title = get_string('abouttext', 'theme_cajasan');
    $description = get_string('abouttextdesc', 'theme_cajasan');
    $default = get_string('abouttext_default', 'theme_cajasan');
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $asettings->add_tab($page);

    // Si existen pestañas del tema padre, combinarlas
    if ($parent_tabs !== null) {
        $all_tabs = array_merge($asettings->get_tabs(), $parent_tabs);
        $asettings->set_tabs($all_tabs);
    }
}

// Agregar la página de configuraciones a la categoría de apariencia
$ADMIN->add('theme_cajasan', $asettings);
