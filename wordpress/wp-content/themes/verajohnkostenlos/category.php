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
                            <h1><?php the_field('categories_for','options'); single_cat_title(); ?></h1>
                            <?php get_template_part('loop'); ?>
                            <?php get_template_part('pagination'); ?>
                         </div>
					</div>
				</div>          
			</section>
		</div> 

<?php get_footer(); ?>
