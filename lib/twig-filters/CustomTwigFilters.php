<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Filters specially created for Aoi Tori
 */

Class CustomTwigFilters {

	/**
	 * Uses the indices provided by Twitter to autolink hashtags, atreplies, and URLs (both media & otherwise).
	 * Original str_replace system left (but commented out) as there could be undiscovered bugs in edge cases such as when any of the entites are cut short due to tweet quoting
	 *
	 *
	 * @param  string  $string
	 * @param  object  $tweet
	 * @param  boolean $hash
	 * @param  boolean $atreply
	 * @param  boolean $hyperlinks
	 * @return string  $string
	 */
	public function process($string, $tweet, $hash = true, $atreply = true, $hyperlinks = true) {

		$oStrLen = mb_strlen($string);
		$nStrLen = 0;
		$indicesMove = 0;

		//Standard URLs
		$urls = (isset($tweet->entities->urls) ? $tweet->entities->urls : false);

		if($urls && !empty($urls) && $hyperlinks) {
			foreach($urls as $url) {

				$string = self::mb_substr_replace($string, '<a class="aoitori_url" href="'.$url->url.'">', ($url->indices[0] + $indicesMove), 0);

				$nStrLen = mb_strlen($string);
				$indicesMove = ($nStrLen - $oStrLen);

				$string = self::mb_substr_replace($string, '</a>', ($url->indices[1] + $indicesMove), 0);

				$nStrLen = mb_strlen($string);
				$indicesMove = ($nStrLen - $oStrLen);

				//$string = str_replace($url->url, '<a class="aoitori_url" href="'.$url->url.'" title="Expanded link: ' . $url->display_url . '">' . $url->url . '</a>', $string);
			}
		}

		//Media URLs
		$media = (isset($tweet->entities->media) ? $tweet->entities->media : false);

		if($media && !empty($media) && $hyperlinks) {
			foreach($media as $item) {

				$string = self::mb_substr_replace($string, '<a class="aoitori_url" href="'.$item->url.'">', ($item->indices[0] + $indicesMove), 0);

				$nStrLen = mb_strlen($string);
				$indicesMove = ($nStrLen - $oStrLen);

				$string = self::mb_substr_replace($string, '</a>', ($item->indices[1] + $indicesMove), 0);

				$nStrLen = mb_strlen($string);
				$indicesMove = ($nStrLen - $oStrLen);

				//$string = str_replace($item->url, '<a class="aoitori_url" href="'.$item->url.'">' . $item->url . '</a>', $string);
			}
		}

		//Hashtags
		$hashtags = (isset($tweet->entities->hashtags) ? $tweet->entities->hashtags : false);

		if($hashtags && !empty($hashtags) && $hash) {
			foreach($hashtags as $hashtag) {

				/*$string = self::mb_substr_replace($string, '<a class="aoitori_hash_tag" href="http://twitter.com/search?q='.urlencode('#' . $hashtag->text).'">', ($hashtag->indices[0] + $indicesMove), 0);

				$nStrLen = mb_strlen($string);
				$indicesMove = ($nStrLen - $oStrLen);

				$string = self::mb_substr_replace($string, '</a>', ($hashtag->indices[1] + $indicesMove), 0);

				$nStrLen = mb_strlen($string);
				$indicesMove = ($nStrLen - $oStrLen);*/

				$string = str_replace('#' . $hashtag->text, '<a class="aoitori_hash_tag" href="http://twitter.com/search?q='.urlencode('#' . $hashtag->text).'">#' . $hashtag->text . '</a>', $string);
			}
		}

		//Users
		$users = (isset($tweet->entities->user_mentions) ? $tweet->entities->user_mentions : false);

		if($users && !empty($users) && $atreply) {
			foreach($users as $user) {

				/*$string = self::mb_substr_replace($string, '<a class="aoitori_atreply" href="http://twitter.com/'.urlencode($user->screen_name).'">', ($user->indices[0] + $indicesMove), 0);

				$nStrLen = mb_strlen($string);
				$indicesMove = ($nStrLen - $oStrLen);

				$string = self::mb_substr_replace($string, '</a>', ($user->indices[1] + $indicesMove), 0);

				$nStrLen = mb_strlen($string);
				$indicesMove = ($nStrLen - $oStrLen);*/

				$string = str_replace('@' . $user->screen_name, '<a class="aoitori_atreply" href="http://twitter.com/'.urlencode($user->screen_name).'">@' . $user->screen_name . '</a>', $string);
			}
		}

		return $string;

	}


	/**
	 * Multi-byte substr_replace function as PHP does not provide a multi-byte version of substr_replace
	 * Must be called statically as Twig uses filters out of $this context
	 *
	 * Credit to Stemar (https://gist.github.com/stemar/8287074)
	 *
	 * @param  string 		$string
	 * @param  string/array $replacement
	 * @param  integer 		$start
	 * @param  integer 		$length
	 * @return string
	 */
	private static function mb_substr_replace($string, $replacement, $start, $length=NULL) {
	    if (is_array($string)) {
	        $num = count($string);
	        // $replacement
	        $replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
	        // $start
	        if (is_array($start)) {
	            $start = array_slice($start, 0, $num);
	            foreach ($start as $key => $value)
	                $start[$key] = is_int($value) ? $value : 0;
	        }
	        else {
	            $start = array_pad(array($start), $num, $start);
	        }
	        // $length
	        if (!isset($length)) {
	            $length = array_fill(0, $num, 0);
	        }
	        elseif (is_array($length)) {
	            $length = array_slice($length, 0, $num);
	            foreach ($length as $key => $value)
	                $length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
	        }
	        else {
	            $length = array_pad(array($length), $num, $length);
	        }
	        // Recursive call
	        return array_map(__FUNCTION__, $string, $replacement, $start, $length);
	    }
	    preg_match_all('/./us', (string)$string, $smatches);
	    preg_match_all('/./us', (string)$replacement, $rmatches);
	    if ($length === NULL) $length = mb_strlen($string);
	    array_splice($smatches[0], $start, $length, $rmatches[0]);
	    return join($smatches[0]);
	}

	/**
	 * Time Ago
	 *
	 * Convert provided date to Time Since
	 *
	 * @param date
	 * @return return string
	 */
	public function timeago($date)
	{
		$time = time() - strtotime($date);

		$units = array (
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day',
			3600 => 'hour',
			60 => 'minute',
			1 => 'second'
		);

		foreach ($units as $unit => $val) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			return ($val == 'second')? 'a few seconds ago' :
			   (($numberOfUnits>1) ? $numberOfUnits : 'a')
			   .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
		}
	}

}
