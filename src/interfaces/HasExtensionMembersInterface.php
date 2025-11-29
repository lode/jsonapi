<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

interface HasExtensionMembersInterface {
	public function addExtensionMember(ExtensionInterface $extension, $key, $value);
	public function hasExtensionMembers();
	public function getExtensionMembers();
}
