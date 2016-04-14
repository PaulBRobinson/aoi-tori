# Useage

There are two options available to you for using Aoi Tori. For those wishing to get started straight away you can use a Widget, which can be found in the Widget screen in your WordPress admin. For those who need something more advanced there is a PHP function available which allows you to place the output where ever you'd like.

## Widget

To use the widget do the following. **Please note this will only work if your theme is Widgetized**

1. Go to Appearence → Widgets
2. Open up the Widgetized area you wish to add the widget too.
3. Look for the 'Aoi Tori Widget' in the list of widgets to the left.
4. Drag the widget over to the widgetized area.
5. Adjust the options as needed.

## PHP Function

More advanced users that are familiar with PHP & their theme can use the PHP function provided to have the tweets output anywhere you wish, not just in a widgetized area.

To use the function just use `aoiToriOutput($args, $echo)` somewhere in your theme. I advise creating a child theme if you are using a purchased or WordPress theme directory theme as any updates will wipe edits such as this. More on creating a child theme can be found in the [WordPress Codex](https://codex.wordpress.org/Child_Themes).

More information on the parameters that can be passed to the function can be found below.

**Note:** Currently the widget & PHP function use the options you have set via the options page, other than changing the options passed to Twitter it is currently not possible to override the options page settings such as colors and template used.

### PHP Function Usage

If you wish to use the PHP function there are a few things to know. The basic usage is as follows:

``` PHP
aoiToriOutput( $args = array(), $echo = true );
```

The first parameter is an Array. The options available on that Array will be covered below. The second parameter is a Boolean and tells the plugin if the data should be echoed or returned to a variable.

**Note:** When using the return option, please be aware that it will return the raw data from Twitter. That is data that has not been passed through Aoi Tori's templating system.

Here are the options available to be set in the `$args` Array. There are no required items, however not setting them will result in the plugin using its defaults.

#### screen_name

This is the Twitter Screen Name used to determine whose Tweets to show. You can use any username however the tweets will only be shown if the user's timeline is public. If you wish to show a private timeline the user that the authorized the plugin has permission to see that users tweets. **Default:** `Empty`

#### count

Determines how many Tweets are returned by Twitter. This can be anything up to 3200. **Default:** `10`

#### exclude_replies

If set to true this will exclude any tweet that starts with a reply to another Twitter screen name. A Tweet that contains a mention elsewhere in the Tweet will not be excluded. Please note that Twitter removes these Tweets after retrieving them, this can result in Twitter returning less than the amount requested using the `count` option. **Default:** `false`

#### include_rts

If set to false this will exclude ReTweets. Again Twitter removes these after returning the Tweets and therefore it may result in Twitter returning less than the amount requested using the `count` options. **Default:** `false`

#### cache_time

Determines how long the data returned from Twitter is cached for. Data is cached using a WordPress Transient. The cache time specified is passed to the expiration parameter or `set_transient()`. Since this option should be provided in minutes the value set is converted to seconds using the `MINUTES_IN_SECONDS` constant. **Default:** `30`
