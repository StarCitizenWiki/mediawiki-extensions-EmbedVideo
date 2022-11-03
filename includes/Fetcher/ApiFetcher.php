<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Fetcher;

use ApiBase;
use ApiUsageException;
use InvalidArgumentException;
use Status;
use Wikimedia\ParamValidator\ParamValidator;

class ApiFetcher extends ApiBase {

	/**
	 * This module should only be used from embed video to request embed info
	 *
	 * @return bool
	 */
	public function isInternal() {
		return true;
	}

	/**
	 * Setup the allowed and required parameters
	 *
	 * @return array
	 */
	public function getAllowedParams(): array {
		return [
			'service' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'id' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
		];
	}

	/**
	 * @return bool
	 * @throws ApiUsageException
	 */
	public function execute(): bool {
		try {
			$service = $this->makeService( $this->getParameter( 'service' ), $this->getParameter( 'id' ) );
		} catch ( InvalidArgumentException $e ) {
			throw new ApiUsageException( $this, Status::newFatal( $e->getMessage() ) );
		}

		$result = $service->execute();

		if ( $result['title'] !== null && $result['thumbnail'] !== null ) {
			$this->getResult()->addValue( null, 'title', $result['title'] );
			$this->getResult()->addValue( null, 'thumbnail', $result['thumbnail'] );
		}

		return true;
	}

	/**
	 * Create the fetcher service from a given name
	 *
	 * @param string $serviceName
	 * @param string $id
	 * @return FetcherBase
	 * @throws InvalidArgumentException
	 */
	private function makeService( string $serviceName, string $id ): FetcherBase {
		switch ( $serviceName ) {
			case 'youtube':
				return new YouTube( $id );

			default:
				throw new InvalidArgumentException( 'Unknown service' );
		}
	}
}
