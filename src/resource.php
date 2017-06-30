<?php

namespace alsvanzelf\jsonapi;

/**
 * single resource object
 * 
 * main actions
 * - primary data @see ->add_data() or ->fill_data()
 * - self link    @see ->set_self_link()
 * - output       @see ->send_response() or ->get_json()
 * 
 * extra elements
 * - relations @see ->add_relation() or ->fill_relations()
 * - links     @see ->add_link() or ->fill_links()
 * - meta data @see ->add_meta() or ->fill_meta()
 * - included  @see ->add_included_resource() or ->fill_included_resources()
 */

class resource extends response {

/**
 * relation types
 */
const RELATION_TO_MANY = 'to_many';
const RELATION_TO_ONE  = 'to_one';

/**
 * placement of link objects
 */
const LINK_LEVEL_DATA    = 'data';
const LINK_LEVEL_ROOT    = 'root';
const LINK_LEVEL_BOTH    = 'both';

/**
 * methods for filling the self link
 * @see ::$self_link_method
 */
const SELF_LINK_SERVER = 'server';
const SELF_LINK_TYPE   = 'type';
const SELF_LINK_NONE   = 'none';

/**
 * the method to use for filling the self link
 * 
 * the current default ::SELF_LINK_SERVER fills the link using the $_SERVER request info
 * for backwards compatibility this stays for the 1.x releases
 * from 2.x this will (probably) switch to ::SELF_LINK_TYPE
 */
public static $self_link_data_level = self::SELF_LINK_SERVER;

/**
 * internal data containers
 */
protected $primary_type          = null;
protected $primary_id            = null;
protected $primary_attributes    = array();
protected $primary_relationships = array();
protected $primary_links         = array();
protected $primary_meta_data     = array();

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
 * get the primary type as set via the constructor
 * 
 * @return string|null
 */
public function get_type() {
	return $this->primary_type;
}

/**
 * get the primary id as set via the constructor
 * 
 * @return mixed|null
 */
public function get_id() {
	return $this->primary_id;
}

/**
 * whether data has been added via ->add_data()/->fill_data()
 * this can be useful when adding a resource to another one as included resource
 * 
 * @return boolean
 */
public function has_data() {
	return (bool)$this->primary_attributes;
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
 * adds a data-point to the primary data
 * this will end up in response.data.attributes.{$key}
 * 
 * values don't have to be scalar, it can be lists or objects as well
 * 
 * @see ->fill_data() for adding a whole array directly
 * 
 * @param  string $key
 * @param  mixed  $value objects are converted in arrays, @see base::convert_object_to_array()
 * @return void
 */
public function add_data($key, $value) {
	if (is_object($value)) {
		$value = parent::convert_object_to_array($value);
	}
	
	$this->primary_attributes[$key] = $value;
}

/**
 * fills the primary data
 * this will end up in response.data.attributes
 * 
 * this is meant for adding an array as the primary data
 * objects will be converted using their public keys
 * 
 * @note skips an 'id'-key inside $values if identical to the $id given during construction
 * 
 * @see ->add_data()
 * 
 * @param  mixed $values objects are converted in arrays, @see base::convert_object_to_array()
 * @return void
 */
public function fill_data($values) {
	if (is_object($values)) {
		$values = parent::convert_object_to_array($values);
	}
	if (is_array($values) == false) {
		throw new \Exception('use add_data() for adding scalar values');
	}
	
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
 * if $relation is a jsonapi\resource or jsonapi\collection, it will also add an included resource
 * @see ->add_included_resource()
 * 
 * @param  string  $key
 * @param  mixed   $relation     can be array or jsonapi\resource or jsonapi\collection
 * @param  boolean $skip_include optional, defaults to false
 * @param  string  $type         optional, defaults to null
 * @return void
 * 
 * @todo allow to add collections as well
 */
public function add_relation($key, $relation, $skip_include=false, $type=null) {
	if ($type && in_array($type, array(self::RELATION_TO_ONE, self::RELATION_TO_MANY)) == false) {
		throw new \Exception('unknown relation type');
	}
	if (isset($this->primary_relationships[$key]) && $relation instanceof \alsvanzelf\jsonapi\resource == false) {
		throw new \Exception('can not add a relation twice, unless using a resource object');
	}
	if (isset($this->primary_relationships[$key]) && $relation instanceof \alsvanzelf\jsonapi\resource) {
		if ($type != self::RELATION_TO_MANY || isset($this->primary_relationships[$key]['data']['type'])) {
			throw new \Exception('$type should be set to RELATION_TO_MANY for resources using the same key');
		}
	}
	if ($relation instanceof \alsvanzelf\jsonapi\collection && $type == self::RELATION_TO_ONE) {
		throw new \Exception('collections can only be added as RELATION_TO_MANY');
	}
	
	if ($relation instanceof \alsvanzelf\jsonapi\resource) {
		// add whole resources as included resource, while keeping the relationship
		if ($relation->has_data() && $skip_include == false) {
			$this->add_included_resource($relation);
		}
		
		$base_url      = (isset($this->links['self']['href'])) ? $this->links['self']['href'] : $this->links['self'];
		$relation_id   = $relation->get_id() ?: null;
		$relation_data = [
			'type' => $relation->get_type(),
			'id'   => $relation_id,
		];
		
		if (isset($this->primary_relationships[$key])) {
			$this->primary_relationships[$key]['data'][] = $relation_data;
			return;
		}
		if ($type == self::RELATION_TO_MANY) {
			$relation_data = array($relation_data);
		}
		
		$relation = array(
			'links' => array(
				'self'    => $base_url.'/relationships/'.$key,
				'related' => $base_url.'/'.$key,
			),
			'data'  => $relation_data,
		);
	}
	
	if ($relation instanceof \alsvanzelf\jsonapi\collection) {
		$relation_resources = $relation->get_resources();
		
		// add whole resources as included resource, while keeping the relationship
		if ($relation_resources && $skip_include == false) {
			$this->fill_included_resources($relation);
		}
		
		$base_url      = (isset($this->links['self']['href'])) ? $this->links['self']['href'] : $this->links['self'];
		$relation_data = array();
		foreach ($relation_resources as $relation_resource) {
			$relation_data[] = [
				'type' => $relation_resource->get_type(),
				'id'   => $relation_resource->get_id(),
			];
		}
		
		$relation = array(
			'links' => array(
				'self'    => $base_url.'/relationships/'.$key,
				'related' => $base_url.'/'.$key,
			),
			'data'  => $relation_data,
		);
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
 * this will end up in response.data.links.{$key}
 * if $also_root is set to true, it will also end up in response.links.{$key}
 * 
 * @see jsonapi\response->add_link()
 * 
 * @param  string $key
 * @param  mixed  $link      objects are converted in arrays, @see base::convert_object_to_array()
 * @param  mixed  $meta_data should not be used if $link is non-string
 * @param  string $level     one of the predefined ones in ::LINK_LEVEL_*
 * @return void
 */
public function add_link($key, $link, $meta_data=null, $level=self::LINK_LEVEL_DATA) {
	if (is_object($link)) {
		$link = parent::convert_object_to_array($link);
	}
	
	// can not combine both raw link object and extra meta data
	if ($meta_data && is_string($link) == false) {
		throw new \Exception('link "'.$key.'" should be a string if meta data is provided separate');
	}
	
	if ($level === self::LINK_LEVEL_DATA) {
		$revert_root_level = (isset($this->links[$key])) ? $this->links[$key] : null;
	}
	
	parent::add_link($key, $link, $meta_data);
	
	if ($level === self::LINK_LEVEL_DATA || $level === self::LINK_LEVEL_BOTH) {
		$this->primary_links[$key] = $this->links[$key];
	}
	if ($level === self::LINK_LEVEL_DATA) {
		if ($revert_root_level) {
			$this->links[$key] = $revert_root_level;
		}
		else {
			unset($this->links[$key]);
		}
	}
}

/**
 * sets the link to the request used to give this response
 * this will end up in response.links.self and response.data.links.self
 * this overrides the jsonapi\response->set_self_link() which only adds it to response.links.self
 * 
 * @see jsonapi\response->set_self_link()
 * 
 * by default this is already set using $_SERVER variables
 * use this method to override this default behavior
 * @see jsonapi\response::__construct()
 * 
 * @param  string $link
 * @param  mixed  $meta_data optional, meta data as key-value pairs
 *                           objects are converted in arrays, @see base::convert_object_to_array()
 * @return void
 */
public function set_self_link($link, $meta_data=null) {
	parent::set_self_link($link, $meta_data);
	
	if (self::$self_link_data_level == self::SELF_LINK_SERVER) {
		$this->add_link($key='self', $link, $meta_data);
	}
	if (self::$self_link_data_level == self::SELF_LINK_TYPE) {
		$link = '/'.$this->primary_type.'/'.$this->primary_id;
		$this->add_link($key='self', $link, $meta_data);
	}
}

/**
 * adds meta data to the default self link
 * this will end up in response.links.self.meta.{$key} and response.data.links.self.meta.{$key}
 * this overrides the jsonapi\response->add_self_link_meta() which only adds it to response.links.self.meta.{$key}
 * 
 * @see jsonapi\response->add_self_link_meta()
 * 
 * @note you can also use ->set_self_link() with the whole meta object at once
 * 
 * @param  string  $key
 * @param  mixed   $meta_data objects are converted in arrays, @see base::convert_object_to_array()
 * @return void
 */
public function add_self_link_meta($key, $meta_data) {
	parent::add_self_link_meta($key, $meta_data);
	
	$this->primary_links['self'] = $this->links['self'];
}

/**
 * adds some meta data
 * this will end up in response.meta.{$key} or response.data.meta.{$key} ..
 * .. depending on $data_level
 * 
 * @param  string  $key
 * @param  mixed   $meta_data  objects are converted in arrays, @see base::convert_object_to_array()
 * @param  boolean $data_level optional, defaults to false
 * @return void
 */
public function add_meta($key, $meta_data, $data_level=false) {
	if ($data_level == false) {
		return parent::add_meta($key, $meta_data);
	}
	
	if (is_object($meta_data)) {
		$meta_data = parent::convert_object_to_array($meta_data);
	}
	
	$this->primary_meta_data[$key] = $meta_data;
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
