<?php get_header(); ?>

<?php if (have_posts()): while (have_posts()) : the_post(); ?>
    <div class="wrapper">
			<!-- Sidebar  -->
            <?php get_sidebar(); ?>
			<!-- Page Content  -->
			<section id="content">
				<div class="container-fluid">
                    <?php include (TEMPLATEPATH . '/inc/ads/ad_leaderboard.php' ); ?>
				</div>

				<div class="container">
                    <div id="sidebarCollapse" class="btn btn-info m-only">					
                        <i class="material-icons">dehaze</i>
                    </div>
					<div class="row game-content">
						<div class="col-12">
							<div id="page-header" class="m-only">
							  <?php include (TEMPLATEPATH . '/inc/logo.php' ); ?>
							</div>
						</div>
                        <div id="page-content" class="col-12">
                            <!-- article -->
                            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                                <!-- post thumbnail -->
                                <?php if ( has_post_thumbnail()) : // Check if Thumbnail exists ?>
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                                        <?php the_post_thumbnail(); // Fullsize image for the single post ?>
                                    </a>
                                <?php endif; ?>
                                <!-- /post thumbnail -->

                                <!-- post title -->
                                <h1>
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                                </h1>
                                <!-- /post title -->

                                <!-- post details -->
                                <span class="date"><?php the_time('F j, Y'); ?> <?php the_time('g:i a'); ?></span>
                                <span class="author"><?php the_field('published_by','options'); ?> <?php the_author_posts_link(); ?></span>
                                <span class="comments"><?php if (comments_open( get_the_ID() ) ) comments_popup_link( __( 'Leave your thoughts', 'verajohn' ), __( '1 Comment', 'verajohn' ), __( '% Comments', 'verajohn' )); ?></span>
                                <!-- /post details -->

                                <?php the_content(); // Dynamic Content ?>

                                <?php the_tags( __( 'Tags: ', 'verajohn' ), ', ', '<br>'); // Separated by commas with a line break at the end ?>

                                <p><?php the_field('categorised_in','options'); the_category(', '); // Separated by commas ?></p>

                                <p><?php the_field('this_post_was_written_by','options'); the_author(); ?></p>

                                <?php edit_post_link(); // Always handy to have Edit Post Links available ?>

                                <?php comments_template(); ?>

                            </article>
                            <!-- /article -->
                         </div>
					</div>
				</div>          
			</section>
		</div> 

<?php endwhile; ?>

<?php else: ?>

    <div class="container">
        <div class="row content-center">
            <div class="col-12">
                <h1><?php the_title(); ?></h1>
             </div>
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