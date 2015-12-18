<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\AttributeType;
use Selenia\Matisse\VisualComponent;

class ImageFieldAttributes extends VisualComponentAttributes
{
  public $crop       = true;
  public $disabled   = false;
  public $imageHeight;
  public $imageWidth = 160;
  public $name;
  public $noClear    = false;
  public $sortable   = false;
  public $value;

  protected function typeof_crop () { return AttributeType::BOOL; }

  protected function typeof_disabled () { return AttributeType::BOOL; }

  protected function typeof_imageHeight () { return AttributeType::NUM; }

  protected function typeof_imageWidth () { return AttributeType::NUM; }

  protected function typeof_name () { return AttributeType::ID; }

  protected function typeof_noClear () { return AttributeType::BOOL; }

  protected function typeof_sortable () { return AttributeType::BOOL; }

  protected function typeof_value () { return AttributeType::TEXT; }
}

class ImageField extends VisualComponent
{

  protected $autoId = true;

  /**
   * Returns the component's attributes.
   * @return ImageFieldAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return ImageFieldAttributes
   */
  public function newAttributes ()
  {
    return new ImageFieldAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $this->page->enableFileUpload = true;

    $this->begin ('input');
    $this->attr ('type', 'hidden');
    $this->attr ('id', "{$attr->id}Field");
    if (isset($attr->name))
      $this->attr ('name', $attr->name);
    else $this->attr ('name', $attr->id);
    $this->attr ('value', $attr->value);
    $this->end ();

    if (isset($attr->value)) {
      $image = new Image($this->context, [
        'value' => $attr->value,
        'class' => 'img-thumbnail',
      ], [
        'width'  => $attr->imageWidth,
        'height' => $attr->imageHeight,
        'crop'   => $attr->getScalar ('crop'),
      ]);
      $this->attachAndRender ($image);
    }
    else $this->tag ('div', [
      'class' => 'emptyImg',
      'style' => enum (';',
        "width:{$attr->imageWidth}px",
        isset($attr->imageHeight) ? "height:{$attr->imageHeight}px" : ''
      ),
    ]);

    $this->begin ('div');
    $this->attr ('class', 'buttons');

    $this->begin ('div');
    $this->attr ('class', 'fileBtn');
    $this->beginContent ();

    $button = new Button($this->context, [
      'disabled' => $attr->disabled,
      'class'    => 'btn-default glyphicon glyphicon-picture',
    ]);
    $this->attachAndRender ($button);

    $this->tag ('input', [
      'id'        => "{$attr->id}File",
      'type'      => 'file',
      'class'     => 'fileBtn',
      'size'      => 1,
      'tabindex'  => -1,
      'onchange'  => "ImageField_onChange('{$attr->id}')",
      'name'      => isset($attr->name) ? $attr->name . '_file' : 'file',
      'hidefocus' => $this->page->browserIsIE ? 'true' : null,
    ]);

    $this->end ();

    if (!$attr->noClear) {
      $button = new Button($this->context, [
        'id'       => "{$attr->id}Clear",
        'script'   => "ImageField_clear('{$attr->id}')",
        'disabled' => $attr->disabled || !isset($attr->value),
        'class'    => 'btn-default glyphicon glyphicon-remove',
      ]);
      $this->attachAndRender ($button);
    }
    if ($attr->sortable) {
      $button = new Button($this->context, [
        'action'   => 'down',
        'param'    => $attr->value,
        'disabled' => $attr->disabled || !isset($attr->value),
        'class'    => 'ImageField_next',
      ]);
      $this->attachAndRender ($button);

      $button = new Button($this->context, [
        'action'   => 'up',
        'param'    => $attr->value,
        'disabled' => $attr->disabled || !isset($attr->value),
        'class'    => 'ImageField_prev',
      ]);
      $this->attachAndRender ($button);
    }
    echo '</div><div class="end"></div>';
  }
}

