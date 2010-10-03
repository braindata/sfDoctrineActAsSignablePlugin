<?php

/**
 * Easily add created and updated by fields to your doctrine records that are
 * automatically set when records are saved.
 *
 * @package     sfDoctrineActAsSignablePlugin
 * @subpackage  template
 * @since       1.0
 * @author      Vitaliy Tverdokhlib <new2@ua.fm>
 * @author      Tomasz Ducin <tomasz.ducin@gmail.com>
 */
class Doctrine_Template_Signable extends Doctrine_Template
{

  /**
   * Array of Signable options
   *
   * @var string
   */
  protected $_options = array(
    'created' => array(
      'name' => 'created_by',
      'type' => 'integer',
      'disabled' => false,
      'expression' => false,
      'options' => array()
    ),
    'updated' => array(
      'name' => 'updated_by',
      'type' => 'integer',
      'disabled' => false,
      'expression' => false,
      'onInsert' => true,
      'options' => array()
    ),
  );

  /**
   * __construct
   *
   * @param string $array
   * @return void
   */
  public function __construct(array $options = array())
  {
    $this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $options);
  }

  /**
   * Set table definition for signable behavior.
   *
   * @return void
   */
  public function setTableDefinition()
  {
    if (!$this->_options['created']['disabled'])
    {
      $this->hasColumn(
        $this->_options['created']['name'],
        $this->_options['created']['type'],
        null,
        $this->_options['created']['options']
      );
    }
    if (!$this->_options['updated']['disabled'])
    {
      $this->hasColumn(
        $this->_options['updated']['name'],
        $this->_options['updated']['type'],
        null,
        $this->_options['updated']['options']
      );
    }
    $this->addListener(new Doctrine_Template_Listener_Signable($this->_options));
  }
}
