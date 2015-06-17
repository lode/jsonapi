<?php

namespace alsvanzelf\jsonapi;

/**
 * resource collection array
 * 
 * main actions
 * - primary data @see ->fill_collection() or ->add_resource()
 * - self link    @see ->set_self_link()
 * - output       @see ->send_response() or ->get_json()
 * 
 * extra elements
 * - meta data @see ->add_meta() or ->fill_meta()
 * - included  although possible, you should set those via the resource
 */

class collection extends response {

/**
 * internal data containers
 */
protected $primary_type       = null;
protected $primary_collection = array();
protected $included_data = array();

/**
 * creates a new collection
 * 
 * @param string $type typically the name of the endpoint or database table
 */
public function __construct($type=null) {
	parent::__construct();
	
	$this->primary_type = $type;
}

/**
 * generates an array for the whole response body
 * 
 * @see jsonapi.org/format
 * 
 * @return array, containing:
 *         - links
 *         - data []
 *           - {everything from the resource's data-key}
 *         - included {from the resource's included-key}
 *         - meta
 */
public function get_array() {
	$response = array();
	
	// links
	if ($this->links) {
		$response['links'] = $this->links;
	}
	
	// primary data
	$response['data'] = $this->primary_collection;
	
	// included resources
	if ($this->included_data) {
		$response['included'] = array_values($this->included_data);
	}
	
	// meta data
	if ($this->meta_data) {
		$response['meta'] = $this->meta_data;
	}
	
	return $response;
}

/**
 * adds a resource to the primary collection
 * this will end up in response.data[]
 * 
 * @note only the data-key of a resource is used
 *       that is its type, id, attributes, relations, links, meta(data-level)
 *       further, its included resources are separately added to response.included[]
 * 
 * @see jsonapi\resource
 * @see ->fill_collection() for adding a whole array of resources directly
 * 
 * @param  \alsvanzelf\jsonapi\resource $resource
 * @return void
 */
public function add_resource(\alsvanzelf\jsonapi\resource $resource) {
	$resource_array = $resource->get_array();
	
	$included_resources = $resource->get_included_resources();
	if (!empty($included_resources)) {
		$this->fill_included_resources($included_resources);
	}
	
	$this->primary_collection[] = $resource_array['data'];
}

/**
 * fills the primary collection with resources
 * this will end up in response.data[]
 * 
 * @see ->add_resource()
 * 
 * @param  array $resources
 * @return void
 */
public function fill_collection($resources) {
	foreach ($resources as $resource) {
		$this->add_resource($resource);
	}
}

}
