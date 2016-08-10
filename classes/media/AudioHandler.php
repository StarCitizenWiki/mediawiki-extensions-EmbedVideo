<?php
/**
 * EmbedVideo
 * AudioHandler Class
 *
 * @author		Alexia E. Smith
 * @license		MIT
 * @package		EmbedVideo
 * @link		https://www.mediawiki.org/wiki/Extension:EmbedVideo
 *
 **/

namespace EmbedVideo;

class AudioHandler extends \MediaHandler {
	/**
	 * Get an associative array mapping magic word IDs to parameter names.
	 * Will be used by the parser to identify parameters.
	 */
	public function getParamMap() {
		return [
			'img_width'	=> 'width',
			'ev_start'	=> 'start',
			'ev_end'	=> 'end'
		];
	}

	/**
	 * Validate a thumbnail parameter at parse time.
	 * Return true to accept the parameter, and false to reject it.
	 * If you return false, the parser will do something quiet and forgiving.
	 *
	 * @access	public
	 * @param	string	$name
	 * @param	mixed	$value
	 */
	public function validateParam($name, $value) {
		if ($name === 'width' || $name === 'width') {
			return $value > 0;
		}

		if ($name === 'start' || $name === 'end') {
			
			return true;
		}
	}

	/**
	 * Merge a parameter array into a string appropriate for inclusion in filenames
	 *
	 * @access	public
	 * @param	array	Array of parameters that have been through normaliseParams.
	 * @return	string
	 */
	public function makeParamString($parameters) {
		//var_dump(__METHOD__);
		//var_dump($parameters);
	}

	/**
	 * Parse a param string made with makeParamString back into an array
	 *
	 * @access	public
	 * @param 	string	The parameter string without file name (e.g. 122px)
	 * @return	mixed	Array of parameters or false on failure.
	 */
	public function parseParamString($string) {
		//var_dump(__METHOD__);
		//var_dump($string);
	}

	/**
	 * Changes the parameter array as necessary, ready for transformation.
	 * Should be idempotent.
	 * Returns false if the parameters are unacceptable and the transform should fail
	 *
	 * @access	public
	 * @param	object	File
	 * @param	array	Parameters
	 * @return	boolean	Success
	 */
	public function normaliseParams($file, &$parameters) {
		list($width, $height) = $this->getImageSize($file, $file->getLocalRefPath());

		$parameters['page'] = 1;

		return true;
	}

	/**
	 * Get an image size array like that returned by getimagesize(), or false if it
	 * can't be determined.
	 *
	 * This function is used for determining the width, height and bitdepth directly
	 * from an image. The results are stored in the database in the img_width,
	 * img_height, img_bits fields.
	 *
	 * @note If this is a multipage file, return the width and height of the
	 *  first page.
	 *
	 * @access	public
	 * @param	File	$image The image object, or false if there isn't one
	 * @param	string	$path The filename
	 * @return	mixed	An array following the format of PHP getimagesize() internal function or false if not supported.
	 */
	public function getImageSize($file, $path) {
		return false;
	}

	/**
	 * Get a MediaTransformOutput object representing the transformed output. Does the
	 * transform unless $flags contains self::TRANSFORM_LATER.
	 *
	 * @param	File	$image The image object
	 * @param	string	$dstPath Filesystem destination path
	 * @param	string	$dstUrl Destination URL to use in output HTML
	 * @param	array	$params Arbitrary set of parameters validated by $this->validateParam()
	 *   Note: These parameters have *not* gone through $this->normaliseParams()
	 * @param	integer	$flags A bitfield, may contain self::TRANSFORM_LATER
	 * @return	MediaTransformOutput
	 */
	public function doTransform($file, $dstPath, $dstUrl, $parameters, $flags = 0) {
		$this->normaliseParams($file, $parameters);

		if (!($flags & self::TRANSFORM_LATER)) {
			//@TODO: Thumbnail generation here.
		}

		return new AudioTransformOutput($file, $parameters);
	}

	/**
	 * Shown in file history box on image description page.
	 *
	 * @access	public
	 * @param	File	$file
	 * @return	string	Dimensions
	 */
	public function getDimensionsString($file) {
		global $wgLang;

		$probe = new FFProbe($file->getLocalRefPath());

		$format = $probe->getFormat();
		$stream = $probe->getStream("a:0");

		if ($format === false || $stream === false) {
			return parent::getDimensionsString($file);
		}

		return wfMessage('ev_audio_short_desc', $wgLang->formatTimePeriod($format->getDuration()))->text();
	}

	/**
	 * Short description. Shown on Special:Search results.
	 *
	 * @access	public
	 * @param	File	$file
	 * @return	string
	 */
	public function getShortDesc($file) {
		global $wgLang;

		$probe = new FFProbe($file->getLocalRefPath());

		$format = $probe->getFormat();
		$stream = $probe->getStream("a:0");

		if ($format === false || $stream === false) {
			//return self::getGeneralShortDesc($file);
		}

		return wfMessage('ev_audio_short_desc', $wgLang->formatTimePeriod($format->getDuration()), $wgLang->formatSize($file->getSize()))->text();
	}

	/**
	 * Long description. Shown under image on image description page surounded by ().
	 *
	 * @access	public
	 * @param	File	$file
	 * @return	string
	 */
	public function getLongDesc($file) {
		global $wgLang;

		$probe = new FFProbe($file->getLocalRefPath());

		$format = $probe->getFormat();
		$stream = $probe->getStream("a:0");

		if ($format === false || $stream === false) {
			return self::getGeneralLongDesc($file);
		}

		$extension = pathinfo($file->getLocalRefPath(), PATHINFO_EXTENSION);

		return wfMessage('ev_audio_long_desc', strtoupper($extension), $stream->getCodecName(), $wgLang->formatTimePeriod($format->getDuration()), $wgLang->formatBitrate($format->getBitRate()))->text();
	}
}
