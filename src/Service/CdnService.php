<?php

namespace Lorry\Service;

use Aws\S3\S3Client;
use Aws\S3\Model\MultipartUpload\UploadBuilder;
use Lorry\Model\File;
use Lorry\Service;
use Lorry\Logger\LoggerFactoryInterface;

class CdnService extends Service {

	/**
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;

	public function __construct(LoggerFactoryInterface $loggerFactory, ConfigService $config) {
		parent::__construct($loggerFactory);
		$this->config = $config;
	}

	/**
	 * @var \Aws\S3\S3Client
	 */
	protected $client;

	public function ensureClient() {
		if($this->client)
			return;
		$this->client = S3Client::factory(array(
					'region' => $this->config->get('cdn/region'),
					'signature' => 'v4',
					'key' => $this->config->get('cdn/key'),
					'secret' => $this->config->get('cdn/secret')
		));
	}

	public function transfer(File $file) {
		$base = $this->config->get('upload/data');
		$path = $base.'/'.$file;
		if(!file_exists($path)) {
			throw new \Exception('file does not exist');
		}
		$this->ensureClient();
		$uploader = UploadBuilder::newInstance()
				->setClient($this->client)
				->setSource($path)
				->setBucket($this->config->get('cdn/bucket'))
				->setKey('test/'.basename($path))
				->build();

		$uploader->upload();
	}

	public function getDownloadUrl(File $file) {
		return 'https://'.$this->config->get('cdn/bucket').'.s3.'.$this->config->get('cdn/region').'.amazonaws.com/'.$file->getName();
	}

}
