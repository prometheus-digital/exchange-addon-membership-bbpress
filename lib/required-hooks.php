<?php
/**
 * iThemes Exchange Membership bbPress Add-on
 * @package exchange-addon-membership-bbpress
 * @since 1.0.0
*/

/**
 * Shows the nag when needed.
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_bbpress_addon_show_version_nag() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
	if ( !is_plugin_active( 'exchange-addon-membership/exchange-addon-membership.php' ) ) {
		?>
		<div id="it-exchange-add-on-required-plugin-nag" class="it-exchange-nag">
			<?php _e( 'The Membership bbPress add-on requires the iThemes Exchange Membership addon. Please install it.', 'LION' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery("#it-exchange-add-on-required-plugin-nag").insertAfter('.wrap > h2').addClass( 'after-h2' );
				}
			});
		</script>
		<?php
	}
	
	if ( !is_plugin_active( 'bbpress/bbpress.php' ) ) {
		?>
		<div id="it-exchange-add-on-required-plugin-nag" class="it-exchange-nag">
			<?php _e( 'The Membership bbPress add-on requires bbPress plugin. Please install it.', 'LION' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery("#it-exchange-add-on-required-plugin-nag").insertAfter('.wrap > h2').addClass( 'after-h2' );
				}
			});
		</script>
		<?php
	}
}
add_action( 'admin_notices', 'it_exchange_membership_bbpress_addon_show_version_nag' );

/**
 * Display Restricted or Dripped message.
 *
 * Only shows on Replies that are restricted/dripped (though I doubt any would be
 * dripped).
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_bbpress_addon_bbp_get_content( $content, $id ) {
	if ( it_exchange_membership_addon_is_content_restricted() )
		return it_exchange_membership_addon_content_restricted_template();
	if ( it_exchange_membership_addon_is_content_dripped() )
		return it_exchange_membership_addon_content_dripped_template();
	return $content;
}
//add_filter( 'bbp_get_forum_content', 'it_exchange_membership_bbpress_addon_bbp_get_content', 10, 2 );
//add_filter( 'bbp_get_topic_content', 'it_exchange_membership_bbpress_addon_bbp_get_content', 10, 2 );
add_filter( 'bbp_get_reply_content', 'it_exchange_membership_bbpress_addon_bbp_get_content', 10, 2 );

/**
 * Display Restricted or Dripped message.
 *
 * Filters single-forum and single-topic content templates.
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_bbpress_get_template_part( $templates, $slug, $name ) {
	if ( 'content' === $slug && ( 'single-forum' === $name || 'single-topic' == $name ) ) {
		if ( it_exchange_membership_addon_is_content_restricted() )
			array_unshift( $templates, 'content-restricted.php' );
		if ( it_exchange_membership_addon_is_content_dripped() )
			array_unshift( $templates, 'content-dripped.php' );
	}
	return $templates;
}
add_filter( 'bbp_get_template_part', 'it_exchange_membership_bbpress_get_template_part', 10, 3 );

/**
 * Adds our templates directory to the bbPress template stack
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_bbpress_get_template_stack( $stack ) {
	array_unshift( $stack, dirname( __FILE__ ) . '/templates' );
	return $stack;
}
add_filter( 'bbp_get_template_stack', 'it_exchange_membership_bbpress_get_template_stack', 10 );

/**
 * This adds the bbPress restricted content to the Membership Dashboard
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_bbpress_addon_membership_content_restricted_posts( $restricted_posts, $selection, $selected, $value ) {

	if ( empty( $restricted_posts ) ) {
	
		//We need to do this because bbPress sets exclude_from_search for their post types.
	
		switch ( $selection ) {
			
			case 'forum':
				$args = array(
					'post_type' => apply_filters( 'bbp_forum_post_type', 'forum' ),
					'p'   => $value,
				);
				$restricted_posts = get_posts( $args );
				break;
			case 'topic':
				$args = array(
					'post_type' => apply_filters( 'bbp_topic_post_type', 'topic' ),
					'p'   => $value,
				);
				$restricted_posts = get_posts( $args );
				break;
			case 'reply':
				$args = array(
					'post_type' => apply_filters( 'bbp_reply_post_type', 'reply' ),
					'p'   => $value,
				);
				$restricted_posts = get_posts( $args );
				break;
			
		}
		
	}

	return $restricted_posts;
	
}
add_filter( 'it_exchange_membership_addon_membership_content_restricted_posts', 'it_exchange_membership_bbpress_addon_membership_content_restricted_posts', 10, 4 );