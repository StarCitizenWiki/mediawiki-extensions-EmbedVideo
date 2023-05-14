<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\TransformOutput;

use MediaTransformOutput;
use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter;

/**
 * This faux service takes a local file for use in EmbedHtmlFormatter
 * @see EmbedHtmlFormatter
 */
class FauxEmbedService extends AbstractEmbedService {
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
	public function getAspectRatio(): ?float {
		return $this->transformOutput->getWidth() / $this->transformOutput->getHeight();
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
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [];
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
