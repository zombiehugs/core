<?php
/**
 * ownCloud
 *
 * @author Michael Gapczynski
 * @copyright 2013 Michael Gapczynski mtgap@owncloud.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OC\Share\Controller;

use OC\Share\Share;
use OC\Share\ShareManager;
use OC\Share\Exception\ShareDoesNotExistException;
use OC\Share\Exception\ShareBackendDoesNotExistException;
use OC\Log;
use OC_JSON;
use Exception;

/**
 * Controller for the share dialog
 *
 * Routes are setup in core/routes.php as:
 *  - /shares/{itemTypePlural}/{itemSource}/{id}
 *  - /sharewiths/{itemTypePlural}
 *  - /sharesettings/arelinksallowed
 *  - /sharesettings/ispublicUploadAllowed
 *  - /sharesettings/isResharingAllowed
 */
class ShareDialogController {

	private $shareManager;
	private $logger;
	private $params;

	/**
	 * The constructor
	 * @param \OC\Share\ShareManager $shareManager
	 * @param \OC\Log $logger
	 * @param array $params
	 */
	public function __construct(ShareManager $shareManager, Log $logger, $params) {
		$this->shareManager = $shareManager;
		$this->logger = $logger;
		$this->params = $params;
	}

	public function share() {
		try {
			$share = Share::fromParams($this->params);
			$share = $this->shareManager->share($share);
			$share = $share->toAPI($share);
			OC_JSON::success(array('data' => $share));
		} catch (Exception $exception) {
			$this->onException($exception);
		}
	}

	public function unshare() {
		try {
			$share = Share::fromParams($this->params);
			$this->shareManager->unshare($share);
			OC_JSON::success();
		} catch (Exception $exception) {
			$this->onException($exception);
		}
	}

	public function update() {
		$itemType = $this->getItemType($this->params['itemTypePlural']);
		$id = $this->params['id'];
		try {
			$oldShare = $this->shareManager->getShareById($id, $itemType);
			// Compare properties to determine which need to be set
			$properties = $oldShare->toAPI();
			foreach ($properties as $property => $oldValue) {
				if (isset($this->params[$property]) && $this->params[$property] !== $oldValue) {
					$setter = 'set'.ucfirst($property);
					$oldShare->$setter($this->params[$property]);
				}
			}
			$this->shareManager->update($oldShare);
			OC_JSON::success();
		} catch (Exception $exception) {
			$this->onException($exception);
		}
	}
	
	public function getShares() {
		$itemType = $this->getItemType($this->params['itemTypePlural']);
		$filter = array();
		if (isset($this->params['itemSource'])) {
			$filter['itemSource'] = $this->params['itemSource'];
		}
		if (isset($this->params['shareOwner'])) {
			$filter['shareOwner'] = $this->params['shareOwner'];
		}
		$limit = null;
		$offset = null;
		if (isset($this->params['limit'])) {
			$limit = $this->params['limit'];
		}
		if (isset($this->params['offset'])) {
			$offset = $this->params['offset'];
		}
		try {
			$shares = $this->shareManager->getShares($itemType, $filter, $limit, $offset);
			foreach ($shares as &$share) {
				$share = $share->toAPI($share);
			}
			OC_JSON::success(array('data' => $shares));
		} catch (Exception $exception) {
			$this->onException($exception);
		}
	}

	public function getShareById() {
		$itemType = $this->getItemType($this->params['itemTypePlural']);
		$id = $this->params['id'];
		try {
			$share = $this->shareManager->getShareById($id, $itemType);
			$share = $share->toAPI($share);
			OC_JSON::success(array('data' => array($share)));
		} catch (Exception $exception) {
			$this->onException($exception);
		}
	}

	public function searchForPotentialShareWiths() {
		$itemType = $this->getItemType($this->params['itemTypePlural']);
		$shareOwner = $this->params['shareOwner'];
		$pattern = '';
		$limit = null;
		$offset = null;
		if (isset($this->params['pattern'])) {
			$pattern = $this->params['pattern'];
		}
		if (isset($this->params['limit'])) {
			$limit = $this->params['limit'];
		}
		if (isset($this->params['offset'])) {
			$offset = $this->params['offset'];
		}
		try {
			$shareWiths = array();
			$shareBackend = $this->shareManager->getShareBackend($itemType);
			$shareTypes = $shareBackend->getShareTypes();
			foreach ($shareTypes as $shareType) {
				$shareTypeId = $shareType->getId();
				$result = $shareType->searchForPotentialShareWiths($shareOwner, $pattern, $limit,
					$offset
				);
				foreach ($result as $shareWith) {
					$shareWith['shareTypeId'] = $shareTypeId;
					$shareWiths[] = $shareWith;
					if (isset($limit)) {
						$limit--;
						if ($limit === 0) {
							break 2;
						}
					}
					if (isset($offset) && $offset > 0) {
						$offset--;
					}
				}
			}
			OC_JSON::success(array('data' => $shareWiths));
		} catch (Exception $exception) {
			$this->onException($exception);
		}
	}

	public function areLinksAllowed() {
		$links = 'no';
		$itemType = $this->getItemType($this->params['itemTypePlural']);
		$shareTypes = $shareBackend->getShareTypes();
		foreach ($shareTypes as $shareType) {
			if ($shareType->getId() === 'link') {
				$links = \OC_Appconfig::getValue('core', 'shareapi_allow_links', 'yes');
				break;
			}
		}
		OC_JSON::success(array('data' => array('areLinksAllowed' => $links === 'yes')));
	}

	public function isPublicUploadAllowed() {
		$publicUpload = \OC_Appconfig::getValue('core', 'shareapi_allow_public_upload', 'yes');
		OC_JSON::success(array('data' => array('isPublicUploadAllowed' => $publicUpload === 'yes')));
	}

	public function isResharingAllowed() {
		$resharing = \OC_Appconfig::getValue('core', 'shareapi_allow_resharing', 'yes');
		OC_JSON::success(array('data' => array('isResharingAllowed' => $resharing === 'yes')));
	}

	/**
	 * Get the item type based on the plural form
	 * @param string $itemTypePlural
	 * @return string
	 */
	protected function getItemType($itemTypePlural) {
		$shareBackends = $this->shareManager->getShareBackends();
		foreach ($shareBackends as $shareBackend) {
			if ($shareBackend->getItemTypePlural() === $itemTypePlural) {
				return $shareBackend->getItemType();
			}
		}
		throw new ShareBackendDoesNotExistException($itemTypePlural);
	}

	/**
	 * Handle a caught exception
	 * @param \Exception $exception
	 */
	protected function onException($exception) {
		$this->logger->error($exception->getMessage());
		OC_JSON::error(array('data' => array('message' => $exception->getMessage())));
	}

}