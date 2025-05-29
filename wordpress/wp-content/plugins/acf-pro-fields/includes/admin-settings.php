// Admin Settings (in includes/admin-settings.php)
<?php
class ACF_Pro_Admin_Settings {

    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'acf-pro'));
        }

        $field_groups = get_posts([
            'post_type' => 'acf_pro_field_group',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);

        ?>
        <div class="wrap">
            <h1><?php _e('ACF Pro Fields', 'acf-pro'); ?></h1>
            <h2><?php _e('Field Groups', 'acf-pro'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th><?php _e('Title', 'acf-pro'); ?></th>
                    <th><?php _e('Fields', 'acf-pro'); ?></th>
                    <th><?php _e('Rules', 'acf-pro'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($field_groups as $group): ?>
                    <?php
                    $fields = get_post_meta($group->ID, 'acf_pro_fields', true);
                    $rules = get_post_meta($group->ID, 'acf_pro_rules', true);
                    ?>
                    <tr>
                        <td><a href="<?php echo get_edit_post_link($group->ID); ?>"><?php echo esc_html($group->post_title); ?></a></td>
                        <td><?php echo esc_html(is_array($fields) ? count($fields) : 0); ?> <?php _e('fields', 'acf-pro'); ?></td>
                        <td><?php echo esc_html($rules ? json_encode($rules) : __('None', 'acf-pro')); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <a href="<?php echo admin_url('post-new.php?post_type=acf_pro_field_group'); ?>" class="button button-primary"><?php _e('Add New Field Group', 'acf-pro'); ?></a>
        </div>
        <?php
    }
}