<?php

/*
 * Default strings and links for the custom theme
 * 
 */

class OC_Theme {

	private $customEntity;
	private $customName;
	private $customTitle;
	private $customBaseUrl;
	private $customDocBaseUrl;
	private $customSyncClientUrl;
	private $customSlogan;
	private $customLogoClaim;

	function __construct() {
		$this->customEntity = "Custom Cloud";
		$this->customName = "ownCloud";
		$this->customTitle = "Custom Cloud";
		$this->customBaseUrl = "https://owncloud.org";
		$this->customDocBaseUrl = "http://doc.owncloud.org";
		$this->customSyncClientUrl = "https://owncloud.org/sync-clients";
		$this->customSlogan = "The place to put all your stuff!";
		$this->customLogoClaim = "";
	}

	public function getBaseUrl() {
		return $this->customBaseUrl;
	}

	public function getSyncClientUrl() {
		return $this->customSyncClientUrl;
	}

	public function getDocBaseUrl() {
		return $this->customDocBaseUrl;
	}

	public function getTitle() {
		return $this->customTitle;
	}
	
	public function getName() {
		return $this->customName;
	}

	public function getEntity() {
		return $this->customEntity;
	}

	public function getSlogan() {
		return $this->customSlogan;
	}

	public function getLogoClaim() {
		return $this->customLogoClaim;
	}

	public function getShortFooter() {
		$footer = "© 2013 <a href=\"".$this->getBaseUrl()."\" target=\"_blank\">".$this->getEntity()."</a>".
			" – " . $this->getSlogan();

		return $footer;
	}

	public function getLongFooter() {
		$footer = "© 2013 <a href=\"".$this->getBaseUrl()."\" target=\"_blank\">".$this->getEntity()."</a>".
			"<br/>" . $this->getSlogan();

		return $footer;
	}

}

