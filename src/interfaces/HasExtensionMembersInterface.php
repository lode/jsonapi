<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapi\interfaces;

use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

interface HasExtensionMembersInterface {
	public function addExtensionMember(ExtensionInterface $extension, string $key, mixed $value): void;
	
	/**
	 * @internal
	 */
	public function hasExtensionMembers(): bool;
	
	/**
	 * @internal
	 * 
	 * @return array<string, mixed>
	 */
	public function getExtensionMembers(): array;
}
