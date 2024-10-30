<?php
/*
Plugin Name: Mobile App Manager For GeoDirectory
Description: Connect your GeoDirectory content to a mobile app using the Mobile App Manager plugin.
Version: 1.0
Author: tinyscreenlabs
Author URI: https://tinyscreenlabs.com
License: GPLv2+ or later
Text Domain: mobile-app-manager-for-geodirectory
*/

include_once 'mobile-app-manager-for-geodirectory-location-manager.php';
include_once 'mobile-app-manager-for-geodirectory-install-manager.php';

add_action( 'plugins_loaded', array( 'tsl_mobile_app_manager_for_geodirectory', 'init' ));


class tsl_mobile_app_manager_for_geodirectory{

    private $text_domain = 'mobile-app-manager-for-geodirectory';
    private $js_version = '1.0';
    private $gd_installed = false;
    private $mam_installed = false;

    public static function init(){
        $class = __CLASS__;
        new $class;
    }

    function __construct(){

        add_action('init', array( $this, 'load_textdomain' ));
        add_action( 'admin_menu', array( $this , 'settings_page' ) , 60 );
        add_action( 'admin_enqueue_scripts', array( $this , 'admin_enqueue_scripts' ) );

        $plugin = plugin_basename( __FILE__ );
        add_filter("plugin_action_links_$plugin", array( $this , 'settings_link' ) );

    }

    function admin_enqueue_scripts(){

        wp_register_script('mobile-app-manager-for-geodirectory', plugins_url( 'js/mobile-app-manager-for-geodirectory.js', __FILE__ ), array(), $this->js_version, true);

        wp_enqueue_script(array(  'jquery' , 'jquery-ui-dialog' , 'mobile-app-manager-for-geodirectory' ));

        wp_register_style( 'mobile-app-manager-for-geodirectory', plugins_url( "css/mam_class.css", __FILE__ ), array(), '1.0', 'screen' );
        wp_enqueue_style(array( 'wp-jquery-ui-dialog', 'mobile-app-manager-for-geodirectory' ));

    }

    function settings_link($links) {

        $mylinks = array('<a href="' . admin_url('options-general.php?page=mobile-app-manager-for-geodirectory-settings') . '">' . __('Settings', $this->text_domain) . '</a>',);
        return array_merge($mylinks, $links);

    }

    function load_textdomain(){

        load_plugin_textdomain( $this->text_domain, false, dirname(plugin_basename(__FILE__)) . '/languages');

    }

    function settings_page(){

        if ( is_admin() ) {
            add_options_page('Settings Admin', 'Mobile App Manager', 'manage_options', $this->text_domain . '-settings', array($this, 'create_admin_page'));
        }

    }

    function create_admin_page(){

        if ( !function_exists('get_plugins') ){
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

        if( is_plugin_active( 'geodirectory/geodirectory.php' ) ) $this->gd_installed = true;
        if( is_plugin_active( 'wp-local-app/wp-local-app.php' ) ) $this->mam_installed = true;

        $html_line = '';

        if( ! $this->gd_installed || ! $this->mam_installed ) {

            $html_line = '<div class="mam_content_holder">';
            $html_line .= '<h2>' . __('Mobile App Manager for GeoDirectory', $this->text_domain) . '</h2>';
            $html_line .= '<div class="mam_section_holder">';
            $html_line .= '<p>' . __('Mobile App Manager for GeoDirectory enables you to use your ', $this->text_domain) . '<a href="https://wordpress.org/plugins/geodirectory/" target="_blank">' . __('GeoDirectory', $this->text_domain) . '</a>' . __(' plugin content to build a mobile app using the ', $this->text_domain) . '<a href="https://tinyscreenlabs.com" target="_blank">' . __('Tiny Screen Labs', $this->text_domain) . '</a>' . __(' Mobile App Manager service.', $this->text_domain) . '</p>';

            if (!$this->gd_installed) {
                $html_line .= '<p>' . __('You currently DO NOT have the GeoDirectory plugin activated. If you download and activate this plugin then you can configure the GeoDirectory content in the Mobile App Manager.', $this->text_domain) . '</p>';
            }

            if (!$this->mam_installed) {
                $html_line .= '<p>' . __('You currently DO NOT have the Mobile App Manager plugin activated. In order to use GeoDirectory content as part of a mobile app, you will need to install and activate the plugin.', $this->text_domain) . '</p>';
            }
            $html_line .= '</div>';
        }

        if (!$this->mam_installed) $html_line .= $this->display_mam_info();

        $html_line .= $this->display_plugin_settings();

        $html_line .= '<h2>'.__('Need help or have questions?', $this->text_domain ).'</h2>';
        $html_line .= '<div class="mam_section_holder" style="margin-bottom:3em;">';
        $html_line .= '<p>'.__('Web site support: ', $this->text_domain ).'<a href="https://tinyscreenlabs.com/help-center/" target="_blank">tinyscreenlabs.com/help-center/</a>.</p>';
        $html_line .= '<p>'.__('Phone support: +1-847-497-8469.', $this->text_domain ).'</p>';
        $html_line .= '<p>'.__('Email support: ', $this->text_domain ).'<a href="mailto:info@tinyscreenlabs.com">info@tinyscreenlabs.com</a>.</p>';
        $html_line .= '</div>';

        echo $html_line;

    }

    function display_mam_info(){

        $html_line = '';

        if( ! is_plugin_active( 'wp-local-app/wp-local-app.php' ) ) {

            $installer = new mamgd_install_manager();
            $is_on_internet = $installer->is_connected_to_internet();
            $can_user_install = current_user_can('install_plugins');
            $button = '';

            $html_line .= '<br><div class="tsl_section" style="max-width:65em;">';
            if($is_on_internet) {
                if($can_user_install) $button = '<input id="tsl-install-plugin" class="button button-primary" value="Install from tinyscreenlabs.com" type="submit">';
                $html_line .= '<form method="post" action="https://tinyscreenlabs.com/install-plugins/">';
                $html_line .= '<input type="hidden" name="tslplugin" value="gdmam">';
                $html_line .= '<table style="width:100%"><tr><td><h2>' . __('Mobile App Manager (Premium)', 'business-directory-offers') . '</h2></td><td align="right">' . $button . '</td></tr></table>';
                $html_line .= '</form>';
            }else{
                if($can_user_install) $button = '<input id="tsl-install-plugin" class="button button-primary" value="Download from tinyscreenlabs.com" type="submit" >';
                $html_line .= '<form target="_blank" action="https://tinyscreenlabs.com/">';
                $html_line .= '<table style="width:100%"><tr><td><h2>' . __('Mobile App Manager (Premium)', 'business-directory-offers') . '</h2></td><td align="right">' . $button . '</td></tr></table>';
                $html_line .= '</form>';
            }
            $html_line .= '<p><span>' . __('TSL Mobile App Manager is a WordPress plugin and cloud based service that enables WordPress Admins to design a mobile app and complete the submission process right inside the WordPress dashboard.', 'business-directory-offers') . '</span></p>';
            $html_line .= '<ul style="list-style-type:disc;margin-left:2em;">';
            $html_line .= '<li>' . __('WordPress administrators have the ability to manage content for their website and mobile apps in one place<', 'business-directory-offers') . '/li>';
            $html_line .= '<li>' . __('Business Directory Offers are displayed on your mobile app', 'business-directory-offers') . '</li>';
            $html_line .= '<li>' . __('App Setup is a drag and drop interface where you design your mobile app before you purchase a TSL Pro Plan', 'business-directory-offers') . '</li>';
            $html_line .= '<li>' . __('The TSL Local App Previewer is a WYSIWYG viewer that connects to your website', 'business-directory-offers') . '</li>';
            $html_line .= '<li>' . __('Updates to app page content are automatically pushed to the mobile app whenever you update pages and posts in WordPress', 'business-directory-offers') . '</li>';
            $html_line .= '<li>' . __('TSL publishes your app to iTunes and Google Play when you purchase the TSL Pro Plan', 'business-directory-offers') . '</li>';
            $html_line .= '</ul>';

            $html_line .= '<p><span>' . __('For more information go to the ', 'business-directory-offers') . '<a href="https://tinyscreenlabs.com/?tslref=tslaffiliate" target="_blank">' . __('Tiny Screen Labs', 'business-directory-offers') . '</a> (TSL) '.__('website', 'business-directory-offers').'.  </span></p>';

            $html_line .= '</div>';
        }

        return $html_line;
    }

    function display_plugin_settings(){

        $html_line = '';

        $header = '<table style="width:100%;max-width:75em;">';

        $html_line .= '<br><h2>'.__('Mobile App Manager for GeoDirectory - GeoFilter Locations Settings', $this->text_domain ).'</h2>';
        $html_line .= '<h4>'.__('Add locations to enable GeoFilters in Mobile App Manager.', $this->text_domain ).'</h4>';
        $html_line .= '<div class="mam_section_holder">';

        $location_manager = new geodirmam_location_manager_tab();
        $lm_line = '<tr><td colspan="2">' . $location_manager->create_tab() . '</td></tr>';
        $html_line = $header . $lm_line . $html_line;

        $html_line .= '</table>';
        $html_line .= '</div>';

        return $html_line;

        }
}