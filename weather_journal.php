<?php
/* 
Plugin Name: Weather Journal
Plugin URI: http://wppluginsj.sourceforge.jp/weather_journal/
Version: 1.3.0
Description: Insert a weather symbol at date output.
Author: IKEDA Yuriko
Author URI: http://www.yuriko.net/cat/wordpress/
Text Domain: weather_journal
Domain Path: lang/
*/
/*  Copyright (c) 2007-2010 yuriko

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( !defined('WP_INSTALLING') || !WP_INSTALLING ) :
/* ==================================================
 *   WeatherJournal classses
   ================================================== */

class WeatherJournal_Item {
	public $type;
	public $desc;
}

class WeatherJournal {
	private	static $wp_vers;
	private	$plugin_dir;
	private	$plugin_url;
	private	$table_name;
	public	$textdomain_loaded = false;
	public	static $type;
	public	static $ktai_pict = array(
		'sun'          => '44',
		'cloud'        => '107',
		'thunder'      => '16',
		'fog'          => '305',
		'rain'         => '95',
		'storm'        => '190',
		'snow'         => '191',
		'snowfall'     => '191',
		'sleet'        => '191',
		'snow_thunder' => '191',
	);
	const TEXT_DOMAIN = 'weather_journal';
	const DOMAIN_PATH = '/lang';
	const TABLE_NAME = 'weatherjournal';
	const PICS_DIR = 'pics/';
	const SET_STRING = 'WEATHER:';

/* ==================================================
 * @param	none
 * @return	object $weatherjournal
 * @since   1.0.0
 */
public function __construct() {
	global $wpdb;
	$this->set_plugin_dir();

	$this->table_name = $wpdb->prefix . self::TABLE_NAME;  
	if (defined('WEATHER_JOURNAL_DB_PREFIX') && !preg_match('/[^0-9a-zA-Z_]/', WEATHER_JOURNAL_DB_PREFIX)) {
		$this->table_name = WEATHER_JOURNAL_DB_PREFIX . self::TABLE_NAME;
	}
	add_action('plugins_loaded', array($this, 'load_textdomain'));
	add_action('plugins_loaded', array($this, 'init'), 11);

	if ( defined('WP_USE_THEMES') && WP_USE_THEMES ) {
		add_filter('the_date', array($this, 'show'), 10, 4);	
	} else {
		add_action('publish_phone', array($this, 'set_by_content'), 10);
		if (is_admin()) {
			add_action('admin_init', array($this, 'add_weather_meta'));
			if (function_exists('wp_transition_post_status')) {
				add_action('save_post', array($this, 'set'), 10, 2);
			} else {
				add_action('save_post', array($this, 'set'), 10);
			}
			register_activation_hook(__FILE__, array($this, 'install'));
			if (function_exists('get_blog_list')) {
				add_action('activate_sitewide_plugin', array($this, 'install_sitewidely'));
			}
		}
	}
}

/* ==================================================
 * @param	none
 * @return	none
 * @since	1.2.0
 */
private function set_plugin_dir() {
	$this->plugin_dir = basename(dirname(__FILE__));
	if (function_exists('plugins_url')) {
		$this->plugin_url = plugins_url($this->plugin_dir . '/');
	} else {
		$this->plugin_url = get_bloginfo('wpurl') . '/' 
		. (defined('PLUGINDIR') ? PLUGINDIR . '/': 'wp-content/plugins/')
		. $this->plugin_dir . '/';
	}
}

/* ==================================================
 * @param	none
 * @return	none
 * @since	1.2.0
 */
public function load_textdomain() {
	if ( !$this->textdomain_loaded ) {
		$lang_dir = $this->plugin_dir . self::DOMAIN_PATH;
		$plugin_path = defined('PLUGINDIR') ? PLUGINDIR . '/': 'wp-content/plugins/';
		load_plugin_textdomain(self::TEXT_DOMAIN, $plugin_path . $lang_dir, $lang_dir);
		$this->textdomain_loaded = true;
	}
}

/* ==================================================
 * @param	string   $version
 * @param	string   $operator
 * @return	boolean  $result
 * @since	1.1.3
 */
public function check_wp_version($version, $operator = '>=') {
	if ( !isset(self::$wp_vers) ) {
		self::$wp_vers = get_bloginfo('version');
		if ( !is_numeric(self::$wp_vers) ) {
			self::$wp_vers = preg_replace('/[^.0-9]/', '', self::$wp_vers);  // strip 'ME'
		}
	}
	return version_compare(self::$wp_vers, $version, $operator);
}

/* ==================================================
 * @param	string $key
 * @return	mix    $value
 * @since	1.2.0
 */
public function get($key) {
	return isset($this->$key) ? $this->$key : NULL;
}

/* ==================================================
 * @param	none
 * @return	none
 * @since	1.3.0
 */
public function init() {
	self::$type = array(
		'sun'                => __('Sunny', 'weather_journal'),
		'sun+cloud'          => __('Sunny intervals', 'weather_journal'), 
		'sun-cloud'          => __('Sunny then Cloudy', 'weather_journal'), 
		'sun+rain'           => __('Sunny, and Shower', 'weather_journal'), 
		'sun.rain'           => __('Sunny, intermittent Shower', 'weather_journal'), 
		'sun-rain'           => __('Sunny then Rain', 'weather_journal'), 
		'sun.thunder'        => __('Sunny, intermittent Thunderstorm', 'weather_journal'), 
		'sun-thunder'        => __('Sunny then Thunderstorm', 'weather_journal'), 
		'sun+snow'           => __('Sunny, and Snow Shower', 'weather_journal'), 
		'sun.snow'           => __('Sunny, intermittent Snow', 'weather_journal'), 
		'sun-snow'           => __('Sunny then Snow', 'weather_journal'), 
		'cloud'              => __('Cloudy', 'weather_journal'), 
		'cloud+sun'          => __('Partly Cloudy', 'weather_journal'), 
		'cloud-sun'          => __('Cloudy then Sunny', 'weather_journal'), 
		'cloud+rain'         => __('Shower', 'weather_journal'), 
		'cloud.rain'         => __('Intermittent Shower', 'weather_journal'), 
		'cloud-rain'         => __('Cloudy then Rain', 'weather_journal'), 
		'cloud+thunder'      => __('Shower with Thunder', 'weather_journal'), 
		'cloud.thunder'      => __('Intermittent Thunderstorm', 'weather_journal'), 
		'cloud-thunder'      => __('Cloudy then Thunderstorm', 'weather_journal'), 
		'cloud+snow'         => __('Snow Shower', 'weather_journal'), 
		'cloud.snow'         => __('Intermittent Snow', 'weather_journal'), 
		'cloud-snow'         => __('Cloudy then Snow', 'weather_journal'), 
		'cloud+snow_thunder' => __('Snow Shower with Thunder', 'weather_journal'), 
		'fog'                => __('Fog', 'weather_journal'), 
		'rain'               => __('Rain', 'weather_journal'), 
		'storm'              => __('Storm', 'weather_journal'), 
		'thunder'            => __('Thunderstorm', 'weather_journal'), 
		'rain+sun'           => __('Rain, Sunny at times', 'weather_journal'), 
		'rain-sun'           => __('Rain then Sunny', 'weather_journal'), 
		'rain+cloud'         => __('Mostly Rain', 'weather_journal'), 
		'rain-cloud'         => __('Rain then Cloudy', 'weather_journal'), 
		'rain.sleet'         => __('Rain, intermittent Sleet', 'weather_journal'), 
		'rain+snow'          => __('Rain with Snow Shower', 'weather_journal'), 
		'rain.snow'          => __('Rain, intermittent Snow', 'weather_journal'), 
		'rain-snow'          => __('Rain then Snow', 'weather_journal'), 
		'snow'               => __('Snow', 'weather_journal'), 
		'snowfall'           => __('Heavy Snow', 'weather_journal'), 
		'snow+sun'           => __('Snow, Sunny at times', 'weather_journal'), 
		'snow-sun'           => __('Snow then Sunny', 'weather_journal'), 
		'snow+cloud'         => __('Mostly Snow', 'weather_journal'), 
		'snow-cloud'         => __('Snow then Cloudy', 'weather_journal'), 
		'snow+rain'          => __('Snow with Rain Shower', 'weather_journal'), 
		'snow.rain'          => __('Snow, intermittent Rain Shower', 'weather_journal'), 
		'snow-rain'          => __('Snow then Rain', 'weather_journal'), 
		'snow.sleet'         => __('Snow, intermittent Sleet', 'weather_journal'), 
		'snow-sleet'         => __('Snow then Sleet', 'weather_journal'), 
		'snow_thunder'       => __('Snow with Thunder', 'weather_journal'), 
	);
}

/* ==================================================
 * @param	string $the_date
 * @param	string $d
 * @param	string $before
 * @param	string $after
 * @return	none
 * @since   1.0.0
 */
public function show($the_date, $d, $before, $after) {
	if (! $the_date) {
		return $the_date;
	}
	global $post;
	$weather = $this->read($post->post_date);
	if (is_null($weather)) {
		return $the_date;
	}
	if (function_exists('is_ktai') && is_ktai() 
	||  function_exists('is_mobile') && is_mobile()
	||  function_exists('mobile_press')) {
		$output = $this->mobile_weather($weather);
		if ($output) {
			$the_date = preg_replace(
				'/' . preg_quote($after, '/') . '$/', 
				' ' . __('Weather:', 'weather_journal') . $output . $after, 
				$the_date
			);
		}
	} else {
		if ($weather->type) {
			$output .= sprintf('<img src="%s.png" alt="%s" />', 
				attribute_escape($this->plugin_url . self::PICS_DIR . $weather->type), 
				attribute_escape($weather->desc)
			);
		}
		$the_date .= "\n" . '<div class="weather">' . $output . '</div>';
	}
	return $the_date;
}

/* ==================================================
 * @param	string $type
 * @param	string $desc
 * @return	none
 * @since   1.1.0
 */
private function ktai_pict($type, $desc) {
	return sprintf('<img localsrc="%s" alt="%s" />', attribute_escape(self::$ktai_pict[$type]), attribute_escape($desc));
}

/* ==================================================
 * @param	object $weather
 * @return	none
 * @since   1.1.0
 */
private function mobile_weather($weather) {
	if (function_exists('is_ktai') && strcmp(is_ktai(), 'Unknown') !== 0) {
		if (strpos($weather->type, '+') !== false || strpos($weather->type, '.') !== false) {
			$w = preg_split('/[+.]/', $weather->type, 2);
			$output = $this->ktai_pict($w[0], $weather->desc) . '/' . $this->ktai_pict($w[1], '');
		} elseif (strpos($weather->type, '-') !== false) {
			$w = explode('-', $weather->type);
			$output = $this->ktai_pict($w[0], $weather->desc) . '<img localsrc="63" alt="" />' . $this->ktai_pict($w[1], '');
		} elseif ($weather->type) {
			$output = $this->ktai_pict($weather->type, $weather->desc);
		} else {
			$output = '';
		}
	} else {
		$output = $weather->desc;
	}
	return $output;
}

/* ==================================================
 * @param	int      $post_ID
 * @param	object   $post
 * @return	int      $post_ID
 * @since   1.0.0
 */
public function set($post_ID, $post) {
	if ( !is_numeric($post_ID) || $post_ID <= 0 ) {
		return;
	}
	if ( is_object($post) ) {
		if ($post->post_type == 'revision') {
			return;
		}
	} else {
		$post = get_post($post_ID);
		if (! $post || $post->ID != $post_ID) {
			return;
		}
	}
	if ( !isset($_POST['weather_of_date']) ) {
		return;
	}
	$weather = stripslashes($_POST['weather_of_date']);
	$this->update($post->post_date, $weather);
	return;
}

/* ==================================================
 * @param	int      $post_ID
 * @return	none
 * @since	1.2.0
 */
public function set_by_content($post_ID) {
	$post = get_post($post_ID);
	if (! $post) {
		return;
	}

	if (! preg_match('/^(<p>|<div>)?' . preg_quote(self::SET_STRING, '/') . '(.*)$/m', $post->post_content, $w)) {
		return;		
	}
	$weather = trim($w[2]);

	if (! array_key_exists($weather, self::$type)) {
		$weather = array_search($weather, self::$type);
		if ($weather === false) {
			return $post_ID;
		}
	}

	$result = $this->update($post->post_date, $weather);
	global $wpdb;
	$post->post_content = trim(preg_replace('/^' . preg_quote($w[0], '/') . '[ \t\r]*(\n|\z)/m', '', $post->post_content, 1));
	if (method_exists($wpdb, 'update')) {
		$wpdb->update($wpdb->posts, array('post_content', $post->post_content), array('ID', $post->ID));
	} else {
		$content_sql = $wpdb->escape($post->post_content);
		$id_sql = intval($post->ID);
		$wpdb->query("UPDATE {$wpdb->posts} SET post_content = '$content_sql' WHERE ID = $id_sql");
	}
	$posts = array($post);
	update_post_cache($posts);
	return;
}

/* ==================================================
 * @param	string $date
 * @return	object $weather
 * @since   1.0.0
 */
private function read($date) {
	global $wpdb;
	if (is_array($date)) {
		$date_list = $date;
	} else {
		$date_list = array($date);
	}
	$result = array();
	foreach ($date_list as $pub_date) {
		$date_sql = mysql2date('Y-m-d', $pub_date);
		$type = $wpdb->get_var("SELECT type FROM `{$this->table_name}` WHERE pub_date = '$date_sql' LIMIT 1");
		$w = new WeatherJournal_Item;
		$w->type = $type;
		$w->desc = $type ? self::$type[$type] : '';
		$result[$d] = $w;
	}
	if (is_array($date)) {
		return $result;
	}
	return $result[$d];
}

/* ==================================================
 * @param	string $pub_date
 * @param	string $type
 * @return	string $result
 * @since   1.0.0
 */
private function update($pub_date, $type) {
	if ( $type != 'x' && !array_key_exists($type, self::$type) ) {
		return false;
	}
	global $wpdb;
	$date_sql = mysql2date('Y-m-d', $pub_date);
	if ($type == 'x') {
		$result = $wpdb->query("DELETE FROM `{$this->table_name}` WHERE pub_date = '$date_sql' LIMIT 1");
	} else {
		$type_sql = $wpdb->escape($type);
		$result = $wpdb->query("REPLACE INTO `{$this->table_name}` (pub_date, type) VALUES ('$date_sql', '$type_sql')");
	}
	return $result;
}

/* ==================================================
 * @param	none
 * @return	none
 * @since   1.0.0
 */
public function install() {
	global $wpdb;
	if (! current_user_can('activate_plugins')) {
		return;
	}
	$types = implode("','", array_keys(self::$type));
	$charset_collate = '';
	if ( $wpdb->supports_collation() ) {
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE {$wpdb->collate}";
	}
	$sql = "CREATE TABLE IF NOT EXISTS `{$this->table_name}` (
	        `pub_date` date NOT NULL default '1900-00-00',
	        `type` enum('$types') default NULL,
	        PRIMARY KEY (`pub_date`)
	        )  $charset_collate;";
	if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	} else {
		require_once ABSPATH . 'wp-admin/upgrade-functions.php';
	}
	dbDelta($sql);
	return;
}

/* ==================================================
 * @param	none
 * @return	none
 * @since   1.3.0
 */
public function install_sitewidely() {
	$blogs = get_blog_list(0, 'all', false);
	if (is_array($blogs)) {
		reset($blogs);
		foreach((array) $blogs as $key => $details) {
			switch_to_blog($details['blog_id']);
			$this->install();
			restore_current_blog();
		}
	}
}

/* ==================================================
 * @param	none
 * @return	none
 * @since   1.0.0
 */
public function add_weather_meta() {
	if (function_exists('add_meta_box')) {
		$context = $this->check_wp_version(2.7) ? 'side' : 'advanced';
		add_meta_box('weather_journal', __('Weather of this date', 'weather_journal'), array($this, 'edit'), 'post', $context);
	} else {
		add_action('dbx_post_sidebar', array($this, 'edit_23'), 10);
	}
}

/* ==================================================
 * @param	object $post
 * @param	object $box
 * @return	none
 * @since   1.1.3
 */
public function edit($post, $box) {
	$dropdown = $this->get_weather_dropdown($post);
?>
<select name="weather_of_date"><?php echo $dropdown; ?></select>
<?php
}

/* ==================================================
 * @param	none
 * @return	none
 * @since   1.0.0
 */
public function edit_23() {
	global $post;
	$dropdown = $this->get_weather_dropdown($post);
?>
<fieldset id="weatherdiv" class="dbx-box">
<h3 class="dbx-handle"><?php _e('Weather of this date', 'weather_journal') ?></h3> 
<div class="dbx-content"><select name="weather_of_date"><?php echo $dropdown; ?></select></div>
</fieldset>
<?php
}

/* ==================================================
 * @param	object $post
 * @return	none
 * @since   1.1.3
 */
public function get_weather_dropdown($post) {
	if (isset($post->post_date)) {
		$weather = $this->read($post->post_date);
		$type = $weather->type;
	} else {
		$type = '-';
	}
	$list = array('-' => __('(unknown)', 'weather_journal')) + self::$type + array('x' => __('-Delete-', 'weather_journal'));
	$dropdown = '';
	foreach ($list as $k => $w) {
		$selected = ($type == $k) ? '" selected="selected"' : '"';
		$dropdown .= '<option value="' . $k . $selected . '>' . $w . '</option>' . "\n";
	}
	return $dropdown;
}
// ===== End of class ==============================
}

global $WeatherJournal;
$WeatherJournal = new WeatherJournal;
endif;
?>