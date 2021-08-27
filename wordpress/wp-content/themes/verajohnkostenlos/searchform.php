<!-- search -->
<form class="search" method="get" action="<?php echo home_url(); ?>" role="search">
	<input class="search-input" type="search" name="s" placeholder="<?php the_field('to_search_type_and_hit_enter','options'); ?>">
	<button class="search-submit" type="submit" role="button"><?php the_field('search','options'); ?></button>
</form>
<!-- /search -->
