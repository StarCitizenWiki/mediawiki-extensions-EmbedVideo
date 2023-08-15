<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

use MediaTransformOutput;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput;

/**
 * This faux service takes a local file for use in EmbedHtmlFormatter
 * @see EmbedHtmlFormatter
 */
class LocalVideo extends AbstractEmbedService {
	/**
	 * This is the local video
	 *
	 * @var MediaTransformOutput
	 */
	private $transformOutput;

	/**
	 * Properties set on the local embed
	 * This corresponds to wikitext like '|320px' or '|title=...'
	 *
	 * @var array
	 */
	private $properties;

	/**
	 * @param MediaTransformOutput $transformOutput
	 * @param array $properties
	 */
	public function __construct( MediaTransformOutput $transformOutput, array $properties ) {
		parent::__construct( '' );

		$this->transformOutput = $transformOutput;
		$this->properties = $properties;

		$this->setTitle( $properties['title'] ?? null );
		$poster = $properties['poster'] ?? $properties['cover'] ?? null;
		if ( $poster !== null ) {
			$this->setLocalThumb( $poster );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return (int)$this->transformOutput->getWidth();
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return (int)$this->transformOutput->getHeight();
	}

	/**
	 * Returns a <video> Element
	 *
	 * @return string
	 */
	public function __toString() {
		if ( isset( $this->properties['likeEmbed'] ) ) {
			unset( $this->properties['poster'] );
		}

		return ( new VideoTransformOutput( $this->transformOutput->getFile(), $this->properties ) )->toHtml();
	}
}
