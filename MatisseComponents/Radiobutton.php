<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class RadiobuttonProperties extends HtmlComponentProperties
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
  public $name = type::id;
  /**
   * @var string
   */
  public $script = '';
  /**
   * @var string
   */
  public $testValue = '';
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
  protected static $propertiesClass = RadiobuttonProperties::class;

  /** @var RadiobuttonProperties */
  public $props;

  protected $autoId = true;
  protected $containerTag = 'label';

  protected function render ()
  {
    $prop = $this->props;

    $this->attr ('for', "{$prop->id}Field");
    $this->attr ('title', $prop->tooltip);

//            if (isset($this->style()->icon) && $this->style()->icon_align == 'left')
//                $this->renderIcon();

    $this->begin ('input');
    $this->attr ('id', "{$prop->id}Field");
    $this->attr ('type', 'radio');
    $this->attr ('value', $prop->get ('value'));
    $this->attr ('name', $prop->name);
    $this->attrIf ($prop->checked ||
                   (isset($prop->testValue) &&
                    $prop->value == $prop->testValue), 'checked', 'checked');
    $this->attrIf ($prop->disabled, 'disabled', 'disabled');
    $this->attr ('onclick', $prop->script);
    $this->end ();

//            if (isset($this->style()->icon) && $this->style()->icon_align == 'center')
//                $this->renderIcon();

    if (isset($prop->label)) {
      $this->begin ('span');
      $this->attr ('class', 'text');
      $this->setContent ($prop->label);
      $this->end ();
    }

//            if (isset($this->style()->icon) && $this->style()->icon_align == 'right')
//                $this->renderIcon();

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
