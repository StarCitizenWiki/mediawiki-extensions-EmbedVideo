<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic;
use MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum;
use MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist;
use MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist;
use MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerTrack;
use MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum;
use MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack;
use MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyAlbum;
use MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist;
use MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyEpisode;
use MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyPlaylist;
use MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyShow;
use MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyTrack;
use MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalAlbum;
use MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalMix;
use MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack;
use MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo;
use MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\Twitch;
use MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip;
use MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeOEmbed;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubePlaylist;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList;

final class EmbedServiceFactory {

	/**
	 * List of all available services
	 *
	 * @var AbstractEmbedService[]
	 */
	private static $availableServices = [
		AmazonMusic::class,
		AppleMusicAlbum::class,
		AppleMusicArtist::class,
		AppleMusicTrack::class,
		AppleMusicPlaylist::class,
		ArchiveOrg::class,
		Bandcamp::class,
		Bilibili::class,
		Aparat::class,
		Ccc::class,
		DailyMotion::class,
		Reddit::class,
		Podbean::class,
		DeezerAlbum::class,
		DeezerArtist::class,
		DeezerEpisode::class,
		DeezerPlaylist::class,
		DeezerShow::class,
		DeezerTrack::class,
		ExternalVideo::class,
		KakaoTV::class,
		Loom::class,
		NaverTV::class,
		Niconico::class,
		QobuzAlbum::class,
		QobuzTrack::class,
		SharePoint::class,
		SoundCloud::class,
		SpotifyAlbum::class,
		SpotifyArtist::class,
		SpotifyEpisode::class,
		SpotifyShow::class,
		SpotifyTrack::class,
		SpotifyPlaylist::class,
		TakhteSefid::class,
		TidalAlbum::class,
		TidalMix::class,
		TidalTrack::class,
		TidalVideo::class,
		Substack::class,
		Twitch::class,
		TwitchClip::class,
		TwitchVod::class,
		VideoLink::class,
		Vimeo::class,
		Vk::class,
		Wistia::class,
		YouTube::class,
		YouTubeOEmbed::class,
		YouTubePlaylist::class,
		YouTubeVideoList::class,
		Youku::class,
	];

	/**
	 * @param string $serviceName
	 * @param string $id
	 * @return AbstractEmbedService
	 */
	public static function newFromName( string $serviceName, string $id ): AbstractEmbedService {
		switch ( strtolower( $serviceName ) ) {
			case 'amazonmusic':
				return new AmazonMusic( $id );

			case 'applemusicalbum':
				return new AppleMusicAlbum( $id );

			case 'applemusicartist':
				return new AppleMusicArtist( $id );

			case 'applemusic':
			case 'applemusictrack':
				return new AppleMusicTrack( $id );

			case 'applemusicplaylist':
				return new AppleMusicPlaylist( $id );

			case 'archive':
			case 'archiveorg':
			case 'archive.org':
				return new ArchiveOrg( $id );

			case 'aparat':
				return new Aparat( $id );

			case 'bandcamp':
				return new Bandcamp( $id );

			case 'bilibili':
			case 'player.bilibili':
				return new Bilibili( $id );

			case 'ccc':
			case 'media.ccc':
			case 'media.ccc.de':
				return new Ccc( $id );

			case 'dailymotion':
				return new DailyMotion( $id );

			case 'deezeralbum':
				return new DeezerAlbum( $id );

			case 'deezerartist':
				return new DeezerArtist( $id );

			case 'deezerplaylist':
				return new DeezerPlaylist( $id );

			case 'deezer':
			case 'deezertrack':
				return new DeezerTrack( $id );

			case 'deezerpodcast':
			case 'deezershow':
				return new DeezerShow( $id );

			case 'deezerpodcastepisode':
			case 'deezerepisode':
				return new DeezerEpisode( $id );

			case 'external':
			case 'externalvideo':
				return new ExternalVideo( $id );

			case 'kakaotv':
			case 'play-tv.kakao':
				return new KakaoTV( $id );

			case 'loom':
				return new Loom( $id );
			
			case 'qobuzalbum':
				return new QobuzAlbum( $id );

			case 'qobuz':
			case 'qobuztrack':
				return new QobuzTrack( $id );
			
			case 'reddit':
			case 'reddit.com':
			case 'www.reddit':
			case 'embed.reddit':
				return new Reddit( $id );

			case 'podbean':
				return new Podbean( $id );

			case 'nicovideo':
			case 'niconico':
			case 'embed.nicovideo':
				return new Niconico( $id );

			case 'navertv':
			case 'tv.naver':
				return new NaverTV( $id );

			case 'sharepoint':
				return new SharePoint( $id );

			case 'soundcloud':
				return new SoundCloud( $id );

			case 'spotifyalbum':
				return new SpotifyAlbum( $id );

			case 'spotifyartist':
				return new SpotifyArtist( $id );

			case 'spotify':
			case 'spotifytrack':
				return new SpotifyTrack( $id );

			case 'spotifypodcast':
			case 'spotifyshow':
				return new SpotifyShow( $id );

			case 'spotifypodcastepisode':
			case 'spotifyepisode':
				return new SpotifyEpisode( $id );

			case 'spotifyplaylist':
				return new SpotifyPlaylist( $id );

			case 'tidalalbum':
				return new TidalAlbum( $id );

			case 'tidalmix':
				return new TidalMix( $id );

			case 'tidal':
			case 'tidaltrack':
				return new TidalTrack( $id );

			case 'tidalvideo':
				return new TidalVideo( $id );

			case 'substack':
				return new Substack( $id );

			case 'takhtesefid':
				return new TakhteSefid( $id );

			case 'twitch':
				return new Twitch( $id );

			case 'twitchclip':
				return new TwitchClip( $id );

			case 'twitchvod':
				return new TwitchVod( $id );

			case 'videolink':
				return new VideoLink( $id );

			case 'vimeo':
				return new Vimeo( $id );

			case 'vk':
			case 'vkvideo':
				return new Vk( $id );

			case 'wistia':
				return new Wistia( $id );

			case 'youtubeoembed':
				return new YouTubeOEmbed( $id );

			case 'youtube':
				return new YouTube( $id );

			case 'youtubeplaylist':
				return new YouTubePlaylist( $id );

			case 'youtubevideolist':
				return new YouTubeVideoList( $id );
			case 'youku':
				return new Youku( $id );

			default:
				throw new InvalidArgumentException( sprintf( 'VideoService "%s" not recognized.', $serviceName ) );
		}
	}

	/**
	 * @return AbstractEmbedService[]
	 */
	public static function getAvailableServices(): array {
		return self::$availableServices;
	}
}
