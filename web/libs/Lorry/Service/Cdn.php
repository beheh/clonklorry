<?php

require ROOT.'libs/AmazonS3/sdk.class.php';

class Lorry_Service_Cdn extends Lorry_Service {

	/**
	 *
	 * @var AmazonS3
	 */
	private $s3;

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry);
		$this->s3 = new AmazonS3(array('key' => $this->lorry->config->aws['key'], 'secret' => $this->lorry->config->aws['secret']));
	}

	public function release($file) {
		set_time_limit(600);

		// Open a file resource
		//$file_resource = fopen('large_video.mov', 'r');
		// Upload a file
		$response = $this->s3->create_object('lorrycdn', $file, array(
			//'fileUpload' => $file_resource,
			'contentType' => 'application/octet-stream',
			'body' => 'testing S3',
			'acl' => AmazonS3::ACL_PUBLIC,
			'meta' => array('md5' => md5('testing S3'))
		  ));

		if(!$response->isOK()) {
			throw new Exception($response->to_string());
		}

		return $response;
	}

}