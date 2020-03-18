<?php

/**
 * Plugin Name: RSS SD
 * Version: 1.0.0
 * Plugin URI: http://www.hughlashbrooke.com/
 * Description: This is your starter template for your next WordPress plugin.
 * Author: Hugh Lashbrooke
 * Author URI: http://www.hughlashbrooke.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: rss-sd
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}

// Load plugin class files.
require_once 'includes/class-rss-sd.php';
require_once 'includes/class-rss-sd-settings.php';

// Load plugin libraries.
require_once 'includes/lib/class-rss-sd-admin-api.php';
require_once 'includes/lib/class-rss-sd-post-type.php';
require_once 'includes/lib/class-rss-sd-taxonomy.php';

/**
 * Returns the main instance of RSS_SD to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object RSS_SD
 */
function rss_sd()
{
	$instance = RSS_SD::instance(__FILE__, '1.0.0');

	if (is_null($instance->settings)) {
		$instance->settings = RSS_SD_Settings::instance($instance);
	}

	return $instance;
}

function render_xml($url, $keyword)
{
	$lang = wpml_get_language_information();

	$atributos = shortcode_atts(
		array(
			'url' => 'No Data',
			'keyword' => ''
		),
		$url,
		$keyword
	);

	$url = $atributos['url'];
	$words[] = $atributos['keyword'];

	if ($atributos['keyword'] != "") {
		$explode = explode(",", ($atributos['keyword']));
		if (!empty($explode)) {
			$words = $explode;
		}
	}

	$xmlDoc = new DOMDocument();
	$source = trim(file_get_contents($url));
	$xmlDoc->loadXML($source);

	if ($xmlDoc->getElementsByTagName('feed')->length) {
		$type = 'atom';
	} elseif ($xmlDoc->getElementsByTagName('rss')->length) {
		$type = 'rss';
	} else {
		$type = '';
	}

	setlocale(LC_ALL, $lang['locale']);

	switch ($type) {
		case 'atom':
			$items = $xmlDoc->getElementsByTagName('entry');
			$countItems = ($xmlDoc->getElementsByTagName('entry')->length);
			$combineItems = '';

			for ($i = 0; $i < $countItems; $i++) {
				$item = $items->item($i);
				$title = getAttribute($item->getElementsByTagName('title'));
				$link = getAttribute($item->getElementsByTagName('link'), 'href');
				$description = getAttribute($item->getElementsByTagName('content'));
				$pubDate = getAttribute($item->getElementsByTagName('published'));
				$updated = getAttribute($item->getElementsByTagName('updated'));
				$published = $pubDate ? $pubDate : $updated;

				if (!stristrArray($title, $words) && !(stristrArray($description, $words))) {
					$combineItems .= creatorPost($title, $link, $published, $description);
				}
			}
			break;

		case 'rss':
			$items = $xmlDoc->getElementsByTagName('item');
			$countItems = ($xmlDoc->getElementsByTagName('item')->length);
			$combineItems = '';

			for ($i = 0; $i < $countItems; $i++) {
				$item = $items->item($i);
				$title = getAttribute($item->getElementsByTagName('title'));
				$link = getAttribute($item->getElementsByTagName('link'));
				$description1 = getAttribute($item->getElementsByTagName('description'));
				$description2 = getAttribute($item->getElementsByTagName('encoded'));
				$description = $description1 ? $description1 : $description2;
				$pubDate = getAttribute($item->getElementsByTagName('pubDate'));

				$date = new DateTime($pubDate);
				$pubDate = $date->format('d F, Y H:i:s');

				if (stristrArray($title, $words) && (stristrArray($description, $words))) {
					$combineItems .= creatorPost($title, $link, $pubDate, $description);
				}
			}
			break;
	}

	if ($type) {
		$result = $combineItems;
	} else {
		$result = 'undefined type';
	}

	return $result;
}

function render_url($url)
{
	$lang = wpml_get_language_information();

	$atributos = shortcode_atts(
		array(
			'url' => 'No Data'
		),
		$url
	);

	$url = $atributos['url'];

	$content = file_get_contents($url);

	$doc = new DOMDocument();

	// squelch HTML5 errors
	@$doc->loadHTML($content);


	$meta = $doc->getElementsByTagName('meta');
	foreach ($meta as $element) {
		$tag = [];
		foreach ($element->attributes as $node) {
			$tag[$node->name] = $node->value;
		}
		$tags[] = $tag;
	}


	foreach ($tags as $key ) {
		switch ($key['property']) {
			case 'og:title':
				$title = $key['content'];
				break;

			case 'og:updated_time':
				$time = $key['content'];
				break;

			case 'og:description':
				$desc = $key['content'];
				break;

			case 'og:image':
				$img = $key['content'];
				break;
		}
	}

	$date = new DateTime($time);
	$pubDate = $date->format('d F, Y H:i:s');

	$combineItems = creatorPost($title, $url, $pubDate, $desc);

	return $combineItems;
}

add_shortcode("rss-sd", 'render_xml');
add_shortcode("url-sd", 'render_url');

rss_sd();


function stristrArray($haystack, $needle)
{
	if ($needle[0] != "") {
		if (!is_array($needle)) {
			$needle = [$needle];
		}

		foreach ($needle as $searchstring) {
			$found = stristr($haystack, $searchstring);
			if ($found) {
				return $found;
			}
		}

		return false;
	} else {
		return true;
	}
}

function getAttribute($string, $attribute = '')
{
	if ($string->length === 0) {
		$result = '';
	} else {
		if ($attribute) {
			$result = $string->item(0)->getAttribute($attribute);
		} else {
			if ($string->item(0)->childNodes->item(1)) {
				$result = $string->item(0)->childNodes->item(1)->nodeValue;
			} elseif ($string->item(0)->childNodes->item(0)) {
				$result = $string->item(0)->childNodes->item(0)->nodeValue;
			} else {
				$result = '';
			}
		}
	}
	return $result;
}




function fetch_string($content)
{
	$content = preg_replace('@<script[^>]*?>.*?</script>@si', '', $content);
	$content = preg_replace('@<style[^>]*?>.*?</style>@si', '', $content);
	$content = strip_tags($content);
	$content = trim($content);
	return $content;
}

function creatorPost($title, $link, $date, $description)
{
	$item = '
	<div class="gdlr-core-blog-full-content-wrap" style="margin-bottom: 5rem;">
		<div class="gdlr-core-blog-full-head clearfix">
			<div class="gdlr-core-blog-full-head-right">
				<h3 class="gdlr-core-blog-title gdlr-core-skin-title" style="font-size: 28px ;"><a
						href="' . $link . '" target="_blank" rel="noopener noreferrer">' . $title . '</a>
				</h3>
				<div class="gdlr-core-blog-info-wrapper gdlr-core-skin-divider">
					<span class="gdlr-core-blog-info gdlr-core-blog-info-font gdlr-core-skin-caption gdlr-core-blog-info-category">' . $date . '</span>
				</div> 
			</div>
		</div>
		<div class="gdlr-core-blog-content">' . fetch_string($description) . '</div>
	</div>';

	return $item;
}
