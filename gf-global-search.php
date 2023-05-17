<?php
/**
 * Plugin Name: Gravity Forms Global Search
 * Description: Adds a global search feature to search across all Gravity Forms entries.
 * Version: 1.0
 * Author: Johnathon Williams
 * Author URI: https://glug.blog
 */

// Check if Gravity Forms is active
if (class_exists('GFForms')) {
    add_action('admin_menu', 'gf_global_search_menu_item', 11);

    function gf_global_search_menu_item() {
        add_submenu_page('gf_edit_forms', __('Global Search', 'gravityforms'), __('Global Search', 'gravityforms'), 'manage_options', 'gf_global_search', 'gf_global_search_page');
    }

    function gf_global_search_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (isset($_POST['search_query'])) {
            $search_query = sanitize_text_field($_POST['search_query']);
            $results = gf_global_search_entries($search_query);
        }

        ?>
        <div class="wrap">
            <h1><?php _e('Gravity Forms Global Search', 'gravityforms'); ?></h1>
            <form method="post" action="">
                <input type="text" name="search_query" value="<?php echo isset($search_query) ? esc_attr($search_query) : ''; ?>" placeholder="<?php _e('Search Entries', 'gravityforms'); ?>">
                <input type="submit" value="<?php _e('Search', 'gravityforms'); ?>" class="button button-primary">
            </form>
            <?php if (isset($results)): ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Entry ID', 'gravityforms'); ?></th>
                            <th><?php _e('View Entry', 'gravityforms'); ?></th>
                            <th><?php _e('Form Name', 'gravityforms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo esc_html($result['entry_id']); ?></td>
                                <td><a href="<?php echo admin_url('admin.php?page=gf_entries&view=entry&id=' . $result['form_id'] . '&lid=' . $result['entry_id']); ?>"><?php _e('View Entry', 'gravityforms'); ?></a></td>
                                <td><?php echo esc_html($result['form_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    function gf_global_search_entries($search_query) {
        global $wpdb;
        $entry_table = GFFormsModel::get_entry_table_name();
        $entry_meta_table = GFFormsModel::get_entry_meta_table_name();
        $form_table = GFFormsModel::get_form_table_name();
    
        $sql = $wpdb->prepare("SELECT e.id as entry_id, e.form_id, f.title as form_name
                               FROM {$entry_table} e
                               INNER JOIN {$entry_meta_table} em ON e.id = em.entry_id
                               INNER JOIN {$form_table} f ON e.form_id = f.id
                               WHERE em.meta_value LIKE %s
                               AND e.status = 'active'
                               GROUP BY e.id", '%' . $wpdb->esc_like($search_query) . '%');
    
        $results = $wpdb->get_results($sql, ARRAY_A);
    
        return $results;
    }
    
    
}
