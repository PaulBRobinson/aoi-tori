<?php

class AoiToriWidget extends WP_Widget {

	function __construct() {

		parent::__construct(
			'aoitori_widget',
			__('Aoi Tori Twitter Widget', 'aoitori'),
			array(
				'description' => __('Uses a widget to display a Twitter Timeline using the options selected on the Aoi Tori options page.', 'aoitori')
			)
		);

	}

	public function widget($args, $instance) {

		global $aoiTori;

		//Auto create name for this call of aoiToriOutput function and add it io instance
		$instance['name'] = $this->id_base . '_' . $this->number;


		echo $args['before_widget'];

		if(!empty($instance['title'])) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		aoiToriOutput($instance, true);

		echo $args['after_widget'];

	}

	public function form($instance) {

		$title = !empty($instance['title']) ? $instance['title'] : __('Tweets', 'aoitori');
		$screenName = !empty($instance['screen_name']) ? $instance['screen_name'] : '';
		$count = !empty($instance['count']) ? $instance['count'] : 5;
		$replies = $instance['exclude_replies'];
		$rts = $instance['include_rts'];
		$cacheTime = !empty($instance['cache_time']) ? $instance['cache_time'] : 30;
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'aoitori'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
				<small><?php _e('<strong>Note:</strong> If you set a title it will show in addition to the template you defined.'); ?></small>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('screen_name'); ?>"><?php _e('Screen Name:', 'aoitori'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('screen_name'); ?>" name="<?php echo $this->get_field_name('screen_name'); ?>" type="text" value="<?php echo esc_attr($screenName); ?>">
				<small><?php _e('Screen name is also sometimes called a Twitter Handle.', 'aoitori'); ?></small>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number of Tweets:', 'aoitori'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="number" value="<?php echo esc_attr($count); ?>" step="1" min="1">
				<br>
				<small><?php _e('The number of tweets shown may be less than requested if either of the options below are ticked.', 'aoitori'); ?></small>
			</p>
			<p>
				<input class="checkbox" id="<?php echo $this->get_field_id('exclude_replies'); ?>" name="<?php echo $this->get_field_name('exclude_replies'); ?>" type="checkbox" <?php checked($replies, 1); ?>>
				<label for="<?php echo $this->get_field_id('exclude_replies'); ?>"><?php _e('Exclude Replies', 'aoitori'); ?></label>
			</p>
			<p>
				<input class="checkbox" id="<?php echo $this->get_field_id('include_rts'); ?>" name="<?php echo $this->get_field_name('include_rts'); ?>" type="checkbox" <?php checked($rts, 1); ?>>
				<label for="<?php echo $this->get_field_id('include_rts'); ?>"><?php _e('Include Retweets', 'aoitori'); ?></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('cache_time'); ?>"><?php _e('Cache Time (minutes):', 'aoitori'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('cache_time'); ?>" name="<?php echo $this->get_field_name('cache_time'); ?>" type="number" value="<?php echo esc_attr($cacheTime); ?>" step="5" min="15">
				<br>
				<small><?php _e('How long should the data from Twitter be cached for. Lower will update your tweets more often, higher will make less calls to Twitter.', 'aoitori'); ?></small>
		<?php
	}

	public function update($new_instance, $old_instance) {

		global $aoiTori;

		//On update we need to clear the cache of the plugin or changes to these settings will not take effect.
		$aoiTori->purgeCache($this->id_base . '_' . $this->number);

		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['screen_name'] = (!empty($new_instance['screen_name'])) ? strip_tags($new_instance['screen_name']) : '';
		$instance['count'] = (!empty($new_instance['count'])) ? intval($new_instance['count']) : 5;
		$instance['exclude_replies'] = (!isset($new_instance['exclude_replies'])) ? false : true;
		$instance['include_rts'] = (!isset($new_instance['include_rts'])) ? false : true;
		$instance['cache_time'] = (!empty($new_instance['cache_time'])) ? intval($new_instance['cache_time']) : 30;

		return $instance;
	}

}
