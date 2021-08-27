<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active search-tab" id="search-tab" data-toggle="pill" href="#search" role="tab" aria-controls="search" aria-selected="true" data-game-type="all"><?php the_field('search','options'); ?></a>
  </li>
  <li class="nav-item">
    <a class="nav-link search-tab" id="game-categories-tab" data-toggle="pill" href="#game-categories" role="tab" aria-controls="profile" aria-selected="false" data-game-type="all"><?php the_field('game_categories','options'); ?></a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="game-providers-tab" data-toggle="pill" href="#game-providers" role="tab" aria-controls="game-providers" aria-selected="false" data-game-type="all"><?php the_field('game_providers','options'); ?></a>
  </li>
</ul>

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="search" role="tabpanel" aria-labelledby="search-tab">
        <input class="form-control mb-3" type="search" name="filter" id="search-field" value="" placeholder="<?php the_field('game_search_placeholder','options'); ?>"/>
    </div>
    
    <div class="tab-pane fade" id="game-categories" role="tabpanel" aria-labelledby="game-categories-tab">
        <ul class="nav nav-pills mb-3">
            <?php
            $taxonomy     = 'game-categories';
            $show_count   = 1;      // 1 for yes, 0 for no
            $pad_counts   = 0;      // 1 for yes, 0 for no
            $hierarchical = 0;      // 1 for yes, 0 for no
            $title        = '';
            $child_of     = 0;
            $parent       = 0;


            $args = array(
             'taxonomy'     => $taxonomy,
             'hide_empty'   => false,
             'orderby'      => $orderby,
             'show_count'   => $show_count,
             'pad_counts'   => $pad_counts,
             'hierarchical' => $hierarchical,
             'title_li'     => $title,
             'child_of'     => $child_of,               
             'parent'         => $parent,
            );
            $categories = get_categories($args);
            foreach($categories as $category) {
                $category_lc = strtolower($category->name);
               echo '<li class="nav-item"><a class="nav-link search-tab" href="#" tabindex="-1" data-game-type="' . $category_lc . '">' . $category->name . '</a></li>';
               }
            ?>
        </ul> 
    </div>
    
    <div class="tab-pane fade" id="game-providers" role="tabpanel" aria-labelledby="game-providers-tab">
        <ul class="nav nav-pills mb-3">
            <?php
            $taxonomy     = 'providers';
            $show_count   = 1;      // 1 for yes, 0 for no
            $pad_counts   = 0;      // 1 for yes, 0 for no
            $hierarchical = 0;      // 1 for yes, 0 for no
            $title        = '';
            $child_of     = 0;
            $parent       = 0;


            $args = array(
             'taxonomy'     => $taxonomy,
             'hide_empty'   => false,
             'orderby'      => $orderby,
             'show_count'   => $show_count,
             'pad_counts'   => $pad_counts,
             'hierarchical' => $hierarchical,
             'title_li'     => $title,
             'child_of'     => $child_of,               
             'parent'         => $parent,
            );
            $categories = get_categories($args);
            foreach($categories as $category) {
                $category_lc = strtolower($category->name);
               echo '<li class="nav-item"><a class="nav-link search-tab" href="#" tabindex="-1" data-game-type="' . $category_lc . '">' . $category->name . '</a></li>';
               }
            ?>
        </ul>
    </div>      
</div>