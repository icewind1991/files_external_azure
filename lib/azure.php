<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\Files_External_Azure;

use League\Flysystem\FileNotFoundException;
use OC\Files\Storage\Flysystem;
use League\Flysystem\Azure\AzureAdapter;
use OC\Files\Storage\PolyFill\CopyDirectory;
use WindowsAzure\Common\ServicesBuilder;

class Azure extends Flysystem {
	use CopyDirectory;

	private $name;
	private $key;
	private $container;

	/**
	 * @var \League\Flysystem\Azure\AzureAdapter
	 */
	private $adapter;

	public function __construct($params) {
		if (isset($params['name']) && isset($params['key']) && isset($params['container'])) {
			$this->name = $params['name'];
			$this->key = $params['key'];
			$this->container = $params['container'];

			$endpoint = sprintf('DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s', $this->name, $this->key);
			/** @var \WindowsAzure\Blob\Internal\IBlob $blobRestProxy */
			$blobRestProxy = ServicesBuilder::getInstance()->createBlobService($endpoint);

			$this->adapter = new Adapter($blobRestProxy, $this->container);
			$this->buildFlySystem($this->adapter);
		} else {
			throw new \Exception('Creating \OCA\Files_External_Azure\Azure storage failed');
		}
	}

	public function clean() {
		$this->flysystem->getAdapter()->deleteDir('');
	}

	public function file_exists($path) {
		if ($path === '' or $path === '/') {
			return true;
		}
		return parent::file_exists($path);
	}

	public function getId() {
		return 'azure::' . $this->name . '/' . $this->container;
	}

	public function filetype($path) {
		if ($path === '' or $path === '/') {
			return 'dir';
		}
		try {
			$info = $this->flysystem->getMetadata($this->buildPath($path));
		} catch (FileNotFoundException $e) {
			return false;
		}
		return ($info['mimetype'] === 'httpd/unix-directory') ? 'dir' : 'file';
	}

	public function rename($source, $target) {
		$result = $this->copy($source, $target);
		return ($result and $this->unlink($source));
	}

	/**
	 * get the free space in the storage
	 *
	 * @param string $path
	 * @return int|false
	 */
	public function free_space($path) {
		return \OCP\Files\FileInfo::SPACE_UNLIMITED;
	}
}
