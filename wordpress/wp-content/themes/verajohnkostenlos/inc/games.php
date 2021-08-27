<?php if(get_field('games',  'options')): ?>
<div class="row">
<?php while(the_repeater_field('games',  'options')): ?>
       <!-- Game Tile -->

        <?php /*?>
            <?php echo '<pre>';
            print_r( get_sub_field('game_categories') );
             echo '</pre>';
            die; ?>
        <?php */?>


        <?php  
            //filter strings   
            $game_name = get_sub_field('game_name');
            $game_name_uc = ucwords($game_name);
            $game_name_lc = strtolower($game_name); 

            $game_categories_id = get_sub_field('game_categories');
            $game_categories = $game_categories_id->name;
            $game_categories_uc = ucwords($game_categories);
            $game_categories_lc = strtolower($game_categories);
            
            $game_providers_id = get_sub_field('game_providers');
            $game_providers = $game_providers_id->name;
            $game_providers_uc = ucwords($game_providers);
            $game_providers_lc = strtolower($game_providers);
        ?>
    
        <?php
        if ( wp_is_mobile() ) {
            $game_tile = wp_get_attachment_image_src(get_sub_field('game_tile'), 'game_tile_mobile');
        } else {
            $game_tile = wp_get_attachment_image_src(get_sub_field('game_tile'), 'full');
        }
        ?>
        
        <div class="gt col-lg-6 col-xl-4 animated fadeIn nopadding" data-filter="<?php echo $game_name; ?> <?php echo $game_name_uc; ?> <?php echo $game_name_lc; ?> <?php echo $game_categories; ?> <?php echo $game_categories_uc; ?> <?php echo $game_categories_lc; ?> <?php echo $game_providers; ?> <?php echo $game_providers_uc; ?> <?php echo $game_providers_lc; ?>">             
            
            
                <?php if ( is_user_logged_in() ) { ?>
                    <a href="#" class="game-tile hvr-shrink" data-toggle="modal" data-target="#gameModal" data-game_name="<?php echo $game_name_uc; ?>" data-game_link="<?php the_sub_field('game_url'); ?>"> 
                <?php } else { ?>                     
                    <a href="<?php the_field('registration_page','options'); ?>">
                <?php } ?>      
                <div class="card">
                  <div class="inner-gt text-center d-flex w-100 h-100">
                      <div class="gt-hvr d-flex w-100 h-100 justify-content-center align-self-center text-center">
                          <div class="justify-content-center align-self-center text-center">
                              <h2><?php echo $game_name_uc; ?></h2>
                              <div class="gt-tag gt-game-pro"><?php echo $game_providers_uc; ?></div>
                              <div class="btn d-flex"><?php the_sub_field('game_button'); ?></div>
                              <div class="gt-tag gt-game-cat"><?php echo $game_categories_uc; ?></div>
                          </div>
                      </div>
                   </div>
                  <img class="card-img-top" src="<?php echo $game_tile[0]; ?>" alt="Card image cap">
                </div>                
            </a>
        </div>
        <!-- END Game Tile -->
<?php endwhile; ?>
</div>
<?php endif; ?>