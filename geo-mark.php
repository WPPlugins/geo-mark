<?php
/**
Plugin Name: Geo Mark
Plugin URI: http://sudarmuthu.com/wordpress/geo-mark
Description: Parses geo information in your content and can expose it either in microformat or as geo rss.
Author: Sudar
Version: 0.9.1
Author URI: http://sudarmuthu.com/
Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
Text Domain: geo-mark

=== RELEASE NOTES ===
2009-07-07 - v0.1 - Initial Release
2009-07-21 - v0.2 - Fixed issue in handling empty arrays
2009-07-22 - v0.3 - Added support for translation.
2009-08-15 - v0.4 - Fixed a small typo.
2009-08-18 - v0.5 - Removed hard coded Plugin path and some bug fixes.
2010-01-02 - v0.6 - Added Belorussian translation.
2011-09-05 - v0.7 - Added Lithuanian and Bulgarian translations.
2011-12-13 - v0.8 - Added Spanish translations.
2012-03-13 - v0.9 - Added translation support for Romanian 
2012-07-23 - v0.9.1 - (Dev time: 0.5 hour)
                  - Added Hindi translations

*/
/*  Copyright 2011  Sudar Muthu  (email : sudar@sudarmuthu.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * The basic Plugin class
 */
class GeoMark {

    /**
     * Initalize the plugin by registering the hooks
     */
    function __construct() {

        // Load localization domain
        load_plugin_textdomain( 'geo-mark', false, dirname(plugin_basename(__FILE__)) . '/languages' );

        // Register hooks
        add_action( 'admin_menu', array(&$this, 'register_settings_page') );
        /* Use the admin_menu action to define the custom boxes */
        add_action( 'admin_menu', array(&$this, 'add_custom_box') );
        add_action( 'admin_init', array(&$this, 'add_settings') );

        /* Use the save_post action to do something with the data entered */
        add_action('save_post', array(&$this, 'save_postdata'));
        add_action('edit_post', array(&$this, 'save_postdata'));
        add_action('publish_post', array(&$this, 'save_postdata'));
        add_action('edit_page_form', array(&$this, 'save_postdata'));

        add_action('draft_to_publish', array(&$this, 'save_geo_info'));
        add_action('future_to_publish', array(&$this, 'save_geo_info'));

        // XML Feed hooks //
        add_action('atom_ns', array(&$this, 'geomark_namespace'));
        add_action('atom_entry', array(&$this, 'geomark_item'));

        add_action('rss2_ns', array(&$this, 'geomark_namespace'));
        add_action('rss2_item', array(&$this, 'geomark_item'));

        add_action('rdf_ns', array(&$this, 'geomark_namespace'));
        add_action('rdf_item', array(&$this, 'geomark_item'));
        
        add_action('rss_ns', array(&$this, 'geomark_namespace'));
        add_action('rss_item', array(&$this, 'geomark_item'));

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array(&$this, 'add_action_links'));
    }

    /**
     * Register the settings page
     */
    function register_settings_page() {
        add_options_page( __('Geo Mark', 'geo-mark'), __('Geo Mark', 'geo-mark'), 8, 'geo-mark', array(&$this, 'settings_page') );
    }

    /**
     * add options
     */
    function add_settings() {
        // Register options
        register_setting( 'geo-mark', 'geo-rss');
    }

    /**
     * hook to add action links
     * @param <type> $links
     * @return <type>
     */
    function add_action_links( $links ) {
        // Add a link to this plugin's settings page
        $settings_link = '<a href="options-general.php?page=geo-mark">' . __("Settings", 'geo-mark') . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Dipslay the Settings page
     */
    function settings_page() {
?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e( 'Geo Mark Settings', 'geo-mark' ); ?></h2>

            <form id="smdf_form" method="post" action="options.php">
                <?php settings_fields('geo-mark'); ?>
                <?php $options = get_option('geo-rss'); ?>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="geo-rss[enabled]"><?php _e( 'Geo RSS Feeds', 'geo-mark' ); ?></label></th>
                        <td><input type="checkbox" name="geo-rss[enabled]" id="geo-rss[enabled]" value="1"  <?php checked('1', $options['enabled']); ?> /> <?php _e("Enable GeoRSS tags in feed", 'geo-mark');?></td>
                    </tr>
                </table>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="geo-rss[feed-format]"><?php _e( 'Feed Format', 'geo-mark' ); ?></label></th>
                        <td>
                            <select name="geo-rss[feed-format]" id="geo-rss[feed-format]">
                                <option value ="simple" <?php selected('simple', $options['feed-format']);?> >Simple &lt;georss:point&gt;</option>
                                <option value='gml' <?php selected('gml', $options['feed-format']);?> >GML &lt;gml:pos&gt;</option>
                                <option value='w3c' <?php selected('w3c', $options['feed-format']);?> >W3C &lt;geo:lat&gt;</option>
                            </select>
                            <p><?php _e("The format of your syndication feeds (Simple is recommended)", 'geo-mark');?> </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="geo-mark-submit" class="button-primary" value="<?php _e('Save Changes', 'geo-mark') ?>" />
                </p>
            </form>
        </div>
<?php
        // Display credits in Footer
        add_action( 'in_admin_footer', array(&$this, 'add_footer_links'));

    }

    /**
     * Adds Footer links. Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
     */
    function add_footer_links() {
        $plugin_data = get_plugin_data( __FILE__ );
        printf('%1$s ' . __("plugin", 'geo-mark') .' | ' . __("Version", 'geo-mark') . ' %2$s | '. __('by', 'geo-mark') . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
    }

    /* Adds a custom section to the "advanced" Post and Page edit screens */
    function add_custom_box() {
        add_meta_box( 'geo-mark-section', __( 'Geo Mark', 'geo-mark' ),
                    array(&$this ,'inner_custom_box'), 'post', 'side' );
    }

    /* Prints the inner fields for the custom post/page section */
    function inner_custom_box() {

      // Use nonce for verification

      echo '<input type="hidden" name="noncename" id="noncename" value="' .
        wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

      // The actual fields for data entry

      echo '<input type="checkbox" name="get_geo" value="1" checked />';
      echo '<label for="get_geo"> ' . __("Populate Geo info on saving this post", 'geo-mark' ) . '</label> ';
    }

    /* When the post is saved, saves our custom data */
    function save_postdata( $post_id ) {

      // verify this came from the our screen and with proper authorization,
      // because save_post can be triggered at other times

      if ( !wp_verify_nonce( $_POST['noncename'], plugin_basename(__FILE__) )) {
        return $post_id;
      }

      if ( 'page' == $_POST['post_type'] ) {
        if ( !current_user_can( 'edit_page', $post_id ))
          return $post_id;
      } else {
        if ( !current_user_can( 'edit_post', $post_id ))
          return $post_id;
      }

      // OK, we're authenticated: we need to find and save the data

      $get_geo = $_POST['get_geo'];
      $get_geo = ($get_geo == "1") ? $get_geo : "0";

      update_post_meta($post_id, "get_geo", $get_geo);
    }

    /**
     * parse and save Geo info in custom fields
     * @param <object> $post
     * @return <type> 
     */
    function save_geo_info($post) {

        $post_id = $post->ID;
        $get_geo = $_POST['get_geo'];
        $get_geo = ($get_geo == "1") ? $get_geo : "0";
        update_post_meta($post_id, "get_geo", $get_geo);
        
        $get_geo = get_post_meta($post_id, "get_geo", true);

        if ($get_geo == "1") {

            $post = &get_post($post_id);

            $geoResults = $this->get_geo_places($post->post_content);

            if (!$geoResults) {
                // Some problem in retrieving geo information.
                return $post_id;
            }

            $geoResults = json_decode($geoResults, true);

            $geoResults = $geoResults['query']['results']['matches']['match'];

            if (!$geoResults) {
                // Some problem in retrieving geo information.
                return $post_id;
            }

            $places = array();

            if (!empty($geoResults['place'])) {
                // if only one place is present
                    $place = array();

                    $place['woeid'] = $geoResults['place']['woeId'];
                    $place['type']  = $geoResults['place']['type'];
                    $place['name']  = $geoResults['place']['name'];
                    $place['latitude']  = $geoResults['place']['centroid']['latitude'];
                    $place['longitude']  = $geoResults['place']['centroid']['longitude'];

                    $places[] = $place;
            } else {
                // if more than one place is present
                foreach ($geoResults as $match) {
                    $place = array();

                    $place['woeid'] = $match['place']['woeId'];
                    $place['type']  = $match['place']['type'];
                    $place['name']  = $match['place']['name'];
                    $place['latitude']  = $match['place']['centroid']['latitude'];
                    $place['longitude']  = $match['place']['centroid']['longitude'];

                    $places[] = $place;
                }
            }
            update_post_meta($post_id, "geo-info", $places);
        }
    }

    /**
     * Retrieve geo information from Yahoo
     * @param <type> $content
     * @param <type> $output_format
     * @return <type> 
     */
    function get_geo_places($content, $output_format="json") {

        // Retrieve search results using YQL

        $root = "http://query.yahooapis.com/v1/public/yql?q=";
        $sql  = "SELECT * FROM geo.placemaker WHERE documentContent='{$content}' and documentType='text/html' and appid=''";
        $format = "&env=http://datatables.org/alltables.env&format=$output_format";

        $yql_url = $root . urlencode($sql) . $format;

        //The following code was influenced by http://planetozh.com/blog/2009/08/how-to-make-http-requests-with-wordpress/
        $request = new wp_Http;
        $result = $request->request($yql_url);
        return $result['body'];
    }

    /**
     * Add geo info to rss namespace
     */
    function geomark_namespace() {
        $option = get_option('geo-rss');

        if($option['enabled'] == "1") {
            switch($option['feed-format']) {
                case "w3c":
                    echo 'xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"'."\n";
                    break;
                case "gml":
                case "simple":
                default:
                    echo 'xmlns:georss="http://www.georss.org/georss" xmlns:gml="http://www.opengis.net/gml"'."\n";
            }
        }
    }

    /**
     * Add geo info to rss feed
     * @param <type> $post_ID
     */
    function geomark_item() {
        global $post;
        $post_ID = $post->ID;
        
        $option = get_option('geo-rss');

        if($option['enabled'] == "1") {
            $places = get_post_meta($post_ID, 'geo-info');

            if(is_array($places) && !empty ($places)) {
                $places = $places[0][0];
                
                $rss_format = $option['feed-format'];

                switch($rss_format) {
                    case "w3c":
                        $coord_tag = "\t<geo:lat>{$places['latitude']}</geo:lat>\n\t\t<geo:lon>{$places['longitude']}</geo:lon>\n";
                        break;
                    case "gml":
                        $coord_tag = "\t<georss:where>\n\t\t<gml:Point>\n\t\t\t<gml:pos>{$places['latitude']} {$places['longitude']}</gml:pos>\n\t\t</gml:Point>\n\t</georss:where>";
                        break;
                    case "simple": // cascade to default
                    default:
                        $coord_tag = "\t<georss:point";
                        if($places['name'] != ""){
                            $coord_tag .= " featurename=\"{$places['name']}\"";
                        }
                        $coord_tag .= ">{$places['latitude']} {$places['longitude']}</georss:point>\n";
                        break;
                }
                echo $coord_tag;
            }
        }
    }

    // PHP4 compatibility
    function GeoMark() {
        $this->__construct();
    }
}

/**
 * Template function to display geo info for a particular post
 * @param <type> $post_id 
 */
function get_geo_info($post_id) {
    $places = get_post_meta($post_id, "geo-info");
    $places = $places[0];
    
    if(is_array($places) && !empty ($places)) {

        foreach ($places as $place) {
            echo <<<GEO
            \n\n<!-- match:{$place['name']} -->\n
                   <span class="vcard">
                      <span class="adr">
                        <span class="locality">{$place['name']}</span>
                      </span>
                      (
                      <span class="geo">
                        <span class="latitude">{$place['latitude']}</span>
                        <span class="longitude">{$place['longitude']}</span>
                      </span>
                      )
                   </span>
GEO;
        }
    }
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'GeoMark' ); function GeoMark() { global $GeoMark; $GeoMark = new GeoMark(); }
?>
