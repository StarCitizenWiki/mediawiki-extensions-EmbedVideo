<?php
/**
 * EmbedVideo
 * ApiEmbedVideo class
 *
 * @license MIT
 * @package EmbedVideo
 * @link    https://gitlab.com/hydrawiki/extensions/EmbedVideo
 **/

class ApiEmbedVideo extends ApiBase {
	/**
	 * Execute the API call.
	 */
	public function execute() {
		$getHTML = \EmbedVideoHooks::parseEV(
			null,
			$this->getMain()->getVal('service'),
			$this->getMain()->getVal('id'),
			$this->getMain()->getVal('dimensions'),
			$this->getMain()->getVal('alignment'),
			$this->getMain()->getVal('description'),
			$this->getMain()->getVal('container'),
			$this->getMain()->getVal('urlargs'),
			$this->getMain()->getVal('autoresize'),
			$this->getMain()->getVal('valignment')
		);

		if (is_array($getHTML)) {
			$HTML = $getHTML[0];
		} else {
			$HTML = "Unable to load video from API.";
		}

		$this->getResult()->addValue(null, $this->getModuleName(), ['html' => $HTML]);
		return true;
	}

	/**
	 * Setup the allowed and required parameters
	 *
	 * @return array
	 */
	public function getAllowedParams() {
		return array_merge(parent::getAllowedParams(), [
			'service' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			],
			'id' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			],
			'dimensions' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			],
			'alignment' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			],
			'description' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			],
			'container' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			],
			'urlargs' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			],
			'autoresize' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			],
		]);
	}
}
