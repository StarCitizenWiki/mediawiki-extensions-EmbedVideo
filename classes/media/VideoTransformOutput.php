<?php
/**
 * EmbedVideo
 * VideoTransformOutput Class
 *
 * @author  Alexia E. Smith
 * @license MIT
 * @package EmbedVideo
 * @link    https://www.mediawiki.org/wiki/Extension:EmbedVideo
 **/

namespace EmbedVideo;

use Html;

class VideoTransformOutput extends \MediaTransformOutput {
	/** @var array */
	private $parameters;

	/**
	 * Main Constructor
	 *
	 * @access public
	 * @param  \File $file
	 * @param  array $parameters	Parameters for constructing HTML.
	 * @return void
	 */
	public function __construct($file, $parameters) {
		$this->file = $file;
		$this->parameters = $parameters;
		$this->width = (isset($parameters['width']) ? $parameters['width'] : null);
		$this->height = (isset($parameters['height']) ? $parameters['height'] : null);
		$this->path = null;
		$this->lang = false;
		$this->page = $parameters['page'];
		$this->url = $file->getFullUrl();
	}

	/**
	 * Fetch HTML for this transform output
	 *
	 * @access public
	 * @param  array $options Associative array of options. Boolean options
	 *                        should be indicated with a value of true for true, and false or
	 *                        absent for false.
	 *                        alt                Alternate text or caption
	 *                        desc-link          Boolean, show a description link
	 *                        file-link          Boolean, show a file download link
	 *                        custom-url-link    Custom URL to link to
	 *                        custom-title-link  Custom Title object to link to
	 *                        valign             vertical-align property, if the output is an inline element
	 *                        img-class          Class applied to the "<img>" tag, if there is such a tag
	 *
	 * @return string	HTML
	 */
	public function toHtml($options = []) {
		$parameters = $this->parameters;

		$style = [];
		$style[] = "max-width: 100%;";
		$style[] = "max-height: 100%;";
		if (empty($options['no-dimensions'])) {
			$parameters['width'] = $this->getWidth();
			$parameters['height'] = $this->getHeight();
			$style[] = "width: {$this->getWidth()}px;";
			$style[] = "height: {$this->getHeight()}px;";
		}

		if (!empty($options['valign'])) {
			$style[] = "vertical-align: {$options['valign']};";
		}

		if (!empty($options['img-class'])) {
			$class = $options['img-class'];
		}

		if (!isset($parameters['start'])) {
			$parameters['start'] = null;
		}
		if (!isset($parameters['end'])) {
			$parameters['end'] = null;
		}

		$inOut = false;
		if ($parameters['start'] !== $parameters['end']) {
			if ($parameters['start'] !== false) {
				$inOut[] = $parameters['start'];
			}

			if ($parameters['end'] !== false) {
				$inOut[] = $parameters['end'];
			}
		}

		$descLink = Html::element( 'a', [ 'href' => $parameters['descriptionUrl'] ], $parameters['descriptionUrl'] );

		return Html::rawElement( 'video', [
			'src' => $this->url . ($inOut !== false ? '#t=' . implode(',', $inOut) : ''),
			'width' => $this->getWidth(),
			'height' => $this->getHeight(),
			'class' => $class ?? false,
			'style' => $style ? implode( ' ', $style ) : false,
			'controls' => true,
		], $descLink );
	}
}
