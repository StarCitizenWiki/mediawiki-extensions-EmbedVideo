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
				'innerClass' => 'embedvideo-wrapper',
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
				<picture class="embedvideo-thumbnail">
					<img src="{$url}" loading="lazy" class="embedvideo-thumbnail__image" alt="Thumbnail for {$service->getTitle()}"/>
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

		return sprintf( '<div class="embedvideo-loader__title">%s</div>', $service->getTitle() );
	}

	/**
	 * Generates the HTML consent container used when explicit consent is activated in the settings
	 *
	 * @param AbstractEmbedService $service
	 * @return string
	 */
	public static function makeConsentContainerHtml( AbstractEmbedService $service ): string {
		$template = <<<HTML
<div class="embedvideo-consent" data-show-privacy-notice="%s">%s<!--
--><div class="embedvideo-overlay"><!--
	--><div class="embedvideo-loader" role="button">%s<!--
		--><div class="embedvideo-loader__fakeButton">%s</div><!--
		--><div class="embedvideo-loader__footer"><!--
			--><div class="embedvideo-loader__service">%s</div><!--
		--></div><!--
	--></div><!--
	--><div class="embedvideo-privacyNotice hidden"><!--
		--><div class="embedvideo-privacyNotice__content">%s%s</div><!--
		--><div class="embedvideo-privacyNotice__buttons"><!--
			--><button class="embedvideo-privacyNotice__continue">%s</button><!--
			--><button class="embedvideo-privacyNotice__dismiss">%s</button><!--
		--></div><!--
	--></div><!--
--></div><!--
--></div>
HTML;

		$showPrivacyNotice = false;
		try {
			$showPrivacyNotice = MediaWikiServices::getInstance()
				->getConfigFactory()
				->makeConfig( 'EmbedVideo' )
				->get( 'EmbedVideoShowPrivacyNotice' );
		} catch ( ConfigException $e ) {
			//
		}

		$contentTypeMsg = new Message( 'embedvideo-type-' . $service::getContentType() );

		return sprintf(
			$template,
			// data-show-privacy-notice
			$showPrivacyNotice,
			// -thumbnail
			self::makeThumbHtml( $service ),
			// -loader__title
			self::makeTitleHtml( $service ),
			// -loader__fakeButton content
			( new Message( 'embedvideo-load', [ $contentTypeMsg ] ) )->text(),
			// -loader__service content
			$service::getServiceNiceName(),
			// -privacyNotice text
			( new Message( 'embedvideo-consent-privacy-notice-text', [ $service::getServiceNiceName() ] ) )->text(),
			// -privacyNotice link to Privacy Policy (may be empty)
			self::makePrivacyPolicyLink( $service ),
			// -continue
			( new Message( 'embedvideo-consent-privacy-notice-continue' ) )->text(),
			// -dismiss
			( new Message( 'embedvideo-consent-privacy-notice-dismiss' ) )->text()
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
			$privacyUrl = sprintf(
				' <a href="%s" rel="nofollow,noopener" target="_blank" class="embedvideo-privacyNotice__link">%s</a>',
				$privacyUrl,
				( new Message( 'embedvideo-consent-privacy-policy' ) )->text()
			);
		}

		return $privacyUrl ?? '';
	}
}
