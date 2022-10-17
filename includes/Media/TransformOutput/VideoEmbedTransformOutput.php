<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\TransformOutput;

use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter;

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
		$service = new FauxEmbedService( $this, $this->parameters );

		return EmbedHtmlFormatter::toHtml( $service, [
			'outerClass' => 'embedvideo local-embed',
			'service' => 'local-embed',
			'withConsent' => true,
			'description' => $this->parameters['description'] ?? null,
		] );
	}
}
