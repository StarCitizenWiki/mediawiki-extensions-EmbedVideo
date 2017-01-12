<?php
/**
 * EmbedVideo
 * ApiEmbedVideo class
 *
 * @license		MIT
 * @package		EmbedVideo
 * @link		https://github.com/HydraWiki/mediawiki-embedvideo
 *
 **/
class ApiEmbedVideo extends ApiBase {

    /**
     * Execute the API call.
     */
    public function execute() {

        $getHTML = \EmbedVideoHooks::parseEV(
			NULL,
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

		$this->getResult()->addValue(null, $this->getModuleName(), array ( 'html' => $HTML ) );
		return true;
	}

	/**
	 * [getDescription description]
	 * @return [type] [description]
	 */
	public function getDescription() {
		return 'Get generated embed code for given parameters';
	}

    /**
     * Setup the allowed and required parameters
     * @return array
     */
    public function getAllowedParams() {
		return array_merge( parent::getAllowedParams(), array(
			'service' => array (
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
            'id' => array (
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
            'dimensions' => array (
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			),
            'alignment' => array (
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			),
            'description' => array (
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			),
            'container' => array (
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			),
            'urlargs' => array (
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			),
            'autoresize' => array (
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false
			),
		) );
	}

	// Describe the parameter
	public function getParamDescription() {
		return array_merge( parent::getParamDescription(), array(
            'service' => 'Name of the service (youtube, twitch, ect)',
			'id' => 'The ID of the video for that service',
			'dimensions' => 'Either a numeric width (100) or width by height (100x100)',
			'alignment' => 'Alignment of video',
			'description' => 'Description of video',
			'container' => 'Accepts frame, or leave empty',
			'urlargs' => 'Additional arguments to pass in the video url (for some services)',
			'autoresize' => 'Auto resize video? (true or false)'
		) );
	}

}