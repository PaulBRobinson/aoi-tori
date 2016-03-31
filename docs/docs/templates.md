# Template Customization

If you need a layout for your tweets not covered by the built in layouts then this is the section for you. Please be aware that you will need basic knowledge of HTML and CSS to create a custom template. Knowledge of a template language such as Handlebars or Twig (The template engine used) is also helpful, but it should be simple enough to learn.

If you would like to read up on Twig before continuing please check out [Twig's documentation](http://twig.sensiolabs.org/documentation).

## Twig Templates

Aoi Tori uses Twig as its template language. This helps keeps things simple and removes the need for lengthy PHP logic in the template files. For example a PHP can be a little too verbose:

``` PHP
<?php
if(!empty($this)) {
  echo $this;
} else {
  echo 'It is empty';
}
?>
```

With Twig it is a lot shorter:

``` Twig
{{ $this|default('It is empty') }}
```

The PHP version could be made shorter with a ternary, but that makes things harder to read, not easier like in the Twig example.

## Aoi Tori Template

The template is Aoi Tori is fairly simple. The template is passed 1 variable that is available at any time throughout the template. That is the variable `tweets`. `tweets` contains the raw data returned from Twitter. This means you have access to any of the data that was returned and can output whatever you wish from the object. Please check out the [Twitter Development](https://dev.twitter.com/rest/reference/get/statuses/user_timeline) website for more details of what is available in the object.

There is an example already written in the custom template box for you to use as an example. Notice the use of the extra filter `process()` this filter allows you to pass through the tweet to be processed and choose if Hashtags, AtReplys, and URLs should be processed or not. It looks a little like this:

``` Twig
process(object tweet, bool hashtag, bool atreply, bool url)
```

The last three parameters are true by default. If set to false each item will just return the default textual representation instead of the processed hyperlink.

**Note:** The `process()` filter will only work when handed a single tweet object and not the whole tweets object returned by Twitter. To this end it is advised only to use it while inside a loop.

A more detailed template example is available below for your reference. Please use it as an example.

``` Twig
{% if tweets|length > 0 %}
	<ul class="aoitori_tweets">
		{% for tweet in tweets %}
			<li class="aoitori_tweet">{{ tweet.text|process(tweet) }}</li>
		{% endfor %}
	</ul>
{% endif %}
```
