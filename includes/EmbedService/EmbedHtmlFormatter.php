<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

use Exception;
use JsonException;
use MediaWiki\Config\ConfigException;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Extension\EmbedVideo\OEmbed;
use MediaWiki\Html\Html;
use MediaWiki\Html\TemplateParser;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use UnexpectedValueException;
use Wikimedia\Message\MessageValue;

final class EmbedHtmlFormatter {
	/**
	 * Builds the complete HTML output for a service in question
	 *
	 * This is called by:
	 * @see EmbedVideo::output()
	 *
	 * @param AbstractEmbedService $service
	 * @param array $config Array containing the following keys:
	 * class: String - Class added to the container,
	 * style: String - CSS Style added to the container,
	 * withConsent: Boolean - Whether to add the consent HTML,
	 * description: String - Optional Description
	 * @param array $args - Optional args from the actual parser call
	 * @return string
	 */
	public static function toHtml( AbstractEmbedService $service, array $config = [], array $args = [] ): string {
		if ( $service instanceof OEmbedServiceInterface ) {
			return self::makeIframe( $service );
		}

		$width = (int)$service->getWidth();
		$height = (int)$service->getHeight();
		$hasWidth = $width > 0;
		$hasHeight = $height > 0;

		$config = array_merge(
			[
				'class' => 'embedvideo',
				'style' => '',
				'service' => '',
				'withConsent' => false,
				'withLocalEmbedStyle' => false,
				'autoresize' => false,
				'description' => '',
			],
			$config
		);

		if ( !empty( $config['img-class'] ) ) {
			$config['class'] .= ' ' . $config['img-class'];
		}

		if ( $service instanceof LocalVideo && ( $config['withLocalEmbedStyle'] ?? false ) === true ) {
			$config['class'] .= ' embedvideo--local-embed-style';
		}

		// Detect gallery-like contexts for local videos (packed galleries provide override-* options)
		$isGalleryLike = $service instanceof LocalVideo && (
			isset( $args['override-width'] ) || isset( $args['override-height'] )
		);

		$inlineStyles = [
			'container' => $config['style'] ?? '',
			'wrapper' => '',
		];

		// Force autoresize for gallery-like local embeds to avoid fixed dimensions
		if ( $isGalleryLike ) {
			$config['autoresize'] = true;
		}

		if ( $config['autoresize'] === true ) {
			$config['class'] .= ' embedvideo--autoresize';
			if ( isset( $args['width'] ) && $hasWidth ) {
				$inlineStyles['container'] .= sprintf( 'max-width:%dpx', $width );
			}
			if ( isset( $args['height'] ) && $hasHeight ) {
				$inlineStyles['wrapper'] .= sprintf( 'max-height:%dpx', $height );
			}
		} else {
			// Autoresize does not need inline width and height
			if ( $hasWidth ) {
				$inlineStyles['container'] .= sprintf( 'width:%dpx', $width );
			}

			if ( $hasHeight ) {
				$inlineStyles['wrapper'] .= sprintf( 'height:%dpx', $height );
			}
		}

		$templateArgs = [];

		if ( !empty( $config['description'] ?? '' ) ) {
			$templateArgs['captionHtml'] = $config['description'];
		}

		if ( !( $service instanceof LocalVideo ) || ( $config['withConsent'] ?? false ) === true ) {
			try {
				$consent = MediaWikiServices::getInstance()
					->getConfigFactory()
					->makeConfig( 'EmbedVideo' )
					->get( 'EmbedVideoRequireConsent' );
				if ( $consent === true ) {
					$templateArgs['iframeConfig'] = $service->getIframeConfig( $width, $height );
				}
			} catch ( JsonException | ConfigException $e ) {
				//
			}
		}

		/**
		 * TODO: Sync syntax with core image syntax
		 * @see: https://www.mediawiki.org/wiki/Help:Images
		 *
		 * 1. Make caption/description acts the same as core, any unnamed attribute will become caption
		 * 2. Sync container attribute with core
		 * 3. typeof should be set according to attribute instead of hard-coded
		 */
		/**
		 * @see https://www.mediawiki.org/wiki/Specs/HTML/2.7.0#Audio/Video
		 */
		$templateParser = new TemplateParser( __DIR__ . '/templates' );

		$wrapperContentsHtml = '';
		if ( ( $config['withConsent'] ?? false ) === true ) {
			$wrapperContentsHtml .= self::makeConsentContainerHtml( $service, $templateParser );
		}
		if ( $service instanceof LocalVideo && ( $config['withLocalEmbedStyle'] ?? false ) === true ) {
			$wrapperContentsHtml .= self::makeLocalVideoEmbedStyleHtml( $service );
		}
		if ( $service instanceof LocalVideo ) {
			$wrapperContentsHtml .= $service->renderVideoHtml( $args );
		} else {
			$wrapperContentsHtml .= (string)$service;
		}

		$templateArgs += [
			'class' => $config['class'] ?? '',
			'service' => $config['service'] ?? '',
			'containerStyles' => $inlineStyles['container'],
			'wrapperStyles' => $inlineStyles['wrapper'],
			'wrapperContentsHtml' => $wrapperContentsHtml,
		];
		return $templateParser->processTemplate( 'wrapper', $templateArgs );
	}

	/**
	 * Generates the iframe html
	 *
	 * @param AbstractEmbedService $service
	 * @return string
	 */
	public static function makeIframe( AbstractEmbedService $service ): string {
		if ( $service instanceof OEmbedServiceInterface ) {
			try {
				$data = OEmbed::newFromRequest( $service->getUrl() );
				return $data->getHtml();
			} catch ( UnexpectedValueException $e ) {
				return $e->getMessage();
			}
		}

		$attributes = $service->getIframeAttributes();
		$attributes['width'] = $service->getWidth();
		$attributes['height'] = $service->getHeight();

		$srcType = 'src';
		try {
			$consent = MediaWikiServices::getInstance()
				->getConfigFactory()
				->makeConfig( 'EmbedVideo' )
				->get( 'EmbedVideoRequireConsent' );
			if ( $consent === true ) {
				// Iframe is created through JS
				return '';
			}
		} catch ( ConfigException $e ) {
			//
		}

		$attributes[$srcType] = $service->getUrl();

		return Html::element( 'iframe', $attributes );
	}

	/**
	 * Generates the html used for embed thumbnails
	 *
	 * @param AbstractEmbedService $service
	 * @return string The final html (can be empty on error or missing data)
	 */
	public static function makeThumbHtml( AbstractEmbedService $service ): string {
		$emptyThumb = '';
		$emptyThumbServices = [ LocalVideo::getServiceName(), ExternalVideo::getServiceName() ];
		if ( in_array( $service::getServiceName(), $emptyThumbServices, true ) ) {
			$emptyThumb = Html::element( 'div', [ 'class' => 'embedvideo-thumbnail' ] );
		}

		if ( $service->getLocalThumb() === null ) {
			return $emptyThumb;
		}

		try {
			$url = MediaWikiServices::getInstance()->getUrlUtils()->expand( $service->getLocalThumb()->getUrl() );

			return Html::rawElement(
				'picture',
				[
					'class' => 'embedvideo-thumbnail',
				],
				Html::element(
					'img',
					[
						'src' => $url,
						'loading' => 'lazy',
						'class' => 'embedvideo-thumbnail__image',
						'alt' => 'Thumbnail for ' . ( $service->getTitle() ?? '' ),
					]
				)
			);
		} catch ( Exception $e ) {
			return $emptyThumb;
		}
	}

	/**
	 * Generates the html used for embed titles
	 *
	 * @param AbstractEmbedService $service
	 * @return string The final html (can be empty on error or missing data)
	 */
	public static function makeTitleHtml( AbstractEmbedService $service ): string {
		if ( $service->getTitle() === null ) {
			return '';
		}

		return Html::element(
			'div',
			[ 'class' => 'embedvideo-loader__title embedvideo-loader__title--manual' ],
			$service->getTitle()
		);
	}

	/**
	 * Generates passive local-video embed styling that preserves the embed look without
	 * blocking native playback controls or browser features such as PiP.
	 *
	 * @param AbstractEmbedService $service
	 * @return string
	 */
	public static function makeLocalVideoEmbedStyleHtml( AbstractEmbedService $service ): string {
		return Html::rawElement(
			'div',
			[
				'class' => 'embedvideo-localEmbedStyle',
				'aria-hidden' => 'true',
			],
			Html::rawElement(
				'div',
				[ 'class' => 'embedvideo-overlay' ],
				Html::rawElement(
					'div',
					[ 'class' => 'embedvideo-loader' ],
					self::makeTitleHtml( $service ) .
					Html::element(
						'div',
						[ 'class' => 'embedvideo-loader__fakeButton' ],
						( new Message( 'embedvideo-play' ) )->text()
					) .
					Html::rawElement(
						'div',
						[ 'class' => 'embedvideo-loader__footer' ],
						Html::element(
							'div',
							[ 'class' => 'embedvideo-loader__service' ],
							( new Message( 'embedvideo-service-' . $service->getServiceKey() ) )->text()
						)
					)
				)
			)
		);
	}

	/**
	 * Generates the HTML consent container used when explicit consent is activated in the settings
	 *
	 * @param AbstractEmbedService $service
	 * @param TemplateParser $templateParser
	 * @return string
	 */
	public static function makeConsentContainerHtml(
		AbstractEmbedService $service,
		TemplateParser $templateParser
	): string {
		$showPrivacyNotice = false;
		try {
			$showPrivacyNotice = MediaWikiServices::getInstance()
				->getConfigFactory()
				->makeConfig( 'EmbedVideo' )
				->get( 'EmbedVideoShowPrivacyNotice' );
		} catch ( ConfigException $e ) {
			//
		}

		$serviceNameMsg = MessageValue::new( 'embedvideo-service-' . $service->getServiceKey() );
		$contentTypeMsg = MessageValue::new( 'embedvideo-type-' . $service->getContentType() );

		return $templateParser->processTemplate(
			'consent-container',
			[
				'showPrivacyNotice' => $showPrivacyNotice,
				'thumbnailHtml' => self::makeThumbHtml( $service ),
				'titleHtml' => self::makeTitleHtml( $service ),
				'fakeButtonText' => wfMessage( 'embedvideo-load', $contentTypeMsg )->text(),
				'serviceText' => wfMessage( $serviceNameMsg )->text(),
				'privacyNoticeText' => wfMessage( 'embedvideo-consent-privacy-notice-text', $serviceNameMsg )->text(),
				'privacyPolicyLink' => self::makePrivacyPolicyLink( $service ),
				'continueText' => wfMessage( 'embedvideo-consent-privacy-notice-continue' )->text(),
				'dismissText' => wfMessage( 'embedvideo-consent-privacy-notice-dismiss' )->text(),
			]
		);
	}

	/**
	 * Generates the HTML output for the services privacy url and short privacy text
	 *
	 * @param AbstractEmbedService $service
	 * @return string
	 */
	public static function makePrivacyPolicyLink( AbstractEmbedService $service ): string {
		$privacyUrl = $service->getPrivacyPolicyUrl();
		if ( $privacyUrl !== null ) {
			return Html::element(
				'a',
				[
					'href' => $privacyUrl,
					'rel' => 'nofollow,noopener',
					'target' => '_blank',
					'class' => 'embedvideo-privacyNotice__link',
				],
				wfMessage( 'embedvideo-consent-privacy-policy' )->text()
			);
		}

		return '';
	}
}
