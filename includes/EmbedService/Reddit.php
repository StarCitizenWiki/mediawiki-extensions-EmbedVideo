<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

use InvalidArgumentException;

final class Reddit extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://embed.reddit.com%1$s?embed=true';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			// reddit.com permalink (any subdomain), capture full path as group 1
			'#reddit\.com(/r/[A-Za-z0-9_]+/comments/([a-z0-9]{5,8})(?:/[A-Za-z0-9_-]+)?)/?#i',
			// embed.reddit.com permalink, capture full path as group 1
			'#embed\.reddit\.com(/r/[A-Za-z0-9_]+/comments/([a-z0-9]{5,8})(?:/[A-Za-z0-9_-]+)?)/?#i',
			// Path-only input with subreddit present
			'#^(/r/[A-Za-z0-9_]+/comments/([a-z0-9]{5,8})(?:/[A-Za-z0-9_-]+)?)/?$#i',
		];
	}

	/**
	 * Do not accept bare IDs since the subreddit path is required for embedding
	 *
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [];
	}

	/**
	 * Override to normalize plain IDs into a comments path so getBaseUrl can produce a valid embed URL.
	 *
	 * @param string $id
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function parseVideoID( $id ): string {
		$id = trim( (string)$id );

		foreach ( $this->getUrlRegex() as $regex ) {
			if ( preg_match( $regex, $id, $matches ) ) {
				return rtrim( $matches[1], '/' );
			}
		}

		throw new InvalidArgumentException( 'Provided ID could not be validated.' );
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://www.redditinc.com/policies/privacy-policy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://embed.reddit.com',
			'https://www.redditmedia.com',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getAspectRatio(): ?float {
		return 1 / 1.15;
	}
}
