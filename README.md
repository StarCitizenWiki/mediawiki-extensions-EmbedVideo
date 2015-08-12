#EmbedVideo
The EmbedVideo Extension is a MediaWiki extension which adds a parser function called `#ev` for embedding video clips from over 22 popular video sharing services in multiple languages and countries. For more information about EmbedVideo, to download, to contribute, and to report bugs and problems, visit the GitHub project page(https://github.com/Alexia/mediawiki-embedvideo).

Issues, bug reports, and feature requests may be created at the issue tracker(https://github.com/Alexia/mediawiki-embedvideo/issues).

##History
The original version of EmbedVideo was created by Jim R. Wilson.  That version was later forked by Mohammed Derakhshani as the EmbedVideoPlus extension.  In early 2010 Andrew Whitworth took over active maintenance of both extensions and merged them together as "EmbedVideo".  Much later on in September 2014 Alexia E. Smith forcefully took over being unable to contact a current maintainer.

The newer versions of EmbedVideo are intended to be fully backwards-compatible with both older EmbedVideo and EmbedVideoPlus extensions.

#License
EmbedVideo is released under the [MIT license](http://www.opensource.org/licenses/mit-license.php). See LICENSE for more details.

#Installation

##Download
If you have git, you can use this incantation to check out a read-only copy of the extension source:
 ```
git clone https://github.com/Alexia/mediawiki-embedvideo.git
```

Downloadable archive packages for numbered releases will also be available from the github project page.

##Installation Instructions

1. Download the contents of the extension, as outlined above.
2. Create an EmbedVideo folder in the extensions/ folder of your MediaWiki installation.
3. Copy the contents of this distribution into that folder
4. Add the following line to your LocalSettings.php

 ```php
wfLoadExtension( "EmbedVideo" );
```

#Supported Sites

EmbedVideo supports several video hosting sites. Some of the more popular ones
include:

* Archive.org Videos
* Bambuser (Broadcast and Channel)
* Bing/MSN
* Blip.tv
* DailyMotion
* Divshare
* Edutopia (Their content has been moved to YouTube.  Simply use the YouTube service to load these.)
* Funny or Die
* Gfycat
* Kickstarter
* Metacafe
* Nico Nico Video
* RuTube
* TeacherTube
* TED Talks
* Twitch (Live Streams and Archived Videos on Demand)
* Yahoo
* YouTube (Videos and Playlists)
* Videomaten
* Twitch (Channels and Archived Videos on Demand)
* Vimeo
* Vine

#Credits

The original version of EmbedVideo was written by Jim R. Wilson. Additional major upgrades made by Andrew Whitworth, Alexia E. Smith, and other contributors.

See CREDITS for details