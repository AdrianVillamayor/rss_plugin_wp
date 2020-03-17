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

	$atributos = shortcode_atts(
		array(
			'url' => 'No Data',
			'keyword' => ''
		),
		$url,
		$keyword
	);

	$url = $atributos['url'];

	if($atributos['keyword'] != ""){

		$explode = explode("," , ($atributos['keyword']));
		if(!empty($explode)){
			$words = $explode;
		}else{
			$words[] = $atributos['keyword'];
		}
	}else{
		$words[] = $atributos['keyword'];
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
	
	if ($type === 'atom') {
		$rssTitle = getAttribute($xmlDoc->getElementsByTagName('title'));
		$rssLink = getAttribute($xmlDoc->getElementsByTagName('link'), 'href');
		$rssDescription = '';
		$items = $xmlDoc->getElementsByTagName('entry');
		$countItems = ($xmlDoc->getElementsByTagName('entry')->length);
		$combineItems = '';
	
		for ($i = 0; $i < $countItems; $i++) {
			$item = $items->item($i);
			$title = htmlspecialchars(getAttribute($item->getElementsByTagName('title')));
			$link = htmlspecialchars(getAttribute($item->getElementsByTagName('link'), 'href'));
			$description = htmlspecialchars(getAttribute($item->getElementsByTagName('content')));
			$pubDate = getAttribute($item->getElementsByTagName('published'));
			$updated = getAttribute($item->getElementsByTagName('updated'));
			$published = $pubDate ? $pubDate : $updated;
	
			if (!stristrArray($title, $words) && !(stristrArray($description, $words))) {
				$combineItems .= '<div class="gdlr-core-blog-full-content-wrap" style="margin-bottom: 5rem;">
				<div class="gdlr-core-blog-full-head clearfix">
					<div class="gdlr-core-blog-full-head-right">
						<h3 class="gdlr-core-blog-title gdlr-core-skin-title" style="font-size: 28px ;"><a
								href="' . $link . '" target="_blank" rel="noopener noreferrer">' . $title . '</a>
						</h3>
						<div class="gdlr-core-blog-info-wrapper gdlr-core-skin-divider">
							<span class="gdlr-core-blog-info gdlr-core-blog-info-font gdlr-core-skin-caption gdlr-core-blog-info-category">' . strip_tags($published) . '</span>
						</div> 
					</div>
				</div>
				<div class="gdlr-core-blog-content">' . strip_tags($description) . '</div>
			</div>';
			}
		}
	} elseif ($type === 'rss') {
		$channel = $xmlDoc->getElementsByTagName('channel')->item(0);
		$rssTitle = getAttribute($channel->getElementsByTagName('title'));
		$rssLink = getAttribute($channel->getElementsByTagName('link'));
		$rssDescription = getAttribute($channel->getElementsByTagName('description'));
		$items = $xmlDoc->getElementsByTagName('item');
		$countItems = ($xmlDoc->getElementsByTagName('item')->length);
		$combineItems = '';
	
		for ($i = 0; $i < $countItems; $i++) {
			$item = $items->item($i);
			$title = htmlspecialchars(getAttribute($item->getElementsByTagName('title')));
			$link = htmlspecialchars(getAttribute($item->getElementsByTagName('link')));
			$description1 = htmlspecialchars(getAttribute($item->getElementsByTagName('description')));
			$description2 = htmlspecialchars(getAttribute($item->getElementsByTagName('encoded')));
			$description = $description1 ? $description1 : $description2;
			$pubDate = getAttribute($item->getElementsByTagName('pubDate'));
	
			if (stristrArray($title, $words) && (stristrArray($description, $words))) {
				$combineItems .= '<div class="gdlr-core-blog-full-content-wrap" style="margin-bottom: 5rem;">
				<div class="gdlr-core-blog-full-head clearfix">
					<div class="gdlr-core-blog-full-head-right">
						<h3 class="gdlr-core-blog-title gdlr-core-skin-title" style="font-size: 28px ;"><a
								href="' . $link . '" target="_blank" rel="noopener noreferrer">' . $title . '</a>
						</h3>
						<div class="gdlr-core-blog-info-wrapper gdlr-core-skin-divider">
							<span class="gdlr-core-blog-info gdlr-core-blog-info-font gdlr-core-skin-caption gdlr-core-blog-info-category">' . $pubDate . '</span>
						</div> 
					</div>
				</div>
				<div class="gdlr-core-blog-content">' . strip_tags($description) . '</div>
			</div>';
			}

			
		}
	}

	if ($type) {
		$result = $combineItems;
	} else {
		$result = 'undefined type';
	}

	return $result	;
}


add_shortcode("rss-sd", 'render_xml');

rss_sd();


function stristrArray($haystack, $needle) {
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
  }

  function getAttribute($string, $attribute = '') {
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


  