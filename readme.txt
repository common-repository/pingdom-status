=== Pingdom Status ===
Contributors: Pingdom
Donate link: http://www.pingdom.com/
Tags: pingdom, stats, uptime, downtime, webmaster, monitor, hosting
Requires at least: 2.7
Tested up to: 3.0
Stable tag: 1.1.4b

Displays uptime and response time statistics from the website monitoring service Pingdom.

== Description ==

Pingdom is a website monitoring service with tens of thousands of users. With Pingdom, you can monitor the uptime and response time of your websites and servers on the Internet and get alerts immediately if any problems are detected. The service can send alerts via email, SMS, Twitter, and iPhone push notifications, and also lets you examine all monitoring results in an online control panel to help you troubleshoot and discover trends. It's [free to monitor one site](http://pingdom.com/free "Free Pingdom account").

This plugin lets you display your Pingdom monitoring data on your Wordpress site, essentially creating a status page for whatever it is you are monitoring.

With the plugin installed, you can:

* View a list of monitored services in your Pingdom account together with their current status and response time.
* Create groups of services.
* Display uptime and response time graphs per month and a list of outages.
* Style the output using your own CSS.

To learn more about Pingdom, please visit [www.pingdom.com](http://www.pingdom.com/ "Pingdom").

= How does it work? =

First off, you need to use [Pingdom](http://pingdom.com/free/ "Get a Pingdom account now") to monitor your websites and servers. The plugin then uses the Pingdom API to download your monitoring results and make them available on your Wordpress site.

Keep in mind that all data the plugin downloads from Pingdom is stored in your regular Wordpress database, so over time your database will grow in size.

The plugin updates the service status(es) from Pingdom once every 2 minutes, and updates the data used for graphs once every 10 minutes.

The data synchronization depends on a hook in the Wordpress footer, so you must include wp_footer() for the synchronization to work.

Note that the plugin uses SOAP to communicate with the Pingdom API. Make sure that your server has the php-soap module installed and enabled.

== Installation ==
First of all, if you don't already have one, [get yourself a Pingdom account](http://www.pingdom.com/free/ "Get a Pingdom account now"). It's totally free to monitor one site.

1. Add the Pingdom Status plugin directly inside the Wordpress admin control panel ("add new" under Plugins).
2. OR you can add the plugin manually: Create a folder named "pingdom-status" at /wp-content/plugins/ inside your Wordpress installation, and put all the files from pingdom-status.zip there (unzipping it should automatically create that folder).
3. Log in to your Site Admin panel, go to "Plugins" in the admin sidebar and activate Pingdom Status.
4. Now you can navigate down to the Pingdom Status section in the admin sidebar and go to "General Settings" to set up your plugin. You need to input your Pingdom username, password and API key.
5. Go to Pingdom Status > Non-Public Checks and make public the ones you want to be displayed publicly.
6. Go to Pingdom Status > General Settings and click "Synchronize with Pingdom" (this is the only time you have to do this manually).
7. Create a new Page in Wordpress (or use an existing one) and paste the following string (without quotation marks) "[pingdom_status]" and Publish it.
8. You're done. Visit your page and check it out!

If it doesn't look awesome, have a look at the FAQ.

You should give write access to the /php/templates directory in order to use the Edit Templates panel.

Note that the plugin uses SOAP to communicate with the Pingdom API. Make sure that your server has the php-soap module installed and enabled.

== Frequently Asked Questions ==

= The chart width is too large/small =

The width of the charts are set manually.

You can change this value yourself inside the Pingdom Status > Edit Templates panel.
Open up the respective files for the charts and change
<pre><code><div id="xChart" style="width:500px;height:200px"></div></code></pre>

= This looks strange/funny in my theme =

This is probably due to a collision between styles set by us and styles set by your theme. There are two ways to solve this:

1. Figure out what is causing the problem and fix it inside either pingdomstatus.css or your theme's CSS file. The CSS provided by us is more like an guiding example to help you style the plugin to your needs.
2. Comment out or remove pingdomplugin.css to use the styles provided by your theme.

= I want to change the plugin to suit my needs =

You should start by checking out Pingdom Status > Edit Templates in the admin menu.
From there you can change the looks and basic functionality of the plugin.

Or if you are a developer, you can take a look at the code in wp-content/plugins/PingdomStatus/.

= I can't save my templates in Pingdom Status > Edit Templates =

You need to make sure you have write permission to /wp-content/plugins/PingdomStatus/php/templates/.

= When I try to synchronize with Pingdom it says "synchronizing" but nothing more happens =
The plugin uses SOAP to communicate with the Pingdom API. Make sure that your server has the php-soap module installed and enabled.

= I'm getting an error like "Couldn't locate driver named mysql" =
Make sure you have PDO module and driver (for mysql) installed and enabled.

== Screenshots ==
1. The overview page lists all monitored services and their current status.
2. Each service has its own status page where you can view its uptime and response time history.

== Changelog ==
= 1.1.4 =
* Shows error page when trying to view report of non-existing or non-public service (thanks to Yoav Aner for reporting this)
* Strips slashes from template code in admin panel (thanks to Yoav Aner for reporting this)
* Hides deleted services in the service list
* Corrected some spelling errors
* Fixed notice "has_cap was called with an argument that is deprecated since version 2.0"

= 1.1.3 =
* "Go Back to all services"-link bug fix.
* Added option to display verbose output of synchronization (General Settings panel) and synclog.txt (needs to be writeable).
* Background of pingdom_logo.png changed from white to transparent.
* Some bug and filepath fixes.

= 1.1.2 =
* Minor bug fixes.
* Fixes issues with short open tags.

= 1.1.1 =
* Changed root directory of plugin from "PingdomStatus" to "pingdom-status" to support automatic installation.

= 1.1 =
* First public release

== Upgrade Notice ==
= 1.1.3 =
* "Go Back to all services"-link bug fix.
* Added option to display verbose output of synchronization (General Settings panel) and synclog.txt (needs to be writeable).
* Background of pingdom_logo.png changed from white to transparent.
* Some bug and filepath fixes.

= 1.1.2 =
Minro bug fixes.

= 1.1.1 =
Fixes issues with automatic installation.

= 1.1 =
* Upgrading to this version not available, full install required.
