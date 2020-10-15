<?php
/**
 * EmbedVideo
 * VideoHandler Class
 *
 * @author  Alexia E. Smith
 * @license MIT
 * @package EmbedVideo
 * @link    https://www.mediawiki.org/wiki/Extension:EmbedVideo
 **/

namespace EmbedVideo;

class VideoHandler extends AudioHandler {
	/**
	 * Validate a thumbnail parameter at parse time.
	 * Return true to accept the parameter, and false to reject it.
	 * If you return false, the parser will do something quiet and forgiving.
	 *
	 * @access public
	 * @param  string $name
	 * @param  mixed  $value
	 */
	public function validateParam($name, $value) {
		if ($name === 'width' || $name === 'height') {
			return $value > 0;
		}
		return parent::validateParam($name, $value);
	}

	/**
	 * Changes the parameter array as necessary, ready for transformation.
	 * Should be idempotent.
	 * Returns false if the parameters are unacceptable and the transform should fail
	 *
	 * @access public
	 * @param  object	File
	 * @param  array	Parameters
	 * @return boolean	Success
	 */
	public function normaliseParams($file, &$parameters) {
		parent::normaliseParams($file, $parameters);

		// Note: MediaHandler declares getImageSize with a local path, but we don't need it here.
		list($width, $height) = $this->getImageSize($file, '');

		if ($width === 0 && $height === 0) {
			// Force a reset.
			$width = 640;
			$height = 360;
		}

		if (isset($parameters['width']) && isset($parameters['height']) && $parameters['width'] > 0 && $parameters['height'] === $parameters['width']) {
			// special allowance for square video embeds needed by some wikis, otherwise forced 16:9 ratios are followed.
			return true;
		}

		if (isset($parameters['width']) && $parameters['width'] > 0 && $parameters['width'] < $width) {
			$parameters['width'] = intval($parameters['width']);

			if (!isset($parameters['height'])) {
				// Page embeds do not specify thumbnail height so correct it here based on aspect ratio.
				$parameters['height'] = round($height / $width * $parameters['width']);
			}
		} else {
			$parameters['width'] = $width;
		}

		if (isset($parameters['height']) && $parameters['height'] > 0 && $parameters['height'] < $height) {
			$parameters['height'] = intval($parameters['height']);
		} else {
			$parameters['height'] = $height;
		}

		if ($width > 0 && $parameters['width'] > 0) {
			if (($height / $width) != ($parameters['height'] / $parameters['width'])) {
				$parameters['height'] = round($height / $width * $parameters['width']);
			}
		}

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
	 * @access public
	 * @param  \File   $file The file object, or false if there isn't one
	 * @param  string $path  The filename
	 * @return mixed	An array following the format of PHP getimagesize() internal function or false if not supported.
	 */
	public function getImageSize($file, $path) {
		$probe = new FFProbe($file);

		$stream = $probe->getStream("v:0");

		if ($stream !== false) {
			return [$stream->getWidth(), $stream->getHeight(), 0, "width=\"{$stream->getWidth()}\" height=\"{$stream->getHeight()}\"", 'bits' => $stream->getBitDepth()];
		}
		return [0, 0, 0, 'width="0" height="0"', 'bits' => 0];
	}

	/**
	 * Get a MediaTransformOutput object representing the transformed output. Does the
	 * transform unless $flags contains self::TRANSFORM_LATER.
	 *
	 * @param  \File   $file    The image object
	 * @param  string  $dstPath Filesystem destination path
	 * @param  string  $dstUrl  Destination URL to use in output HTML
	 * @param  array   $params  Arbitrary set of parameters validated by $this->validateParam()
	 *                          Note: These parameters have *not* gone through
	 *                          $this->normaliseParams()
	 * @param  integer $flags   A bitfield, may contain self::TRANSFORM_LATER
	 * @return \MediaTransformOutput
	 */
	public function doTransform($file, $dstPath, $dstUrl, $params, $flags = 0) {
		$this->normaliseParams($file, $params);

		if (!($flags & self::TRANSFORM_LATER)) {
			// @TODO: Thumbnail generation here.
		}

		return new VideoTransformOutput($file, $params);
	}

	/**
	 * Shown in file history box on image description page.
	 *
	 * @access public
	 * @param  \File $file
	 * @return string	Dimensions
	 */
	public function getDimensionsString($file) {
		global $wgLang;

		$probe = new FFProbe($file);

		$format = $probe->getFormat();
		$stream = $probe->getStream("v:0");

		if ($format === false || $stream === false) {
			return parent::getDimensionsString($file);
		}

		return wfMessage('ev_video_short_desc', $wgLang->formatTimePeriod($format->getDuration()), $stream->getWidth(), $stream->getHeight())->text();
	}

	/**
	 * Short description. Shown on Special:Search results.
	 *
	 * @access public
	 * @param  \File $file
	 * @return string
	 */
	public function getShortDesc($file) {
		global $wgLang;

		$probe = new FFProbe($file);

		$format = $probe->getFormat();
		$stream = $probe->getStream("v:0");

		if ($format === false || $stream === false) {
			return parent::getGeneralShortDesc($file);
		}

		return wfMessage('ev_video_short_desc', $wgLang->formatTimePeriod($format->getDuration()), $stream->getWidth(), $stream->getHeight(), $wgLang->formatSize($file->getSize()))->text();
	}

	/**
	 * Long description. Shown under image on image description page surounded by ().
	 *
	 * @access public
	 * @param  \File $file
	 * @return string
	 */
	public function getLongDesc($file) {
		global $wgLang;

		$probe = new FFProbe($file);

		$format = $probe->getFormat();
		$stream = $probe->getStream("v:0");

		if ($format === false || $stream === false) {
			return parent::getGeneralLongDesc($file);
		}

		$extension = pathinfo($file->getLocalRefPath(), PATHINFO_EXTENSION);

		return wfMessage('ev_video_long_desc', strtoupper($extension), $stream->getCodecName(), $wgLang->formatTimePeriod($format->getDuration()), $stream->getWidth(), $stream->getHeight(), $wgLang->formatBitrate($format->getBitRate()))->text();
	}
}
