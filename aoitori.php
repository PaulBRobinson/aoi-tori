<?php
/*
Plugin Name: Aoi Tori - Twitter Plugin
Plugin URI: http://return-true.com/
Description: A simple Twitter plugin designed to show a users Tweets. Includes built-in templates, custom templates, color options...
Version: 1.0.2
Author: Paul Robinson
Author URI: http://return-true.com
Text Domain: aoitori
Domain Path: /languages

	Copyright (c) 2016 Paul Robinson (http://return-true.com)
	Aoi Tori is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl.txt

	This is a WordPress plugin (http://wordpress.org). This plugin is the successor to Twitter Stream which I created a long while ago.

  NOTE: Titan Framework auto-prefixes the option names with the registered plugin name, that is why none of the Titan options have been prefixed
        Options that do not use Titan and are saved directly to WordPress are prefixed with the plugin name.
*/

/**
 * Nope, nope, nope, nope, nope. You can't directly access this file.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Require Composer Autoload & Titan Framework
 */
require_once 'vendor/autoload.php';
require_once 'lib/titan-framework/titan-framework-embedder.php';

/**
 * The Meat & Potatoes... Boil 'em, Mash 'em, Stick 'em in a stew.
 */
Class AoiTori {

  /**
   * Plugin version number
   * @var String
   */
  private $pluginVersion;

  /**
   * Holds the plugin name
   * @var String
   */
  private $pluginName;

  /**
   * Holds the plugin path
   * @var String
   */
  private $pluginPath;

  /**
   * Holds the plugin URL
   * @var String
   */
  private $pluginUrl;

  /**
   * Holds the base URL for the Twitter API
   * @var String
   */
  private $apiUrl;

  /**
   * Holds the tokens for access to Twitter's API
   * @var Array
   */
  private $tokens;

  /**
   * Sets private variables adds actions to WordPress
   */
  public function __construct() {

    //Prevent errors on theme activation by cancelling loading of the plugin. Prevents declaration of the same class twice.
    if ( is_admin() ) {
      if ( ! empty( $_GET['action'] ) && ! empty( $_GET['plugin'] ) ) {
          if ( $_GET['action'] == 'activate' ) {
              return;
          }
      }
    }

    //Assign Variables
    $this->pluginVersion = '1.0.1';
    $this->pluginName = 'aoitori';
    $this->pluginPath = plugin_dir_path( __FILE__ );
    $this->pluginUrl = plugin_dir_url( __FILE__ );
    $this->apiUrl = 'https://api.twitter.com/1.1/';

    //Add text domain for translations
    add_action('plugins_loaded', array($this, 'loadPluginTextdomain'));

    //Add check to see if major version update. Also triggered on activation.
    add_action('plugins_loaded', array($this, 'pluginUpdateCheck'));

    //Add JS to Admin
    add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));

    //Add CSS to front end
    add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));

    //Add Ajax Methods
    add_action('wp_ajax_check_tokens_are_valid', array($this, 'checkTokensAreValidAjax'));

    //Add switch to enable token validity check on options save
    add_action('tf_admin_options_saved_' . $this->pluginName, array($this, 'setCheckTokensAreValid'));

    //Put access tokens into a class accessable array.
    //Must be done via hook as Titan is not available until after_setup_theme hook
    add_action('init', array($this, 'populateTokens'));

    //Register our welcome page
    //Do not run on activation as loading of the welcome screen is handled by pluginUpdateCheck
    add_action('admin_init', array($this, 'welcomeScreenDoRedirect'));
    add_action('admin_menu', array($this, 'welcomeScreenPages'));
    add_action('admin_head', array($this, 'welcomeRemoveMenu'));

    //Uninstall is performed by the uninstall.php file in the plugin root

    //Attach Class methods to actions/filters
    add_action( 'tf_create_options', array( $this, 'createOptions' ) );

    //Add widget
    add_action('widgets_init', array($this, 'registerWidget'));

  }

  public function registerWidget() {
    // Widget class is autoloaded via Composer
    if(class_exists('AoiToriWidget'))
      register_widget('AoiToriWidget');
  }

  public function loadPluginTextdomain() {
    load_plugin_textdomain( 'aoitori', false,  basename(dirname( __FILE__ )) . '/languages/');
  }

  public function pluginUpdateCheck() {

    //Get current stored version number
    $instVersion = get_option('aoitori_version');

    //If there is no version number, set the version + load the welcome screen
    if(!$instVersion) {
      update_option('aoitori_version', $this->pluginVersion);
      $this->welcomeScreenActivate();
      return;
    }

    //Check to see if the version is the same
    if(version_compare($this->pluginVersion, $instVersion, '==')) {
      //Bail
      return;
    }

    //If there has been a major version update we will show the welcome screen
    //Casting the versions from string to integer converts them to whole integers instead of floats saves using explode
    if((int) $this->pluginVersion > (int) $instVersion)
      $this->welcomeScreenActivate();

    //Update the version number regardless of what happened (for minor version updates)
    update_option('aoitori_version', $this->pluginVersion);

  }

  /**
   * Enqueue Admin scripts
   * @return Void
   */
  public function adminEnqueueScripts() {
    wp_enqueue_script('aoitori-ajax', $this->pluginUrl . 'js/aoitori.ajax.js', array('jquery'), '1.0', true);
    wp_localize_script('aoitori-ajax', 'AoiToriAjax', array(
      'check_tokens' => get_option('aoitori_need_check_token_valid', '0'),
      'valid_tokens' => __('Tokens Valid', 'aoitori'),
      'invalid_tokens' => __('Tokens Invalid', 'aoitori'),
      'no_check' => __('No Token Check Needed', 'aoitori'),
      'nonce' => wp_create_nonce($this->pluginName)
    ));
  }

  /**
   * Enqueue CSS files for plugin to front-end
   * @return Void
   */
  public function enqueueStyles() {
    wp_enqueue_style( 'aoitori-layout-styles', $this->pluginUrl . 'css/aoitori-layout.css', null, '3.0');
  }

  /**
   * Set transient to force redirect to Welcome screen on activation
   * @return Void
   */
  public function welcomeScreenActivate() {
    //Set a temporary option to tell welcome screen to activate.
    set_transient('_aoitori_welcome_screen_redirect_', true, 30);
  }

  /**
   * Do actual redirect to Welcome screen on activation
   * @return Void
   */
  public function welcomeScreenDoRedirect() {
      if( ! get_transient('_aoitori_welcome_screen_redirect_') )
        return;

      //Still here? Then we should redirect

      //First get rid of our transient so we don't end up in neverending loop land
      delete_transient('_aoitori_welcome_screen_redirect_');

      //If we are activating network wide on multisite. Bail.
      if( is_network_admin() || isset($_GET['activate-multi']) )
        return;

      //Redirect to welcome page
      wp_safe_redirect( add_query_arg( array( 'page' => 'aoitori-welcome-screen'), admin_url('index.php') ) );
  }

  /**
   * Register welcome page on menu so it is accessable
   * @return Void
   */
  public function welcomeScreenPages() {
    //Register out welcome page
    add_dashboard_page(
      __('Aoi Tori is Installed', 'aoitori'),
      __('Aoi Tori is Installed', 'aoitori'),
      'read',
      'aoitori-welcome-screen',
      array($this, 'WelcomeScreenContent')
    );
  }

  /**
   * Output actual Welcome page content
   * @return Void
   */
  public function welcomeScreenContent() {
    //The welcome page content Abstract to an HTML template later
  ?>
      <div class="wrap about-wrap">
        <h1><?php _e('Aoi Tori Installed', 'aoitori'); ?></h1>
        <div class="about-text">
          <?php _e('Thank you for choosing Aoi Tori', 'aoitori'); ?><br>
          <?php _e('A Twitter plugin designed to be simple &amp; easy to use for everyone.', 'aoitori'); ?>
        </div>

        <hr>

        <div class="feature-section two-col">

          <div class="col">
            <div class="media-container">
              <img src="<?php echo $this->pluginUrl; ?>images/tsp-about-styles-min.jpg">
            </div>
            <h3><?php _e('Easily Customizable', 'aoitori'); ?></h3>
            <p><?php _e('Easily change the color of hashtags, @replies, and URLs. Right from the options page without touching any code at all.', 'aoitori'); ?></p>
          </div>

          <div class="col">
            <div class="media-container">
              <img src="<?php echo $this->pluginUrl; ?>images/tsp-about-keys-min.jpg">
            </div>
            <h3><?php _e('Simple Connection', 'aoitori'); ?></h3>
            <p><?php _e('Create a Twitter Application, enter 4 keys, and you are done. No need to bounce back and forth between Twitter for authorization.', 'aoitori'); ?></p>
          </div>

          <div class="col">
            <div class="media-container">
              <img src="<?php echo $this->pluginUrl; ?>images/tsp-about-template-min.jpg">
            </div>
            <h3><?php _e('Built-in or Custom Templates', 'aoitori'); ?></h3>
            <p><?php _e('Choose from 3 commonly used layouts to display your Tweets. Don\'t like any of those? You can roll your own template with the built-in editor.', 'aoitori'); ?></p>
          </div>

          <div class="col">
            <div class="media-container">
              <img src="<?php echo $this->pluginUrl; ?>images/tsp-about-advanced-min.jpg">
            </div>
            <h3><?php _e('Advanced Features', 'aoitori'); ?></h3>
            <p><?php _e('Change margins &amp; paddings or add your own custom CSS. You can even use SCSS syntax.', 'aoitori'); ?></p>
          </div>

        </div>

        <!-- This is for changes after updates
        <div class="changelog">
          <div class="feature-section under-the-hood three-col">
            <h3><?php _e('Updates', 'aoitori'); ?></h3>
            <div class="col">
              <h4><?php _e('Something Cool', 'aoitori'); ?></h4>
              <p><?php _e('This will have been updated. Awesome.', 'aoitori'); ?></p>
            </div>
            <div class="col">
              <h4><?php _e('Something Cooler', 'aoitori'); ?></h4>
              <p><?php _e('This will also have been updated. Super Cool.', 'aoitori'); ?></p>
            </div>
            <div class="col">
              <h4><?php _e('This Too?', 'aoitori'); ?></h4>
              <p><?php _e('This is all awesome stuff.', 'aoitori'); ?></p>
            </div>
          </div>
        </div> -->

        <div class="return-to-dashboard">
          <?php
          printf(
            __(
              '<a href="%s">Go to Settings &rarr; Aoi Tori Options</a>', 'aoitori'
            ),
            esc_url( admin_url( 'options-general.php?page=options-general.php-aoi-tori-options' ) )
          );
          ?>
        </div>

      </div>
  <?php
  }

  /**
   * Remove welcome page from menu so it is not normally accessable - Cancel out to reveal page for debugging
   * @return Void
   */
  public function welcomeRemoveMenu() {
    remove_submenu_page('index.php', 'aoitori-welcome-screen');
  }

  /**
   * Create options page using Titan
   * @return Void
   */
  public function createOptions() {

    //Bring out the Titan
    $titan = TitanFramework::getInstance( $this->pluginName );

    //Create a Options panel
    $panel = $titan->createAdminPanel( array(
      'name' => __('Aoi Tori Options', 'aoitori'),
      'parent' => 'options-general.php'
    ) );

    $instructions = $panel->createTab( array(
      'name' => __('Instructions', 'aoitori'),
    ) );

    $instructions->createOption( array(
      'type' => 'twitter-how-to',
    ) );

    $keys = $panel->createTab( array(
      'name' => __('API Keys', 'aoitori'),
    ) );

    //Create Options
    $keys->createOption( array(
      'name' => __('API Keys', 'aoitori'),
      'type' => 'heading',
    ) );

    $keys->createOption( array(
      'name' => __('Consumer Key', 'aoitori'),
      'id' => 'consumer_key',
      'type' => 'text',
      'desc' => __('Your Consumer Key', 'aoitori'),
    ) );

    $keys->createOption( array(
      'name' => __('Consumer Secret', 'aoitori'),
      'id' => 'consumer_secret',
      'type' => 'text',
      'desc' => __('Your Consumer Secret', 'aoitori'),
    ) );

    $keys->createOption( array(
      'name' => __('oAuth Access Token', 'aoitori'),
      'id' => 'oauth_access_token',
      'type' => 'text',
      'desc' => __('Your oAuth Access Token', 'aoitori'),
    ) );

    $keys->createOption( array(
      'name' => __('oAuth Access Token Secret', 'aoitori'),
      'id' => 'oauth_access_token_secret',
      'type' => 'text',
      'desc' => __('Your oAuth Access Token Secret', 'aoitori'),
    ) );

    $keys->createOption( array(
      'type' => 'twitter-tokens-valid',
      'name' => __('Token Validity Check', 'aoitori'),
    ) );

    //Create save button
    $keys->createOption( array(
      'type' => 'save',
    ) );

    $template = $panel->createTab( array(
      'name' => __('Template', 'aoitori'),
    ) );

    $template->createOption( array(
      'name' => __('Built-In Template Selection', 'aoitori'),
      'type' => 'heading',
    ) );

    $template->createOption( array(
      'name' => __('Tweet Template', 'aoitori'),
      'id' => 'template',
      'type' => 'radio-image',
      'options' => array(
        'list' => $this->pluginUrl . 'images/list-layout.png',
        'paragraph' => $this->pluginUrl . 'images/paragraph-layout.png',
        'media' => $this->pluginUrl . 'images/media-layout.png',
      ),
      'default' => 'list',
      'desc' => __('You can use a built-in template to display your tweets. Please choose one here or if you would prefer to use a custom template you can refer to the option below.', 'aoitori'),
    ) );

    $template->createOption( array(
      'name' => __('Custom Template Options', 'aoitori'),
      'type' => 'heading',
    ) );

    $template->createOption( array(
      'name' => __('Enable Custom Template', 'aoitori'),
      'id' => 'template_code_enabled',
      'type' => 'enable',
      'default' => false,
      'desc' => __('Enable this to use a custom template below. This will override the template selected above.', 'aoitori'),
    ) );

    $template->createOption( array(
      'name' => __('Template Code', 'aoitori'),
      'id' => 'template_code',
      'type' => 'textarea',
      'desc' => __('Custom template for displaying your tweets, if the option above is not enabled changes will be saved but will have no effect. Uses Twig templating language. More information on the available varaiables is in the <a href="#">documentation</a>.', 'aoitori'),
      'is_code' => 'true',
      'default' => '
{% if tweets|length > 0 %}
  <ul>
    {% for tweet in tweets %}
      <li>{{ tweet.text|process(tweet) }}</li>
    {% endfor %}
  </ul>
{% endif %}
      '
    ) );

    $template->createOption( array(
      'type' => 'save',
    ) );

    $styles = $panel->createTab( array(
      'name' => __('Styles', 'aoitori'),
    ) );

    $styles->createOption( array(
      'name' => __('Hashtag Styles', 'aoitori'),
      'type' => 'heading',
    ) );

    $styles->createOption( array(
      'name' => __('Hashtag Color', 'aoitori'),
      'id' => 'hash_tag_color',
      'type' => 'color',
      'desc' => __('The color of any hash tags found within tweets', 'aoitori'),
      'default' => '#234332',
      'css' => '.aoitori_hash_tag { color: value; }',
    ) );

    $styles->createOption( array(
      'name' => __('Hashtag Hover Color', 'aoitori'),
      'id' => 'hash_tag_hover_color',
      'type' => 'color',
      'desc' => __('The color of any hash tags found within tweets when hovered', 'aoitori'),
      'default' => '#234332',
      'css' => '.aoitori_hash_tag:hover { color: value; }',
    ) );

    $styles->createOption( array(
      'name' => __('@reply Styles', 'aoitori'),
      'type' => 'heading',
    ) );

    $styles->createOption( array(
      'name' => __('@reply Color', 'aoitori'),
      'id' => 'atreply_color',
      'type' => 'color',
      'desc' => __('The color of any @reply found within tweets', 'aoitori'),
      'default' => '#234332',
      'css' => '.aoitori_atreply { color: value; }',
    ) );

    $styles->createOption( array(
      'name' => __('@reply Hover Color', 'aoitori'),
      'id' => 'atreply_hover_color',
      'type' => 'color',
      'desc' => __('The color of any @reply found within tweets when hovered', 'aoitori'),
      'default' => '#234332',
      'css' => '.aoitori_atreply:hover { color: value; }',
    ) );

    $styles->createOption( array(
      'name' => __('Hyperlink (URL) Styles', 'aoitori'),
      'type' => 'heading',
    ) );

    $styles->createOption( array(
      'name' => __('Hyperlink (URL) Color', 'aoitori'),
      'id' => 'url_color',
      'type' => 'color',
      'desc' => __('The color of any hyperlink (URL) found within tweets', 'aoitori'),
      'default' => '#234332',
      'css' => '.aoitori_url { color: value; }',
    ) );

    $styles->createOption( array(
      'name' => __('Hyperlink (URL) Hover Color', 'aoitori'),
      'id' => 'url_hover_color',
      'type' => 'color',
      'desc' => __('The color of any hyperlink (URL) found within tweets when hovered', 'aoitori'),
      'default' => '#234332',
      'css' => '.aoitori_url:hover { color: value; }',
    ) );

    $styles->createOption( array(
      'type' => 'save',
    ) );

    $advanced = $panel->createTab( array(
      'name' => __('Advanced CSS', 'aoitori'),
    ) );

    $advanced->createOption( array(
      'name' => __('About Margins &amp; Paddings', 'aoitori'),
      'type' => 'note',
      'notification' => false,
      'desc' => __('Please note that the margin &amp; padding settings are only guaranteed to apply when using one of the built-in templates. If you use a custom template and do not apply the same classes as the built-in template\'s the margins/paddings defined here will not apply. In this case please use the custom CSS option below to add your own styling to your custom template.', 'aoitori'),
    ) );

    $advanced->createOption( array(
      'name' => __('Tweets Container', 'aoitori'),
      'type' => 'heading'
    ) );

    $advanced->createOption( array(
      'name' => __('Margin', 'aoitori'),
      'id' => 'tweets_container_margin',
      'type' => 'text',
      'desc' => __('The margin applied to the containing element for all tweets. Accepts any valid value for the CSS property margin, for example: 5px 3px 5px 3px', 'aoitori'),
      'default' => 'inherit',
      'css' => '.aoitori_tweets { margin: value; }'
    ) );

    $advanced->createOption( array(
      'name' => __('Padding', 'aoitori'),
      'id' => 'tweets_container_padding',
      'type' => 'text',
      'desc' => __('The padding applied to the containing element for all tweets. Accepts any valid value for the CSS property padding, for example: 5px 3px 5px 3px', 'aoitori'),
      'default' => 'inherit',
      'css' => '.aoitori_tweets { padding: value; }'
    ) );

    $advanced->createOption( array(
      'name' => __('Tweet Container', 'aoitori'),
      'type' => 'heading'
    ) );

    $advanced->createOption( array(
      'name' => __('Margin', 'aoitori'),
      'id' => 'tweet_container_margin',
      'type' => 'text',
      'desc' => __('The margin applied to each element containing a tweet. Accepts any valid value for the CSS property margin, for example: 5px 3px 5px 3px', 'aoitori'),
      'default' => 'inherit',
      'css' => '.aoitori_tweets .aoitori_tweet { margin: value; }'
    ) );

    $advanced->createOption( array(
      'name' => __('Padding', 'aoitori'),
      'id' => 'tweet_container_padding',
      'type' => 'text',
      'desc' => __('The padding applied to each element containing a tweet. Accepts any valid value for the CSS property padding, for example: 5px 3px 5px 3px', 'aoitori'),
      'default' => 'inherit',
      'css' => '.aoitori_tweets .aoitori_tweet { padding: value; }'
    ) );

    $advanced->createOption( array(
      'name' => __('For Advanced Users', 'aoitori'),
      'type' => 'heading'
    ) );

    $advanced->createOption( array(
      'name' => __('Custom CSS', 'aoitori'),
      'id' => 'user_custom_css',
      'type' => 'code',
      'lang' => 'css',
      'theme' => 'github',
      'desc' => __('If you are familiar with CSS you can add any custom code here. The field can even compile/parse SCSS if you wish to use that. <strong>N.B.</strong> If you choose to use SCSS you must use the SCSS syntax. The Sass syntax is not supported.', 'aoitori'),
    ) );

    $advanced->createOption( array(
      'type' => 'save',
    ) );

    $faq = $panel->createTab( array(
      'name' => __('FAQ', 'aoitori'),
    ) );

    $faq->createOption( array(
      'type' => 'twitter-how-to',
      'faq' => true,
      'how-to' => false,
    ) );

  }

  /**
   * Populate the $this->tokens property with the access tokens - done via hook as Titan is not available until after_setup_theme
   * @return Void
   */
  public function populateTokens() {

    $titan = TitanFramework::getInstance( $this->pluginName );

    $this->tokens = array(
      'oauth_access_token' => $titan->getOption('oauth_access_token'),
      'oauth_access_token_secret' => $titan->getOption('oauth_access_token_secret'),
      'consumer_key' => $titan->getOption('consumer_key'),
      'consumer_secret' => $titan->getOption('consumer_secret'),
    );

  }

  /**
   * Updates token validation check option
   * @return Void
   */
  public function setCheckTokensAreValid() {
    update_option('aoitori_need_check_token_valid', true);
  }

  /**
   * Actually check to see if tokens are valid after save
   * @return Void
   */
  public function checkTokensAreValidAjax() {

    check_ajax_referer($this->pluginName, 'security', true);

    $url = $this->apiUrl . 'account/verify_credentials.json';
    $requestMethod = 'GET';

    if(empty($this->tokens)) {
      //If tokens are empty we don't need to check validity anymore
      update_option('aoitori_need_check_token_valid', '0');
      die('false');
    }

    $twitter = new TwitterAPIExchange($this->tokens);

    $data = json_decode(
      $twitter
      ->buildOauth($url, $requestMethod)
      ->performRequest()
    );

    if(isset($data->id)) {
      update_option('aoitori_need_check_token_valid', '0');
      die('true');
    }

    die('false');

  }

  /**
   * Output stream, referenced via a external global function for ease of use - See end of file for said function
   * @param  array   $args Array of options
   * @param  boolean $echo Should we echo or return
   * @return String  Tweets
   */
  public function outputStream($args = array(), $echo = true) {

    $defaults = array(
      'name' => '',
      'screen_name' => '',
      'count' => 10,
      'exclude_replies' => false,
      'include_rts' => false,
      'cache_time' => 30 * MINUTE_IN_SECONDS,
    );

    $args = array_merge($defaults, $args);

    //Name is used to make each call of the function unique. If we don't have a name, die.
    if(empty($args['name']))
      return __('Cannot continue without a name defined', 'aoitori');

    //If we don't got tokens... We ain't got the poower Capin!
    if(empty($this->tokens))
      return __('Tokens Empty or Invalid', 'aoitori');

    //Do we already have cached data?
    $data = get_transient( 'aoitori_cached_data_' . $args['name'] );

    if(!$data) {

      //Great we got tokens. Engage, Number One!
      $url = $this->apiUrl . 'statuses/user_timeline.json';
      $getField = '?'.http_build_query($args);
      $requestMethod = 'GET';

      $twitter = new TwitterAPIExchange($this->tokens);

      $data = json_decode(
        $twitter
        ->setGetfield($getField)
        ->buildOauth($url, $requestMethod)
        ->performRequest()
      );

      //We got fresh data, cache it. So we Staaay Fresh!
      set_transient( 'aoitori_cached_data_' . $args['name'], $data, $args['cache_time'] );

    }

    if($data) {
        return $this->processTweets($data, $echo);
    }

  }

  /**
   * Processes the tweets into a Twig template or outputs raw data if wanted
   * @param  JSON   $rawData JSON output from Twitter API
   * @param  bool   $echo    Echo or Return
   * @return String/JSON     Template string or JSON depending upon chosen options
   */
  public function processTweets($rawData, $echo) {

    $titan = TitanFramework::getInstance( $this->pluginName );

    //Get default template
    $template = $titan->getOption('template');

    //If the custom code option is enabled use that instead of our default Templates
    if($titan->getOption('template_code_enabled')) {
      $loader = new Twig_Loader_Array(array(
        'standard' => $titan->getOption('template_code'),
      ));
      //If custom code is used push it back to standard to use the Array Loader
      $template = 'standard';
    } else {
      $loader = new Twig_Loader_Filesystem($this->pluginPath . 'templates');
    }

    $twig = new Twig_Environment($loader, array(
      //'cache' => $this->pluginPath . 'templates/cache'
    ));

    //Add custom filters. Look in lib/twig-filters for actual code
    $process = new Twig_SimpleFilter('process', array('CustomTwigFilters', 'process'), array('is_safe' => array('html')));
    $twig->addFilter($process);

    //If echo is true return Twig render. If not return raw data.
    if($echo)
      return $twig->render($template, array('tweets' => $rawData));
    else
      return $rawData;

  }

  public function purgeCache($name) {
    if(get_transient( 'aoitori_cached_data_' . $name )) {
      delete_transient( 'aoitori_cached_data_' . $name );
      return true;
    }

    return false;
  }

}


/**
 * Instance the plugin
 */
$aoiTori = new AoiTori();

/**
 * Create a function that can be easily accessed in themes without needing to globalize variables.
 * @param  array   $args
 * @param  boolean $echo
 * @echo/return String of tweets
 */
function aoiToriOutput($args = array(), $echo = true) {
  global $aoiTori;

  if($echo)
    echo $aoiTori->outputStream($args, $echo);
  else
    return $aoiTori->outputStream($args, $echo);

}
