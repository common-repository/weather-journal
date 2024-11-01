=== Weather Journal ===
Contributors: lilyfan
Tags: weather, journal, daily, japan
Requires at least: 2.2
Tested up to: 2.9
Stable tag: 1.3.0

Weather Journal is a plugin to record and show the daily weather with a WordPress weblog.

== Description ==

[ì˙ñ{åÍÇÃê‡ñæÇì«Çﬁ](http://wppluginsj.sourceforge.jp/weather_journal/)

Weather Journal is a plugin to record and show daily weather with a WordPress weblog.
Each weather is needed to input manually, this plugin can not poll from weather reports.
Therefore, the author can set the right weather for each day, and record weather for past days.
Weather terms are adopted along [Japan Meteorological Agency](http://www.jma.go.jp/).

== Screenshots == 

1. A weather icon will shown next to the date display. You can arrange the position by editing stylesheets.
2. At "Add new post" screen, "Weather of this date" appears under the right sidebar.
3. "Weather of this date" for Japanese translation.
4. Weather selection menu. You can choose one weather for each date.
5. Weather for Japanese translation.

== Requirements ==

* WordPress 2.2 or later
* PHP 5.0 or later (NOT support PHP 4.x!!)

== Installation ==

1. Extract the plugin package and transmit the weather-journal folder to the server.
   It is not necessary to transform this README file, LICENSE file, screenshotN.png file, 
   and lang/weather-journal-XX.po resource file (mo file is needed)
1. Enable the plugin. A weather recoding table is made on your DB.
1. Add below line to your theme's style.css file with theme editor.
   (DO NOT forget dot character at the top!)
   You can arrange the style for your taste.

   `.weather {text-align:right; margin-right:8px;}`
 
== Usage ==

1. In a post editor (when creating or editing an post), "Weather of this date" 
   column will be appeared under the right sidebar.
1. Please choose appropriate weather and save/publish the post.
1. In your weblog, the weather icon will be shown in the next line of the date.
   If you use mobile plugin as [Ktai Style](http://wppluginsj.sourceforge.jp/ktai_style/), use weather pictograms instead of PNG image. 
   If you use mobile plugin as [Mobile Eye+](http://hrlk.com/script/mobile-eye-plus/), weather will be output by text.
1. To delete weather of a date, choose "-Delete-" at "Weather of this date" column.
   Weather does not removed if you choose "(Unknown)".
1. You can use commands below when you post by email by wp-mail.php, [MobG](http://junklog.cocolog-nifty.com/blog/wp_mobg/) by norida, [Ktai Entry](http://wppluginsj.sourceforge.jp/ktai_entry/) by IKEDA Yuriko (me).
   You can write commands wherever in mail message, but you must write top of each line.
   Commands are deleted after post was saved.

	* `WEATHER:sun`
	* `WEATHER:sun.thunder`

	Command text are below. Use "sun" for sunny weather, not "fine".

	`sun,sun+cloud, sun-cloud, sun+rain, sun.rain, sun-rain, sun.thunder, sun-thunder, sun+snow, sun.snow, sun-snow, cloud, cloud+sun, cloud-sun, cloud+rain, cloud.rain, cloud-rain, cloud+thunder, cloud.thunder, cloud-thunder, cloud+snow, cloud.snow, cloud-snow, cloud+snow_thunder, fog, rain, storm, thunder, rain+sun, rain-sun, rain+cloud, rain-cloud, rain.sleet, rain+snow, rain.snow, rain-snow, snow, snowfall, snow+sun, snow-sun, snow+cloud, snow-cloud, snow+rain, snow.rain, snow-rain, snow.sleet, snow-sleet, snow_thunder`
	

== Terms ==

"Intermittent", "Mostly", "With", "Partly", and "Then" are defined as below at Japan Meteorological Agency.
You don't need to obey this definition, please choose weather for your favor.

* XX, intermittent YY : Mainly XX is seen, but YY occurred within less than quarter of the day.
* Mostly XX           : Mainly XX is seen, but Cloudy within less than quarter of the day.
* XX, with YY         : Mainly XX is seen, but YY occurred within less than half of the day.
* Partly XX           : Mainly XX is seen, but Sunny within less than half of the day.
* XX, then YY         : Weather is different between first half and last half of the day.

== Frequently Asked Questions ==

* This plugin hooks to `the_date()` function. If your theme does not use `the_date()` template tag, weather is not displayed.
* Weather terms are adopted along Japan Meteorological Agency. 
  English strings are translated from Japanese. There might be unfamiliar terms outside Japan.
* Journaling is one for each date. Not each post. 
  Therefore, this plugin cannot record sudden weather phenomenon.
* If you create a new post after you set a weather for another post, 
  the preset weather will be shown at "Weather of this date" field. 
  If you change the weather, the weather for the date is fixed. 
  Therefore, the change will affect to all posts of the same day.
* If you change the publishing date, the weather is recorded for the new date. 
  The weather of the old date is remained at the DB.

== Difference with wp-otenki plugin ==

[wp-otenki](http://wppluginsj.sourceforge.jp/wp-otenki/) is a plugin that polls weather infomation from Livedoor weather API in Japan.

* Recording of weather is manually. This plugin does not poll weather information from other server.
* It is possible to change weather when you edit a post, and to set weather at whatever old post.
* No configuration is needed like setting area.
* Journaling  of weather is one for each date. It is not able to record weather for each post.

== Licence ==

The license of this plugin is GPL v2.

== Changelog ==

= 1.3.0 (2009-01-05) =
* Distrubute at the official WordPress plugin directory.
* Localized the plugin description at the plugin admin panel after WordPress 2.7.
* Fix side effect to other plugins after WordPress 2.6.
* Fix creating weather table after MySQL 4.1.

= --snip-- =

= 1.0.0 (2007-08-11) =
* Initial Release
