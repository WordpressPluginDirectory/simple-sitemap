<?php

namespace WPGO_Plugins\Simple_Sitemap;

/**
 * Enqueue plugin scripts.
 */
class Enqueue_Scripts {
    /**
     * Common root paths/directories.
     *
     * @var $module_roots
     */
    protected $module_roots;

    /**
     * An object of API utilities class.
     *
     * @var object
     */
    protected $utilities_fw;

    /**
     * Plugin framework new features.
     *
     * @var mixed
     */
    protected $new_features_arr;

    /**
     * Plugin data.
     *
     * @var array
     */
    protected $plugin_data;

    /**
     * Custom plugin data.
     *
     * @var array
     */
    protected $custom_plugin_data;

    /**
     * Plugin version.
     *
     * @var string
     */
    protected $plugin_version;

    /**
     * Enqueue prefix.
     *
     * @var string
     */
    protected $enq_pfx;

    /**
     * Plugin settings prefix.
     *
     * @var string
     */
    protected $plugin_settings_prefix;

    /**
     * Dependencies for JavaScripts.
     *
     * @var array
     */
    protected $js_deps;

    /**
     * Main class constructor.
     *
     * @global type $pagenow
     * @param array  $module_roots Root plugin path/dir.
     * @param object $utilities_fw An object of API utilities class.
     * @param mixed  $new_features_arr Plugin framework new features.
     * @param array  $plugin_data Plugin data.
     * @param array  $custom_plugin_data Custom plugin data.
     */
    public function __construct(
        $module_roots,
        $utilities_fw,
        $new_features_arr,
        $plugin_data,
        $custom_plugin_data
    ) {
        global $pagenow;
        $this->module_roots = $module_roots;
        $this->utilities_fw = $utilities_fw;
        $this->new_features_arr = $new_features_arr;
        $this->plugin_data = $plugin_data;
        $this->custom_plugin_data = $custom_plugin_data;
        $this->plugin_version = get_plugin_data( $module_roots['file'] )['Version'];
        $this->enq_pfx = 'simple-sitemap';
        $this->plugin_settings_prefix = 'simple_sitemap';
        // Scripts for plugin settings page.
        add_action( 'admin_enqueue_scripts', array(&$this, 'enqueue_admin_settings_scripts') );
        add_action(
            'admin_enqueue_scripts',
            array(&$this, 'enqueue_admin_scripts'),
            9,
            2
        );
        // Enqueue frontend/editor scripts.
        add_action( 'enqueue_block_assets', array(&$this, 'enqueue_assets') );
        add_action( 'enqueue_block_editor_assets', array(&$this, 'enqueue_block_editor_scripts') );
        // $this->js_deps = [ 'wp-element', 'wp-i18n', 'wp-hooks', 'wp-components', 'wp-blocks', 'wp-editor', 'wp-compose' ];
        $this->js_deps = array(
            'wp-plugins',
            'wp-element',
            'wp-i18n',
            'wp-api-request',
            'wp-data',
            'wp-hooks',
            'wp-plugins',
            'wp-components',
            'wp-blocks',
            'wp-compose'
        );
        if ( 'widgets.php' !== $pagenow ) {
            $this->js_deps = array_merge( $this->js_deps, array('wp-edit-post', 'wp-editor') );
        }
        add_filter( 'should_load_separate_core_block_assets', '__return_true' );
    }

    /**
     * Scripts for all admin pages. This is necessary as we need to modify the main admin menu from JS.
     *
     * @param string $hook Passed as parameter.
     */
    public function enqueue_admin_scripts( $hook ) {
        $admin_settings_js = $this->utilities_fw->get_enqueue_version( '/lib/assets/js/update-menu.js', $this->custom_plugin_data->plugin_data['Version'] );
        // Register and localize the script with new data.
        // wp_register_script( $this->enq_pfx . '-update-menu-js', $admin_settings_js['uri'], array( 'wpgo-all-admin-pages-fw-js' ), $admin_settings_js['ver'], true );
        wp_register_script(
            $this->enq_pfx . '-update-menu-js',
            $admin_settings_js['uri'],
            array(),
            $admin_settings_js['ver'],
            true
        );
        $data = array(
            'admin_url'       => admin_url(),
            'nav_status'      => SITEMAP_FREEMIUS_NAVIGATION,
            'hook'            => $hook,
            'menu_type'       => $this->custom_plugin_data->menu_type,
            'main_menu_label' => $this->custom_plugin_data->main_menu_label,
            'plugin_prefix'   => $this->enq_pfx,
        );
        wp_localize_script( $this->enq_pfx . '-update-menu-js', $this->custom_plugin_data->plugin_settings_prefix . '_admin_menu_data', $data );
        wp_enqueue_script( $this->enq_pfx . '-update-menu-js' );
    }

    /**
     * Enqueue front end and editor JavaScript and CSS assets.
     */
    public function enqueue_assets() {
        $simple_sitemap_css = $this->utilities_fw->get_enqueue_version( '/lib/assets/css/simple-sitemap.css', $this->plugin_version );
        wp_register_style(
            'simple-sitemap-css',
            $simple_sitemap_css['uri'],
            array(),
            $simple_sitemap_css['ver']
        );
    }

    /**
     * Scripts for plugin settings page only.
     *
     * @param string $hook Page hook name.
     * @return void
     */
    public function enqueue_admin_settings_scripts( $hook ) {
        if ( 'toplevel_page_simple-sitemap-menu' === $hook ) {
            $ss_settings_css = $this->utilities_fw->get_enqueue_version( '/lib/assets/css/admin-settings.css', $this->plugin_version );
            $ss_settings_js = $this->utilities_fw->get_enqueue_version( '/lib/assets/js/simple-sitemap-admin.js', $this->plugin_version );
            wp_enqueue_style(
                'simple-sitemap-settings-welcome-css',
                $ss_settings_css['uri'],
                array(),
                $ss_settings_css['ver']
            );
            wp_enqueue_script(
                'simple-sitemap-settings-welcome-js',
                $ss_settings_js['uri'],
                array(),
                $ss_settings_js['ver']
            );
        }
        // Having to do it this way as for the welcome page the hook has the numbered icon number included (when rendered).
        if ( strpos( $hook, '_page_simple-sitemap-menu-welcome' ) !== false ) {
            // if ( 'simple-sitemap_page_simple-sitemap-menu-welcome' === $hook ) {
            $ss_settings_css = $this->utilities_fw->get_enqueue_version( '/lib/assets/css/admin-settings.css', $this->plugin_version );
            $ss_settings_js = $this->utilities_fw->get_enqueue_version( '/lib/assets/js/simple-sitemap-admin.js', $this->plugin_version );
            wp_enqueue_style(
                'simple-sitemap-settings-css',
                $ss_settings_css['uri'],
                array(),
                $ss_settings_css['ver']
            );
            // wp_enqueue_script( 'simple-sitemap-settings-js', $ss_settings_js['uri'], array(), $ss_settings_js['ver'] );
        }
    }

    /**
     * Add scripts for block editor only.
     **/
    public function enqueue_block_editor_scripts() {
        $block_editor_js = $this->utilities_fw->get_enqueue_version( '/lib/block_assets/js/blocks.editor.js', $this->plugin_version );
        $deps = $this->js_deps;
        // Block editor script.
        wp_register_script(
            $this->enq_pfx . '-block-editor-js',
            $block_editor_js['uri'],
            $deps,
            $block_editor_js['ver'],
            true
        );
        $data = array(
            'is_premium'           => ss_fs()->is_premium(),
            'can_use_premium_code' => ss_fs()->can_use_premium_code(),
        );
        wp_localize_script( $this->enq_pfx . '-block-editor-js', $this->plugin_settings_prefix . '_editor_data', $data );
        wp_enqueue_script( $this->enq_pfx . '-block-editor-js' );
        $block_editor_css = $this->utilities_fw->get_enqueue_version( '/lib/assets/css/simple-sitemap-block-editor.css', $this->plugin_version );
        // Block editor styles.
        wp_enqueue_style(
            'simple-sitemap-block-editor-css',
            $block_editor_css['uri'],
            array(),
            $block_editor_css['ver']
        );
    }

}

/* End class definition */