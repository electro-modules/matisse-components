<?php
namespace Electro\Plugins\MatisseComponents;

use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\type;

class CheckboxProperties extends HtmlComponentProperties
{
	/**
	 * @var bool
	 */
	public $beforeLabelTemplate = "";
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
  public $label = type::string;
  /**
   * > **Note:** it supports `field[]` syntax.
   * @var string
   */
  public $name = type::string;
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
  public $value = '1';
}

class Checkbox extends HtmlComponent
{
  const propertiesClass = CheckboxProperties::class;
  /** @var CheckboxProperties */
  public    $props;
  protected $autoId       = true;
  protected $containerTag = 'label';

  protected function preRender ()
  {
    $prop = $this->props;

    // Output a hidden checkbox that will submit an empty value if the visible checkbox is not checked.
    // Does not apply to checkboxes of array fields.

    if (exists($prop->name) && !str_endsWith ($prop->name, '[]'))
      echo "<input type=checkbox name=\"$prop->name\" value=\"\" checked style=\"display:none\">";
    $id = property ($prop, 'id');
    if ($id)
      $prop->containerId = $prop->id . 'Container';
    echo "<!--CHECKBOX-->";
    parent::preRender ();
  }

  protected function render ()
  {
    $prop = $this->props;

    $this->begin ('input');
    $this->attr ('id', $prop->id);
    $this->attr ('type', 'checkbox');
    $this->attr ('value', $prop->get ('value'));
    $this->attr ('name', $prop->name);
    $this->attrIf ($prop->checked || (isset($prop->testValue) && $prop->value === $prop->testValue), 'checked');
    $this->attrIf ($prop->disabled, 'disabled');
    $this->attr ('onclick', $prop->script);
    $this->end ();

		echo $this->props->beforeLabelTemplate;

    if (isset($prop->label))
      echo "<span>$prop->label</span>";
  }

}
