<?php get_header(); ?>


    <div class="wrapper">
			<!-- Sidebar  -->
            <?php get_sidebar(); ?>
			<!-- Page Content  -->
			<section id="content">
				<div class="container-fluid">
                    <?php include (TEMPLATEPATH . '/inc/ads/ad_leaderboard.php' ); ?>
				</div>

				<div class="container-fluid">
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
                            <main role="main">
                                <!-- section -->
                                <section>

                                <?php if (have_posts()): the_post(); ?>

                                    <h1><?php the_field('author_archives','options'); echo get_the_author(); ?></h1>

                                <?php if ( get_the_author_meta('description')) : ?>

                                <?php echo get_avatar(get_the_author_meta('user_email')); ?>

                                    <h2><?php the_field('about','options'); echo get_the_author() ; ?></h2>

                                    <?php echo wpautop( get_the_author_meta('description') ); ?>

                                <?php endif; ?>

                                <?php rewind_posts(); while (have_posts()) : the_post(); ?>

                                    <!-- article -->
                                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                                        <!-- post thumbnail -->
                                        <?php if ( has_post_thumbnail()) : // Check if Thumbnail exists ?>
                                            <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                                                <?php the_post_thumbnail(array(120,120)); // Declare pixel size you need inside the array ?>
                                            </a>
                                        <?php endif; ?>
                                        <!-- /post thumbnail -->

                                        <!-- post title -->
                                        <h2>
                                            <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                                        </h2>
                                        <!-- /Post title -->

                                        <!-- post details -->
                                        <span class="date"><?php the_time('F j, Y'); ?> <?php the_time('g:i a'); ?></span>
                                        <span class="author"><?php the_field('published_by','options'); ?> <?php the_author_posts_link(); ?></span>
                                        <span class="comments"><?php comments_popup_link( __( 'Leave your thoughts', 'verajohn' ), __( '1 Comment', 'verajohn' ), __( '% Comments', 'verajohn' )); ?></span>
                                        <!-- /post details -->

                                        <?php verajohnwp_excerpt('verajohnwp_index'); // Build your custom callback length in functions.php ?>

                                        <br class="clear">

                                        <?php edit_post_link(); ?>

                                    </article>
                                    <!-- /article -->

                                <?php endwhile; ?>

                                <?php else: ?>

                                    <!-- article -->
                                    <article>

                                        <h2><?php the_field('sorry_nothing_to_display','options'); ?></h2>
                                        <a class="btn" href="<?php echo home_url(); ?>"><?php the_field('return_home','options'); ?></a>
           

                                    </article>
                                    <!-- /article -->

                                <?php endif; ?>

                                    <?php get_template_part('pagination'); ?>

                                </section>
                                <!-- /section -->
                            </main>
                         </div>
					</div>
				</div>          
			</section>
		</div> 

<?php get_footer(); ?>