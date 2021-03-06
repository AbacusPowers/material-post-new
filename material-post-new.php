<?php
/*
Plugin Name: Post New FAB
Plugin URI:  http://advisantgroup.com
Description: Add a Google-style, material design floating action button for posting new items. Works with post types or URL links.
Version:     1.2.1
Author:      Justin Maurer
Author URI:  http://advisantgroup.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: material_post
*/

/*
 * Require necessary libraries
 */
define('MATERIAL_POST_PATH', plugin_dir_path(__FILE__));

if (file_exists(__DIR__ . '/vendor/CMB2/init.php')) {
    require_once __DIR__ . '/vendor/CMB2/init.php';
}
require 'plugin_update_check.php';
$MyUpdateChecker = new PluginUpdateChecker_2_0 (
    'https://kernl.us/api/v1/updates/56feced9eba7ad1a1c8312fe/',
    __FILE__,
    'post-new-fab',
    1
);

include(MATERIAL_POST_PATH . 'options-page.php');

/**
 * Activation hook
 */
function materialPostActivation()
{
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'materialPostActivation');

/**
 * Deactivation hook
 */
function materialPostDeactivation()
{
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'materialPostDeactivation');

/**
 * Load styles, scripts and fonts
 */
function materialPostNewScripts()
{
    /**
     * Register styles to be used
     */

    wp_register_style('material-design-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', '', null);

    wp_register_style('material-post-new-materialize-css', plugin_dir_url(__FILE__) . '/vendor/materialize-src/materialize.css',
        array('material-design-icons'), null);

    wp_register_script('materialize-js', plugin_dir_url(__FILE__) . '/vendor/materialize-src/js/bin/materialize.min.js',
        array('jquery'), true);

    wp_register_style('material-post-new-styles', plugin_dir_url(__FILE__) . 'material-post-new-styles.css',
        array('material-design-icons'), null);

    wp_register_script('material-post-new-buttons', plugin_dir_url(__FILE__) . 'js/buttons.js', array('jquery'), null, true);

    /**
     * Enqueue all dependencies
     */
    
    wp_enqueue_script('jquery');

    wp_enqueue_script('jquery-ui-core');

    wp_enqueue_script('jquery-ui-effects-core');
    
    wp_enqueue_style('material-design-icons');
    
    wp_enqueue_style('material-post-new-materialize-css');
    
    wp_enqueue_script('materialize-js');
    
    wp_enqueue_style('material-post-new-styles');
    
    wp_enqueue_script('material-post-new-buttons');

}

add_action('wp_enqueue_scripts', 'materialPostNewScripts');

/**
 * Build options page
 */
materialPostAdmin();

/**
 * Get all publicly queryable post types
 *
 * @return array
 */
function getAllPostTypes()
{
    $postTypes = get_post_types(array(
        'publicly_queryable' => true
    ));

    $options = array();
    foreach ($postTypes as $postType) {
        $options[$postType] = $postType;
    }
    return $options;
}

/**
 * Build HTML based on plugin options and add it to the footer
 */
function materialPostContent()
{
    //Get all post types options from options page
    $buttonPostTypes = materialPostGetOption('material_post_post_new_item');
    $mainButtonColor = materialPostGetOption('material_post_post_new_button_colorpicker');
    if (is_user_logged_in()) {
        ?>

        <div class="fixed-action-btn click-to-toggle" style="bottom: 45px; right: 24px;">
            <a class="btn-floating btn-large" style="background-color:<?= $mainButtonColor; ?>;">
                <i class="large material-icons">mode_edit</i>
            </a>
            <ul id="material-post-post-types-list">
                <?php
                //Loop through all post types and decide whether to use text URL or WP admin's post new page
                foreach ($buttonPostTypes as $option) {

                    $label = $option['post_new_label'];
                    $color = $option['post_type_button_colorpicker'];

                    $link = materialGetURL($option);


                    echo '<li><span class="post-new-label btn btn-floating">' . $label . '</span><a href="' . $link . '" class="material-post-new-button btn-floating"><i class="material-icons" style="background-color:' . $color . ';">&#xE150;</i></a></li>';
                }
                ?>
            </ul>
        </div>
        <div id="material-post-overlay"></div>
        <?php
    }
}
add_action('wp_footer', 'materialPostContent');

/**
 * Do logic for determining URL
 *
 * @param array $option
 */
function materialGetURL(Array $option)
{
    $method = $option['url_method'];
    $url = $option['post_new_url'];
    $post_type = $option['post_type'];
    $variableURL = $option['post_new_variable_url'];

    $variableType = $option['post_new_variable'];

    if ($method === 'post-type') {
        $link = 'wp-admin/post-new.php?post_type=' . $post_type;
    } elseif ($method === 'variable-url') {
        $currentUserID = get_current_user_id();
        $nicename = get_userdata($currentUserID)->user_nicename;
        $bpUserSlug = bp_core_get_username($currentUserID);
        $pattern = '/({#})/';
        switch ($variableType) {
            case 'user-id':
                $variable = $currentUserID;
                break;
            case 'user-slug':
                $variable = $nicename;
                break;
            case 'bp-user-slug':
                $variable = $bpUserSlug;
                break;
        }


        $link = preg_replace($pattern, $variable, $variableURL);
    } else {
        $link = $url;
    }
    return $link;
}