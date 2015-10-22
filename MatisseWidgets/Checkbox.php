<?php
namespace Selenia\Plugins\MatisseWidgets;

use Selenia\Matisse\AttributeType;
use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\VisualComponent;

class CheckboxAttributes extends VisualComponentAttributes
{
  public $name;
  public $label;
  public $value     = 1;
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

class Checkbox extends VisualComponent
{
  protected $autoId = true;

  protected $containerTag = 'label';

  /**
   * Returns the component's attributes.
   * @return CheckboxAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return CheckboxAttributes
   */
  public function newAttributes ()
  {
    return new CheckboxAttributes($this);
  }

  protected function preRender ()
  {
    $attr = $this->attrs ();
    if ($this->autoId)
      $this->setAutoId ();
    $id = property ($attr, 'id');
    if ($id) {
      $attr->id = "$id-wrapper";
      parent::preRender ();
      $attr->id = $id;
    }
    else parent::preRender ();
  }

  protected function render ()
  {
    $attr = $this->attrs ();
    //if (isset($this->style()->icon) && $this->style()->icon_align == 'left')
    //    $this->renderIcon();

//    $this->beginTag ('label');
//    $this->addAttribute ('for', "{$attr->id}Field");

    $this->beginTag ('input');
    $this->addAttribute ('id', $attr->id);
    $this->addAttribute ('type', 'checkbox');
    $this->addAttribute ('value', $attr->get ('value'));
    $this->addAttribute ('name', $attr->name);
    $this->addAttributeIf ($attr->checked ||
                           (isset($attr->testValue) &&
                            $attr->value == $attr->testValue), 'checked', 'checked');
    $this->addAttributeIf ($attr->disabled, 'disabled');
    $this->addAttribute ('onclick', $attr->script);
    $this->endTag ();

    /** The checkmark */
    echo "<i></i>";

    //if (isset($this->style()->icon) && $this->style()->icon_align == 'center')
    //    $this->renderIcon();

//    $this->endTag ();
    if (isset($attr->label))
      echo "<span>$attr->label</span>";
//    if (isset($attr->label)) {
//      $this->endTag ();
//      $this->beginTag ('label');
//      $this->addAttribute ('for', "{$attr->id}Field");
//      $this->addAttribute ('title', $attr->tooltip);
//      $this->setContent ($attr->label);
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
