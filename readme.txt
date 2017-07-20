=== Geo Mark ===
Contributors: sudar 
Tags: geo, YQL, placemaker, geo rss, microformats
Requires at least: 2.8
Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
Tested up to: 3.4.1
Stable tag: 0.9.1
	
Parses geo information in your content and can expose it either in microformat or as geo rss

== Description ==

Geo Mark is a WordPress Plugin which will automatically locate Geo information in your WordPress posts using Yahoo Placemaker and YQL API's.

Once the Geo location is located, it will add this information as custom fields to the post. The information stored in the custom field can be displayed anywhere in the post using the build in get_post_meta() function. The Plugin also exposes template functions which can be used to generate [GEO Microformats][1] based on the location information found in the post.

The Geo Mark Plugin also lets you to expose the location based information in RSS feeds. The Geo information stored in the custom field can be used to create [Geo tags in the RSS feed][2]. The Plugin supports the following Geo RSS formats.

*   Simple (georss:point) 
*   GML (gml:pos) 
*   W3C (geo:lat)

###Translation

*   Belorussian (Thanks [FatCow][4])
*   Lithuanian (Thanks Nata of [Web Hub][5])
*   Bulgarian (Thanks Dimitar Kolevski of [Web Geek][6])
*   Spanish (Thanks Brian Flores of [InMotion Hosting][7])
*   Romanian (Thanks Alexander Ovsov of [Web Geek Sciense][8])
*   Hindi (Thanks Love Chandel)

The pot file is available with the Plugin. If you are willing to do translation for the Plugin, use the pot file to create the .po files for your language and let me know. I will add it to the Plugin after giving credit to you.

### Support

Support for the Plugin is available from the [Plugin's home page][3]. If you have any questions or suggestions, do leave a comment there.

 [1]: http://microformats.org/wiki/geo "GEO Microformats"
 [2]: http://georss.org/Main_Page
 [3]: http://sudarmuthu.com/wordpress/geo-mark
 [4]: http://www.fatcow.com/
 [5]: http://www.webhostinghub.com/
 [6]: http://webhostinggeeks.com/
 [7]: http://www.inmotionhosting.com/ 
 [8]: http://webhostinggeeks.com/

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Screenshots ==
1. Settings page

### Usage

#### Template Function

The geo information stored in the custom fields can be exposed as [GEO Microformats][1] by calling the following template function, anywhere within [the Loop][2].

<?php get_geo_info(the_ID()) ?>

#### Enabling Geo RSS

To enable Geo RSS, go to settings -> Geo Mark and check the "Enable GeoRSS tags in feed" option. You can also choose which format of Geo RSS that you want to support.

 [1]: http://microformats.org/wiki/geo
 [2]: http://codex.wordpress.org/The_Loop

== Changelog ==

###v0.1 (2009-07-07)

*   Initial Version

###v0.2 (2009-07-21)
*   Fixed issue in handling empty arrays

###v0.3 (2009-07-22)
*   Added support for translation.

###v0.4 (2009-08-15)
*   Fixed a small typo.

###v0.5 (2009-08-18)
*   Fixed a small typo.

###v0.6 (2010-02-01)
*   Added Belorussian translation.

###v0.7 (2011-09-05)
*   Added Lithuanian and Bulgarian translations.

###v0.8 (2011-12-13)
*   Added Spanish translations.

###v0.9 (2012-03-13)
*   Added translation support for Romanian 

###v0.9.1 (2012-07-23) (Dev time: 0.5 hour)
* Added translation support for Hindi

==Readme Generator== 

This Readme file was generated using <a href = 'http://sudarmuthu.com/wordpress/wp-readme'>wp-readme</a>, which generates readme files for WordPress Plugins.
