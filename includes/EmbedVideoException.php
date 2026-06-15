<?php

namespace MediaWiki\Extension\EmbedVideo;

use Exception;
use HtmlArmor;

class EmbedVideoException extends Exception {

	/**
	 * @param string|HtmlArmor $msg
	 */
	public function __construct(
		private readonly string|HtmlArmor $msg,
	) {
		parent::__construct( $this->getHtml() );
	}

	/**
	 * Get the HTML representation of the message.
	 *
	 * @return string
	 */
	public function getHtml(): string {
		return HtmlArmor::getHtml( $this->msg );
	}

	/**
	 * Create a new instance from a raw HTML string.
	 *
	 * @param string $html
	 * @return self
	 */
	public static function newWithHtml( string $html ): self {
		return new self( new HtmlArmor( $html ) );
	}

}
