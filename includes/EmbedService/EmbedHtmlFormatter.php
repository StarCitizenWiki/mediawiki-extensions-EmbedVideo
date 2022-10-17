<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

use ConfigException;
use Exception;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Extension\EmbedVideo\OEmbed;
use MediaWiki\MediaWikiServices;
use Message;
use UnexpectedValueException;

final class EmbedHtmlFormatter {
	/**
	 * Builds the complete HTML output for a service in question
	 *
	 * This is called by:
	 * @see EmbedVideo::output()
	 *
	 * @param AbstractEmbedService $service
	 * @param array $config Array containing the following keys:
	 * outerClass: String - Class added to the thumb div,
	 * class: String - Class added to the div following .thumb,
	 * style: String - CSS Style added to the div,
	 * innerClass: String - Class added to the inner div,
	 * withConsent: Boolean - Whether to add the consent HTML,
	 * description: String - Optional Description
	 * @return string
	 */
	public static function toHtml( AbstractEmbedService $service, array $config = [] ): string {
		if ( $service instanceof OEmbedServiceInterface ) {
			return self::makeIframe( $service );
		}

		$width = (int)$service->getWidth();
		$widthPad = $width + 8;

		$config = array_merge(
			[
				'outerClass' => 'embedvideo',
				'class' => 'embedvideo thumbinner',
				'style' => '',
				'innerClass' => 'embedvideowrap',
				'service' => '',
				'withConsent' => false,
				'description' => '',
			],
			$config
		);

		$caption = !empty( $config['description'] ?? '' )
			? sprintf( '<div class="thumbcaption">%s</div>', $config['description'] )
			: '';

		$template = <<<HTML
			<div class="thumb %s" style="width: %dpx;">
				<div class="%s" style="%s">
					<div class="%s" data-service="%s" style="width: %dpx">%s%s</div>%s
				</div>
			</div>
			HTML;

		return sprintf(
			$template,
			$config['outerClass'] ?? '',
			$widthPad,
			$config['class'] ?? '',
			$config['style'] ?? '',
			$config['innerClass'] ?? '',
			$config['service'] ?? '',
			$width,
			( $config['withConsent'] ?? false ) === true ? self::makeConsentContainerHtml( $service ) : '',
			$service,
			$caption
		);
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
				$srcType = 'data-src';
			}
		} catch ( ConfigException $e ) {
			//
		}

		$attributes[$srcType] = $service->getUrl();

		$out = array_map( static function ( $key, $value ) {
			return sprintf( '%s="%s"', $key, $value );
		}, array_keys( $attributes ), $attributes );

		return sprintf( '<iframe %s></iframe>', implode( ' ', $out ) );
	}

	/**
	 * Generates the html used for embed thumbnails
	 *
	 * @param AbstractEmbedService $service
	 * @return string The final html (can be empty on error or missing data)
	 */
	public static function makeThumbHtml( AbstractEmbedService $service ): string {
		if ( $service->getLocalThumb() === null ) {
			return '';
		}

		try {
			$url = wfExpandUrl( $service->getLocalThumb()->getUrl() );

			// phpcs:disable
			return <<<HTML
				<picture class="embedvideo-consent__thumbnail">
					<img src="{$url}" loading="lazy" class="embedvideo-consent__thumbnail__image" alt="Thumbnail for {$service->getTitle()}"/>
				</picture>
				HTML;
			// phpcs:enable
		} catch ( Exception $e ) {
			return '';
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

		return sprintf( '<div class="embedvideo-consent__title">%s</div>', $service->getTitle() );
	}

	/**
	 * Generates the HTML consent container used when explicit consent is activated in the settings
	 *
	 * @param AbstractEmbedService $service
	 * @return string
	 */
	public static function makeConsentContainerHtml( AbstractEmbedService $service ): string {
		$template = <<<HTML
			<div class="embedvideo-consent">%s
				<div class="embedvideo-consent__overlay%s">%s
					<div class="embedvideo-consent__message">%s</div>
				</div>
			</div>
			HTML;

		$titleHtml = self::makeTitleHtml( $service );

		return sprintf(
			$template,
			self::makeThumbHtml( $service ),
			$titleHtml !== '' ? ' embedvideo-consent__overlay--hastitle' : '',
			$titleHtml,
			( new Message( 'embedvideo-consent-text' ) )->text(),
		);
	}
}
