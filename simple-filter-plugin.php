<?php
/*
Plugin Name: Simple Filter Plugin
Description: A simple blog post filter plugin for WordPress.
Version: 1.0
Author: Linus Becker
*/

// Hook to initialize the plugin
add_action('init', 'sfp_init');

function sfp_init() {
    // Add shortcode for displaying filters
    add_shortcode('sfp_filter', 'sfp_display_filters');
    // Modify the main query
    add_action('pre_get_posts', 'sfp_modify_query');
    // Enqueue styles
    add_action('wp_enqueue_scripts', 'sfp_enqueue_scripts');
}

function sfp_enqueue_scripts() {
    wp_enqueue_style('sfp-style', plugin_dir_url(__FILE__) . 'sfp-style.css');
}

function sfp_display_filters() {
    ob_start();
    ?>
    <div id="sfp-filter-buttons">
        <form id="sfp-filter-form" method="GET" action="<?php echo esc_url(get_permalink()); ?>">
            <button type="submit" name="sfp-category" value="">All Categories</button>
            <?php
            $categories = get_categories();
            foreach ($categories as $category) {
                $selected = (isset($_GET['sfp-category']) && $_GET['sfp-category'] == $category->slug) ? 'selected' : '';
                echo '<button type="submit" name="sfp-category" value="' . esc_attr($category->slug) . '" ' . $selected . '>' . esc_html($category->name) . '</button>';
            }
            ?>
        </form>
    </div>

    <?php
    // Display filtered posts
    if (isset($_GET['sfp-category']) && !empty($_GET['sfp-category'])) {
        $query_args = array(
            'post_type' => 'post',
            'category_name' => sanitize_text_field($_GET['sfp-category']),
            'posts_per_page' => -1,
        );

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            echo '<div id="sfp-posts">';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<h2>' . get_the_title() . '</h2>';
                the_excerpt();
            }
            echo '</div>';
        } else {
            echo '<p>No posts found.</p>';
        }

        wp_reset_postdata();
    } else {
        // Display all posts initially
        $query_args = array(
            'post_type' => 'post',
            'posts_per_page' => -1, // Display all posts
        );

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            echo '<div id="sfp-posts">';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<h2>' . get_the_title() . '</h2>';
                the_excerpt();
            }
            echo '</div>';
        } else {
            echo '<p>No posts found.</p>';
        }

        wp_reset_postdata();
    }

    return ob_get_clean();
}

function sfp_modify_query($query) {
    if (!is_admin() && $query->is_main_query() && isset($_GET['sfp-category'])) {
        $category = sanitize_text_field($_GET['sfp-category']);
        
        // Only filter if a specific category is selected
        if (!empty($category)) {
            $query->set('category_name', $category);
        } else {
            // If no category is selected, ensure all posts are displayed
            $query->set('category_name', ''); // Clear category filter
        }
    }
}