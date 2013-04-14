<?php
/*
Plugin Name: Stack Overflow Activity Feed Widget
Plugin URI: http://arjunabhynav.com/stackfeed
Description: This plugins shows your Stack Overflow activity feed as a widget in your WordPress site
Author: Arjun Abhynav
Version: 1.0.0
Author URI: http://www.arjunabhynav.com
License: GPL2
*/

/*  Copyright 2013 Arjun Abhynav (email: arjun.abhynav@gmail.com, twitter: @arjunabhynav)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( ABSPATH . WPINC . '/feed.php' );


/** Widget Class */

class StackOverflowActivityWidget extends WP_Widget
{
	// Constructor
	public function __construct()
	{
		// Parent WP_Widget Constructor
		parent::WP_Widget(
			'stackoverflowactivitywidget',
			'Stack Overflow Activity Feed',
			array( 'description' => __( 'Display StackOverflow user activity feed', 'stackoverflowactivitywidget' ) )
		);
	}

	/** @see WP_Widget::form
	  * Used to take field inputs from the user for the widget
	  * Takes widget title, user's Stack Oveflow ID, and number of items in the feed */
	  
	function form( $instance )
	{
		$title = esc_attr( $instance['title'] );
		if ( empty( $title ) )
			$title = __( 'Stack Overflow Activity Feed', 'stackoverflowactivitywidget' );

		$userID = esc_attr( $instance['userID'] );
		if ( empty( $userID ) )
			$userID = __( 'xxxxxx', 'stackoverflowactivitywidget' );

		$maxFeedItems = $instance['maxFeedItems'];
		if ( !isset( $maxFeedItems ) || $maxFeedItems <= 0 )
			$maxFeedItems = 5;
		else if ( $maxFeedItems > 30)
			$maxFeedItems = 30;
			
		// Form input elements for the widget configuration
?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title:', 'stackoverflowactivitywidget' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>

		
		<p>
			<label for="<?php echo( $this->get_field_id( 'userID' ) ); ?>">
				<?php _e( 'Stack Overflow User ID:', 'stackoverflowactivitywidget' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'userID' ); ?>" name="<?php echo $this->get_field_name( 'userID' ); ?>" type="text" value="<?php echo $userID; ?>" />
			</label>
		</p>

		
		<p>
			<label for="<?php echo( $this->get_field_id( 'maxFeedItems' ) ); ?>">
				<?php _e( 'Number of items in feed:', 'stackoverflowactivitywidget' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'maxFeedItems' ); ?>" name="<?php echo $this->get_field_name( 'maxFeedItems' ); ?>" type="text" value="<?php echo $maxFeedItems; ?>" />
			</label>
		</p>
		
<?php

	}
	
	/** @see WP_Widget::update 
	  * Used to process the form on saving after the parameters are input */
	  
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['userID'] = strip_tags( $new_instance['userID'] );
		$instance['maxFeedItems'] = $new_instance['maxFeedItems'];
		
		// The number of feed items can vary between 1 to 30
		if ( !is_numeric( $instance['maxFeedItems'] ) || $instance['maxFeedItems'] <= 0 )
			$instance['maxFeedItems'] = 5;
		else if ( $instance['maxFeedItems'] > 30 )
			$instance['maxFeedItems'] = 30;

		return $instance;
	}
	
	/** @see WP_Widget::widget 
	  * Outputs the content of the widget
	  * Fetches items from the user feed and displays in order 
	  * Uses builtin SimplePie WP feed fetcher */
	  
	function widget( $args, $instance )
	{
		extract( $args );

		echo $before_widget;

		// Get title of the widget
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_title . $title . $after_title;

		// Get the user ID
		$userID = $instance['userID'];
		$userFeed = 'http://stackoverflow.com/feeds/user/' . $userID;
		
		// Get maximum number of items to be displayed
		$maxFeedItems = $instance['maxFeedItems'];
		if ( !isset( $maxFeedItems ) || $maxFeedItems <= 0 )
			$maxFeedItems = 5;
		else if ( $maxFeedItems > 30)
			$maxFeedItems = 30;
	
		if ( !empty( $userID ) )
		{
			// Fetch the feed for the user's activity
			$feed = fetch_feed( $userFeed );
			if ( !is_wp_error( $feed ) )
			{
				// Calculate number of items present in the feed retrieved
				$maxItems = $feed->get_item_quantity( $maxFeedItems );
				if ( $maxItems > 0 )
				{
					$feedItems = $feed->get_items( 0, $maxItems );
				}
			}
			else
			{
				// An error has been encountered with retrieving the feed
				echo $feed->get_error_message();
			}
		}

		// If at least one item is there to display
		if ( !empty( $feedItems ) )
		{
			echo '<ul>';
			$displayedItemsCount = 0;
			foreach ( $feedItems as $item )
			{
				// Displaying the activity feed item
				echo '<li> <a title="'.date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ).'" href="'.$item->get_permalink().'">';
				echo $item->get_title();
				echo '</a></li>';
				$displayedItemsCount++;
				//Keeping check on number of items
				if ( $displayedItemsCount > $maxFeedItems )
				{
					break;
				}
			}
			echo '</ul>';
		}

		echo $after_widget;
	}
	
}

// Function to register the widget
function StackOverflowActivityWidget_Register()
{
	return register_widget( 'StackOverflowActivityWidget' );
}

// Invoking the widget registration
add_action( 'widgets_init', 'StackOverflowActivityWidget_Register');
?>