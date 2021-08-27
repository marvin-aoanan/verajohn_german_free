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
                            <h1><?php the_title(); ?></h1>
                           <?php if ( is_page( 11 )) { ?>
                                 <h2 class="text-center">
                                    <a class="btn" href="<?php the_field('account_page','options'); ?>"><?php the_field('login','options'); ?></a>
                                  </h2>
                            <?php } else if ( is_page( 10 )) { ?>
                                  <h2 class="text-center">
                                    <a class="btn" href="<?php the_field('registration_page','options'); ?>"><?php the_field('register','options'); ?></a>
                                  </h2>
                            <?php } ?>
                            <?php the_content(); ?>                            
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
