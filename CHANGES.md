##Patch Notes
###v2.2.3 Patch Notes
* Added support for Youku and Tudou.

###v2.2.2 Patch Notes
* Updated regular expression replacement pattern for Twitch URLs.  Old Twitch embed URLs do not automatically redirect.

###v2.2.1 Patch Notes
* Fixed E_NOTICE being thrown for [undefined array indexes](https://github.com/Alexia/mediawiki-embedvideo/issues/25).
* Back ported some [PHP 5.3 compatibility changes](https://github.com/Alexia/mediawiki-embedvideo/issues/23).  Please note that future releases of EmbedVideo may not support PHP 5.3 as it is an outdated version.  Upgrading to PHP 5.4 at a minimum is recommended.

###v2.2.0 Patch Notes
* Fixed a bug with alignment that would cause the left align to not work similar to how Mediawiki handles images and other media.
* New parser tag better suited for templates; #evt.
* New HTML like tag format that can take parameters.

###v2.1.8 Patch Notes
* Translations updated.
* Fixed a PHP notice being thrown for the new mobile check.

###v2.1.7 Patch Notes
* German translation thanks to [[User:Messerjokke79]].

###v2.1.6 Patch Notes
* Added to the ability to add optional URL arguments to the generated embed URL.

###v2.1.5 Patch Notes
* Fixed context in which resource modules are loaded.  This resolves an issue with CSS not always applying.

###v2.1.4 Patch Notes
* [Problem with Dailymotion videos // EmbedVideo 2.1.3 (running on MediaWiki 1.23.5)](https://github.com/Alexia/mediawiki-embedvideo/issues/16)  Thanks to [Pierre-Yves](https://github.com/gentilvirus) for reporting this issue.

###v2.1.3 Patch Notes
* [Accidental usage of PHP 5.4+ array syntax would cause a fatal error for older Mediawiki installations.](https://github.com/Alexia/mediawiki-embedvideo/pull/14)  Thanks to [Rich Bowen](https://github.com/rbowen) for reporting and submitting a patch for this issue.
* Fix for a CSS loading order issue on some wiki configurations.

###v2.1.2 Patch Notes
* [Missing CSS for right alignment on the default container.](https://github.com/Alexia/mediawiki-embedvideo/issues/12)
* [Parameters were not being reset between parses.](https://github.com/Alexia/mediawiki-embedvideo/issues/13)

###v2.1.1 Patch Notes
* Fixed a logic issue where the $wgEmbedVideoDefaultWidth global override was not obeyed if the video service specified a default width.
* Actually bumped the version number this time.

###v2.1 Patch Notes
* The width parameter was changed to dimensions.  See parameter documentation above.
* New container parameter to use a standard Mediawiki thumb frame or default to a generic container.
* The description parameter no longer forces the thumb frame to be used.
* Added support for Archive.org, Blip.tv, CollegeHumor, Gfycat, Nico Nico Video, TED Talks, and Vine.
* Ability to center align embeds.
* CSS resource module.

###v2.0 Patch Notes
* URLs from the player pages that contain the raw video ID can now be used as the ID parameter.
* Validation of the raw IDs is improved.
* Code base rewritten to have a VideoService class for future extensibility.
* Switched to HTML5 iframes wherever possible for embeds.
* All services overhauled to be up to date and working.
* The 'auto' and 'center' alignment values were removed as they were not working.  They are planned to be implement properly in the future.