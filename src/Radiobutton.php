<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\Attributes\VisualComponentAttributes;
use Selene\Matisse\VisualComponent;

class RadiobuttonAttributes extends VisualComponentAttributes
{
  public $name;
  public $label;
  public $value;
  public $disabled  = false;
  public $checked   = false;
  public $autofocus = false;
  public $tooltip;
  public $script;
  public $testValue;

  protected function typeof_name () { return AttributeType::ID; }

  protected function typeof_label () { return AttributeType::TEXT; }

  protected function typeof_value () { return AttributeType::TEXT; }

  protected function typeof_disabled () { return AttributeType::BOOL; }

  protected function typeof_checked () { return AttributeType::BOOL; }

  protected function typeof_autofocus () { return AttributeType::BOOL; }

  protected function typeof_tooltip () { return AttributeType::TEXT; }

  protected function typeof_script () { return AttributeType::TEXT; }

  protected function typeof_testValue () { return AttributeType::TEXT; }
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
    $this->addAttribute ('for', "{$this->attrs()->id}Field");
    $this->addAttribute ('title', $this->attrs ()->tooltip);

//            if (isset($this->style()->icon) && $this->style()->icon_align == 'left')
//                $this->renderIcon();

    $this->beginTag ('input');
    $this->addAttribute ('id', "{$this->attrs()->id}Field");
    $this->addAttribute ('type', 'radio');
    $this->addAttribute ('value', $this->attrs ()->get ('value'));
    $this->addAttribute ('name', $this->attrs ()->name);
    $this->addAttributeIf ($this->attrs ()->checked ||
                           (isset($this->attrs ()->testValue) &&
                            $this->attrs ()->value == $this->attrs ()->testValue), 'checked', 'checked');
    $this->addAttributeIf ($this->attrs ()->disabled, 'disabled', 'disabled');
    $this->addAttribute ('onclick', $this->attrs ()->script);
    $this->endTag ();

//            if (isset($this->style()->icon) && $this->style()->icon_align == 'center')
//                $this->renderIcon();

    if (isset($this->attrs ()->label)) {
      $this->beginTag ('span');
      $this->addAttribute ('class', 'text');
      $this->setContent ($this->attrs ()->label);
      $this->endTag ();
    }

//            if (isset($this->style()->icon) && $this->style()->icon_align == 'right')
//                $this->renderIcon();

    $this->handleFocus ();
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
