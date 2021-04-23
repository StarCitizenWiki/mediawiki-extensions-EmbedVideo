# About

The EmbedVideo Extension is a MediaWiki extension which adds a parser function called #ev for embedding video clips from over various popular video sharing services in multiple languages and countries.  It also adds video and audio media handlers to support transforming standard `[[File:Example.mp4]]` file links into embedded HTML5 `<video>` and `<audio>` tags.

For more information about EmbedVideo, to download, to contribute, and to report bugs and problems, visit the GitHub project page:

https://github.com/StarCitizenWiki/mediawiki-extensions-EmbedVideo

Issues, bug reports, and feature requests may be created at the issue tracker:

https://github.com/StarCitizenWiki/mediawiki-extensions-EmbedVideo/issues

The original MediaWiki extension page is located at:

https://www.mediawiki.org/wiki/Extension:EmbedVideo

## History

Large parts of the codebase are taken from Extension:EmbedVideo v2.9.0

# License

EmbedVideo is released under the MIT license

http://www.opensource.org/licenses/mit-license.php

See LICENSE for more details

# Installation

## Download

There are three places to download the EmbedVideo extension. The first is directly from its GitHub project page, where active development takes place.  If you have git, you can use this incantation to check out a read-only copy of the extension source:

```
git clone https://github.com/StarCitizenWiki/mediawiki-extensions-EmbedVideo.git
```

Downloadable archive packages for numbered releases will also be available from the github project page.

## Installation Instructions

1. Download the contents of the extension, as outlined above.
2. Create an EmbedVideo folder in the extensions/ folder of your MediaWiki installation.
3. Copy the contents of this distribution into that folder

Add the following line to your LocalSettings.php:

```php
wfLoadExtension("EmbedVideo");
```

# Usage

## Media Handler
For locally uploaded content the process for displaying it on a page is the same as an image.  [See the image syntax documentation](https://www.mediawiki.org/wiki/Help:Images#Syntax) on MediaWiki.org for complete reference on this feature.

This example would display a video in page using a HTML5 `<video>` tag.

	[[File:Example.mp4]]

To specify the start and end timestamps in the media use the start and end parameters.  The timestamp can be formatted as one of: ss, :ss, mm:ss, hh:mm:ss, or dd:hh:mm:ss.

	[[File:Example.mp4|start=2|end=6]]

Additionally a cover image can be set for video files by specifying a `cover=` key.

	[[File:Example.mp4|start=2|end=6|cover=File:LocalFile.png]]

## Tags

The EmbedVideo parser function expects to be called in any of the following ways:

### \#ev - Classic Parser Tag

* `{{#ev:service|id}}`
* `{{#ev:service|id|dimensions}}`
* `{{#ev:service|id|dimensions|alignment}}`
* `{{#ev:service|id|dimensions|alignment|description}}`
* `{{#ev:service|id|dimensions|alignment|description|container}}`
* `{{#ev:service|id|dimensions|alignment|description|container|urlargs}}`
* `{{#ev:service|id|dimensions|alignment|description|container|urlargs|autoresize}}`

However, if needed optional arguments may be left blank by not putting anything between the pipes:

* `{{#ev:service|id|||description}}`

## Examples

### Example #1

For example, a video from YouTube use the 'youtube' service selector enter the raw ID:

    {{#ev:youtube|pSsYTj9kCHE}}

Or the full URL:

    {{#ev:youtube|https://www.youtube.com/watch?v=pSsYTj9kCHE}}

### Example #2

To display the same video as a right aligned large thumbnail with a description:

    {{#ev:youtube|https://www.youtube.com/watch?v=pSsYTj9kCHE|1000|right|Let eet GO|frame}}

For YouTube to have the video start at a specific time code utilize the urlargs(URL arguments) parameter. Take the rest of the URL arguments from the custom URL and place them into the urlargs. Please note that not all video services support extra URL arguments or may have different keys for their URL arguments.

    https://www.youtube.com/watch?v=pSsYTj9kCHE&start=76

    {{#ev:youtube|https://www.youtube.com/watch?v=pSsYTj9kCHE|||||start=76}}

### Example #3

Creating a video list for Youtube. This allows you to queue a set of video in a temporary playlist. Use the 'youtubevideolist` service selector:

    {{#ev:youtubevideolist|-D--GWwca0g|||||playlist=afpRzcAAZVM,gMEHZPZTAVc,lom_plwy9iA,BSWYMQEQhEo,EREaWhXj4_Q}}

## Supported Services

As of version 2.x, EmbedVideo supports embedding video content from the following services:

| Site                                                     | Service Name(s)                                                                       | ID Example                                                                            | URL Example(s)                                                                                                 |
|----------------------------------------------------------|---------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------|
| [Archive.org Videos](https://archive.org/details/movies) | `archiveorg`                                                                          | electricsheep-flock-244-80000-6                                                       | https://archive.org/details/electricsheep-flock-244-80000-6<br/>https://archive.org/embed/electricsheep-flock-244-80000-6 |
| [SoundCloud](http://soundcloud.com/)                     | `soundcloud`                                                                          |                                                                                       | https://soundcloud.com/skrillex/skrillex-rick-ross-purple-lamborghini                                          |
| [Spotify](http://spotify.com/)                           | `spotifyalbum` - Album embed                                                          | 1EoDsNmgTLtmwe1BDAVxV5                                                                | https://open.spotify.com/album/1EoDsNmgTLtmwe1BDAVxV5                                                          |
| [Spotify](http://spotify.com/)                           | `spotifyartist` - Artist embed                                                        | 06HL4z0CvFAxyc27GXpf02                                                                | https://open.spotify.com/artist/06HL4z0CvFAxyc27GXpf02                                                         |
| [Spotify](http://spotify.com/)                           | `spotifytrack` - Song embed                                                           | 72jCZdH0Lhg93z6Z4hBjgj                                                                | https://open.spotify.com/track/72jCZdH0Lhg93z6Z4hBjgj                                                          |
| [Twitch](http://www.twitch.tv)                           | `twitch` - Live Streams                                                               | `twitchvod` - Archived Videos on Demand                                               | twitchplayspokemon                                                                                             |
| [Vimeo](http://www.vimeo.com)                            | `vimeo`                                                                               | 105035718                                                                             | http://vimeo.com/105035718                                                                                     |
| [YouTube](http://www.youtube.com/)                       | `youtube` - Single Videos                                                             | pSsYTj9kCHE                                                                           | https://www.youtube.com/watch?v=pSsYTj9kCHE                                                                    |
| [YouTube](http://www.youtube.com/)                       | `youtubeplaylist` - Playlists                                                         | PLY0KbDiiFYeNgQkjujixr7qD-FS8qecoP                                                    | https://www.youtube.com/embed/?listType=playlist&list=PLY0KbDiiFYeNgQkjujixr7qD-FS8qecoP                       |
| [YouTube](http://www.youtube.com/)                       | `youtubevideolist` - Video List                                                       | pSsYTj9kCHE - urlargs=playlist=pSsYTj9kCHE,pSsYTj9kCHE                                | https://www.youtube.com/embed/pSsYTj9kCHE?playlist=pSsYTj9kCHE,pSsYTj9kCHE                                     |

# Configuration Settings

| Variable                        | Default Value    | Description                                                                                                                                             |
|---------------------------------|------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| $wgEmbedVideoAddFileExtensions  | true             | Boolean - Enable or disable adding video/audio file extensions to the list of allowable files to be uploaded.                                           |
| $wgEmbedVideoEnableVideoHandler | true             | Boolean - Enable or disable the video media handlers for displaying embedded video in articles.                                                         |
| $wgEmbedVideoEnableAudioHandler | true             | Boolean - Enable or disable the audio media handlers for displaying embedded audio in articles.                                                         |
| $wgEmbedVideoDefaultWidth       |                  | Integer - Globally override the default width of video players. When not set this uses the video service's default width which is typically 640 pixels. |
| $wgEmbedVideoMinWidth           |                  | Integer - Minimum width of video players. Widths specified below this value will be automatically bounded to it.                                        |
| $wgEmbedVideoMaxWidth           |                  | Integer - Maximum width of video players. Widths specified above this value will be automatically bounded to it.                                        |
| $wgFFprobeLocation              | /usr/bin/ffprobe | String - Set the location of the ffprobe binary.                                                                                                        |
| $wgEmbedVideoEnabledServices    |                  | Array - Array of service names that are allowed, if empty all services are available.                                                                   |
| $wgEmbedVideoRequireConsent     | true             | Boolean - Set to true to _only_ load the iframe if the user clicks it.                                                                                  |

# Credits

The original version of EmbedVideo was written by Jim R. Wilson.  Additional major upgrades made by Andrew Whitworth, Alexia E. Smith, and other contributors.

See CREDITS for details
