<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\FFProbe;

class FormatInfo {
	/**
	 * Format Info
	 *
	 * @var array
	 */
	private $info;

	/**
	 * Main Constructor
	 *
	 * @param array $info Format Info from FFProbe
	 * @return void
	 */
	public function __construct( $info ) {
		$this->info = $info;
	}

	/**
	 * Simple helper instead of repeating an if statement everything.
	 *
	 * @param string $field Field Name
	 * @return mixed
	 */
	private function getField( $field ) {
		return ( $this->info[$field] ?? false );
	}

	/**
	 * Get the file path.
	 *
	 * @return mixed File path or false if unavailable.
	 */
	public function getFilePath() {
		return $this->getField( 'filename' );
	}

	/**
	 * Get the duration in seconds.
	 *
	 * @return mixed Duration in seconds or false if unavailable.
	 */
	public function getDuration() {
		return $this->getField( 'duration' );
	}

	/**
	 * Bit rate in bPS.
	 *
	 * @return mixed Bite rate in bPS or false if unavailable.
	 */
	public function getBitRate() {
		return $this->getField( 'bit_rate' );
	}
}
