<?php

namespace MediaWiki\Extension\EmbedVideo;

use Exception;
use HtmlArmor;

class EmbedVideoException extends Exception {

	public function __construct(
		private readonly string|HtmlArmor $msg,
	) {
		parent::__construct( $this->getHtml() );
	}

	public function getHtml(): string {
		return HtmlArmor::getHtml( $this->msg );
	}

	public static function newWithHtml( string $html ): self {
		return new self( new HtmlArmor( $html ) );
	}

}
