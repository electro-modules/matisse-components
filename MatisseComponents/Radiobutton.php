<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\Type;
use Selenia\Matisse\VisualComponent;

class RadiobuttonAttributes extends VisualComponentAttributes
{
  public $autofocus = false;
  public $checked   = false;
  public $disabled  = false;
  public $label;
  public $name;
  public $script;
  public $testValue;
  public $tooltip;
  public $value;

  protected function typeof_autofocus () { return Type::BOOL; }

  protected function typeof_checked () { return Type::BOOL; }

  protected function typeof_disabled () { return Type::BOOL; }

  protected function typeof_label () { return Type::TEXT; }

  protected function typeof_name () { return Type::ID; }

  protected function typeof_script () { return Type::TEXT; }

  protected function typeof_testValue () { return Type::TEXT; }

  protected function typeof_tooltip () { return Type::TEXT; }

  protected function typeof_value () { return Type::TEXT; }
}

class Radiobutton extends VisualComponent
{

  protected $autoId = true;

  protected $containerTag = 'label';

  /**
   * Returns the component's attributes.
   * @return RadiobuttonAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return RadiobuttonAttributes
   */
  public function newAttributes ()
  {
    return new RadiobuttonAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $this->attr ('for', "{$attr->id}Field");
    $this->attr ('title', $attr->tooltip);

//            if (isset($this->style()->icon) && $this->style()->icon_align == 'left')
//                $this->renderIcon();

    $this->begin ('input');
    $this->attr ('id', "{$attr->id}Field");
    $this->attr ('type', 'radio');
    $this->attr ('value', $attr->get ('value'));
    $this->attr ('name', $attr->name);
    $this->attrIf ($attr->checked ||
                   (isset($attr->testValue) &&
                            $attr->value == $attr->testValue), 'checked', 'checked');
    $this->attrIf ($attr->disabled, 'disabled', 'disabled');
    $this->attr ('onclick', $attr->script);
    $this->end ();

//            if (isset($this->style()->icon) && $this->style()->icon_align == 'center')
//                $this->renderIcon();

    if (isset($attr->label)) {
      $this->begin ('span');
      $this->attr ('class', 'text');
      $this->setContent ($attr->label);
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
