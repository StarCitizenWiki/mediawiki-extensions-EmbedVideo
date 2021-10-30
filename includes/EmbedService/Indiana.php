<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Indiana extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		//return '//player.vimeo.com/video/%1$s';
		//return '//purl.dlib.indiana.edu/iudl/media/%1$s' . '/embed';
		return '//media.dlib.indiana.edu/master_files/%1$s' . '/embed';
	}

	/**
	 * @inheritDoc
	 */
	public function getAspectRatio(): ?float {
		return 600 / 450;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultWidth(): int {
		return 600;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultHeight(): int {
		return 450;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'Indiana'
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([\d\w\-_][^/\?\#]+)$#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			//'https://purl.dlib.indiana.edu/iudl/media'
			'https://media.dlib.indiana.edu/'
		];
	}
}
