<!-- sidebar -->
	<nav id="sidebar">
        <div id="fixed-sidebar-container">            
            <div class="sidebar-header">
            <a href="<?php echo home_url( '/' ); ?>" data-dismiss="modal" aria-label="Close">
                <?php include (TEMPLATEPATH . '/inc/logo.php' ); ?>
            </a>
            </div>			
        
            <div class="ctas">
                <ul class="list-unstyled">

                    <?php $get_account_page_id = get_field( 'account_page', 'options', false ); ?> 
                    <?php $get_reg_page_id = get_field( 'registration_page', 'options', false ); ?> 

                    <?php /*?>// Iframe login *** OLD
                    <?php if ( is_user_logged_in() ) { ?>
                        <li>
                            <a href="#" data-toggle="modal" data-target="#gameModal" class="btn" data-game_name="<?php the_field('my_account','options'); ?>" data-game_link="<?php echo get_the_permalink( $get_account_page_id ); ?>"><?php the_field('my_account','options'); ?></a>
                        </li> 
                        <li>
                            <a href="<?php echo wp_logout_url( home_url() ); ?>" class="btn"><?php the_field('logout','options'); ?></a>
                        </li> 
                    <?php } else { ?> 
                        <li>
                            <a href="#" data-toggle="modal" data-target="#gameModal" class="btn" data-game_name="<?php the_field('register','options'); ?>" data-game_link="<?php echo get_the_permalink( $get_reg_page_id ); ?>"><?php the_field('register','options'); ?></a>
                        </li> 
                        <li>
                            <a href="#" data-toggle="modal" data-target="#gameModal" class="btn" data-game_name="<?php the_field('login','options'); ?>" data-game_link="<?php echo get_the_permalink( $get_account_page_id ); ?>"><?php the_field('login','options'); ?></a>
                        </li>                
                    <?php } ?>
                    <?php */?>
                        
                        <li>
                            <a href="<?php echo home_url( '/' ); ?>"><?php the_field('games-title','options'); ?></a>
                        </li>                    
                    <?php if ( is_user_logged_in() ) { ?>
                        <li>
                            <a href="<?php echo get_the_permalink( $get_account_page_id ); ?>"><?php the_field('my_account','options'); ?></a>
                        </li> 
                        <li>
                            <a href="<?php echo wp_logout_url( ); ?>" class="btn"><?php the_field('logout','options'); ?></a>
                        </li> 
                    <?php } else { ?> 
                        <li>
                            <a href="<?php echo get_the_permalink( $get_reg_page_id ); ?>"><?php the_field('register','options'); ?></a>
                        </li> 
                        <li>
                            <a href="<?php echo get_the_permalink( $get_account_page_id ); ?>"><?php the_field('login','options'); ?></a>
                        </li>                
                    <?php } ?>                

                </ul>

                <?php verajohn_nav(array( 'theme_location' => 'sidebar-menu' )); ?>
                
            </div>
            
                <?php include (TEMPLATEPATH . '/inc/ads/ad_skyscraper.php' ); ?>
        
            
            <?php include (TEMPLATEPATH . '/inc/copyright.php' ); ?>
        </div>        
    </nav>
<!-- /sidebar -->
