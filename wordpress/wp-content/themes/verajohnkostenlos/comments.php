<div class="comments">
	<?php if (post_password_required()) : ?>
	<p><?php the_field('post_is_password_protected_enter_the_password_to_view_any_comments','options'); ?></p>
</div>

	<?php return; endif; ?>

<?php if (have_comments()) : ?>

	<h2><?php comments_number(); ?></h2>

	<ul>
		<?php wp_list_comments('type=comment&callback=verajohncomments'); // Custom callback in functions.php ?>
	</ul>

<?php elseif ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) : ?>

	<p><?php the_field('comments_are_closed_here','options'); ?></p>

<?php endif; ?>

<?php comment_form(); ?>

</div>
