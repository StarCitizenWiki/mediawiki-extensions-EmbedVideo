<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\TransformOutput;

class ExternalVideoTransformOutput extends VideoTransformOutput {
	/**
	 * Allow to overwrite the url that is else taken from the passed File
	 *
	 * @param string $url
	 * @return void
	 */
	public function setUrl( string $url ) {
		$this->url = $url;
	}
}
