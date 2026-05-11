<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\TransformOutput;

use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter;
use MediaWiki\Extension\EmbedVideo\EmbedService\LocalVideo;
use MediaWiki\Html\Html;

class VideoEmbedTransformOutput extends VideoTransformOutput {
	/**
	 * Outputs 'embed like' local files
	 * This essentially replaces the <iframe> element with the local <video> in question
	 *
	 * @param array $options
	 *
	 * @return string HTML
	 */
	public function toHtml( $options = [] ): string {
		$service = new LocalVideo( $this, $this->parameters );

		// Core wraps the output in <figure> for frames / thumbs.
		// This avoids generating a second <figure> which causes unwanted nesting figure elements.
		if ( !empty( $options ) ) {
			$videoHtml = $service->renderVideoHtml( $options );
			$overlayHtml = EmbedHtmlFormatter::makeLocalVideoEmbedStyleHtml( $service );

			return Html::rawElement(
				'div',
				[
					'class' => 'embedvideo-wrapper embedvideo--local-embed-style',
					'style' => 'position: relative;',
				],
				$overlayHtml . $videoHtml
			);
		}

		return EmbedHtmlFormatter::toHtml( $service, [
			'service' => 'local-embed',
			'withConsent' => false,
			'withLocalEmbedStyle' => true,
			'autoresize' => ( $this->parameters['autoresize'] ?? false ) === true,
			'description' => $this->parameters['description'] ?? null,
			'img-class' => $this->parameters['img-class'] ?? null,
		], $options );
	}
}
