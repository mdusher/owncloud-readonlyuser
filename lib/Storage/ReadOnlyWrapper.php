<?php

namespace OCA\ReadOnlyUser\Storage;

use \OC\Files\Filesystem;
use \OC\Files\Storage\Storage;
use \OC\Files\Storage\Wrapper\Wrapper;
use \OCA\ReadOnlyUser\Exceptions\ReadOnlyException;
use \OCP\Constants;

class ReadOnlyWrapper extends Wrapper {

	// The name of the group to add users to to make them read only
	const READ_ONLY_GROUP = "readonly-users";

	// Wrap all storages in the ReadOnlyWrapper
	public static function setupWrapper() {
		\OC\Files\Filesystem::addStorageWrapper('readonlyuser', function ($mountPoint, $storage) {
				return new ReadOnlyWrapper(array('storage' => $storage));
		});
	}

	// Check if the user is part of
	protected function exceptionIfReadOnlyFlag($path) {
		// Make sure the readonly-users group exists
		$groupManager = \OC::$server->getGroupManager();
		if (!$groupManager->groupExists(self::READ_ONLY_GROUP)) {
				\OC::$server->getGroupManager()->createGroup(self::READ_ONLY_GROUP);
		}

		$isReadOnly = false;

		// Check if the user trying to perform the action is read only
		$currentUser = \OC_User::getUser();
		if ($groupManager->isInGroup($currentUser, self::READ_ONLY_GROUP)) {
			$isReadOnly = true;
		}

		// Check if the user that owns the storage is read only
		if ($path) {
			$storageUser = $this->getWrapperStorage()->getOwner($path);
			if ($groupManager->isInGroup($currentUser, self::READ_ONLY_GROUP)) {
				$isReadOnly = true;
			}
		}

		// If it looks like they're read only, throw an exception saying so
		if ($isReadOnly) {
			// Throw an exception if it does
			// this exception currently throws but does not stop anything further happening
			throw new ReadOnlyException("Storage has been marked as read only.");
		}
	}

	public function file_put_contents($path, $data) {
		$this->exceptionIfReadOnlyFlag($path);
		return $this->getWrapperStorage()->file_put_contents($path, $data);
	}

	public function copy($path1, $path2) {
		$this->exceptionIfReadOnlyFlag($path1);
		$this->exceptionIfReadOnlyFlag($path2);
		return $this->getWrapperStorage()->copy($path1, $path2);
	}

	public function rename($path1, $path2) {
		$this->exceptionIfReadOnlyFlag($path1);
		$this->exceptionIfReadOnlyFlag($path2);
		return $this->getWrapperStorage()->rename($path1, $path2);
	}

	public function fopen($path, $mode) {
		switch ($mode) {
			case 'r+':
			case 'rb+':
			case 'w+':
			case 'wb+':
			case 'x+':
			case 'xb+':
			case 'a+':
			case 'ab+':
			case 'c+':
			case 'w':
			case 'wb':
			case 'x':
			case 'xb':
			case 'a':
			case 'ab':
			case 'c':
				$this->exceptionIfReadOnlyFlag($path);
				break;
		}
		return $this->getWrapperStorage()->fopen($path, $mode);
	}

	public function mkdir($path) {
		$this->exceptionIfReadOnlyFlag($path);
		return $this->getWrapperStorage()->mkdir($path);
	}

	public function rmdir($path) {
		$this->exceptionIfReadOnlyFlag($path);
		return $this->getWrapperStorage()->rmdir($path);
	}

	public function isCreatable($path) {
		$this->exceptionIfReadOnlyFlag($path);
		return $this->getWrapperStorage()->isCreatable($path);
	}

	public function isUpdatable($path) {
		$this->exceptionIfReadOnlyFlag($path);
		return $this->getWrapperStorage()->isUpdatable($path);
	}

	public function isDeletable($path) {
		$this->exceptionIfReadOnlyFlag($path);
		return $this->getWrapperStorage()->isDeletable($path);
	}

	public function getPermissions($path) {
		try {
			$this->exceptionIfReadOnlyFlag($path);
		} catch (ReadOnlyException $e) {
			return Constants::PERMISSION_READ;
		}
		return $this->getWrapperStorage()->getPermissions($path);

	}

	public function touch($path, $mtime = null) {
		$this->exceptionIfReadOnlyFlag($path);
		return $this->getWrapperStorage()->touch($path, $mtime);
	}

	public function unlink($path) {
		$this->exceptionIfReadOnlyFlag($path);
		return $this->storage->unlink($path);
	}

	public function copyFromStorage(\OCP\Files\Storage $sourceStorage, $sourceInternalPath, $targetInternalPath) {
		$this->exceptionIfReadOnlyFlag($targetInternalPath);
		return $this->getWrapperStorage()->copyFromStorage($sourceStorage, $sourceInternalPath, $targetInternalPath);
	}

	public function moveFromStorage(\OCP\Files\Storage $sourceStorage, $sourceInternalPath, $targetInternalPath) {
		$this->exceptionIfReadOnlyFlag($targetInternalPath);
		return $this->getWrapperStorage()->moveFromStorage($sourceStorage, $sourceInternalPath, $targetInternalPath);
	}

}