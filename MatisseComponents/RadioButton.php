<?php
namespace Electro\Plugins\MatisseComponents;

use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\type;

class RadioButtonProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $autofocus = false;
  /**
   * @var bool
   */
  public $beforeLabelTemplate = "";
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
  const propertiesClass = RadioButtonProperties::class;

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

    echo $this->props->beforeLabelTemplate;

    if (exists ($prop->label))
      echo "<span>$prop->label</span>";
  }

}
