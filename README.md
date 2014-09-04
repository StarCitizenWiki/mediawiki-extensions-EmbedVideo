#About

The EmbedVideo extension is used to embed videos from video hosting sites such as YouTube.com into a MediaWiki wiki website.  It adds parser functions {{#ev:}} and {{#evp:}} which can be added to pages to embed videos in the wiki page.

For more information about EmbedVideo, to download, to contribute, and to report bugs and problems, visit the GitHub project page:

    http://www.github.com/Whiteknight/mediawiki-embedvideo

##History

The original version of EmbedVideo was created by Jim R. Wilson.  That version was later forked by Mohammed Derakhshani as the EmbedVideoPlus extension.  In early 2010 Andrew Whitworth took over active maintenance of both extensions and merged them together as "EmbedVideo".

The newer versions of EmbedVideo are intended to be fully backwards-compatible with both older EmbedVideo and EmbedVideoPlus extensions.

#License

EmbedVideo is released under the MIT license

    http://www.opensource.org/licenses/mit-license.php

See LICENSE for more details

#Installation

##Download

There are three places to download the EmbedVideo extension. The first is directly from its GitHub project page, where active development takes place.  If you have git, you can use this incantation to check out a read-only copy of the extension source:

	git clone https://github.com/Alexia/mediawiki-embedvideo.git

Downloadable archive packages for numbered releases will also be available from the github project page.

##Installation Instructions

1. Download the contents of the extension, as outlined above.
2. Create an EmbedVideo folder in the extensions/ folder of your MediaWiki installation.
3. Copy the contents of this distribution into that folder
4. Add the following line to your LocalSettings.php:

```
require_once("$IP/extensions/EmbedVideo/EmbedVideo.php");
```

#Supported Sites

EmbedVideo supports several video hosting sites. Some of the more popular ones
include:

* Bambuser (Broadcast and Channel)
* Bing/MSN
* DailyMotion
* Divshare
* Funny or Die
* Kickstarter
* Metacafe
* RuTube
* Screen9
* TeacherTube
* Yahoo
* Yandex
* YouTube (Videos and Playlists)
* Videomaten
* Twitch (Channels and Archived Videos on Demand)
* Vimeo

#Credits

The original version of EmbedVideo was written by Jim R. Wilson.  Additional major upgrades made by Andrew Whitworth, Alexia E. Smith, and other contributors.

See CREDITS for details