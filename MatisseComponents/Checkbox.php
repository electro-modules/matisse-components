<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\Types\type;

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
   * @var int
   */
  public $value = '1';
}

class Checkbox extends HtmlComponent
{
  protected $autoId = true;

  protected $containerTag = 'label';

  /**
   * Returns the component's attributes.
   * @return CheckboxProperties
   */
  public function props ()
  {
    return $this->props;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return CheckboxProperties
   */
  public function newProperties ()
  {
    return new CheckboxProperties($this);
  }

  protected function preRender ()
  {
    $attr = $this->props ();

    // Output a hudden checkbox that will submit value 0 if the visible checkbox is not checked.
    echo "<input type=checkbox name=\"$attr->name\" value=0 checked style=\"display:none\">";

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
    $attr = $this->props ();
    //if (isset($this->style()->icon) && $this->style()->icon_align == 'left')
    //    $this->renderIcon();

//    $this->beginTag ('label');
//    $this->addAttribute ('for', "{$attr->id}Field");

    $this->begin ('input');
    $this->attr ('id', $attr->id);
    $this->attr ('type', 'checkbox');
    $this->attr ('value', $attr->get ('value'));
    $this->attr ('name', $attr->name);
    $this->attrIf ($attr->checked ||
                   (isset($attr->testValue) &&
                    $attr->value == $attr->testValue), 'checked');
    $this->attrIf ($attr->disabled, 'disabled');
    $this->attr ('onclick', $attr->script);
    $this->end ();

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
