<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class CheckboxProperties extends HtmlComponentProperties
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
  public $value = '1';
}

class Checkbox extends HtmlComponent
{
  protected static $propertiesClass = CheckboxProperties::class;
  /** @var CheckboxProperties */
  public    $props;
  protected $autoId       = true;
  protected $containerTag = 'label';

  protected function preRender ()
  {
    $prop = $this->props;

    // Output a hidden checkbox that will submit an empty value if the visible checkbox is not checked.
    // Does not apply to checkboxes of array fields.

    if (!str_endsWith ($prop->name, '[]'))
      echo "<input type=checkbox name=\"$prop->name\" value=\"\" checked style=\"display:none\">";
    $id = property ($prop, 'id');
    if ($id) {
      $prop->id = "$id-wrapper";
      parent::preRender ();
      $prop->id = $id;
    }
    else parent::preRender ();
  }

  protected function render ()
  {
    $prop = $this->props;
    //if (isset($this->style()->icon) && $this->style()->icon_align == 'left')
    //    $this->renderIcon();

//    $this->beginTag ('label');
//    $this->addAttribute ('for', "{$prop->id}Field");

    $this->begin ('input');
    $this->attr ('id', $prop->id);
    $this->attr ('type', 'checkbox');
    $this->attr ('value', $prop->get ('value'));
    $this->attr ('name', $prop->name);
    $this->attrIf ($prop->checked || (isset($prop->testValue) && $prop->value === $prop->testValue), 'checked');
    $this->attrIf ($prop->disabled, 'disabled');
    $this->attr ('onclick', $prop->script);
    $this->end ();

    /** The checkmark */
    echo "<i></i>";

    //if (isset($this->style()->icon) && $this->style()->icon_align == 'center')
    //    $this->renderIcon();

//    $this->endTag ();
    if (isset($prop->label))
      echo "<span>$prop->label</span>";
//    if (isset($prop->label)) {
//      $this->endTag ();
//      $this->beginTag ('label');
//      $this->addAttribute ('for', "{$prop->id}Field");
//      $this->addAttribute ('title', $prop->tooltip);
//      $this->setContent ($prop->label);
//    }

    //if (isset($this->style()->icon) && $this->style()->icon_align == 'right')
    //    $this->renderIcon();
  }
  /*
      private function renderIcon() {
          $this->beginTag('img',array(
              'class' => 'icon icon_'.$this->style()->icon_align,
              'src'   => $this->style()->icon
          ));
          $this->endTag();
      }*/
}
