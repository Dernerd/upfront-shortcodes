<?php defined( 'ABSPATH' ) || exit; ?>

<?php
/**
 * READ BEFORE EDITING!
 *
 * Do not edit templates in the plugin folder, since all your changes will be
 * lost after the plugin update. Read the following article to learn how to
 * change this template or create a custom one:
 *
 * https://nerds.work/docs/posts/#built-in-templates
 */
?>

<div class="su-posts su-posts-default-loop">

	<?php if ( $posts->have_posts() ) : ?>

		<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>

			<div id="su-post-<?php the_ID(); ?>" class="su-post">

				<?php if ( has_post_thumbnail( get_the_ID() ) ) : ?>
					<a class="su-post-thumbnail" href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
				<?php endif; ?>

				<h2 class="su-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

				<div class="su-post-meta">
					<?php _e( 'Veröffentlicht', 'upfront-shortcodes' ); ?>: <?php the_time( get_option( 'date_format' ) ); ?>
				</div>

				<div class="su-post-excerpt">
					<?php the_excerpt(); ?>
				</div>

				<?php if ( have_comments() || comments_open() ) : ?>
					<a href="<?php comments_link(); ?>" class="su-post-comments-link"><?php comments_number( __( '0 Kommentare', 'upfront-shortcodes' ), __( '1 Kommentar', 'upfront-shortcodes' ), '% comments' ); ?></a>
				<?php endif; ?>

			</div>

		<?php endwhile; ?>

	<?php else : ?>
		<h4><?php _e( 'Beiträge nicht gefunden', 'upfront-shortcodes' ); ?></h4>
	<?php endif; ?>

</div>
