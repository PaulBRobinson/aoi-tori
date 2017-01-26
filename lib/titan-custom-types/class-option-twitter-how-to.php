<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TitanFrameworkOptionTwitterHowTo extends TitanFrameworkOption {

	public $defaultSecondarySettings = array(
		'how-to' => true,
		'faq' => false,
	);

	public function display() {
		if ( !empty( $this->owner->postID ) ) {
			return;
		}

		if($this->settings['how-to'] === true) :

		?>

		<tr valign="top" class="even first tf-heading">
			<th scope="row" class="first last" colspan="2">
				<h3 id="how-to"><?php _e('Instructions', 'aoitori'); ?></h3>
			</th>
		</tr>
		<tr valign="top" class="row-1 odd">
			<td class="first" colspan="2">
				<p>
					<?php _e('To allow Aoi Tori to access your tweets you must authorize it by creating a Twitter App. This is a requirement made by Twitter. To do this please follow these instructions.', 'aoitori'); ?>
				</p>
				<ol>
					<li>
						<?php
							printf(
								__('Go to the <a href="%s" target="aoitori_app_tab">Twitter Application Management</a> website. Click \'Create New App\'.', 'aoitori'),
								'https://apps.twitter.com/'
							);
						?>
					</li>
					<li><?php _e('Enter details based on the following.', 'aoitori'); ?>
						<ul>
							<li>
								<?php
									_e('<strong>Name:</strong> The name for your application. Can be whatever you wish.', 'aoitori');
								?>
							</li>
							<li>
								<?php
									_e('<strong>Description:</strong> A short description for your application. Again, can be whatever you wish.', 'aoitori');
								?>
							</li>
							<li>
								<?php
									printf(
										__('<strong>Website:</strong> Just enter your home web address. For reference that is: <strong>%s</strong>', 'aoitori'),
										get_bloginfo('url')
									);
								?>
							</li>
							<li>
								<?php
									_e('<strong>Callback URL:</strong> You must leave this field blank.', 'aoitori');
								?>
							</li>
						</ul>
					</li>
					<li><?php _e('Read &amp; agree to the developer terms and press \'Create your Twitter application\'.', 'aoitori'); ?></li>
					<li><?php _e('Click on the \'Keys and Access Tokens\' tab. Now copy:', 'aoitori'); ?>
						<ul>
							<li><strong><?php _e('Consumer Key', 'aoitori'); ?></strong></li>
							<li><strong><?php _e('Consumer Secret', 'aoitori'); ?></strong></li>
							<li><strong><?php _e('Access Token', 'aoitori'); ?></strong></li>
							<li><strong><?php _e('Access Token Secret', 'aoitori'); ?></strong></li>
						</ul>
					</li>
					<li><?php _e('Enter these keys in the relevent fields shown on the API Keys tab &amp; hit \'Save\'', 'aoitori'); ?></li>
					<li>
						<?php
							printf(
								__('If you wish you can go back to the <a href="%s" target="aoitori_app_tab">Twitter Application Management</a> and click \'Change App Permissions\' and set them to \'Read only\' as Twitter Stream does not need write permissions to work', 'aoitori'),
								'https://apps.twitter.com/'
							);
						?>
					</li>
					<li>
						<?php
							printf(
								__('Check the <a href="%s" target="aoitori_app_tab">documentation</a> for more detailed information on how to use the plugin past this point. It is highly recommended you read though it at least once.', 'aoitori'),
								 'https://nabesaka.github.io/aoi-tori/'
							);
						?>
					</li>
				</ol>
			</td>
		</tr>

		<?php
		endif;

		if($this->settings['faq'] === true) :

		?>

	<tr valign="top" class="even first tf-heading">
		<th scope="row" class="first last" colspan="">
			<h3 id="how-to"><?php _e('Instructions', 'aoitori'); ?></h3>
		</th>
	</tr>
	<tr valign="top" class="row-1 odd">
		<td class="first" colspan="2">
			<p><?php _e('Below you can find some of the most commonly asked questions about the Twitter Stream plugin.', 'aoitori'); ?></p>
			<h3><?php _e('The Tweets Are All Crushed/Spacing Is Wrong!', 'aoitori'); ?></h3>
			<p><?php _e('Please use the margin/padding options on the Advanced CSS tab to change the spacing. The default setting is to inherit your theme margin/padding which can sometimes display things incorrectly.', 'aoitori'); ?></p>
			<h3><?php _e('Which Tokens Should I Copy?', 'aoitori'); ?></h3>
			<p><?php _e('As mentioned on the first tab you want to copy the <strong>Consumer Key</strong>, <strong>Consumer Secret</strong>, <strong>Access Token</strong>, and <strong>Access Token Secret</strong>. Paste them into the relevant fields, save and you should be good to go.', 'aoitori'); ?></p>
			<h3><?php _e('Why 4 Tokens?', 'aoitori'); ?></h3>
			<p><?php _e('Using 4 tokens instead of 2 allows the plugin to authenticate and access your tweets without having to force the end-user (you) to visit Twitter through a special URL to authenticate. Feedback received overwhelmingly said that everyone hated having to go backward &amp; forward to Twitter and it was the common cause of issues (such as the dreaded 403 error). Using 4 keys removes that and reduces the chance of any errors occuring.', 'aoitori'); ?></p>
			<h3><?php _e('What About Security?', 'aoitori'); ?></h3>
			<p>
				<?php
					printf(
						__('Using 4 token authentication is no less secure than 2 tokens. If you are very security concious then you can feel free to visit your <a href="%s" target="aoitori_app_tab">Twitter Application Management</a> page and set the access mode to Read-Only. Since Twitter Stream does not update or post Tweets on your behalf it only needs read access.', 'aoitori'),
							'https://apps.twitter.com/'
					);
				?>
			</p>
			<p>
				<?php
					printf(
						__(
							'Your tokens are saved in the WordPress database and are never displayed by this plugin anywhere outside of this options page. They are handed to Twitter during authentication via cURL. If you ever need to refresh your tokens you can by visiting the <a href="" target="aoitori_app_tab">Twitter Application Management</a> page and regenerate both your <strong>Consumer Keys</strong> and your <strong>Access Keys</strong>. If you do this remember that you will need to resave the new tokens in the API Keys tab. This would be the recommended process regardless of the Twitter plugin you are using.', 'aoitori'),
							'https://apps.twitter.com/'
					);
				?>
			</p>
			<h3><?php _e('My Tokens Are Invalid. Help!', 'aoitori'); ?></h3>
			<p><?php _e('If you were given a red message saying "Tokens Invalid" under \'Token Validity Check\' after saving it means your Tokens were refused by Twitter.', 'aoitori'); ?></p>
			<p><?php _e('The most common cause is Whitespace or an incorrectly copied key/token. Please double check that you are not copying any whitespace either side (front or back) of any of the keys when copying them from the Twitter Application Management website. Also try copying them &amp; try pasting them one more time just in case a character was missed when copying the first time. A common trick is to paste the key into a notepad application (Notepad, Notepad++, GEdit) to check for whitespace, then copy it from there into the field.', 'aoitori'); ?></p>
			<h3><?php _e('My Tokens Still Don\'t Work. Please Help!', 'aoitori'); ?></h3>
			<p><?php _e('Please double check your server has the PHP extention cURL installed, although you should have seen other errors before now if you don\'t. Also make sure you have PHP 5.3 or above, again you should have hit a problem before this point if you do not have either of these, but it is worth a check. If you are missing either of these requirements please ask your host to install them for you. Any good host would normally have both these requirements installed, if they will not install them I would strongly advise finding a more accommodating host.', 'aoitori'); ?></p>
			<h3><?php _e('My Tokens Are Still Invalid. Eeek!', 'aoitori'); ?></h3>
			<p>
				<?php
					printf(
						__('You may be one of the few people who have an unusual server setup or have a conflict caused by another plugin or theme. While I try to limit these issues it is impossible to account for the multitude of setups &amp; combinations of plugins/themes out there. You will more than likely need 1-on-1 support. I am more than happy to provide you with support for any issue you are having with my plugin. Please send an email to <a href="%s">support@return-true.com</a> to contact me for help.', 'aoitori'),
							'support@return-true.com'
					);
				?>
			</p>
		</td>
	</tr>
		<?php
		endif;

	}
}
