<?php

declare(strict_types=1);

namespace alsvanzelf\jsonapiTests\example_output;

use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;
use alsvanzelf\jsonapi\interfaces\HasExtensionMembersInterface;
use alsvanzelf\jsonapi\interfaces\ResourceInterface;

class ExampleVersionExtension implements ExtensionInterface {
	public function getOfficialLink(): string {
		return 'https://jsonapi.org/format/1.1/#extension-rules';
	}
	
	public function getNamespace(): string {
		return 'version';
	}
	
	public function setVersion(ResourceInterface & HasExtensionMembersInterface $resource, string $version): void {
		if ($resource instanceof ResourceDocument) {
			$resource->getResource()->addExtensionMember($this, 'id', $version);
		}
		else {
			$resource->addExtensionMember($this, 'id', $version);
		}
	}
}
