# About

The EmbedVideo Extension is a MediaWiki extension which adds a parser function called #ev for embedding video clips from over 22 popular video sharing services in multiple languages and countries.  It also adds video and audio media handlers to support transforming standard `[[File:Example.mp4]]` file links into embedded HTML5 `<video>` and `<audio>` tags.

For more information about EmbedVideo, to download, to contribute, and to report bugs and problems, visit the GitHub project page:

https://github.com/HydraWiki/mediawiki-embedvideo

Issues, bug reports, and feature requests may be created at the issue tracker:

https://github.com/HydraWiki/mediawiki-embedvideo/issues

The MediaWiki extension page is located at:

https://www.mediawiki.org/wiki/Extension:EmbedVideo

## History

The original version of EmbedVideo was created by Jim R. Wilson.  That version was later forked by Mohammed Derakhshani as the EmbedVideoPlus extension.  In early 2010 Andrew Whitworth took over active maintenance of both extensions and merged them together as "EmbedVideo".  Much later on in September 2014 Alexia E. Smith forcefully took over being unable to contact a current maintainer.

The newer versions of EmbedVideo are intended to be fully backwards-compatible with both older EmbedVideo and EmbedVideoPlus extensions.

# License

EmbedVideo is released under the MIT license

http://www.opensource.org/licenses/mit-license.php

See LICENSE for more details

# Installation

## Download

There are three places to download the EmbedVideo extension. The first is directly from its GitHub project page, where active development takes place.  If you have git, you can use this incantation to check out a read-only copy of the extension source:

```
git clone https://github.com/HydraWiki/mediawiki-embedvideo.git
```

Downloadable archive packages for numbered releases will also be available from the github project page.

## Installation Instructions

1. Download the contents of the extension, as outlined above.
2. Create an EmbedVideo folder in the extensions/ folder of your MediaWiki installation.
3. Copy the contents of this distribution into that folder

For Mediawiki 1.19 through 1.23 add the following line to your LocalSettings.php:

```php
require_once("$IP/extensions/EmbedVideo/EmbedVideo.php");
```

For Mediawiki 1.24 and up add the following line to your LocalSettings.php:

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

### \#evt - Parser Tag for Templates

The \#evt parser tag allows for key=value pairs which allows for easier templating and readability.

    {{#evt:
    service=youtube
    |id=https://www.youtube.com/watch?v=pSsYTj9kCHE
    |alignment=right
    }}

### \#evu - Parser Tag for URLs

The \#evu parser tag is like the \#evt tag, but its first parameter is a URL that will be parsed to determine the service automatically.

	{{#evu:https://www.youtube.com/watch?v=pSsYTj9kCHE
	|alignment=right
	}}

### &lt;embedvideo&gt; - Tag Hook

Videos can easily be embedded with the &lt;embedvideo&gt;&lt;/embedvideo&gt; tag hook. The ID/URL goes as the input between the tags and parameters can be added as the tag arguments.

    <embedvideo service="youtube">https://www.youtube.com/watch?v=pSsYTj9kCHE</embedvideo>


Alternativly, you can also use the service id as the tag (assuming another extension isn't already using this tag).

    <youtube>https://www.youtube.com/watch?v=pSsYTj9kCHE</youtube>

## Attributes for Parser Tags

| Attribute                                   | Required | Default | Description                                                                                                                                                                                      |
|---------------------------------------------|----------|---------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `service="(See Supported Services below.)"` | yes      |         | The video service to call.                                                                                                                                                                       |
| `id="[id|url]"`                             | yes      |         | The raw ID of the video or URL from the player page.                                                                                                                                             |
| `dimensions="[WIDTH|WIDTHxHEIGHT|xHEIGHT]"` | no       | 640     | Dimensions in pixels to size the embed container. The standard format is width x height where either can be omitted, but the `x` must proceed height to indicate it as the height.<br/>Examples: `480`, `480x320`, `x320`. If the height is not provided it will be calculated automatically from the width and service default ratio.<br/>Some services such as *Gfycat* do not have standard heights and should be specified for each embed. `$wgEmbedVideoDefaultWidth` can be set in `LocalSettings.php` to override the default width. |
| `alignment="[left|center|right|inline]"`    | no       | none    | Align the placement of the video either to the left, centered, or to the right. Inline will allow multiple videos to display side by side without forced line breaks.                            |
| `description="[wiki text]"`                 | no       | none    | Display a description under the embed container.                                                                                                                                                 |
| `container="[frame]"`                       | no       | none    | Specifies the container type to use for the embed.<br/>`frame`: Wrap the video player in a Mediawiki thumbnail box.                                                                              |
| `urlargs="modestbranding=1&version=3"`      | no       | none    | Allows extra URL arguments to be appended to the generated embed URL. This is useful for obscure options only supported on one service.                                                          |
| `autoresize="false"`                        | no       | true    | Automatically resize videos when their size will cause them to break outside of their container element                                                                                          |
| `valignment="[top|middle|bottom|baseline]"` | no       | none    | Align the vertical placement of the video either to the top, middle, bottom, or baseline of the parent element.  Using this parameter forces the alignment parameter to be inline.               |

## Examples

### Example #1

For example, a video from YouTube use the 'youtube' service selector enter either the raw ID:

    {{#ev:youtube|pSsYTj9kCHE}}

Or the full URL:

    {{#ev:youtube|https://www.youtube.com/watch?v=pSsYTj9kCHE}}

### Example #2

To display the same video as a right aligned large thumbnail with a description:

    {{#ev:youtube|https://www.youtube.com/watch?v=pSsYTj9kCHE|1000|right|Let eet GO|frame}}

For YouTube to have the video start at a specific time code utilize the urlargs(URL arguments) parameter. Take the rest of the URL arguments from the custom URL and place them into the urlargs. Please note that not all video services support extra URL arguments or may have different keys for their URL arguments.

    https://www.youtube.com/watch?v=pSsYTj9kCHE&start=76

    {{#ev:youtube|https://www.youtube.com/watch?v=pSsYTj9kCHE|||||start=76}}

# Support for VideoLink Tags

Support for the unmaintained VideoLink extension's tags has been added since version 2.5.

From the original extension documentation:

    The VideoLink extension allows embedding of YouTube videos in articles; allowing for multiple linked videos to be played in a single embedded video player, first shown when a user clicks on a video link.

    The <vplayer /> specifies where the player should appear within the page, and the {{#vlink}} parser function allows creation of links that load a specific video.


### &lt;evlplayer&gt; - Tag Hook for Video Container

_Note that the use of the `<vplayer>` tag is also acceptable here for backwards compatibility._

This evlplayer tag is used to position the video player container within the page.

    <vplayer id="player id" w="width" h="height" class="class" style="style">default content</vplayer>

| Attributes | Required | Default                 | Description                                              |
|------------|----------|-------------------------|----------------------------------------------------------|
| id         | no       | default                 | An optional unique identifier for this container         |
| w          | no       | 800                     | Width to send to the embedded player when its generated  |
| h          | no       | achieve 16:9 from width | Height to send to the embedded player when its generated |
| class      | no       |                         | Additional CSS class to add to the container div         |
| style      | no       |                         | Additional in-line CSS to apply to the container div     |

An important caveat to make note of, is that the `w` and `h` attributes only effect the video that is being included into the container div, and not the actual container. For styling of the container, please use the `class` or `style` attributes.

### \#evl - Parser Function for Video Links

_Note that the use of the `{{#vlink}}` parser function is also acceptable here for backwards compatibility._


    {{#evl:<video id>|<Link text>|service=youtube|player=<player id>}}

In addition to all of the attributes supported by the `#evt` tag, these specific attributes apply to the `#evl` (and `#vlink`) tags. To maintain backwards compatibility, if you do not define a `service` then `youtube` is assumed.

| Attributes    | Required     | Default   | Description                                                                                                                                                                                                                                                                     |
|---------------|--------------|-----------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| video id      | yes          | none      | The ID of the video you would like to play. _Please note that the use of multiple video IDs separated by a semicolon is now deprecated. Please use the proper service for playlists if you would like play multiple videos from a single link_                                  |
| link text     | yes          | none      | The text to display inside the link                                                                                                                                                                                                                                             |
| player        | no           | 'default' | Player container to load video in. _Note that the ID 'default' will only exist if you've defined a player with no ID._                                                                                                                                                          |
| initial video | _deprecated_ |           | In the original VideoLink, this would define what video to play first if multiple videos were define. Please see notes about in `video id` and `start`.                                                                                                                         |
| start         | _deprecated_ | 0:00      | In the original VideoLink, this defined the start time of a video. Since we support multiple video services, this feature can now be replicated with the `urlargs` parameter. For backwards compatibility, this attribute will be respect on videos with the service `youtube`. |


## Supported Services

As of version 2.x, EmbedVideo supports embedding video content from the following services:

| Site                                                     | Service Name(s)                                                                       | ID Example                                                                            | URL Example(s)                                                                                                 |
|----------------------------------------------------------|---------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------|
| [Archive.org Videos](https://archive.org/details/movies) | `archiveorg`                                                                          | electricsheep-flock-244-80000-6                                                       | https://archive.org/details/electricsheep-flock-244-80000-6<br/>https://archive.org/embed/electricsheep-flock-244-80000-6 |
| [Bambuser](http://bambuser.com/)                         | `bambuser` - Broadcasts                                                               | `bambuser_channel` - Channels                                                         | 5262334                                                                                                        |
| [Beam](https://beam.pro/)                                | `beam`                                                                                | RocketBear                                                                            | https://beam.pro/RocketBear                                                                                    |
| [Bing](http://www.bing.com/videos/)                      | `bing`                                                                                | 31ncp9r7l                                                                             | http://www.bing.com/videos/watch/video/adorable-cats-attempt-to-eat-invisible-tuna/31ncp9r7l                   |
| [Blip.tv](http://www.blip.tv/)                           | `blip` - Blip requires the full URL to the video page and does not accept the raw ID. |                                                                                       | http://blip.tv/vinylrewind/review-6864612                                                                      |
| [C3TV](https://media.ccc.de/)                            | `mediacccde`                                                                          | 32c3-7305-quantum_cryptography                                                        | https://media.ccc.de/v/32c3-7305-quantum\_cryptography                                                         |
| [CollegeHumor](http://www.collegehumor.com/)             | `collegehumor`                                                                        | 6875289                                                                               | http://www.collegehumor.com/video/6875289/batman-says-his-goodbyes                                             |
| [Dailymotion](http://www.dailymotion.com/)               | `dailymotion`                                                                         | x1adiiw\_archer-waking-up-as-h-jon-benjamin\_shortfilms                               | http://www.dailymotion.com/video/x1adiiw\_archer-waking-up-as-h-jon-benjamin\_shortfilms                       |
| [Disclose.tv](http://www.disclose.tv/)                   | `disclose`                                                                            | 150781                                                                                | http://www.disclose.tv/action/viewvideo/150781                                                                 |
| [Daum TVPot](http://tvpot.daum.net/)                     | `tvpot` - Obtain the URL or ID from the share menu URL.                               | s9011HdLzYwpLwBodQzCHRB                                                               | http://tvpot.daum.net/v/s9011HdLzYwpLwBodQzCHRB                                                                |
| [Div Share](http://www.divshare.com)                     | `divshare`                                                                            |                                                                                       |                                                                                                                |
| [Edutopia](http://edutopia.org)                          | Edutopia content moved to YouTube. Please use the youtube service selector below.     |                                                                                       |                                                                                                                |
| [FunnyOrDie](http://www.funnyordie.com/)                 | `funnyordie`                                                                          | c61fb67ac9                                                                            | http://www.funnyordie.com/videos/c61fb67ac9/to-catch-a-predator-elastic-heart-edition                          |
| [Gfycat](http://gfycat.com/)                             | `gfycat`                                                                              | BruisedSilentAntarcticfurseal                                                         | http://www.gfycat.com/BruisedSilentAntarcticfurseal                                                            |
| [Hitbox](http://www.hitbox.tv/)                          | `hitbox`                                                                              | Washuu                                                                                | http://www.hitbox.tv/Washuu                                                                                    |
| [JW Player](https://www.jwplayer.com/)                   | `jwplayer`                                                                            | cr5d8nbu-8ZpoNmmJs                                                                    | https://content.jwplatform.com/players/cr5d8nbu-8ZpoNmmJ.html                                                  |
| [Kickstarter](http://www.kickstarter.com/)               | `kickstarter`                                                                         | elanlee/exploding-kittens                                                             | https://www.kickstarter.com/projects/elanlee/exploding-kittens                                                 |
| [Metacafe](http://www.metacafe.com/)                     | `metacafe`                                                                            | 11404579                                                                              | http://www.metacafe.com/watch/11404579/lan\_party\_far\_cry\_4/                                                |
| [Nico Nico Video](http://www.nicovideo.jp/)              | `nico`                                                                                | sm24394325                                                                            | http://www.nicovideo.jp/watch/sm24394325                                                                       |
| [RuTube](http://rutube.ru/)                              | `rutube`                                                                              | b698163ccb67498db74d50cb0f22e556                                                      | http://rutube.ru/video/b698163ccb67498db74d50cb0f22e556/                                                       |
| [SoundCloud](http://soundcloud.com/)                     | `soundcloud`                                                                          |                                                                                       | https://soundcloud.com/skrillex/skrillex-rick-ross-purple-lamborghini                                                     |
| [TeacherTube](http://teachertube.com)                    | `teachertube`                                                                         | 370511                                                                                | http://www.teachertube.com/video/thats-a-noun-sing-along-hd-version-370511                                     |
| [TED Talks](http://www.ted.com/talks/browse/)            | `ted`                                                                                 | bruce\_aylward\_humanity\_vs\_ebola\_the\_winning\_strategies\_in\_a\_terrifying\_war | http://www.ted.com/talks/bruce\_aylward\_humanity\_vs\_ebola\_the\_winning\_strategies\_in\_a\_terrifying\_war |
| [Tubi TV](http://tubitv.com)                             | `tubitv`                                                                              | 318409                                                                                | http://tubitv.com/video/318409                                                                                 |
| [Tudou](http://www.tudou.com/)                           | `tudou`                                                                               | mfQXfumwiew                                                                           | http://www.tudou.com/listplay/mfQXfumwiew.html                                                                 |
| [Twitch](http://www.twitch.tv)                           | `twitch` - Live Streams                                                               | `twitchvod` - Archived Videos on Demand                                               | twitchplayspokemon                                                                                             |
| [Videomaten](http://89.160.51.62/recordme/spelain.htm)   | `videomaten`                                                                          |                                                                                       |                                                                                                                |
| [Vimeo](http://www.vimeo.com)                            | `vimeo`                                                                               | 105035718                                                                             | http://vimeo.com/105035718                                                                                     |
| [Vine](http://vine.co)                                   | `vine`                                                                                | h2B7WMtuX2t                                                                           | https://vine.co/v/h2B7WMtuX2t                                                                                  |
| [Yahoo Screen](http://screen.yahoo.com/)                 | `yahoo`                                                                               | katy-perry-dances-sharks-2015-024409668                                               | https://screen.yahoo.com/videos-for-you/katy-perry-dances-sharks-2015-024409668.html                           |
| [YouTube](http://www.youtube.com/)                       | `youtube` - Single Videos                                                             | `youtubeplaylist` - Playlists                                                         | pSsYTj9kCHE                                                                                                    |
| [Youku](http://www.youku.com/)                           | `youku`                                                                               | XODc3NDgzMTY4                                                                         | http://v.youku.com/v\_show/id\_XODc3NDgzMTY4.html                                                              |

# Configuration Settings

| Variable                        | Default Value    | Description                                                                                                                                             |
|---------------------------------|------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| $wgEmbedVideoAddFileExtensions  | true             | Boolean - Enable or disable adding video/audio file extensions to the list of allowable files to be uploaded.                                           |
| $wgEmbedVideoEnableVideoHandler | true             | Boolean - Enable or disable the video media handlers for displaying embedded video in articles.                                                         |
| $wgEmbedVideoEnableAudioHandler | true             | Boolean - Enable or disable the audio media handlers for displaying embedded audio in articles.                                                         |
| $wgEmbedVideoDefaultWidth       |                  | Integer - Globally override the default width of video players. When not set this uses the video service's default width which is typically 640 pixels. |
| $wgEmbedVideoMinWidth           |                  | Integer - Minimum width of video players. Widths specified below this value will be automatically bounded to it.                                        |
| $wgEmbedVideoMaxWidth           |                  | Integer - Maximum width of video players. Widths specified above this value will be automatically bounded to it.                                        |
| $wgFFmpegLocation               | /usr/bin/ffmpeg  | String - Set the location of the ffmpeg binary.                                                                                                         |
| $wgFFprobeLocation              | /usr/bin/ffprobe | String - Set the location of the ffprobe binary.                                                                                                        |

# Credits

The original version of EmbedVideo was written by Jim R. Wilson.  Additional major upgrades made by Andrew Whitworth, Alexia E. Smith, and other contributors.

See CREDITS for details