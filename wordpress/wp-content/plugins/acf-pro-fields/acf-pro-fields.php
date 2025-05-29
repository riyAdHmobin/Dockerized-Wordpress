<?php
/*
Plugin Name: ACF Pro Fields
Description: A custom fields plugin inspired by Advanced Custom Fields, with Elementor integration
Version: 1.1.0
Author: Your Name
License: GPL-2.0+
*/

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Pro_Fields {

    public function __construct() {
        add_action('init', [$this, 'register_post_types']);
        add_action('acf/include_field_types', [$this, 'register_field_types']);
        add_action('acf/save_post', [$this, 'save_post_fields'], 20);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_menu', [$this, 'add_settings_page']);
    }

    public function register_post_types() {
        register_post_type('acf_pro_field_group', [
            'labels' => [
                'name' => __('Field Groups', 'acf-pro'),
                'singular_name' => __('Field Group', 'acf-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title'],
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'manage_options',
            ],
            'map_meta_cap' => true,
        ]);
    }

    public function add_settings_page() {
        add_menu_page(
            __('ACF Pro Fields', 'acf-pro'),
            __('ACF Pro Fields', 'acf-pro'),
            'manage_options',
            'acf-pro-settings',
            [$this, 'render_settings_page'],
            'dashicons-admin-generic'
        );
    }

    public function render_settings_page() {
        require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
        $settings = new ACF_Pro_Admin_Settings();
        $settings->render();
    }

    public function enqueue_assets() {
        wp_enqueue_script('acf-pro-shared', plugin_dir_url(__FILE__) . 'assets/js/shared.js', ['jquery', 'wp-i18n'], '1.1.0', true);
        wp_enqueue_style('acf-pro-shared', plugin_dir_url(__FILE__) . 'assets/css/shared.css', [], '1.1.0');
        wp_set_script_translations('acf-pro-shared', 'acf-pro', plugin_dir_path(__FILE__) . 'languages');

        wp_localize_script('acf-pro-shared', 'acfProL10n', [
            'confirmRemove' => __('Are you sure you want to remove this?', 'acf-pro'),
            'selectFile' => __('Select File', 'acf-pro'),
            'selectImages' => __('Select Images', 'acf-pro'),
            'addToGallery' => __('Add to Gallery', 'acf-pro'),
        ]);
    }

    public function register_field_types() {
        // Register custom field types if needed
    }

    public function render_field($field, $value, $name) {
        if (!isset($field['type']) || empty($name)) {
            return;
        }

        switch ($field['type']) {
            case 'file':
                $this->render_file_field($field, $value, $name);
                break;
            case 'repeater':
                $this->render_repeater_field($field, $value, $name);
                break;
            case 'flexible_content':
                $this->render_flexible_content_field($field, $value, $name);
                break;
            case 'gallery':
                $this->render_gallery_field($field, $value, $name);
                break;
            case 'select':
                $this->render_select_field($field, $value, $name);
                break;
            case 'date_picker':
                $this->render_date_picker_field($field, $value, $name);
                break;
        }
    }

    private function render_file_field($field, $value, $name) {
        $file_id = !empty($value) ? absint($value) : 0;
        $file_url = $file_id ? wp_get_attachment_url($file_id) : '';
        $file_title = $file_id ? get_the_title($file_id) : '';

        wp_enqueue_script('acf-pro-file', plugin_dir_url(__FILE__) . 'assets/js/file.js', ['acf-pro-shared'], '1.1.0', true);

        ?>
        <div class="acf-pro-file-field">
            <div class="acf-pro-file-preview" style="<?php echo $file_id ? '' : 'display:none;'; ?>">
                <?php if ($file_id): ?>
                    <a href="<?php echo esc_url($file_url); ?>" target="_blank"><?php echo esc_html($file_title); ?></a>
                    <br>
                    <button type="button" class="button acf-pro-remove-file" aria-label="<?php _e('Remove file', 'acf-pro'); ?>">
                        <?php _e('Remove', 'acf-pro'); ?>
                    </button>
                <?php endif; ?>
            </div>
            <button type="button" class="button acf-pro-select-file" aria-label="<?php _e('Select file', 'acf-pro'); ?>">
                <?php _e('Select File', 'acf-pro'); ?>
            </button>
            <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($file_id); ?>">
        </div>
        <?php
    }

    private function render_repeater_field($field, $value, $name) {
        $rows = is_array($value) ? $value : [];
        $sub_fields = isset($field['sub_fields']) ? $field['sub_fields'] : [];

        wp_enqueue_script('acf-pro-repeater', plugin_dir_url(__FILE__) . 'assets/js/repeater.js', ['acf-pro-shared'], '1.1.0', true);
        wp_enqueue_style('acf-pro-repeater', plugin_dir_url(__FILE__) . 'assets/css/repeater.css', [], '1.1.0');

        ?>
        <div class="acf-pro-repeater" data-field-name="<?php echo esc_attr($name); ?>">
            <div class="acf-pro-repeater-rows">
                <?php foreach ($rows as $i => $row): ?>
                    <div class="acf-pro-repeater-row">
                        <div class="acf-pro-repeater-row-header">
                            <span class="acf-pro-repeater-row-title"><?php printf(__('Row %d', 'acf-pro'), $i + 1); ?></span>
                            <button type="button" class="button acf-pro-repeater-remove-row" aria-label="<?php _e('Remove row', 'acf-pro'); ?>">
                                <?php _e('Remove', 'acf-pro'); ?>
                            </button>
                        </div>
                        <div class="acf-pro-repeater-row-content">
                            <?php foreach ($sub_fields as $sub_field): ?>
                                <?php
                                $sub_field_name = sprintf('%s[%d][%s]', $name, $i, $sub_field['name']);
                                $sub_field_value = isset($row[$sub_field['name']]) ? $row[$sub_field['name']] : '';
                                ?>
                                <div class="acf-pro-repeater-field">
                                    <label>
                                        <?php echo esc_html($sub_field['label']); ?>
                                        <?php if (!empty($sub_field['required'])): ?>
                                            <span class="required" aria-hidden="true">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php if (!empty($sub_field['instructions'])): ?>
                                        <p class="description"><?php echo esc_html($sub_field['instructions']); ?></p>
                                    <?php endif; ?>
                                    <?php $this->render_field($sub_field, $sub_field_value, $sub_field_name); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button acf-pro-repeater-add-row" aria-label="<?php _e('Add new row', 'acf-pro'); ?>">
                <?php _e('Add Row', 'acf-pro'); ?>
            </button>
        </div>
        <?php
    }

    private function render_flexible_content_field($field, $value, $name) {
        $layouts = isset($field['layouts']) ? $field['layouts'] : [];
        $values = is_array($value) ? $value : [];

        wp_enqueue_script('acf-pro-flexible-content', plugin_dir_url(__FILE__) . 'assets/js/flexible-content.js', ['acf-pro-shared'], '1.1.0', true);
        wp_enqueue_style('acf-pro-flexible-content', plugin_dir_url(__FILE__) . 'assets/css/flexible-content.css', [], '1.1.0');
        wp_localize_script('acf-pro-flexible-content', 'acfProLayouts', $layouts);

        ?>
        <div class="acf-pro-flexible-content" data-field-name="<?php echo esc_attr($name); ?>">
            <div class="acf-pro-fc-rows">
                <?php foreach ($values as $i => $layout_data): ?>
                    <?php
                    $layout_name = isset($layout_data['acf_fc_layout']) ? $layout_data['acf_fc_layout'] : '';
                    $layout = isset($layouts[$layout_name]) ? $layouts[$layout_name] : null;
                    if (!$layout) continue;
                    ?>
                    <div class="acf-pro-fc-row" data-layout="<?php echo esc_attr($layout_name); ?>">
                        <div class="acf-pro-fc-row-header">
                            <span class="acf-pro-fc-row-title"><?php echo esc_html($layout['label']); ?></span>
                            <button type="button" class="button acf-pro-fc-remove-row" aria-label="<?php _e('Remove layout', 'acf-pro'); ?>">
                                <?php _e('Remove', 'acf-pro'); ?>
                            </button>
                        </div>
                        <div class="acf-pro-fc-row-content">
                            <?php foreach ($layout['sub_fields'] as $sub_field): ?>
                                <?php
                                $sub_field_name = sprintf('%s[%d][%s]', $name, $i, $sub_field['name']);
                                $sub_field_value = isset($layout_data[$sub_field['name']]) ? $layout_data[$sub_field['name']] : '';
                                ?>
                                <div class="acf-pro-fc-field">
                                    <label>
                                        <?php echo esc_html($sub_field['label']); ?>
                                        <?php if (!empty($sub_field['required'])): ?>
                                            <span class="required" aria-hidden="true">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php if (!empty($sub_field['instructions'])): ?>
                                        <p class="description"><?php echo esc_html($sub_field['instructions']); ?></p>
                                    <?php endif; ?>
                                    <?php $this->render_field($sub_field, $sub_field_value, $sub_field_name); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="acf-pro-fc-layouts">
                <select class="acf-pro-fc-layout-select" aria-label="<?php _e('Select layout', 'acf-pro'); ?>">
                    <option value=""><?php _e('Select Layout', 'acf-pro'); ?></option>
                    <?php foreach ($layouts as $layout_name => $layout): ?>
                        <option value="<?php echo esc_attr($layout_name); ?>"><?php echo esc_html($layout['label']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="button acf-pro-fc-add-row" aria-label="<?php _e('Add new layout', 'acf-pro'); ?>">
                    <?php _e('Add Layout', 'acf-pro'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    private function render_gallery_field($field, $value, $name) {
        $image_ids = is_array($value) ? $value : array_filter(explode(',', $value));

        wp_enqueue_script('acf-pro-gallery', plugin_dir_url(__FILE__) . 'assets/js/gallery.js', ['acf-pro-shared'], '1.1.0', true);
        wp_enqueue_style('acf-pro-gallery', plugin_dir_url(__FILE__) . 'assets/css/gallery.css', [], '1.1.0');

        ?>
        <div class="acf-pro-gallery">
            <div class="acf-pro-gallery-thumbnails">
                <?php foreach ($image_ids as $image_id): ?>
                    <?php
                    $image_id = absint($image_id);
                    $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                    if (!$image_url) continue;
                    ?>
                    <div class="acf-pro-gallery-thumbnail" data-id="<?php echo esc_attr($image_id); ?>">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title($image_id)); ?>">
                        <button type="button" class="button-link acf-pro-gallery-remove" aria-label="<?php _e('Remove image', 'acf-pro'); ?>">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button acf-pro-gallery-add" aria-label="<?php _e('Add images', 'acf-pro'); ?>">
                <?php _e('Add Images', 'acf-pro'); ?>
            </button>
            <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr(implode(',', $image_ids)); ?>">
        </div>
        <?php
    }

    private function render_select_field($field, $value, $name) {
        $options = isset($field['choices']) ? $field['choices'] : [];
        $multiple = !empty($field['multiple']) ? 'multiple' : '';

        wp_enqueue_style('acf-pro-select', plugin_dir_url(__FILE__) . 'assets/css/select.css', [], '1.1.0');

        ?>
        <div class="acf-pro-select-field">
            <select name="<?php echo esc_attr($name); ?><?php echo $multiple ? '[]' : ''; ?>" <?php echo $multiple; ?>>
                <?php if (empty($multiple)): ?>
                    <option value=""><?php _e('Select an option', 'acf-pro'); ?></option>
                <?php endif; ?>
                <?php foreach ($options as $key => $label): ?>
                    <?php
                    $selected = $multiple
                        ? (is_array($value) && in_array($key, $value) ? 'selected' : '')
                        : ($value == $key ? 'selected' : '');
                    ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php echo $selected; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    private function render_date_picker_field($field, $value, $name) {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', [], '1.12.1');
        wp_enqueue_script('acf-pro-date-picker', plugin_dir_url(__FILE__) . 'assets/js/date-picker.js', ['jquery-ui-datepicker'], '1.1.0', true);
        wp_enqueue_style('acf-pro-date-picker', plugin_dir_url(__FILE__) . 'assets/css/date-picker.css', [], '1.1.0');

        $date_format = !empty($field['date_format']) ? $field['date_format'] : 'yy-mm-dd';

        ?>
        <div class="acf-pro-date-picker-field">
            <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" class="acf-pro-date-picker" data-format="<?php echo esc_attr($date_format); ?>">
        </div>
        <?php
    }

    public function save_post_fields($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['acf_pro_meta_box_nonce']) || !wp_verify_nonce($_POST['acf_pro_meta_box_nonce'], 'acf_pro_meta_box')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $field_groups = acf_get_field_groups(['post_id' => $post_id]);

        foreach ($field_groups as $group) {
            $fields = acf_get_fields($group['key']);
            if (empty($fields)) continue;

            foreach ($fields as $field) {
                $field_name = 'acf_' . $field['name'];
                if (!isset($_POST[$field_name])) continue;

                $value = $_POST[$field_name];
                $value = $this->sanitize_field_value($field, $value);

                update_post_meta($post_id, $field_name, $value);
            }
        }
    }

    private function sanitize_field_value($field, $value) {
        switch ($field['type']) {
            case 'text':
            case 'textarea':
            case 'wysiwyg':
                return is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field($value);
            case 'email':
                return is_array($value) ? array_map('sanitize_email', $value) : sanitize_email($value);
            case 'url':
                return is_array($value) ? array_map('esc_url_raw', $value) : esc_url_raw($value);
            case 'number':
                return is_array($value) ? array_map('floatval', $value) : floatval($value);
            case 'checkbox':
            case 'select':
                return is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field($value);
            case 'date_picker':
                return sanitize_text_field($value);
            case 'file':
            case 'image':
            case 'gallery':
                return is_array($value) ? array_map('absint', $value) : absint($value);
            case 'repeater':
                if (is_array($value)) {
                    foreach ($value as &$row) {
                        foreach ($field['sub_fields'] as $sub_field) {
                            if (isset($row[$sub_field['name']])) {
                                $row[$sub_field['name']] = $this->sanitize_field_value($sub_field, $row[$sub_field['name']]);
                            }
                        }
                    }
                }
                return $value;
            case 'flexible_content':
                if (is_array($value)) {
                    foreach ($value as &$layout_data) {
                        $layout_name = isset($layout_data['acf_fc_layout']) ? $layout_data['acf_fc_layout'] : '';
                        $layout = isset($field['layouts'][$layout_name]) ? $field['layouts'][$layout_name] : null;
                        if ($layout) {
                            foreach ($layout['sub_fields'] as $sub_field) {
                                if (isset($layout_data[$sub_field['name']])) {
                                    $layout_data[$sub_field['name']] = $this->sanitize_field_value($sub_field, $layout_data[$sub_field['name']]);
                                }
                            }
                        }
                    }
                }
                return $value;
            default:
                return is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field($value);
        }
    }
}

class ACF_Pro_Frontend {

    public function __construct() {
        add_shortcode('acf_field', [$this, 'shortcode_field']);
        add_shortcode('acf_form', [$this, 'shortcode_form']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('elementor/dynamic_tags/register', [$this, 'register_elementor_tags']);
    }

    public function shortcode_field($atts) {
        $atts = shortcode_atts([
            'name' => '',
            'post_id' => get_the_ID(),
            'format' => true,
        ], $atts);

        if (empty($atts['name'])) {
            return '';
        }

        $value = get_post_meta($atts['post_id'], 'acf_' . $atts['name'], true);
        if ($atts['format']) {
            $value = apply_filters('acf_pro_format_field_value', $value, $atts['name']);
        }

        if (is_array($value)) {
            return wp_kses_post(json_encode($value));
        }

        return wp_kses_post($value);
    }

    public function shortcode_form($atts) {
        $atts = shortcode_atts([
            'field_group' => '',
            'post_id' => get_the_ID(),
        ], $atts);

        if (empty($atts['field_group'])) {
            return '';
        }

        $fields = acf_get_fields($atts['field_group']);
        if (empty($fields)) {
            return '';
        }

        ob_start();
        ?>
        <form method="post" class="acf-pro-form">
            <?php wp_nonce_field('acf_pro_form', 'acf_pro_form_nonce'); ?>
            <input type="hidden" name="acf_pro_post_id" value="<?php echo esc_attr($atts['post_id']); ?>">
            <input type="hidden" name="acf_pro_field_group" value="<?php echo esc_attr($atts['field_group']); ?>">
            <?php
            $acf_fields = new ACF_Pro_Fields();
            foreach ($fields as $field) {
                $field_name = 'acf_' . $field['name'];
                $value = get_post_meta($atts['post_id'], $field_name, true);
                ?>
                <div class="acf-pro-form-field">
                    <?php $acf_fields->render_field($field, $value, $field_name); ?>
                </div>
                <?php
            }
            ?>
            <button type="submit" class="button"><?php _e('Submit', 'acf-pro'); ?></button>
        </form>
        <?php
        return ob_get_clean();
    }

    public function register_rest_routes() {
        register_rest_route('acf-pro/v1', '/fields/(?P<post_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_fields'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('acf-pro/v1', '/save', [
            'methods' => 'POST',
            'callback' => [$this, 'save_form'],
            'permission_callback' => [$this, 'check_form_permissions'],
        ]);
    }

    public function get_fields($request) {
        $post_id = $request['post_id'];
        $fields = get_post_meta($post_id);
        $acf_fields = [];

        foreach ($fields as $key => $value) {
            if (strpos($key, 'acf_') === 0) {
                $acf_fields[$key] = $value[0];
            }
        }

        return rest_ensure_response($acf_fields);
    }

    public function check_form_permissions($request) {
        return current_user_can('edit_posts');
    }

    public function save_form($request) {
        if (!isset($_POST['acf_pro_form_nonce']) || !wp_verify_nonce($_POST['acf_pro_form_nonce'], 'acf_pro_form')) {
            return new WP_Error('invalid_nonce', __('Invalid nonce', 'acf-pro'), ['status' => 403]);
        }

        $post_id = isset($_POST['acf_pro_post_id']) ? absint($_POST['acf_pro_post_id']) : 0;
        $field_group = isset($_POST['acf_pro_field_group']) ? sanitize_text_field($_POST['acf_pro_field_group']) : '';

        if (!$post_id || !$field_group) {
            return new WP_Error('invalid_data', __('Invalid post ID or field group', 'acf-pro'), ['status' => 400]);
        }

        $acf_fields = new ACF_Pro_Fields();
        $acf_fields->save_post_fields($post_id);

        return rest_ensure_response(['success' => true, 'message' => __('Fields saved successfully', 'acf-pro')]);
    }

    public function register_elementor_tags($dynamic_tags) {
        if (!class_exists('Elementor\Plugin')) {
            return;
        }

        require_once plugin_dir_path(__FILE__) . 'includes/elementor-dynamic-tag.php';
        $dynamic_tags->register(new ACF_Pro_Elementor_Dynamic_Tag());
    }
}

