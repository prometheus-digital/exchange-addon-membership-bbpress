<?php
/**
 * iThemes Exchange Membership bbPress Add-on
 * @package exchange-addon-membership-bbpress
 * @since 1.0.0
*/

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