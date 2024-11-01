<?php

/** WP8tracks! Widgets
 */
  
class wp8tracks_widget extends WP_Widget {

  function __construct() {
		$widget_ops = array( 'classname' => '8tracks Radio', 'description' => __('Add 8tracks Mix to your sidebar') ); 
		$control_ops = array('id_base' => 'wp8tracks-widget');
		parent::__construct('wp8tracks-widget', __('8tracks Radio'), $widget_ops, $control_ops);
  }

  function widget($args, $instance) {
    extract($args);
    $postid = get_the_ID();

    $show = false;
	
    $title    	= apply_filters('widget_title',$instance['title']);
	$mix_url	= trim($instance['wp8tracks_mix_url']);
	$height		= trim($instance['wp8tracks_height']);
	$width		= trim($instance['wp8tracks_width']);
	$playops	= trim($instance['wp8tracks_playops']);
	$urlops		= trim($instance['wp8tracks_urlops']);
	
	$show = TRUE;
	$show_mix = 'default';
	
	if (is_single() || is_page()) {
		$cf = get_post_meta(get_the_ID(), 'wp8tracks_post_mix_url',TRUE);
		if (($cf !== '') && ($urlops == 'post-only')) {
			$show = TRUE;
			$show_mix = 'post';
			$mix_url = trim($cf);
		} else if (($cf == '') && ($urlops == 'post-only')) {
			$show = FALSE;
		} else if (($cf == '') && ($urlops == 'site-post')) {
			$show = TRUE;
			$show_mix = 'default';
		} else if (($cf !== '') && ($urlops == 'site-post')) {
			$show = TRUE;
			$show_mix = 'post';
			$mix_url = trim($cf);
		} else {
			$show = TRUE;
			$show_mix = 'default';
		}
	}

	if ($show) {
	
	    // Initializing the output code.                                                 
	    $html = '';
	
	    $html .= $before_widget;
	                      
	    if ($title) {
	      $html .= $before_title . $title . $after_title;
	    }
	  
		$url_bits = parse_url( $mix_url );
		if ( '8tracks.com' != $url_bits['host'] )
			return '';
		
		$req = wp_remote_get( esc_url($mix_url) . '.xml' .'?api_key=d7ac7187f6230c9232f2d7d08dc12e4b50d1b8d8' );
	
		if (is_wp_error($req) || $req['response']['code'] != '200')
			return '';
	
		if (!isset( $req['body']))
			return '';
	
		try {	
			$xml = new SimpleXMLElement($req['body']);	
		} catch (Exception $e) {
			return '';
		}
		
		$playops = ($playops != 'standard' ? $playops : '');
	
		$html .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
			codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0"
			height="' . intval( $height ) . '" width="' .intval( $width ) . '">
			<param name="movie" value="http://8tracks.com/mixes/' . intval($xml->mix->id) . '/player_v3/' . $playops .'"></param>
			<param name="allowscriptaccess" value="always"><param name="allowscriptaccess" value="always">
			<embed height="' . intval( $height ) . '" src="http://8tracks.com/mixes/' . intval($xml->mix->id) . '/player_v3/' . $playops . '" 
			pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" 
			allowscriptaccess="always" height="' . intval( $height ) . '" width="' . intval( $width ) . '"></embed></object>';
	
	    $html .= $after_widget;    
	    
		echo $html;
	}
	
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
 
    $instance['title']     			= strip_tags($new_instance['title']);
    $instance['wp8tracks_mix_url']  = strip_tags($new_instance['wp8tracks_mix_url']);
    $instance['wp8tracks_mixops']  = strip_tags($new_instance['wp8tracks_mixops']);
    $instance['wp8tracks_height']   = strip_tags($new_instance['wp8tracks_height']);
    $instance['wp8tracks_width']    = strip_tags($new_instance['wp8tracks_width']);
    $instance['wp8tracks_urlops']    = strip_tags($new_instance['wp8tracks_urlops']);
 
	return $instance;
  }

  function form($instance){
    $defaults = array('title' => '', 'wp8tracks_height' => '220', 'wp8tracks_width' => '260');
    $instance = wp_parse_args( (array) $instance, $defaults);
    
    $title    = strip_tags($instance['title']);
    $width = strip_tags($instance['wp8tracks_width']); 
    $height = strip_tags($instance['wp8tracks_height']); 
    $mix_url    = strip_tags($instance['wp8tracks_mix_url']);
    $urlops    = strip_tags($instance['wp8tracks_urlops']);
    $playops    = strip_tags($instance['wp8tracks_playops']);
	

    ?>
 
    <p style="margin: 5px 0 0; font-weight: bold;">Widget Title</p> 
    <input style="width:100%; margin: 0 0 5px 0;" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    <p style="margin: 10px 0 0 0; font-weight: bold; display: inline-block;">Mix url : </p> 
    <p style="margin:0 0 5px 0; font-size:11px; display: inline-block;">Default Mix URL.</p>
    <input style="width:100%; margin: 0 0 10px 0;" id="<?php echo $this->get_field_id('wp8tracks_mix_url'); ?>" name="<?php echo $this->get_field_name('wp8tracks_mix_url'); ?>" type="text" value="<?php echo $mix_url; ?>" />
	<p style="margin: 15px 0 0 0; font-weight: bold; display: inline-block;">Show Options </p>
		<select id="<?php echo $this->get_field_id('wp8tracks_urlops'); ?>" name="<?php echo $this->get_field_name('wp8tracks_urlops'); ?>">
		<option value="site-wide" <?php if ($urlops == "site-wide") echo 'selected="selected"'; ?>>Site Wide</option>
		<option value="post-only" <?php if ($urlops == "post-only") echo 'selected="selected"'; ?>>Post Only</option>
		<option value="site-post" <?php if ($urlops == "site-post") echo 'selected="selected"'; ?>>Site or Post</option>
	</select>
    <p style="margin: 5px 0 5px 0; font-size:11px; background-color: #e8e8e8;padding: 5px;border: 1px dotted #d0d0d0;"><strong>Site Wide -</strong> Default Mix for all site.<br/>
    	<strong>Post Only -</strong> Appear only in Posts with Mix.<br />
    	<strong>Site or Post -</strong> If not a Post with Mix play Default.
    </p>
    <p style="margin: 10px 0 0 0; font-weight: bold; display: inline-block;">Player Dimensions </p> 
    <p style="margin:0 0 5px 0; font-size:11px; display: inline-block;">(in pixels).</p><br />
    <p style="margin: 5px 0 0 0; font-weight: bold; display: inline-block;">Height</p> 
    <input style="width:50px; margin: 0 0 10px 0;" id="<?php echo $this->get_field_id('wp8tracks_height'); ?>" name="<?php echo $this->get_field_name('wp8tracks_height'); ?>" type="text" value="<?php echo $height; ?>" />
    <p style="margin: 5px 0 0 0; font-weight: bold; display: inline-block;">| Width</p> 
    <input style="width:50px; margin: 0 0 10px 0;" id="<?php echo $this->get_field_id('wp8tracks_width'); ?>" name="<?php echo $this->get_field_name('wp8tracks_width'); ?>" type="text" value="<?php echo $width; ?>" />
    <p style="margin: 10px 0 0 0; font-weight: bold; display: inline-block;">Playing Options </p> 
	<select id="<?php echo $this->get_field_id('wp8tracks_playops'); ?>" name="<?php echo $this->get_field_name('wp8tracks_playops'); ?>">
		<option value="standard" <?php if ($playops == "standard") echo 'selected="selected"'; ?>>Standard</option>
		<option value="shuffle" <?php if ($playops == "shuffle") echo 'selected="selected"'; ?>>Shuffle</option>
		<option value="autoplay" <?php if ($playops == "autoplay") echo 'selected="selected"'; ?>>Autoplay</option>
		<option value="shuffle+autoplay" <?php if ($playops == "shuffle+autoplay") echo 'selected="selected"'; ?>>Shuffle & Autoplay</option>
	</select>
    <?php
  }
}


add_action('widgets_init', 'wp8tracks_widgets_init');

function wp8tracks_widgets_init() {
  register_widget('wp8tracks_widget');
}
?>