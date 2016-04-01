<?php
/**
 * Created by: Justin Maurer for Advisant Group, Inc
 * Date: 3/31/16
 * Time: 11:26 AM
 * Version: 0.1
 */

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class MaterialPostAdmin
{
    /**
     * Option key, and option page slug
     * @var string
     */
    private $key = 'material_post_options';
    /**
     * Options page metabox id
     * @var string
     */
    private $metabox_id = 'material_post_option_metabox';
    /**
     * Options Page title
     * @var string
     */
    protected $title = 'Post New Options';
    /**
     * Options Page hook
     * @var string
     */
    protected $options_page = '';
    /**
     * Holds an instance of the object
     *
     * @var MaterialPostAdmin
     **/
    private static $instance = null;

    /**
     * Constructor
     * @since 0.1.0
     */
    private function __construct()
    {
        // Set our title
        $this->title = __('Post New Button Options', 'material_post');
    }

    /**
     * Returns the running object
     *
     * @return MaterialPostAdmin
     **/
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Initiate our hooks
     * @since 0.1.0
     */
    public function hooks()
    {
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_options_page'));
        add_action('cmb2_admin_init', array($this, 'addOptionsPageMetabox'));
    }

    /**
     * Register our setting to WP
     * @since  0.1.0
     */
    public function init()
    {
        register_setting($this->key, $this->key);
    }

    /**
     * Add menu options page
     * @since 0.1.0
     */
    public function add_options_page()
    {
        $this->options_page = add_menu_page($this->title, $this->title, 'manage_options', $this->key,
            array($this, 'adminPageDisplay'));
        // Include CMB CSS in the head to avoid FOUC
        add_action("admin_print_styles-{$this->options_page}", array('CMB2_hookup', 'enqueue_cmb_css'));
    }

    /**
     * Admin page markup. Mostly handled by CMB2
     * @since  0.1.0
     */
    public function adminPageDisplay()
    {
        ?>
        <div class="wrap cmb2-options-page <?php echo $this->key; ?>">
            <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
            <?php cmb2_metabox_form($this->metabox_id, $this->key); ?>
        </div>
        <?php
    }

    /**
     * Add the options metabox to the array of metaboxes
     * @since  0.1.0
     */
    function addOptionsPageMetabox()
    {
        $prefix = 'material_post_';
        // hook in our save notices
        add_action("cmb2_save_options-page_fields_{$this->metabox_id}", array($this, 'settings_notices'), 10, 2);
        $cmb = new_cmb2_box(array(
            'id' => $this->metabox_id,
            'hookup' => false,
            'cmb_styles' => false,
            'show_on' => array(
                // These are important, don't remove
                'key' => 'options-page',
                'value' => array($this->key,)
            ),
        ));
        // Set our CMB2 fields
        $cmb->add_field(array(
            'name' => __('Button Color', 'material_post'),
            'desc' => __('Select your primary color', 'material_post'),
            'id' => $prefix . 'post_new_button_colorpicker',
            'type' => 'colorpicker',
            'default' => '#904199',
        ));
        $postNewItemField = $cmb->add_field(array(
            'id'            => $prefix . 'post_new_item',
            'type'          => 'group',
            'description'   => __( 'Select a post type OR enter a URL to be linked', 'material_post' ),
            'repeatable'    => true,
            'options'       => array(
                'group_title'   => __( 'Post New {#}', 'material_post'),
                'add_button'    => __( 'New Post Type', 'material_post'),
                'remove_button' => __( 'Remove Post Type', 'material_post'),
                'sortable'      => true
            )
        ));
        $cmb->add_group_field($postNewItemField, array(
            'name' => __('Label', 'material_post'),
            'desc' => __('Label for post type', 'material_post'),
            'id' => 'post_new_label',
            'type' => 'text',
        ));
        $cmb->add_group_field($postNewItemField, array(
            'name'  => 'Post Type',
            'id'    => 'post_type',
            'desc'  => 'Choose a post type from the dropdown',
            'type'  => 'select',
            'show_option_none' => true,
            'default' => 'None - Use URL',
            'options' => 'getAllPostTypes'
        ));
        $cmb->add_group_field($postNewItemField, array(
            'name' => __('Post New URL', 'material_post'),
            'desc' => __('If not using post type, enter URL', 'material_post'),
            'id' => 'post_new_url',
            'type' => 'text_url',
        ));
        $cmb->add_group_field($postNewItemField, array(
            'name' => __('Button Color', 'material_post'),
            'desc' => __('Select color for this post type', 'material_post'),
            'id' => 'post_type_button_colorpicker',
            'type' => 'colorpicker',
            'default' => '#e1df74',
        ));
    }

    /**
     * Register settings notices for display
     *
     * @since  0.1.0
     * @param  int $object_id Option key
     * @param  array $updated Array of updated fields
     * @return void
     */
    public function settings_notices($object_id, $updated)
    {
        if ($object_id !== $this->key || empty($updated)) {
            return;
        }
        add_settings_error($this->key . '-notices', '', __('Settings updated.', 'material_post'), 'updated');
        settings_errors($this->key . '-notices');
    }

    /**
     * Public getter method for retrieving protected/private variables
     * @since  0.1.0
     * @param  string $field Field to retrieve
     * @return mixed          Field value or exception is thrown
     */
    public function __get($field)
    {
        // Allowed fields to retrieve
        if (in_array($field, array('key', 'metabox_id', 'title', 'options_page'), true)) {
            return $this->{$field};
        }
        throw new Exception('Invalid property: ' . $field);
    }

}

/**
 * Helper function to get/return the MaterialPostAdmin object
 * @since  0.1.0
 * @return MaterialPostAdmin object
 */
function materialPostAdmin()
{
    return MaterialPostAdmin::getInstance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key Options array key
 * @return mixed        Option value
 */
function materialPostGetOption($key = '')
{
    return cmb2_get_option(materialPostAdmin()->key, $key);
}