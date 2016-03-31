# Template

These options are all related to the template used when displaying your Tweets. This effects both the widget and PHP Function output unless you choose to return data via the PHP function. Returned data is not passed through the template system and will return the raw data from Twitter instead.

#### Tweet Template

Use this to select the template used when displaying tweets. There are currently 3 built-in templates to choose from.

* **List Style:** Uses a standard unordered list styled to have no bullet points. Your theme's default styling should take over and provide the majority of the styling needed. Some themes do not have different styling for lists in widgetized areas, if this happens please use the margin/padding options to adjust the spacing as needed.
* **Paragraph Style:** Uses paragraph tags for each tweet. Good for footer widgets or areas where paragraphs would be more suitable.
* **Media Object Style:** Uses a media object layout as made popular by the Bootstrap framework. The styling built into the plugin will create the Media Object layout, but your theme styling will do the rest.

#### Enable Custom Template

Use this option to enable the custom template. This will override whichever built-in template has been selected with the code shown in the Template Code option.

#### Template Code
This box, when the option above is enabled, runs the code typed into the box through the Twig template renderer instead of the built-in template selected. There is only 1 variable available to the template, that is the `tweets` object returned by Twitter. You can see the data returned by twitter on their [Developers website](https://dev.twitter.com/rest/reference/get/statuses/user_timeline).
