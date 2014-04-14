=== Rendez Vous ===
Contributors: imath
Donate link: http://imathi.eu/donations/
Tags: buddypress, rendezvous, schedule, meet
Requires at least: 3.8.1
Tested up to: 3.9
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Rendez Vous is a BuddyPress plugin to schedule appointments with your buddies

== Description ==

This is a BuddyPress plugin to let your community members schedule appointments. Rendez-Vous is a french word.. But i think it has an approaching meaning in english ;)
It's a "doodle" like feature plugin where the organizer defines some dates and hours to meet, and the potential attendees will select the ones that match their agenda.
The organizer can then define the definitive date and once this date is past, he will also be able to add some notes or a report to inform about what happened during this meeting.

Available in french and english. 

http://vimeo.com/91172041

== Installation ==

Make sure Rendez Vous is uploaded to "/wp-content/plugins/rendez-vous/".

Activate Rendez Vous in the "Plugins" admin panel using the "Activate" link. If you're using WordPress Multisite, make sure to activate Rendez Vous at the same level than BuddyPress: if BuddyPress is network activated, then network activate Rendez Vous, if BuddyPress is activated on a blog, then activate Rendez Vous on the same blog.

== Frequently Asked Questions ==

= Is there a way to use it in groups component ? =
Not for now, maybe soon :)

== Screenshots ==

1. User choices about the rendez-vous
2. Rendez-vous Editor
3. Member's schedule page

== Upgrade Notice ==

= 1.0.2 =
nothing particular

= 1.0.1 =
nothing particular

= 1.0.0 =
nothing particular

== Changelog ==

= 1.0.2 =

* Fixes a bug while checking BuddyPress config on multisite (props Nat0n)
* Fixes a bug on specific browser for the duration field (props @vegaskev)
* Get rid of some console.log in BackBone js file (pros Nicolas Juen)
* Fixes some english mistakes (props @schwarzaufweiss)

= 1.0.1 =

* Use WordPress start of the week setting to customize calendar's first day (props @schwarzaufweiss)
* Add 2 filters so that themes can override the modal and global css (props @schwarzaufweiss)
* Allow non logged in user to access public rendez-vous (props @pollyplummer)
* Use Display Names in user's rendez-vous preferences
* remove "n" query args before redirecting once the user set his preferences

= 1.0.0 =
initial version
