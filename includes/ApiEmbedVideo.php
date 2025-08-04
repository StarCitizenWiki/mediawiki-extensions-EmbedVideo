<?php
/**
 * EmbedVideo
 * ApiEmbedVideo class
 *
 * @license MIT
 * @package EmbedVideo
 * @link    https://gitlab.com/hydrawiki/extensions/EmbedVideo
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo;

use MediaWiki\Api\ApiBase;
use Wikimedia\ParamValidator\ParamValidator;

class ApiEmbedVideo extends ApiBase {
	/**
	 * Execute the API call.
	 *
	 * @return bool
	 */
	public function execute(): bool {
		$ev = new EmbedVideo( null, [
			'service' => $this->getMain()->getVal( 'service' ),
			'id' => $this->getMain()->getVal( 'id' ),
			'dimensions' => $this->getMain()->getVal( 'dimensions' ),
			'alignment' => $this->getMain()->getVal( 'alignment' ),
			'description' => $this->getMain()->getVal( 'description' ),
			'container' => $this->getMain()->getVal( 'container' ),
			'urlargs' => $this->getMain()->getVal( 'urlargs' ),
			'autoresize' => $this->getMain()->getVal( 'autoresize' ),
			'valignment' => $this->getMain()->getVal( 'valignment' ),
		], true );

		$getHTML = $ev->output();

		if ( is_array( $getHTML ) ) {
			$html = $getHTML[0];
		} else {
			$html = "Unable to load video from API.";
		}

		$this->getResult()->addValue( null, $this->getModuleName(), [ 'html' => $html ] );
		return true;
	}

	/**
	 * Setup the allowed and required parameters
	 *
	 * @return array
	 */
	public function getAllowedParams(): array {
		return array_merge( parent::getAllowedParams(), [
			'service' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'id' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'dimensions' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'alignment' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'description' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'container' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'urlargs' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
			'autoresize' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false
			],
		] );
	}
}
