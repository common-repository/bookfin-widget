<?php
/**  
* Plugin Name: BookFin eBook Widget
* Plugin URI: http://www.bookfin.de
* Description: Zeigt die neusten eBooks von bookfin.de an
* Version: 1.0
* Author: Andreas Ostermann
* Author URI: http://www.bookfin.de
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*  Installation:
*   - extract the archive, and upload the plugin directory to your wp-content/plugins/ folder
*   - activate "Bookfin Widget" in your wordpress admin panel
*   - drag and drop the widget

* Changelog:
*       2014-07-01: 1.0
*           Initial release
*/

/**
 * Add function to widgets_init that'll load our widget.
 */
add_action( 'widgets_init', 'load_bookfin' );

/**
 * Register widget.
 */
function load_bookfin() {
	register_widget( 'BookfinWidget' );
}

/**
 * Widget class.
  */
class BookfinWidget extends WP_Widget {

	function BookfinWidget() {

		/* Widget settings. */
		$widget_ops = array( 'classname' => 'bf-bookfin-widget', 'description' => __('Zeigt die neuesten eBooks von BookFin.de', 'bf-bookfin-widget') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 400, 'height' => 350, 'id_base' => 'bf-bookfin' );

		/* Create the widget. */
		$this->WP_Widget( 'bf-bookfin', __('BookFin.de eBooks', 'bf-bookfin-widget'), $widget_ops, $control_ops );
	}

	
	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$name = $instance['name'];
		$showBooks = $instance['showBooks'];
		$showFooter = isset( $instance['showFooter'] ) ? $instance['showFooter'] : false;
		$linkTitle = $instance['linkTitle'];
		$i = 0;		

		if ($linkTitle) {
			$link1 = "<a rel='nofollow' target='_blank' href='http://www.bookfin.de'>";
			$link2 = "</a>";
		}
		else {
			$link1 = "";
			$link2 = "";
		}

		/* Before widget (defined by themes). */
		echo $before_widget;
		
		/* Get plugin directory */
		$plugin_dir = str_replace( '\\', '/', dirname( __FILE__ ) );
		if( preg_match( '#(/'.PLUGINDIR.'.*)#i', $plugin_dir, $treffer ) )
		  $plugin_dir = $treffer[1];
		else  
		  $plugin_dir = '/'.PLUGINDIR;  
		
		/* Display the widget title */
		if ( $title )
			echo $before_title . "<img src='".$plugin_dir."/icon.png' />&nbsp; $link1". $title . "$link2" . $after_title;

		$xml = file_get_contents('http://www.bookfin.de/rss.xml');

		$xmlobj = new SimpleXMLElement($xml);
		
				
		echo "<ul>";		
		foreach ($xmlobj->channel->item as $item) {	
			if ($i < $showBooks) {
				echo "<li><a rel='nofollow' target='_blank' href='".$item->url."'>".$item->title."</a></li>";
                   $i++;
               }
		}
		echo "</ul>";
		
		if ( $showFooter )
			echo '<p style="font-size:xx-small; text-align:right; line-height:10px"><a rel="nofollow" href="http://www.bookfin.de" target="_blank" title="BookFin eBooks">BookFin.de<br/>eBooks online</a></p>';

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['linkTitle'] = strip_tags( $new_instance['linkTitle'] );

		$instance['showBooks'] = $new_instance['showBooks'];
		$instance['showFooter'] = $new_instance['showFooter'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Aktuelle eBooks', 'bf-bookfin-widget'), 'name' => __('John Doe', 'bf-bookfin-widget'), 'showBooks' => '5', 'linkTitle' => true, 'showFooter' => false);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('&Uuml;berschrift:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- Link the title to our blog? Checkbox-->
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['linkTitle'], on ); ?> id="<?php echo $this->get_field_id( 'linkTitle' ); ?>" name="<?php echo $this->get_field_name( 'linkTitle' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'linkTitle' ); ?>"><?php _e('&Uuml;berschrift als Link?', 'bf-bookfin-widget'); ?></label>
		</p>

	
		<!-- How many eBooks shall be shown?: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'showBooks' ); ?>"><?php _e('Wieviele eBooks sollen angezeigt werden?', 'bf-bookfin-widget'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'showBooks' ); ?>" name="<?php echo $this->get_field_name( 'showBooks' ); ?>">
				<option <?php if ( '1' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>1</option>
				<option <?php if ( '2' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>2</option>
				<option <?php if ( '3' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>3</option>
				<option <?php if ( '4' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>4</option>
				<option <?php if ( '5' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>5</option>
				<option <?php if ( '6' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>6</option>
				<option <?php if ( '7' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>7</option>
				<option <?php if ( '8' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>8</option>
				<option <?php if ( '9' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>9</option>
				<option <?php if ( '10' == $instance['showBooks'] ) echo 'selected="selected"'; ?>>10</option>
			</select>
		</p>

		<!-- Show Footer? Checkbox -->
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['showFooter'], on ); ?> id="<?php echo $this->get_field_id( 'showFooter' ); ?>" name="<?php echo $this->get_field_name( 'showFooter' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'showFooter' ); ?>"><?php _e('Widget Footer anzeigen?', 'bf-bookfin-widget'); ?></label>
		</p>

	<?php
	}
}

?>