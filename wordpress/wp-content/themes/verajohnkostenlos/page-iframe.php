<?php
/*
Template Name: IFrame Page
*/
?>

<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <div id="account" class="container">
        <div class="row content-center">
            <div class="col-12">
                <?php include (TEMPLATEPATH . '/inc/logo.php' ); ?>
                <?php the_content(); ?>
            </div>	
            <div class="col-12 text-center">
                <a class="btn" href="<?php echo home_url(); ?>" onclick="window.parent.closeModal()"><?php the_field('return_home','options'); ?></a>
            </div>
            
        </div>
    </div>
<?php endwhile; ?>

<?php else: ?>

    <div class="container">
        <div class="row content-center">
            <div class="col-12">
                <?php the_field('sorry_nothing_to_display','options'); ?>
            </div>
            <div class="col-12 text-center">
                <a class="btn" href="<?php echo home_url(); ?>"><?php the_field('return_home','options'); ?></a>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php get_footer(); ?>