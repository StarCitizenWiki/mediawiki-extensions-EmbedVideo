<?php
/**
 * EmbedVideo
 * FFProbe
 *
 * @author		Alexia E. Smith
 * @license		MIT
 * @package		EmbedVideo
 * @link		https://www.mediawiki.org/wiki/Extension:EmbedVideo
 *
 **/

namespace EmbedVideo;

class FFProbe {
	/**
	 * File Location
	 *
	 * @var		string
	 */
	private $file;

	/**
	 * Meta Data Cache
	 *
	 * @var		array
	 */
	private $metadata = null;

	/**
	 * Main Constructor
	 *
	 * @access	public
	 * @param	string	File Location on Disk
	 * @return	void
	 */
	public function __construct($file) {
		$this->file = $file;
	}

	/**
	 * Return the entire cache of meta data.
	 *
	 * @access	public
	 * @return	array	Meta Data
	 */
	public function getMetaData() {
		if (!is_array($this->metadata)) {
			$this->invokeFFProbe();
		}
		return $this->metadata;
	}

	/**
	 * Get a selected stream.  Follows ffmpeg's stream selection style.
	 *
	 * @access	public
	 * @param	string	Stream identifier
	 * Examples:
	 *		"v:0" - Select the first video stream
	 * 		"a:1" - Second audio stream
	 * 		"i:0" - First stream, whatever it is.
	 * 		"s:2" - Third subtitle
	 * 		"d:0" - First generic data stream
	 * 		"t:1" - Second attachment
	 * @return	mixed	StreamInfo object or false if does not exist.
	 */
	public function getStream($select) {
		$this->getMetaData();

		$types = [
			'v'	=> 'video',
			'a'	=> 'audio',
			'i'	=> false,
			's'	=> 'subtitle',
			'd'	=> 'data',
			't'	=> 'attachment'
		];

		if (!isset($this->metadata['streams'])) {
			return false;
		}

		list($type, $index) = explode(":", $select);
		$index = intval($index);

		$type = (isset($types[$type]) ? $types[$type] : false);

		$i = 0;
		foreach ($this->metadata['streams'] as $stream) {
			if ($type !== false && isset($stream['codec_type'])) {
				if ($index === $i && $stream['codec_type'] === $type) {
					return new StreamInfo($stream);
				}
			}
			if ($type === false || $stream['codec_type'] === $type) {
				$i++;
			}
		}
		return false;
	}

	/**
	 * Get the FormatInfo object.
	 *
	 * @access	public
	 * @return	mixed	FormatInfo object or false if does not exist.
	 */
	public function getFormat() {
		$this->getMetaData();

		if (!isset($this->metadata['format'])) {
			return false;
		}

		return new FormatInfo($this->metadata['format']);
	}

	/**
	 * Invoke ffprobe on the command line.
	 *
	 * @access	private
	 * @return	boolean	Success
	 */
	private function invokeFFProbe() {
		global $wgFFprobeLocation;

		if (!file_exists($wgFFprobeLocation)) {
			$this->metadata = [];
			return false;
		}

		$json = shell_exec(escapeshellcmd($wgFFprobeLocation.' -v quiet -print_format json -show_format -show_streams ').escapeshellarg($this->file));

		$metadata = @json_decode($json, true);

		if (is_array($metadata)) {
			$this->metadata = $metadata;
		} else {
			$this->metadata = [];
			return false;
		}
		return true;
	}
}

class StreamInfo {
	/**
	 * Stream Info
	 *
	 * @var		array
	 */
	private $info = null;

	/**
	 * Main Constructor
	 *
	 * @access	public
	 * @param	array	Stream Info from FFProbe
	 * @return	void
	 */
	public function __construct($info) {
		$this->info = $info;
	}

	/**
	 * Simple helper instead of repeating an if statement everything.
	 *
	 * @access	private
	 * @param	string	Field Name
	 * @return	void
	 */
	private function getField($field) {
		return (isset($this->info[$field]) ? $this->info[$field] : false);
	}

	/**
	 * Return the codec type.
	 *
	 * @access	public
	 * @return 	string	Codec type or false if unavailable.
	 */
	public function getType() {
		return $this->getField('codec_type');
	}

	/**
	 * Return the codec name.
	 *
	 * @access	public
	 * @return 	string	Codec name or false if unavailable.
	 */
	public function getCodecName() {
		return $this->getField('codec_name');
	}

	/**
	 * Return the codec long name.
	 *
	 * @access	public
	 * @return 	string	Codec long name or false if unavailable.
	 */
	public function getCodecLongName() {
		return $this->getField('codec_long_name');
	}

	/**
	 * Return the width of the stream.
	 *
	 * @access	public
	 * @return 	integer	Width or false if unavailable.
	 */
	public function getWidth() {
		return $this->getField('width');
	}

	/**
	 * Return the height of the stream.
	 *
	 * @access	public
	 * @return 	integer	Height or false if unavailable.
	 */
	public function getHeight() {
		return $this->getField('height');
	}

	/**
	 * Return bit depth for a video or thumbnail.
	 *
	 * @access	public
	 * @return 	integer	Bit Depth or false if unavailable.
	 */
	public function getBitDepth() {
		return $this->getField('bits_per_raw_sample');
	}

	/**
	 * Get the duration in seconds.
	 *
	 * @access	public
	 * @return 	mixed	Duration in seconds or false if unavailable.
	 */
	public function getDuration() {
		return $this->getField('duration');
	}

	/**
	 * Bit rate in bPS.
	 *
	 * @access	public
	 * @return 	mixed	Bite rate in bPS or false if unavailable.
	 */
	public function getBitRate() {
		return $this->getField('bit_rate');
	}
}

class FormatInfo {
	/**
	 * Format Info
	 *
	 * @var		array
	 */
	private $info = null;

	/**
	 * Main Constructor
	 *
	 * @access	public
	 * @param	array	Format Info from FFProbe
	 * @return	void
	 */
	public function __construct($info) {
		$this->info = $info;
	}

	/**
	 * Simple helper instead of repeating an if statement everything.
	 *
	 * @access	private
	 * @param	string	Field Name
	 * @return	void
	 */
	private function getField($field) {
		return (isset($this->info[$field]) ? $this->info[$field] : false);
	}

	/**
	 * Get the file path.
	 *
	 * @access	public
	 * @return 	mixed	File path or false if unavailable.
	 */
	public function getFilePath() {
		return $this->getField('filename');
	}

	/**
	 * Get the duration in seconds.
	 *
	 * @access	public
	 * @return 	mixed	Duration in seconds or false if unavailable.
	 */
	public function getDuration() {
		return $this->getField('duration');
	}

	/**
	 * Bit rate in bPS.
	 *
	 * @access	public
	 * @return 	mixed	Bite rate in bPS or false if unavailable.
	 */
	public function getBitRate() {
		return $this->getField('bit_rate');
	}
}