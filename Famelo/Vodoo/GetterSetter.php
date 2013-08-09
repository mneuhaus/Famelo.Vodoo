<?php
namespace Famelo\Vodoo\Traits;

trait GettersSetters {

	public function __call($name,$arguments) {
		if(count($arguments)>0){
			$value = $arguments[0];
		}

		switch (true) {
			// Magic Setter Function: setProperty($value) sets $this->property = $value;
			case substr($name,0,3) == "set":
				$this->_set($name,$value);
				break;

			// Magic Setter Function: getProperty() return $this->property
			case substr($name,0,3) == "add":
				$this->_add($name,$value);
				break;

			case substr($name,0,6) == "remove":
				return $this->_remove($name,$value);
				break;

			default:
#               echo "trying to call".$name."<br />";
				break;
		}
	}

	public function _set($name,$value){
		$property = $this->getPropertyName(substr($name,3));
		if($property === false)
			throw new \Exception('The Property '.$property.' you are trying to set isn\'t defined in this class '.get_class($this).".");
#       echo $name." "."<br />";
		/*
		if ($this->posts instanceof \TYPO3\Flow\Persistence\LazyLoadingProxy) {
			$this->posts->_loadRealInstance();
		}
		$this->removePosts($this->posts);
		foreach($posts as $post)
			$this->addPost($post);
		*/
		$this->$property = $value;
	}

	public function _get($name){
		if(stristr($name, ".")){
			$parts = explode(".", substr($name,3));
			$name = lcfirst(array_shift($parts));
			$path = implode(".", $parts) ;
			return \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($this->$name, $path);
		}else{
			$property = $this->getPropertyName(substr($name,3));
			if($property === false)
				throw new \Exception('The Property '.$property.' you are trying to get isn\'t defined in this class.');
			if ($this->$property instanceof \TYPO3\Flow\Persistence\LazyLoadingProxy) {
				$this->$property->_loadRealInstance();
			}
			return $this->$property;
		}
	}

	public function _add($name, $value){
		$model = $this->getModelName();
		$property = $this->getPropertyName(substr($name, 3));

		if(!property_exists(get_class($this),$property))
			throw new \Exception('The Property '.$property.' you are trying to add isn\'t defined in this class.');
		array_push($this->$property, $value);
	}

	public function _has($name,$value){
		$property = $this->getPropertyName(substr($name,3));
		$pluralized = Inflect::pluralize($property);
		if ($this->$pluralized instanceof \TYPO3\Flow\Persistence\LazyLoadingProxy) {
			$this->$pluralized->_loadRealInstance();
		}
		return $this->$pluralized->contains($value);
	}

	public function _remove($name,$value){
		$property = $this->getPropertyName(substr($name,3));
		if(!property_exists(get_class($this),$property))
			throw new \Exception('The Property '.$property.' you are trying to set isn\'t defined in this class '.get_class($this).".");
		$pluralized = Inflect::pluralize($property);

		if ($this->$pluralized instanceof \TYPO3\Flow\Persistence\LazyLoadingProxy) {
			$this->$pluralized->_loadRealInstance();
		}

		$this->$pluralized->detach($value);
	}

	public function getPropertyName($property = null){
		$properties = get_class_vars(get_class($this));
		foreach($properties as $p => $value){
			if(strtolower($property) == strtolower($p) || strtolower($property . 's') == strtolower($p)){
				return $p;
			}
		}
		return false;
	}

	public function method_exists($method){
		$model = $this->getModelName();
		switch (true) {
			case substr($method,0,3) == "set":
				$property = strtolower(substr($method,3));
				if(property_exists(get_class($this),$property))
					return true;
				break;
			case substr($method,0,3) == "get":
				$property = strtolower(substr($method,3));
				if(property_exists(get_class($this),$property))
					return true;
				break;
			case substr($method,0,3) == "add":
				$property = strtolower(substr($method,3));
				$pluralized = Inflect::pluralize($property);
				if(property_exists(get_class($this),$pluralized))
					return true;
				break;
			case substr($method,0,3) == "has":
				break;
			default:
				return false;
				break;
		}
	}

}