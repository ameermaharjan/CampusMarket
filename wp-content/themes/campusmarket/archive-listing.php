<?php get_header(); ?>

<h1>All Listings</h1>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="card">
    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <p><?php the_excerpt(); ?></p>
    <a class="button" href="<?php the_permalink(); ?>">View Details</a>
</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>