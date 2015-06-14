<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Vincent Petry <pvince81@owncloud.com>
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

namespace Test\Files_External_Azure;

require_once '../vendor/autoload.php';

use Test\Files\Storage\Storage;

class Azure extends Storage {
	private $config;

	protected function setUp() {
		parent::setUp();

		$this->config = json_decode(file_get_contents('./config.json'), true);
		$this->instance = new \OCA\Files_External_Azure\Azure($this->config);
		$this->instance->clean();
	}

	protected function tearDown() {
		if ($this->instance) {
			$this->instance->clean();
		}

		parent::tearDown();
	}
}
