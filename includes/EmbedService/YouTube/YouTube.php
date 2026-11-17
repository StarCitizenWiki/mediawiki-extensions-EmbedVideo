<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\YouTube;

use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;

class YouTube extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	protected $additionalIframeAttributes = [
		'modestbranding' => 1,
		'allowfullscreen' => 'true',
		'frameBorder' => 0
	];

	/**
	 * @inheritDoc
	 */
	public function getServiceKey(): string {
		return 'youtube';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#v=([\d\w-]+)(?:&\S+?)?#is',
			'#youtu\.be/([\d\w-]+)#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([\d\w-]+)$#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://www.youtube-nocookie.com/embed/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://www.youtube.com/howyoutubeworks/privacy/';
	}

	/**
	 * With $wgEmbedVideoYouTubeHideRelated, add the IFrame Player API arguments the related-videos
	 * overlay needs. Editor-supplied rel/enablejsapi are kept (never duplicated); origin is always this
	 * wiki's server, because a different origin makes the API ignore the page.
	 *
	 * @inheritDoc
	 */
	public function getUrlArgs() {
		if ( !self::$config->get( 'EmbedVideoYouTubeHideRelated' ) ) {
			return parent::getUrlArgs();
		}

		// editors sometimes type the separators as &amp;, which leaves keys like "amp;enablejsapi"
		$args = [];
		foreach ( $this->urlArgs as $key => $value ) {
			$args[ preg_replace( '/^amp;/', '', (string)$key ) ] = $value;
		}
		$args += [ 'enablejsapi' => 1, 'rel' => 0 ];
		$args['origin'] = MediaWikiServices::getInstance()->getMainConfig()->get( MainConfigNames::CanonicalServer );

		return http_build_query( $args );
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://www.youtube-nocookie.com',
			'https://i.ytimg.com'
		];
	}
}
