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
                            <!-- article -->
                            <article id="post-404">

                                <h1><?php the_field('page_not_found','options'); ?></h1>
                                <h2 class="text-center">
                                    <a class="btn" href="<?php echo home_url(); ?>"><?php the_field('return_home','options'); ?></a>
                                </h2>

                            </article>
                            <!-- /article -->
                         </div>
					</div>
				</div>          
			</section>
		</div> 

<?php get_footer(); ?>