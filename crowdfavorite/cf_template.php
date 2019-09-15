<?php
/*
 * Template Name: Crowd Favorite template
 * Description: A Page Template for the plugin to show the team members
 */

get_header();
?>

<section id="primary" class="content-area">
    <main id="main" class="site-main">

    <?php
    if ( have_posts() ) {
        // Load posts loop.
        while ( have_posts() ) {
            the_post();
            get_template_part( 'template-parts/content/content' );
        }
    } else {
        // If no content, include the "No posts found" template.
        get_template_part( 'template-parts/content/content', 'none' );
    }
    ?>

    </main><!-- .site-main -->
</section><!-- .content-area -->

<?php

// Get the custom posts 'team_member'
$posts = get_posts([
  'post_type' => 'team_member', // Post type to get the posts from
  'post_status' => 'publish',   // Only show the published posts
  'numberposts' => 12,          // Number of posts to show
  'orderby' => 'title',         // Order the posts alphabetically by name
  'order'   => 'ASC',           // Order posts ascending
]);

//var_dump($posts);
?>

<div class="main">
    <div class="main-content">
        <?php
        $count = 1;
        // Loop through the posts and get the values, show 3 on a row
        foreach (array_chunk($posts, 3, true) as $array) {
            // Add the section wrapper for each 3 posts
            echo "<div class='team-member-section section group'>";
            foreach($array as $post) {
                 // Functions 'esc_html()' and 'esc_url()' are used to escape the values before displaying them
                 $member_thumbnail = esc_html(get_the_post_thumbnail_url( $post->ID, $size = 'post-thumbnail' ));
                 $member_name = esc_html($post->post_title);
                 $member_description = esc_html($post->post_content);
                 $meta_value_position = esc_html(get_post_meta( $post->ID, '_wp_position_meta_key', true ));
                 $meta_value_twitter = esc_url(get_post_meta( $post->ID, '_wp_twitter_meta_key', true ));
                 $meta_value_facebook = esc_url(get_post_meta( $post->ID, '_wp_facebook_meta_key', true ));

                 // Add a post with all details inside the section wrapper
                 echo "
                    <div class='col span_1_of_3'>
                        <div class='thumbnail_wrap'>
                            <img src='$member_thumbnail' width='318' height='180' alt='member thumbnail'>
                        </div>
                        <h5>$member_name</h5>
                        <i>$meta_value_position</i>
                        <div class='social_wrap'>
                            <a href='$meta_value_twitter' target='_blank'><img src='" . plugin_dir_url( __FILE__ ) . 'img/twitter.png' . "' width='20' height='20' alt='twitter url'></a>
                            <a href='$meta_value_facebook' target='_blank'><img src='" . plugin_dir_url( __FILE__ ) . 'img/facebook.png' . "' width='20' height='20' alt='facebook url'></a>
                        </div>
                        <div class='description_wrap'>
                            <p>$member_description</p>
                        </div>
                        <button class='read_more_btn'>Read more</button>
                    </div>";
            }
            echo "</div>";
        }

        ?>

    </div>
</div>

<?php get_footer(); ?>
