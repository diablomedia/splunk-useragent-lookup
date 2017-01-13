# Parsed UserAgent Lookup for Splunk

This lookup will parse a given UserAgent string (as `http_user_agent`) and return `ua_*` properties as splunk fields
after parsing the UserAgent string.

## Installation

This lookup uses PHP, so the system that the lookup is run on needs to have PHP installed.

Clone this repo into your `$SPLUNK_HOME/etc/apps/` directory (or download the zip file and extract there).

The rest of the installation process should be done from this app's directory:

`cd $SPLUNK_HOME/etc/apps/splunk-useragent-lookup` (or wherever you extracted/cloned to)

Download Composer: https://getcomposer.org/download/

Install dependencies with composer:

`./composer.phar install --no-dev`

Restart Splunk

## Configuration

Once installed, the provided lookups should function just fine, but there will be no caching configured.  If you want to speed up the lookups, you can configure a cache.

In the `config` folder there is a `cache.php.dist` file, copy this file to `cache.php` (still in the `config` directory) and configure the caching driver that you want to use (by default the PhpFileCache is enabled, but any of the Doctrine Cache drivers can be used). The `cache.php.dist` file has further instructions and examples.

We recommend configuring a cache if a lot of UserAgents will be parsed with this lookup.

## Usage

Here's an example splunk search that would use the `ua_browscap_lookup` lookup (this assumes that the UserAgent strings that you want to parse are in the `useragent` field):

> index=web sourcetype=apache_access | lookup ua_browscap_lookup http_user_agent as useragent

This will make the `ua_*` fields listed below available, which you could use to filter the results, for example:

> index=web sourcetype=apache_access | lookup ua_browscap_lookup http_user_agent as useragent | search ua_platform=Android

## Parsers

These are the parsers included with this lookup (each parser has a different lookup command).

### Browscap (using Crossjoin 1.x), `ua_browscap_lookup`

From our testing, the fastest PHP based UserAgent parser that uses Browscap data files is version 1.x of the Crossjoin parser (https://github.com/crossjoin/Browscap), so we've decided to use this parser in this project.  The lookup command for this parser is `browscap_lookup`.

Note that the very first lookup using this library will take a little bit of time as the most up-to-date Browscap file is downloaded and parsed. Subsequent requests should return much faster.

These are the fields returned by this lookup (These fields are named the same way as they are in the Browscap project's data files, so please reference them for what the properties are, and what the values represent.  The only exceptions are `http_user_agent` (UserAgent string passed by splunk) and `ua_fromcache` (true or false if the value was retrieved from the cache):

 * http_user_agent
 * ua_propertyname
 * ua_masterparent
 * ua_litemode
 * ua_parent
 * ua_comment
 * ua_browser
 * ua_browser_type
 * ua_browser_bits
 * ua_browser_maker
 * ua_browser_modus
 * ua_version
 * ua_majorver
 * ua_minorver
 * ua_platform
 * ua_platform_version
 * ua_platform_description
 * ua_platform_bits
 * ua_platform_maker
 * ua_alpha
 * ua_beta
 * ua_win16
 * ua_win32
 * ua_win64 ua_frames
 * ua_iframes
 * ua_tables
 * ua_cookies
 * ua_backgroundsounds
 * ua_javascript
 * ua_vbscript
 * ua_javaapplets
 * ua_activexcontrols
 * ua_ismobiledevice
 * ua_istablet
 * ua_issyndicationreader
 * ua_crawler
 * ua_isfake
 * ua_isanonymized
 * ua_ismodified
 * ua_cssversion
 * ua_device_name
 * ua_device_maker
 * ua_device_type
 * ua_device_pointing_method
 * ua_device_code_name
 * ua_device_brand_name
 * ua_renderingengine_name
 * ua_renderingengine_version
 * ua_renderingengine_description
 * ua_renderingengine_maker
 * ua_fromcache

### Piwik Device Detector, `ua_piwik_lookup`

This parser is really good at parsing Mobile Devices (better than Browscap currently), which is why it's included here.

These are the fields returned by this parser:

 * http_user_agent - The UserAgent string passed by Splunk
 * ua_browser - The browser's name (Firefox, Chrome, etc...)
 * ua_browser_type - Type of browser (browser, mobile app, etc...)
 * ua_version - Version of the browser
 * ua_majorver - Just the Major Version of the browser
 * ua_minorver - Just the Minor Version of the browser
 * ua_platform - Platform/OS (IOS, Android, etc...)
 * ua_platform_version - Version of the Platform
 * ua_ismobiledevice - "true" if the device is a mobile device, "false" if it is not
 * ua_device_name - Name of the Device
 * ua_device_maker - Manufacturer of the Device
 * ua_device_type - Type of device (desktop, smartphone, tablet)
 * ua_fromcache - "true" if the response came from the cache, "false" if not

## Testing

There are a few files in the `tests` directory that can be used to test that the lookups are working properly, you can issue the following commands to test the lookups:

### browscap_lookup

`cat tests/test3.stdin | ./bin/browscap_lookup http_user_agent ua_propertyname ua_masterparent ua_litemode ua_parent ua_comment ua_browser ua_browser_type ua_browser_bits ua_browser_maker ua_browser_modus ua_version ua_majorver ua_minorver ua_platform ua_platform_version ua_platform_description ua_platform_bits ua_platform_maker ua_alpha ua_beta ua_win16 ua_win32 ua_win64 ua_frames ua_iframes ua_tables ua_cookies ua_backgroundsounds ua_javascript ua_vbscript ua_javaapplets ua_activexcontrols ua_ismobiledevice ua_istablet ua_issyndicationreader ua_crawler ua_isfake ua_isanonymized ua_ismodified ua_cssversion ua_aolversion ua_device_name ua_device_maker ua_device_type ua_device_pointing_method ua_device_code_name ua_device_brand_name ua_renderingengine_name ua_renderingengine_version ua_renderingengine_description ua_renderingengine_maker ua_fromcache`

### piwik_lookup

`cat tests/test3.stdin | ./bin/piwik_lookup http_user_agent ua_browser ua_browser_type ua_version ua_majorver ua_minorver ua_platform ua_platform_version ua_ismobiledevice ua_device_name ua_device_maker ua_device_type ua_fromcache`

Just replace the `test3.stdin` with the file you want to test against (or create your own file, they're just a list of UserAgents, 1 per line).  This should simulate how Splunk sends commands to these lookups.

## Updating Data Files

The Crossjoin Browscap parser automatically updates its data files when a new version of Browscap's INI file is released.  This will happen automatically if you send enough lookups to the parser (The check for a new version is probability based, and defaults to 1% of requests. We've configured this to only happen on 1% of the calls to the lookup script, and not on individual UA lookups, which should reduce its frequency). If you'd like to update manually, just remove the files stored in `data/browscap` and trigger a lookup for this parser (either using one of the test commands above, or by triggering a search in splunk). This will force the library to pull down the latest Browscap file and build its cache.

For the piwik parser, a new version needs to be installed via composer. This can be done using this command (in the root of the app's directory):

`./composer.phar update piwik/device-detector`

If there is a new version of the parser available (with accompanying data files), it will be installed and used in any subsequent lookups (any cache entries using the previous version will not be used any longer, but will not be deleted).