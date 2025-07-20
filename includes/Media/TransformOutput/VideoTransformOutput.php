<?php
/**
 * EmbedVideo
 * VideoTransformOutput Class
 *
 * @author  Alexia E. Smith
 * @license MIT
 * @package EmbedVideo
 * @link    https://www.mediawiki.org/wiki/Extension:EmbedVideo
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\TransformOutput;

use MediaWiki\FileRepo\File\File;
use MediaWiki\Html\Html;

class VideoTransformOutput extends AudioTransformOutput {

	/**
	 * Main Constructor
	 *
	 * @param File $file
	 * @param array $parameters Parameters for constructing HTML.
	 * @return void
	 */
	public function __construct( $file, $parameters ) {
		parent::__construct( $file, $parameters );

		if ( isset( $parameters['gif'] ) ) {
			$this->parameters['autoplay'] = true;
			$this->parameters['loop'] = true;
			$this->parameters['nocontrols'] = true;
			$this->parameters['muted'] = true;
		}
	}

	/**
	 * Fetch HTML for this transform output
	 *
	 * @param array $options Associative array of options.
	 *
	 * @return string HTML
	 */
	public function toHtml( $options = [] ): string {
		$attrs = [
			'src' => $this->getSrc(),
			'width' => $this->getWidth(),
			'height' => $this->getHeight(),
			'class' => $options['img-class'] ?? $this->parameters['img-class'] ?? false,
			'style' => $this->getStyle( $options ),
			'poster' => $this->parameters['posterUrl'] ?? false,
			'controls' => !isset( $this->parameters['nocontrols'] ),
			'autoplay' => isset( $this->parameters['autoplay'] ),
			'loop' => isset( $this->parameters['loop'] ),
			'muted' => isset( $this->parameters['muted'] ),
		];

		if ( ( $this->parameters['lazy'] ?? false ) === true && !isset( $this->parameters['gif'] ) ) {
			$attrs['preload'] = 'none';
		}

		// See https://web.dev/lazy-loading-video/#video-gif-replacement
		if ( isset( $this->parameters['gif'] ) ) {
			$attrs['playsinline'] = true;
		}

		return Html::rawElement( 'video', $attrs, $this->getDescription() );
	}

	/**
	 * @inheritDoc
	 */
	protected function getStyle( array $options ): string {
		$style = [];
		$style[] = 'max-width: 100%;';
		$style[] = 'max-height: 100%;';

		if ( empty( $options['no-dimensions'] ) ) {
			$style[] = "width: {$this->getWidth()}px;";
			$style[] = "height: {$this->getHeight()}px;";
		}

		if ( !empty( $options['valign'] ) ) {
			$style[] = "vertical-align: {$options['valign']};";
		}

		return implode( ' ', $style );
	}
}
