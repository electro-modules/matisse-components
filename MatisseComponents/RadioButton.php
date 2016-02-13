<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class RadioButtonProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $autofocus = false;
  /**
   * @var bool
   */
  public $checked = false;
  /**
   * @var bool
   */
  public $disabled = false;
  /**
   * @var string
   */
  public $label = '';
  /**
   * @var string
   */
  public $name = ''; //allow 'field[]'
  /**
   * @var string
   */
  public $script = type::string;
  /**
   * @var string
   */
  public $testValue = type::string;
  /**
   * @var string
   */
  public $tooltip = '';
  /**
   * @var string
   */
  public $value = '';
}

class RadioButton extends HtmlComponent
{
  protected static $propertiesClass = RadioButtonProperties::class;

  /** @var RadioButtonProperties */
  public $props;

  protected $autoId       = true;
  protected $containerTag = 'label';

  protected function preRender ()
  {
    $id = property ($this->props, 'id');
    if ($id)
      $this->props->containerId = $this->props->id . 'Container';
    parent::preRender ();
  }

  protected function render ()
  {
    $prop = $this->props;

    $this->begin ('input');
    $this->attr ('id', $prop->id);
    $this->attr ('type', 'radio');
    $this->attr ('value', $prop->get ('value'));
    $this->attr ('name', $prop->name);
    $this->attrIf ($prop->checked || (isset($prop->testValue) && $prop->value === $prop->testValue), 'checked');
    $this->attrIf ($prop->disabled, 'disabled');
    $this->attr ('onclick', $prop->script);
    $this->end ();

    /** The checkmark */
    echo "<i></i>";

    if (isset($prop->label))
      echo "<span>$prop->label</span>";
  }

}
