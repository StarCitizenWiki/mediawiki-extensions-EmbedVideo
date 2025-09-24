<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\FFProbe;

class StreamInfo {
	/**
	 * Stream Info
	 *
	 * @var array
	 */
	private $info;

	/**
	 * Main Constructor
	 *
	 * @param array $info Stream Info from FFProbe
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
		return $this->info[$field] ?? false;
	}

	/**
	 * Return the codec type.
	 *
	 * @return string Codec type or false if unavailable.
	 */
	public function getType() {
		return $this->getField( 'codec_type' );
	}

	/**
	 * Return the codec name.
	 *
	 * @return string Codec name or false if unavailable.
	 */
	public function getCodecName() {
		return $this->getField( 'codec_name' );
	}

	/**
	 * Return the codec long name.
	 *
	 * @return string Codec long name or false if unavailable.
	 */
	public function getCodecLongName() {
		return $this->getField( 'codec_long_name' );
	}

	/**
	 * Return the width of the stream.
	 *
	 * @return int Width or false if unavailable.
	 */
	public function getWidth() {
		return $this->getField( 'width' );
	}

	/**
	 * Return the height of the stream.
	 *
	 * @return int Height or false if unavailable.
	 */
	public function getHeight() {
		return $this->getField( 'height' );
	}

	/**
	 * Return bit depth for a stream.
	 *
	 * FFprobe reports bit depth using different fields depending on stream type:
	 * - Video: 'bits_per_raw_sample'
	 * - Audio: 'bits_per_sample'
	 *
	 * @return int|false Bit Depth or false if unavailable.
	 */
	public function getBitDepth() {
		$depth = $this->getField( 'bits_per_raw_sample' );
		if ( $depth === false ) {
			$depth = $this->getField( 'bits_per_sample' );
		}
		return $depth;
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
