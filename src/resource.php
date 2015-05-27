<?php

namespace alsvanzelf\jsonapi;

class resource extends base {

/**
 * internal data containers
 */
private $links                 = array();
private $primary_type          = null;
private $primary_id            = null;
private $primary_attributes    = array();
private $primary_relationships = array();
private $primary_links         = array();
private $primary_meta_data     = array();
private $included_resources    = array();
private $meta_data             = array();

/**
 * creates a new resource
 * 
 * @param string $type typically the name of the endpoint or database table
 * @param mixed  $id   optional, provide if you want to provide this to the client
 *                     can be integer or hash or whatever
 */
public function __construct($type, $id=null) {
	parent::__construct();
	
	$this->primary_type = $type;
	$this->primary_id = $id;
}

/**
 * generates an array for the whole response body
 * 
 * @see jsonapi.org/format
 * 
 * @return array, containing:
 *         - links
 *         - data
 *           - type
 *           - id
 *           - attributes
 *           - relationships
 *           - links
 *           - meta
 *         - included
 *         - meta
 */
public function get_array() {
	$response = array();
	
	// links
	if ($this->links) {
		$response['links'] = $this->links;
	}
	
	// primary data
	$response['data'] = array(
		'type' => $this->primary_type,
	);
	if ($this->primary_id) {
		$response['data']['id'] = $this->primary_id;
	}
	if ($this->primary_attributes) {
		$response['data']['attributes'] = $this->primary_attributes;
	}
	if ($this->primary_relationships) {
		$response['data']['relationships'] = $this->primary_relationships;
	}
	if ($this->primary_links) {
		$response['data']['links'] = $this->primary_links;
	}
	if ($this->primary_meta_data) {
		$response['data']['meta'] = $this->primary_meta_data;
	}
	
	// included resources
	if ($this->included_resources) {
		$response['included'] = $this->included_resources;
	}
	
	// meta data
	if ($this->meta_data) {
		$response['meta'] = $this->meta_data;
	}
	
	return $response;
}

/**
 * adds a data-point to the primary data
 * this will end up in response.data.attributes.{$key}
 * 
 * values don't have to be scalar, it can be lists or objects as well
 * 
 * @see ->fill_data() for adding a whole array directly
 * 
 * @param  string $key
 * @param  mixed  $value
 * @return void
 */
public function add_data($key, $value) {
	if (is_scalar($value) == false && is_array($value) == false) {
		$value = parent::convert_to_array($value);
	}
	
	$this->primary_attributes[$key] = $value;
}

/**
 * fills the primary data
 * this will end up in response.data.attributes
 * 
 * this is meant for adding an array as the primary data
 * other types (int, string, object) will be converted to arrays first
 * objects will be converted using their public keys
 * 
 * @note skips an 'id'-key inside $values if identical to the $id given during construction
 * 
 * @see ->add_data()
 * 
 * @param  mixed $values
 * @return void
 */
public function fill_data($values) {
	$values = parent::convert_to_array($values);
	
	if (isset($values['id']) && $values['id'] == $this->primary_id) {
		unset($values['id']);
	}
	
	foreach ($values as $key => $single_value) {
		$this->add_data($key, $single_value);
	}
}

/**
 * adds a relation to another resource
 * this will end up in response.data.relationships.{$key}
 * 
 * $relation should be in the following format (any point can be omitted):
 * - links
 *   - self
 *   - related
 * - data
 *   - type
 *   - id
 * 
 * if $relation is a jsonapi\resource, it will also add an included resource
 * @see ->add_included_resource()
 * 
 * @param  string  $key
 * @param  mixed   $relation     can be an array or a jsonapi\resource
 * @param  boolean $skip_include optional, defaults to false
 * @return void
 * 
 * @todo allow to add collections as well
 */
public function add_relation($key, $relation, $skip_include=false) {
	$resource_class_name = get_class();
	if ($relation instanceof $resource_class_name) {
		$relation_array = $relation->get_array();
		
		// add whole resources as included resource, while keeping the relationship
		if (!empty($relation_array['data']['attributes']) && $skip_include == false) {
			$this->add_included_resource($key, $relation);
		}
		
		$relation = array(
			'links' => array(
				'self'    => $relation_array['links']['self'].'/relationships/'.$key,
				'related' => $relation_array['links']['self'].'/'.$key,
			),
			'data'  => array(
				'type' => $relation_array['data']['type'],
			),
		);
		if (!empty($relation_array['data']['id'])) {
			$relation['data']['id'] = $relation_array['data']['id'];
		}
	}
	
	if (is_array($relation) == false) {
		throw new \Exception('unknown relation format');
	}
	
	$this->primary_relationships[$key] = $relation;
}

/**
 * fills the relationships to other resources
 * this will end up in response.data.relationships
 * 
 * @see ->add_relation()
 * 
 * @param  array $relations
 * @return void
 */
public function fill_relations($relations, $skip_include=false) {
	foreach ($relations as $key => $relation) {
		$this->add_relation($key, $relation, $skip_include);
	}
}

/**
 * adds a link
 * this will end up in response.data.links.{$key} or response.links.{$key} ..
 * .. depending on $data_level
 * 
 * useful for links which can not be added as relation, @see ->add_relation()
 * 
 * @param  string  $key
 * @param  mixed   $link
 * @param  boolean $data_level optional, defaults to true
 * @return void
 */
public function add_link($key, $link, $data_level=true) {
	if (is_string($link) == false && is_array($link) == false) {
		$link = parent::convert_to_array($link);
	}
	
	if ($data_level == false) {
		$this->links[$key] = $link;
	}
	
	$this->primary_links[$key] = $link;
}

/**
 * fills the set of links
 * this will end up in response.data.links or response.links ..
 * .. depending on $data_level
 * 
 * @see ->add_link()
 * 
 * @param  array   $links
 * @param  boolean $data_level optional, defaults to true
 * @return void
 */
public function fill_links($links, $data_level=true) {
	foreach ($links as $key => $link) {
		$this->add_link($key, $link, $data_level);
	}
}

/**
 * sets the link to the request used to give this response
 * this will end up in response.links.self and response.data.links.self
 * this overrides the jsonapi\base->set_self_link() which only adds it to response.links.self
 * 
 * @see jsonapi\base->set_self_link()
 * 
 * by default this is already set using $_SERVER variables
 * use this method to override this default behavior
 * @see jsonapi\base::__construct()
 * 
 * @param  string $link
 * @return void
 */
public function set_self_link($link) {
	parent::set_self_link($link);
	
	$this->add_link($key='self', $link, $data_level=true);
}

/**
 * adds an included resource
 * this will end up in response.included.{$key}
 * 
 * @param string                       $key
 * @param \alsvanzelf\jsonapi\resource $resource
 */
public function add_included_resource($key, \alsvanzelf\jsonapi\resource $resource) {
	$resource_array = $resource->get_array();
	$resource_array = $resource_array['data'];
	
	unset($resource_array['relationships'], $resource_array['meta']);
	
	$this->included_resources[$key] = $resource_array;
}

/**
 * fills the included resources
 * this will end up in response.included
 * 
 * @param  array $resources of \alsvanzelf\jsonapi\resource objects
 * @return void
 */
public function fill_included_resources($resources) {
	foreach ($resources as $key => $resource) {
		$this->add_included_resource($key, $resource);
	}
}

/**
 * adds some meta data
 * this will end up in response.meta.{$key} or response.data.meta.{$key} ..
 * .. depending on $data_level
 * 
 * @param  string  $key
 * @param  mixed   $meta_data  this is not converted in any way
 * @param  boolean $data_level optional, defaults to false
 * @return void
 */
public function add_meta($key, $meta_data, $data_level=false) {
	if (is_scalar($meta_data) == false && is_array($meta_data) == false) {
		$meta_data = parent::convert_to_array($meta_data);
	}
	
	if ($data_level == true) {
		$this->primary_meta_data[$key] = $meta_data;
	}
	
	$this->meta_data[$key] = $meta_data;
}

/**
 * fills the meta data
 * this will end up in response.meta or response.data.meta ..
 * .. depending on $data_level
 * 
 * @param  array   $meta_data
 * @param  boolean $data_level optional, defaults to false
 * @return void
 */
public function fill_meta($meta_data, $data_level=false) {
	foreach ($meta_data as $key => $single_meta_data) {
		$this->add_meta($key, $single_meta_data, $data_level);
	}
}

}
