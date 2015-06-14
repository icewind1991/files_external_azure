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

use League\Flysystem\Azure\AzureAdapter;
use League\Flysystem\Config;
use League\Flysystem\FileNotFoundException;
use WindowsAzure\Blob\Models\CreateBlobOptions;
use WindowsAzure\Blob\Models\GetContainerPropertiesResult;
use WindowsAzure\Common\ServiceException;

class Adapter extends AzureAdapter {
	/**
	 * {@inheritdoc}
	 */
	public function delete($path) {
		if ($path === '') {
			return false;
		} else {
			try {
				return parent::delete($path);
			} catch (ServiceException $e) {
				if ($e->getCode() === 404) {
					throw new FileNotFoundException($path, $e->getCode(), $e);
				} else {
					throw $e;
				}
			}
		}
	}

	private function isRoot($path) {
		return ($path === '/' or $path === '' or $path === '.');
	}

	/**
	 * {@inheritdoc}
	 */
	public function deleteDir($dirname) {
		if ($this->isRoot($dirname)) {
			return parent::deleteDir($dirname);
		} else {
			$result = parent::deleteDir($dirname . '/');
			$result = ($result and $this->delete($dirname));
			return $result;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function createDir($dirname, Config $config) {
		$options = new CreateBlobOptions();
		$options->setBlobContentType('httpd/unix-directory');
		$this->client->createBlockBlob($this->container, $dirname, 'dummy', $options);
		return ['path' => $dirname, 'type' => 'dir'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($path) {
		if ($this->isRoot($path)) {
			return true;
		} else {
			return parent::has($path);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetadata($path) {
		if ($this->isRoot($path)) {
			/** @var GetContainerPropertiesResult $info */
			$info = $this->client->getContainerMetadata($this->container);
			$time = $info->getLastModified();
			return [
				'path' => '',
				'timestamp' => $time->getTimestamp(),
				'dirname' => '',
				'mimetype' => 'httpd/unix-directory',
				'size' => 0,
				'type' => 'dir',
			];
		} else {
			return parent::getMetadata($path);
		}
	}
}
