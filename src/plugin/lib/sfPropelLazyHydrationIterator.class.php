<?php

/**
 * A resource-efficient Iterator, that allows large result sets to be used
 * directly in a foreach() loop.  It leverages Propel's recommended practice
 * of using a model class' hydrate() method to efficiently iterate over a
 * list of retrieved model objects.
 *
 * @package sfPropelLazyHydrationIteratorPlugin
 * @author John Lianoglou <prometheas@gmail.com>
 */
class sfPropelLazyHydrationIterator implements Iterator
{
  private $modelClassName;
  private $resultSet;

  /**
   * Constructor.
   *
   * @param string $model_class_name
   * @param Criteria $c
   * @param Connection $con
   * @param string $peer_name
   *
   * @author John Lianoglou <prometheas@gmail.com>
   */
  public function __construct( $model_class_name, $c, $con=null, $peer_name=null )
  {
    $this->modelClassName = $model_class_name;
    $this->modelPeerName  = empty($peer_name) ? $this->modelClassName.'Peer' : $peer_name;
    $this->resultSet      = call_user_func(array($this->modelPeerName, 'doSelectRS'), $c);
  }

  /**
   * Implements Iterator::rewind()
   * @author John Lianoglou <prometheas@gmail.com>
   */
  public function rewind()
  {
    return $this->resultSet->getIterator()->rewind();
  }

  /**
   * Implements Iterator::current()
   * @author John Lianoglou <prometheas@gmail.com>
   */
  public function current()
  {
    if (false !== $this->resultSet->getIterator()->current())
    {
      return $this->createAndHydrateCurrent();
    }

    return false;
  }

  /**
   * Implements Iterator::key()
   * @author John Lianoglou <prometheas@gmail.com>
   */
  public function key()
  {
    return $this->resultSet->key();
  }

  /**
   * Implements Iterator::next()
   * @author John Lianoglou <prometheas@gmail.com>
   */
  public function next()
  {
    if (false !== $this->resultSet->getIterator()->next())
    {
      return $this->createAndHydrateCurrent();
    }

    return false;
  }

  /**
   * Implements Iterator::valid()
   * @author John Lianoglou <prometheas@gmail.com>
   */
  public function valid()
  {
    return $this->resultSet->getIterator()->valid();
  }
  
  /**
   * Returns a hydrated instance of the appropriate model class, using
   * the row data at the ResultSet's current pointer position.
   *
   * @return mixed
   * @author John Lianoglou <prometheas@gmail.com>
   */
  protected function createAndHydrateCurrent()
  {
    $object = new $this->modelClassName();
    $object->hydrate( $this->resultSet );
    return $object;
  }

}
