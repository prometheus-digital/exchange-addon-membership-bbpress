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
					'post_type'   => apply_filters( 'bbp_forum_post_type', 'forum' ),
					'p'           => $value,
				);
				$restricted_posts = get_posts( $args );
				break;
			case 'topic':
				$args = array(
					'post_type'   => apply_filters( 'bbp_topic_post_type', 'topic' ),
					'p'           => $value,
				);
				$restricted_posts = get_posts( $args );
				break;
			case 'reply':
				$args = array(
					'post_type'  => apply_filters( 'bbp_reply_post_type', 'reply' ),
					'p'           => $value,
				);
				$restricted_posts = get_posts( $args );
				break;
			
		}
		
	}

	return $restricted_posts;
	
}
add_filter( 'it_exchange_membership_addon_membership_content_restricted_posts', 'it_exchange_membership_bbpress_addon_membership_content_restricted_posts', 10, 4 );

/**
 * Creates sessions data with logged in customer's membership access rules
 *
 * @since 1.0.0
 * @param int $post_id WordPress Post ID
 * @return void
*/
function it_exchange_membership_bbpress_trash_bbpress_content( $post_id ){
	$post_type = get_post_type( $post_id );
	
	switch ( $post_type ) {
		case apply_filters( 'bbp_forum_post_type', 'forum' ):
		case apply_filters( 'bbp_topic_post_type', 'topic' ):
		case apply_filters( 'bbp_reply_post_type', 'reply' ):
			$rules = get_post_meta( $post_id, '_item-content-rule', true );
			foreach( $rules as $product_id ) {
				$changed = false;
				$access_meta = get_post_meta( $product_id, '_it-exchange-membership-addon-content-access-meta', true );
				foreach( $access_meta as $key => $meta ) {
					if ( $post_type == $meta['selection'] && $post_id == $meta['term'] ) {
						unset( $access_meta[$key] );
						$changed = true;
					}
				}
				if ( $changed ) {
					update_post_meta( $product_id, '_it-exchange-membership-addon-content-access-meta', $access_meta );
				}
			}
			delete_post_meta( $post_id, '_item-content-rule' );
			break;
	}
}
add_action( 'wp_trash_post', 'it_exchange_membership_bbpress_trash_bbpress_content' );

function it_exchange_membership_bbpress_addon_is_content_restricted( $restriction, $member_access ) {
	if ( empty( $restriction ) ) {
		global $post;
		
		if ( !empty( $post ) ) {
			$tmp_post = $post;
			
			if ( 'reply' === $tmp_post->post_type ) {
				$topic = get_post( $tmp_post->post_parent );
				$tmp_post = $topic;
				
				$restriction_exemptions = get_post_meta( $topic->ID, '_item-content-rule-exemptions', true );
				if ( !empty( $restriction_exemptions ) ) {
					foreach( $member_access as $product_id => $txn_id ) {
						if ( array_key_exists( $product_id, $restriction_exemptions ) )
							$restriction = true; //we don't want restrict yet, not until we know there aren't other memberships that still have access to this content
						else
							continue; //get out of this, we're in a membership that hasn't been exempted
					}
					if ( $restriction ) //if it has been restricted, we can return true now
						return true;
				}
				
				$post_rules = get_post_meta( $topic->ID, '_item-content-rule', true );
				if ( !empty( $post_rules ) ) {
					if ( empty( $member_access ) ) return true;
					foreach( $member_access as $product_id => $txn_id ) {
						if ( in_array( $product_id, $post_rules ) )
							return false;	
					}
					$restriction = true;
				}
			}
			
			if ( empty( $restriction ) ) {
				if ( 'topic' === $tmp_post->post_type ) {
					$forum = get_post( $tmp_post->post_parent );
						
					$restriction_exemptions = get_post_meta( $forum->ID, '_item-content-rule-exemptions', true );
					if ( !empty( $restriction_exemptions ) ) {
						foreach( $member_access as $product_id => $txn_id ) {
							if ( array_key_exists( $product_id, $restriction_exemptions ) )
								$restriction = true; //we don't want restrict yet, not until we know there aren't other memberships that still have access to this content
							else
								continue; //get out of this, we're in a membership that hasn't been exempted
						}
						if ( $restriction ) //if it has been restricted, we can return true now
							return true;
					}
					
					$post_rules = get_post_meta( $forum->ID, '_item-content-rule', true );
					if ( !empty( $post_rules ) ) {
						if ( empty( $member_access ) ) return true;
						foreach( $member_access as $product_id => $txn_id ) {
							if ( in_array( $product_id, $post_rules ) )
								return false;	
						}
						$restriction = true;
					}
				}
			}
		}
	}
	return $restriction;
}
add_filter( 'it_exchange_membership_addon_is_content_restricted', 'it_exchange_membership_bbpress_addon_is_content_restricted', 10, 2 );
