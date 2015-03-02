![WordPress Fields API](https://raw.githubusercontent.com/sc0ttkclark/wordpress-fields-api/assets/banner-github.png)

# WordPress Fields API

**Please note:** We are currently in the research phase of this project.

The Fields API project aims to provide a unified PHP API to work with any screen that has a form on it within the
WordPress admin area.

Currently, WordPress has many different APIs, hooks, and techniques to work with various areas of WordPress. Each of
these areas are entirely separate and some are hardcoded within WordPress itself so they cannot be further enhanced.

* Post Types
    * Classic Editor
    * Block Editor
* Taxonomies
    * Add term form
    * Edit term form (separate from the Add term form in how you work with it)
* Users
    * User profile
    * Add new user form
    * Add existing user to site form (WP Multisite)
* Media
    * Media modals
    * Media full edit screen
* Comments
    * Add new comment (front of site)
    * Edit comment
* Settings
    * Settings screens are mostly hardcoded
    * Settings API for new settings / new settings pages
* Customizer
    * Customizer API (meant as a replacement for custom settings screens for certain theme options)
* Nav Menus
    * Nav Menu Item form
* Widgets (classic)
    * Widget form

## The Team

* [@sc0ttkclark](https://profiles.wordpress.org/sc0ttkclark/) – Scott Kingsley Clark
    * Senior Software Engineer at Pagely / GoDaddy
    * Lead Developer of the Pods Framework
    * Previously Co-Lead of the Fields API project from 2013-2016
      with [@ericlewis](https://profiles.wordpress.org/ericlewis/) and then Lead Developer from 2016-2017
* [@borkweb](https://profiles.wordpress.org/borkweb/) – Matthew Batchelder
    * Been working with WordPress since ~2004
    * Worked with The Events Calendar and now Director of Engineering at StellarWP overseeing their products and
      initiatives
* [@joedolson](https://profiles.wordpress.org/joedolson/) – Joe Dolson
    * Contributing to WordPress since about 2006
    * WP Core Committer
    * Working on the Accessibility team
    * Reached out to Scott about Fields API from the perspective of improving accessibility for the Settings page
* [@jason\_the\_adams](https://profiles.wordpress.org/jason_the_adams/) – Jason Adams
    * Manager of Development at GiveWP
    * Matt and him want to get together about how to help create unified libraries for multiple projects across
      StellarWP and other companies
    * GiveWP has it's own Field API that it's been pushing forward as well
* [@peteringersoll](https://profiles.wordpress.org/peteringersoll/) – Peter Ingersoll
    * Worked with WP for about 10 years
    * Not a developer but a user/self-titled user advocate
    * Coming at this as a perspective as a user who is interested in how this will work

## More Information

You can find out more by looking over the kick-off chat summary:

> [Fields API Kick-off Chat Summary: January 5th, 2023](https://make.wordpress.org/core/2023/01/09/fields-api-kick-off-chat-summary-january-5th-2023/)