<?php
/**
 * Plugin Name: LearnDash Custom Tabs
 * Description: Adds custom tabs to LearnDash.
 * Version: 2.0
 * Author: Muhammad Haris
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type
function lct_register_custom_tabs_post_type() {
    register_post_type('ld_custom_tab', [
        'labels' => [
            'name' => __('Custom Tabs', 'learndash-custom-tabs'),
            'singular_name' => __('Custom Tab', 'learndash-custom-tabs'),
            'add_new' => __('Add New Tab', 'learndash-custom-tabs'),
            'edit_item' => __('Edit Tab', 'learndash-custom-tabs'),
            'new_item' => __('New Tab', 'learndash-custom-tabs'),
            'view_item' => __('View Tab', 'learndash-custom-tabs'),
            'all_items' => __('Custom Tabs', 'learndash-custom-tabs'),
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'editor'],
        'menu_position' => 5,
        'show_in_menu' => 'learndash-lms',
    ]);
}
add_action('init', 'lct_register_custom_tabs_post_type');

// Add Meta Boxes
function lct_add_meta_boxes() {
    add_meta_box(
        'lct_tab_settings',
        __('Custom Tab Settings', 'learndash-custom-tabs'),
        'lct_render_meta_box',
        'ld_custom_tab',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'lct_add_meta_boxes');

// Render Meta Box
function lct_render_meta_box($post) {
    wp_nonce_field('lct_save_meta_box', 'lct_meta_box_nonce');
    $users = get_users();
    $display_to = get_post_meta($post->ID, '_lct_display_to', true) ?: 'all';
    $display_on = get_post_meta($post->ID, '_lct_display_on', true) ?: 'courses';
    $selected_courses = get_post_meta($post->ID, '_lct_courses', true) ?: [];
    $selected_lessons = get_post_meta($post->ID, '_lct_lessons', true) ?: [];
    $selected_topics = get_post_meta($post->ID, '_lct_topics', true) ?: [];
    $selected_quizzes = get_post_meta($post->ID, '_lct_quizzes', true) ?: [];
    $icon_class = get_post_meta($post->ID, '_lct_icon_class', true) ?: '';

    // Fetch LearnDash content
    $courses = get_posts(['post_type' => 'sfwd-courses', 'posts_per_page' => -1, 'post_status' => 'publish']);
    $lessons = get_posts(['post_type' => 'sfwd-lessons', 'posts_per_page' => -1, 'post_status' => 'publish']);
    $topics = get_posts(['post_type' => 'sfwd-topic', 'posts_per_page' => -1, 'post_status' => 'publish']);
    $quizzes = get_posts(['post_type' => 'sfwd-quiz', 'posts_per_page' => -1, 'post_status' => 'publish']);
    ?>
    
    <p style="display: flex; justify-content: space-between; align-items: center;">
        <label for="lct_display_to" style="width: 45%;"><?php _e('Display Tab To', 'learndash-custom-tabs'); ?></label>
        <select name="lct_display_to" id="lct_display_to" style="width: 45%;">
            <option value="all" <?php selected($display_to, 'all'); ?>><?php _e('All Users', 'learndash-custom-tabs'); ?></option>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($display_to, $user->ID); ?>>
                    <?php echo esc_html($user->display_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p style="display: flex; justify-content: space-between; align-items: center;">
        <label for="lct_display_on" style="width: 45%;"><?php _e('Display Tab On', 'learndash-custom-tabs'); ?></label>
        <select name="lct_display_on" id="lct_display_on" style="width: 45%;">
            <option value="all" <?php selected($display_on, 'all'); ?>><?php _e('All Options', 'learndash-custom-tabs'); ?></option>
            <option value="courses" <?php selected($display_on, 'courses'); ?>><?php _e('Courses', 'learndash-custom-tabs'); ?></option>
            <option value="lessons" <?php selected($display_on, 'lessons'); ?>><?php _e('Lessons', 'learndash-custom-tabs'); ?></option>
            <option value="topics" <?php selected($display_on, 'topics'); ?>><?php _e('Topics', 'learndash-custom-tabs'); ?></option>
            <option value="quizzes" <?php selected($display_on, 'quizzes'); ?>><?php _e('Quizzes', 'learndash-custom-tabs'); ?></option>
        </select>
    </p>

    <div id="lct_courses_field" class="lct_field_group" style="display: <?php echo in_array($display_on, ['courses', 'all']) ? 'block' : 'none'; ?>">
        <p style="display: flex; justify-content: space-between; align-items: center;">
            <label for="lct_courses" style="width: 45%;"><?php _e('Select Courses', 'learndash-custom-tabs'); ?></label>
            <select name="lct_courses[]" id="lct_courses" style="width: 45%;">
                <option value="all" <?php in_array('all', (array)$selected_courses) ? selected(true, true) : ''; ?>><?php _e('All Courses', 'learndash-custom-tabs'); ?></option>
                <?php foreach ($courses as $course) : ?>
                    <option value="<?php echo esc_attr($course->ID); ?>" <?php in_array($course->ID, (array)$selected_courses) ? selected(true, true) : ''; ?>>
                        <?php echo esc_html($course->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
    </div>

    <div id="lct_lessons_field" class="lct_field_group" style="display: <?php echo in_array($display_on, ['lessons', 'all']) ? 'block' : 'none'; ?>">
        <p style="display: flex; justify-content: space-between; align-items: center;">
            <label for="lct_lessons" style="width: 45%;"><?php _e('Select Lessons', 'learndash-custom-tabs'); ?></label>
            <select name="lct_lessons[]" id="lct_lessons" style="width: 45%;">
                <option value="all" <?php in_array('all', (array)$selected_lessons) ? selected(true, true) : ''; ?>><?php _e('All Lessons', 'learndash-custom-tabs'); ?></option>
                <?php foreach ($lessons as $lesson) : ?>
                    <option value="<?php echo esc_attr($lesson->ID); ?>" <?php in_array($lesson->ID, (array)$selected_lessons) ? selected(true, true) : ''; ?>>
                        <?php echo esc_html($lesson->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
    </div>

    <div id="lct_topics_field" class="lct_field_group" style="display: <?php echo in_array($display_on, ['topics', 'all']) ? 'block' : 'none'; ?>">
        <p style="display: flex; justify-content: space-between; align-items: center;">
            <label for="lct_topics" style="width: 45%;"><?php _e('Select Topics', 'learndash-custom-tabs'); ?></label>
            <select name="lct_topics[]" id="lct_topics" style="width: 45%;">
                <option value="all" <?php in_array('all', (array)$selected_topics) ? selected(true, true) : ''; ?>><?php _e('All Topics', 'learndash-custom-tabs'); ?></option>
                <?php foreach ($topics as $topic) : ?>
                    <option value="<?php echo esc_attr($topic->ID); ?>" <?php in_array($topic->ID, (array)$selected_topics) ? selected(true, true) : ''; ?>>
                        <?php echo esc_html($topic->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
    </div>

    <div id="lct_quizzes_field" class="lct_field_group" style="display: <?php echo in_array($display_on, ['quizzes', 'all']) ? 'block' : 'none'; ?>">
        <p style="display: flex; justify-content: space-between; align-items: center;">
            <label for="lct_quizzes" style="width: 45%;"><?php _e('Select Quizzes', 'learndash-custom-tabs'); ?></label>
            <select name="lct_quizzes[]" id="lct_quizzes" style="width: 45%;">
                <option value="all" <?php in_array('all', (array)$selected_quizzes) ? selected(true, true) : ''; ?>><?php _e('All Quizzes', 'learndash-custom-tabs'); ?></option>
                <?php foreach ($quizzes as $quiz) : ?>
                    <option value="<?php echo esc_attr($quiz->ID); ?>" <?php in_array($quiz->ID, (array)$selected_quizzes) ? selected(true, true) : ''; ?>>
                        <?php echo esc_html($quiz->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
    </div>

    <p style="display: flex; justify-content: space-between; align-items: center;">
        <label for="lct_icon_class" style="width: 45%;"><?php _e('Icon Classes', 'learndash-custom-tabs'); ?></label>
        <input type="text" name="lct_icon_class" id="lct_icon_class" value="<?php echo esc_attr($icon_class); ?>" style="width: 45%;" />
    </p>

    
    <script>
        jQuery(document).ready(function ($) {
            $('#lct_display_on').change(function () {
                $('.lct_field_group').hide();
                var selected = $(this).val();
                if (selected === 'all') {
                    $('.lct_field_group').show();
                } else {
                    $('#lct_' + selected + '_field').show();
                }
            }).trigger('change');
        });
    </script>
    <?php
}

// Save Meta Box
function lct_save_meta_box($post_id) {
    if (!isset($_POST['lct_meta_box_nonce']) || !wp_verify_nonce($_POST['lct_meta_box_nonce'], 'lct_save_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = [
        '_lct_display_to',
        '_lct_display_on',
        '_lct_courses',
        '_lct_lessons',
        '_lct_topics',
        '_lct_quizzes',
        '_lct_icon_class',
    ];

    foreach ($fields as $field) {
        if (isset($_POST[str_replace('_lct_', 'lct_', $field)])) {
            update_post_meta($post_id, $field, $_POST[str_replace('_lct_', 'lct_', $field)]);
        } else {
            delete_post_meta($post_id, $field);
        }
    }
}
add_action('save_post', 'lct_save_meta_box');


// Display On Frontend 

function display_learndash_custom_tab($tabs, $course_id) {
    global $wpdb;
    $course_id = get_the_ID();
    $current_course_id = $course_id;

    $query = $wpdb->prepare(
        "
        SELECT p.*, pm.meta_value AS lct_courses, pm_icon.meta_value AS lct_icon_class
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_lct_courses'
        LEFT JOIN {$wpdb->postmeta} pm_icon ON p.ID = pm_icon.post_id AND pm_icon.meta_key = '_lct_icon_class'
        WHERE p.post_type = 'ld_custom_tab'
          AND pm.meta_value LIKE %s
          AND p.post_status = 'publish';
        ",
        '%:"' . $current_course_id . '";%' 
    );    

    $results = $wpdb->get_results($query);

    if ($results) {
        foreach ($results as $post) {
            $tabs[$post->post_title] = array(
                'id'      => esc_attr($post->post_title),
                'icon'    => esc_attr($post->lct_icon_class),
                'label'   => esc_html($post->post_title),
                'content' => esc_html($post->post_content),
            );
        }

    }

    return $tabs;
}
add_filter('learndash_content_tabs', 'display_learndash_custom_tab', 10, 2);

