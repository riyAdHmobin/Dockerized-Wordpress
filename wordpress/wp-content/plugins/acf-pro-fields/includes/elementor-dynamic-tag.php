// Elementor Dynamic Tag (in includes/elementor-dynamic-tag.php)
<?php
if (!class_exists('Elementor\Plugin')) {
    return;
}

class ACF_Pro_Elementor_Dynamic_Tag extends \Elementor\Modules\DynamicTags\Tags\Tag {

    public function get_name() {
        return 'acf-pro-field';
    }

    public function get_title() {
        return __('ACF Pro Field', 'acf-pro');
    }

    public function get_group() {
        return 'acf-pro';
    }

    public function get_categories() {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
        ];
    }

    protected function register_controls() {
        $this->add_control(
            'field_name',
            [
                'label' => __('Field Name', 'acf-pro'),
                'type' => \Elementor\Controls_Manager::TEXT,
            ]
        );
        $this->add_control(
            'field_type',
            [
                'label' => __('Field Type', 'acf-pro'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'text' => __('Text', 'acf-pro'),
                    'url' => __('URL', 'acf-pro'),
                    'image' => __('Image', 'acf-pro'),
                    'file' => __('File', 'acf-pro'),
                ],
                'default' => 'text',
            ]
        );
    }

    public function render() {
        $field_name = $this->get_settings('field_name');
        $field_type = $this->get_settings('field_type');
        if (empty($field_name)) {
            return;
        }

        $post_id = get_the_ID();
        $value = get_post_meta($post_id, 'acf_' . $field_name, true);

        switch ($field_type) {
            case 'url':
                echo esc_url($value);
                break;
            case 'image':
                if ($value) {
                    echo wp_get_attachment_image($value, 'full');
                }
                break;
            case 'file':
                if ($value) {
                    echo '<a href="' . esc_url(wp_get_attachment_url($value)) . '">' . esc_html(get_the_title($value)) . '</a>';
                }
                break;
            default:
                echo wp_kses_post(is_array($value) ? json_encode($value) : $value);
                break;
        }
    }
}

// Initialize classes
new ACF_Pro_Fields();
new ACF_Pro_Frontend();