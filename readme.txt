=== Register IPs ===
Contributors: Ipstenu, JohnnyWhite2007
Tags: IP, log, register, multisite,
Requires at least: 4.7
Tested up to: 6.1
Stable tag: 1.8.3
Donate link: https://ko-fi.com/A236CEN/

When a new user registers, their IP address is logged. Supports multisite and single site!

== Description ==

Spam is one thing, but trolls and sock puppets are another.  Sometimes people just decide they're going to be jerks and create multiple accounts with which to harass your honest users.  This plugin helps you fight back by logging the IP address used at the time of creation.

Log into your WP install as an Admin and you can look at their profile or the users table to see what it is. For security purposes a user's own IP is not displayed to them when they look at their own profile.

* [Donate](https://ko-fi.com/A236CEN/)

=== Privacy Notes ===

This plugin adds additional data to a new user's `wp_usermeta` data under the `signup_ip` key. This data is directly tied to the user account, and is only editable via the database. Should a user account be deleted from the site, the data will be automatically deleted.

No external data is transmitted, it all stays on your install.

== Installation ==

No special activation needed.

== Frequently Asked Questions ==

= Why do some users say "None Recorded"? =

This is because the user was registered before the plugin was installed and/or activated.

= Who can see the IP? =

Admins and Network Admins.

= Does this work on MultiSite? =

Yep!

= If this works on SingleSite why the name? =

There's already a plugin called "Register IP", but it didn't work on MultiSite.  I was originally just going to make this a MultiSite-only install, but then I thought 'Why not just go full bore!'  Of course, I decided that AFTER I requested the name and you can't change names. So you can laugh.

= Does this work with BuddyPress? =

It works with BuddyPress on Multisite, so I presume single-site as well. If not, let me know!

= This makes my screen too wide! =

Sorry about that, but that's what happens when you add in more columns.

= What's the difference between MultiSite and SingleSite installs? =

On multisite only the Network admins who have access to Network Admin -> Users can see the IPs on the user list.

= How can I filter the IPs to, say, link to an IP checker? =

There's a filter! Toss this in an MU plugin:

`
function filter_ripm_show_ip($theip) {
	$theip = '<a href="https://duckduckgo.com/?q='.$theip.'">'.$theip.'</a>';
	return $theip;
}
add_filter('ripm_show_ip', 'filter_ripm_show_ip');
`

== Screenshots ==

1. Single Site (regular users menu)
2. Multisite (Network Admin -> Users menu)

== Changelog ==

= 1.8.3 =
* XX November 2022 by Ipstenu
* PHPCS
* Spelling
* Small security improvements

= 1.8.2 =
* 02 August 2020 by Ipstenu
* Fix to show IP on your own page (if you're an admin). This was always there, but only on other people's pages, so you may not have noticed.
* Tested 5.5 compat
* PHPCS cleanup.

= 1.8.1 =
* 07 March 2018 by ipstenu
* Sanitize and escape IP address (props @juliobox)

= 1.8.0 =
* 04 January, 2018 by Ipstenu
* Column sortability (Whaaaaat!?)
* Support for proxies [props @mattpramschufer](https://wordpress.org/support/topic/http_x_forwarded_for-2/)
