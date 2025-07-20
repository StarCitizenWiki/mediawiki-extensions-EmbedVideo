<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

use InvalidArgumentException;
use JsonException;
use MediaTransformOutput;
use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use RuntimeException;
use ThumbnailImage;
use Title;

abstract class AbstractEmbedService {
	/**
	 * Array of attributes that are added to the iframe
	 *
	 * @var array
	 */
	// phpcs:disable Generic.Files.LineLength.TooLong
	protected $iframeAttributes = [
		'class' => 'embedvideo-player',
		'loading' => 'lazy',
		'frameborder' => 0,
		'allow' => 'accelerometer; clipboard-write; encrypted-media; fullscreen; gyroscope; picture-in-picture; autoplay',
	];
	// phpcs:enable Generic.Files.LineLength.TooLong

	/**
	 * Additional attributes that are set on the iframe
	 * This has a precedence over the default attributes
	 *
	 * @var array
	 */
	protected $additionalIframeAttributes = [];

	/**
	 * The id of the targeted embed
	 * E.g. the id of a YouTube video
	 *
	 * @var string
	 */
	protected $id;
	protected $unparsedId;

	/**
	 * Width of the iframe
	 *
	 * @var int
	 */
	protected $width;

	/**
	 * Height of the iframe
	 *
	 * @var int
	 */
	protected $height;

	/**
	 * @var array
	 */
	protected $extraIds = [];

	/**
	 * String array of key value pairs that is added to the embed url
	 * Array is run through http_build_query
	 *
	 * @see http_build_query()
	 *
	 * @var string[]
	 */
	protected $urlArgs = [];

	/**
	 * @var ThumbnailImage|MediaTransformOutput|null
	 */
	protected $localThumb;

	/**
	 * Local title for this embed
	 *
	 * @var string|null
	 */
	protected $title;

	/**
	 * Config object
	 *
	 * @var Config
	 */
	protected static $config;

	/**
	 * AbstractVideoService constructor.
	 * @param string $id
	 * @throws InvalidArgumentException
	 */
	public function __construct( string $id ) {
		if ( self::$config === null ) {
			self::$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'EmbedVideo' );
		}

		$this->unparsedId = $id;
		$this->id = $this->parseVideoID( $id );
	}

	/**
	 * Get the width of the iframe
	 *
	 * @return float|string
	 */
	public function getWidth() {
		return $this->width ?? $this->getDefaultWidth();
	}

	/**
	 * Get the height of the iframe
	 *
	 * @return float|string
	 */
	public function getHeight() {
		return $this->height ?? $this->getDefaultHeight();
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return (string)$this->id;
	}

	/**
	 * Get the embed content type (audio/video)
	 *
	 * @return string
	 */
	public function getContentType(): ?string {
		return 'video';
	}

	/**
	 * An optional link to the services' privacy policy
	 * Shown when explicit consent is activated
	 *
	 * @return string|null
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return null;
	}

	/**
	 * An optional short privacy policy text
	 * Shown when explicit consent is activated
	 *
	 * @return string|null
	 */
	public function getPrivacyPolicyShortText(): ?string {
		return null;
	}

	/**
	 * Parameter that allows autoplaying the embed
	 * Set in the url as &key=value
	 *
	 * @return array
	 */
	public function getAutoplayParameter(): array {
		return [
			'autoplay' => 1,
		];
	}

	/**
	 * Returns the base url for this service.
	 * Specify the location of the id with '%1$s'
	 * E.g.: //www.youtube-nocookie.com/embed/%1$s
	 *
	 * @return string
	 */
	abstract public function getBaseUrl(): string;

	/**
	 * The targeted aspect ratio of the embed
	 * Is used to automatically set the height based on the width
	 *
	 * @return float|null
	 */
	public function getAspectRatio(): ?float {
		if ( $this->width !== null && $this->height !== null ) {
			return $this->width / $this->height;
		}

		return $this->getDefaultWidth() / $this->getDefaultHeight();
	}

	/**
	 * Returns the service name
	 * Unlike getServiceKey(), this can not be mutated so sub-services
	 * can still use different message keys
	 *
	 * @return string
	 */
	final public static function getServiceName(): string {
		return strtolower( substr( static::class, strrpos( static::class, '\\' ) + 1 ) );
	}

	/**
	 * Returns the key for the service, mainly used for messages
	 * Can be overridden when message key does not match the service class
	 * Defaults to the class name
	 *
	 * @return string
	 */
	public function getServiceKey(): string {
		return self::getServiceName();
	}

	/**
	 * The default iframe width if no width is set specified
	 *
	 * @return int
	 */
	public function getDefaultWidth(): int {
		return 640;
	}

	/**
	 * The default iframe height if no height is set specified
	 *
	 * @return int
	 */
	public function getDefaultHeight(): int {
		return 360;
	}

	/**
	 * Array of regexes to validate a given service url
	 *
	 * @return array
	 */
	protected function getUrlRegex(): array {
		return [];
	}

	/**
	 * Array of regexes to validate a given embed id
	 *
	 * @return array
	 */
	protected function getIdRegex(): array {
		return [];
	}

	/**
	 * Returns the full url to the embed
	 *
	 * @return string
	 */
	public function getUrl(): string {
		if ( $this->getUrlArgs() !== false ) {
			return wfAppendQuery(
				sprintf(
					$this->getBaseUrl(),
					$this->getId(),
					...$this->extraIds
				),
				$this->getUrlArgs()
			);
		}

		return sprintf( $this->getBaseUrl(), $this->getId(), ...$this->extraIds );
	}

	/**
	 * Returns an array of Content Security Policy urls for this service.
	 *
	 * @return array
	 */
	public function getCSPUrls(): array {
		return [];
	}

	/**
	 * Set the width of the player. This also will set the height automatically.
	 * Width will be automatically constrained to the minimum and maximum widths.
	 *
	 * @param int|null $width Width of the embed
	 * @return void
	 */
	public function setWidth( $width = null ): void {
		$videoMinWidth = self::$config->get( 'EmbedVideoMinWidth' );
		$videoMaxWidth = self::$config->get( 'EmbedVideoMaxWidth' );
		$videoDefaultWidth = self::$config->get( 'EmbedVideoDefaultWidth' );

		if ( !is_numeric( $width ) ) {
			if ( $width === null && $this->width !== null && $videoDefaultWidth < 1 ) {
				$width = $this->getWidth();
			} else {
				$width = ( $videoDefaultWidth > 0 ? $videoDefaultWidth : 640 );
			}
		} else {
			$width = (int)$width;
		}

		if ( $videoMaxWidth > 0 && $width > $videoMaxWidth ) {
			$width = $videoMaxWidth;
		}

		if ( $videoMinWidth > 0 && $width < $videoMinWidth ) {
			$width = $videoMinWidth;
		}

		$this->width = $width;

		if ( $this->height === null ) {
			$this->setHeight();
		}
	}

	/**
	 * Set the height automatically by a ratio of the width or use the provided value.
	 *
	 * @param int|null $height [Optional] Height Value
	 * @return void
	 */
	public function setHeight( $height = null ): void {
		if ( $height !== null && $height > 0 ) {
			$this->height = (int)$height;
			return;
		}

		$ratio = $this->getAspectRatio() ?? ( 16 / 9 );

		$this->height = round( (int)$this->getWidth() / $ratio );
	}

	/**
	 * Parse the video ID/URL provided.
	 *
	 * @param string $id Video ID/URL
	 * @return string Parsed Video ID or false on failure.
	 * @throws InvalidArgumentException
	 */
	public function parseVideoID( $id ): string {
		$id = trim( $id );
		// URL regexes are put into the array first to prevent cases where the ID regexes might
		// accidentally match an incorrect portion of the URL.
		$regexes = array_merge( $this->getUrlRegex(), $this->getIdRegex() );

		if ( !empty( $regexes ) ) {
			foreach ( $regexes as $regex ) {
				if ( preg_match( $regex, $id, $matches ) ) {
					// Get rid of the full text match.
					array_shift( $matches );

					$id = array_shift( $matches );

					if ( !empty( $matches ) ) {
						$this->extraIds = $matches;
					}

					return $id;
				}
			}

			// If nothing matches and matches are specified then return false for an invalid ID/URL.
			throw new InvalidArgumentException( 'Provided ID could not be validated.' );
		}

		// Service definition has not specified a sanitization/validation regex.
		return $id;
	}

	/**
	 * Return the optional URL arguments.
	 *
	 * @return false|string Http query or false for not set.
	 */
	public function getUrlArgs() {
		if ( !empty( $this->urlArgs ) ) {
			return http_build_query( $this->urlArgs );
		}

		return false;
	}

	/**
	 * Set URL Arguments to optionally add to the embed URL.
	 *
	 * @param array|string $urlArgs Raw Arguments
	 * @return bool Success
	 */
	public function setUrlArgs( $urlArgs ): bool {
		if ( empty( $urlArgs ) ) {
			return true;
		}

		if ( is_array( $urlArgs ) ) {
			$this->urlArgs = array_merge( $this->urlArgs, $urlArgs );
			return true;
		}

		$urlArgs = urldecode( $urlArgs );
		$_args = explode( '&', $urlArgs );
		$arguments = [];

		foreach ( $_args as $rawPair ) {
			[ $key, $value ] = explode( "=", $rawPair, 2 );

			if ( empty( $key ) || ( $value === null || $value === '' ) ) {
				continue;
			}

			$arguments[$key] = htmlentities( $value, ENT_QUOTES );
		}

		$this->urlArgs += $arguments;
		return true;
	}

	/**
	 * Add an attribute to the iframe
	 *
	 * @param string $key Attribute name
	 * @param mixed $value Attribute value
	 */
	public function addIframeAttribute( string $key, $value ): void {
		$this->iframeAttributes[$key] = (string)$value;
	}

	/**
	 * Get the merged list of attributes
	 *
	 * @return array
	 */
	public function getIframeAttributes(): array {
		return array_merge( $this->iframeAttributes, $this->additionalIframeAttributes );
	}

	/**
	 * Set a local filename to be used as the thumbnail for this embed
	 *
	 * @param string $localFileName
	 * @throws InvalidArgumentException When the local file was not found
	 * @throws RuntimeException When the local file could not be transformed
	 */
	public function setLocalThumb( string $localFileName ): void {
		$title = Title::newFromText( $localFileName, NS_FILE );

		if ( $title !== null && $title->exists() ) {
			$coverFile = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
			$transform = $coverFile->transform( [ 'width' => $this->getWidth() ] );

			if ( $transform === false ) {
				throw new RuntimeException( sprintf(
					'Could not transform file "%s".',
					$coverFile->getHashPath() )
				);
			}

			$this->localThumb = $transform;
		} else {
			throw new InvalidArgumentException( sprintf( 'Local file "%s" not found.', $localFileName ) );
		}
	}

	/**
	 * @return ThumbnailImage|MediaTransformOutput|null
	 */
	public function getLocalThumb() {
		return $this->localThumb;
	}

	/**
	 * This title takes precedence over any external fetching
	 *
	 * @param string|null $title
	 */
	public function setTitle( ?string $title ): void {
		if ( ( $title ?? '' ) !== '' ) {
			$this->title = $title;
		}
	}

	/**
	 * @return string|null
	 */
	public function getTitle(): ?string {
		return $this->title;
	}

	/**
	 * @param null|int $width
	 * @param null|int $height
	 * @return string
	 */
	public function getIframeConfig( $width = 0, $height = 0 ): string {
		$attributes = [];
		if ( !empty( $width ) && $width !== $this->getDefaultWidth() ) {
			$attributes['width'] = $width;
		}
		if ( !empty( $height ) && $height !== $this->getDefaultHeight() ) {
			$attributes['height'] = $height;
		}

		$attributes['src'] = $this->getUrl();

		try {
			return json_encode( $attributes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES );
		} catch ( JsonException $e ) {
			return '{"error": "Could not encode iframe config"}';
		}
	}

	/**
	 * A convenience method generating the final HTML from a service
	 *
	 * @return string
	 */
	public function __toString() {
		return EmbedHtmlFormatter::makeIframe( $this );
	}
}
