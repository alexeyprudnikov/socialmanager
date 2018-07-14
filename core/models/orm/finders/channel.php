<?php

/**
 * Class ChannelFinder
 * @author: alexeyprudnikov
 */
class ChannelFinder extends Finder {

    protected function loadElement($class = null, $data = array()) {
        if (is_null($class)) return false;

        $typeStr = '';
        if(array_key_exists('i_Type', $data)) {
            $typeStr = Core::getChannelTypeString($data['i_Type']);
        }

        $class = $typeStr.$class;
        if (!class_exists($class)) return false;
        $element = new $class($data);
        $element->setTypeStr($typeStr);
        return $element;
    }

	public static function find($filter = array(), $orderby = null, $offset = 0, $limit = 0) {
		$self = new static();
		$query = 'SELECT * FROM tbl_channels WHERE '.$self->getWhereByFilter($filter).
			' ORDER BY '.$self->getOrderBy($orderby);

		//add limit+offset
		if ($limit > 0) {
			$query .= ' LIMIT '.($offset > 0 ? (int)$offset : 0).', '.(int)$limit;
		}
		//get result
		#echo $query;die();
		$result = $self->Database->select($query);
		return $self->loadCollection('Channel', $result);
	}

	public static function findById($id) {
        return self::find(array('id'=>$id))->getByIndex(0);
    }

	/**
	 * @param string $orderby
	 * @return string SQL - OrderBy Statement
	 */
	protected function getOrderBy( $orderby = '' ) {
		//set sorting order
		switch( $orderby )
		{
			case 'id:asc':
			default:
				$ordby = 'i_Id ASC';
				break;
			case 'id:desc':
				$ordby = 'i_Id DESC';
				break;

			case 'create-date:asc':
				$ordby = 'd_CreateDate ASC';
				break;
			case 'create-date:desc':
				$ordby = 'd_CreateDate DESC';
				break;

			case 'type:asc':
				$ordby = 'i_Type ASC';
				break;
			case 'type:desc':
				$ordby = 'i_Type DESC';
				break;

		}
		return $ordby;
	}

	/**
	 * @param string $filter
	 * @param string $cond
	 * @return bool|string
	 */
	protected function getWhereByFilter( $filter = '' , $cond = 'AND' ) {
		$where = '';
		if (is_array($filter)){
			foreach( $filter as $key => $value ) {
				switch( strtolower( $key ) ) {
					case 'and' :
					case 'or' :
						$where.= ' '.$cond.' ( '.$this->getWhereByFilter( $value, strtolower($key)).' )';
						break;

					case 'disabled' :
						if( $value != '-1' ) {
							$where.= ' '.$cond.' b_Disabled="'.(int)$value.'"';
						}
						break;

					case 'authorized' :
						if( $value != '-1' ) {
							$where.= ' '.$cond.' b_Authorized="'.(int)$value.'"';
						}
						break;

					case 'type' :
						if( $value != '-1' ) {
							$where.= ' '.$cond.' i_Type="'.(int)$value.'"';
						}
						break;

					case 'id' :
						if (!empty($value))
							$where.= ' '.$cond.' i_Id IN ('.(is_array($value) ? implode(',', $value) : $value).')';
						break;

					case 'notid' :
						if (empty($value)) $value = '0';
						$where.= ' '.$cond.' i_Id NOT IN ('.(is_array($value) ? implode(',', $value) : $value).')';
						break;
				}
			}
		}
		return empty($where) ? '1' : substr($where, strlen($cond)+1);
	}
}