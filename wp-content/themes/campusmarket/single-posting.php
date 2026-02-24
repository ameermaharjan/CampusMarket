<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

<div class="card">
    <h2><?php the_title(); ?></h2>
    <?php the_content(); ?>

    <p><strong>Price:</strong> 
        <?php echo get_post_meta(get_the_ID(), '_price', true); ?>
    </p>

    <p><strong>Condition:</strong> 
        <?php echo get_post_meta(get_the_ID(), '_condition', true); ?>
    </p>

    <p><strong>Type:</strong> 
        <?php the_terms(get_the_ID(), 'listing_type'); ?>
    </p>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>