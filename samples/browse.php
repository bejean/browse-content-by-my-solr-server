<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div class="wrapper">
			<div id="content" role="main">

<?php if ( have_posts() ) : ?>

				<?php
				/* Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called loop-search.php and that will be used instead.
				 */

				while( have_posts() ) { the_post() ?>
        
                    <div id="post-<?php the_ID(); ?>" class="post">
    
						<?php $category = get_the_category(); // To show only 1 Category ?>            
                        <div class="btn-cat"><span class="btn-general"><?php the_category( ',' ); ?></span></div>
                        
                        <h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_attr_e( 'Permanent Link to','woothemes' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                        <div class="date"><?php the_time( get_option( 'date_format' ) ); ?> <?php comments_popup_link( __( 'No comments yet','woothemes' ), __( '1 comment','woothemes' ), __( '% comments','woothemes' ) ); ?></div>
                        
                        <?php the_excerpt(); ?>
                        <div class="btn-continue"><a href="<?php the_permalink(); ?>"><?php _e( 'Continue Reading', 'woothemes' ); ?></a></div>
                    
                    </div>
                    <!--/post-->                            
        
                <?php } // End WHILE Loop ?>

				<br class="fix" />
        
<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (  $wp_query->max_num_pages > 1 ) : ?>
				<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
				</div><!-- #nav-below -->
<?php endif; ?>

<?php else : ?>
				<div id="post-0" class="post no-results not-found">
					<h2 class="entry-title"><?php _e( 'Nothing Found', 'twentyten' ); ?></h2>
					<div class="entry-content">
						<p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'twentyten' ); ?></p>
					</div><!-- .entry-content -->
				</div><!-- #post-0 -->
<?php endif; ?>
			</div><!-- #content -->
			
			<?php get_sidebar(); ?>
			
		</div><!-- wrapper -->
		</div><!-- #container -->

<?php get_footer(); ?>
