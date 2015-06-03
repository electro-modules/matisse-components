<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\Attributes\VisualComponentAttributes;
use Selene\Matisse\VisualComponent;

class ImageFieldAttributes extends VisualComponentAttributes
{
  public $name;
  public $value;
  public $noClear    = false;
  public $disabled    = false;
  public $sortable    = false;
  public $crop        = true;
  public $imageWidth = 160;
  public $imageHeight;

  protected function typeof_name () { return AttributeType::ID; }

  protected function typeof_value () { return AttributeType::TEXT; }

  protected function typeof_noClear () { return AttributeType::BOOL; }

  protected function typeof_disabled () { return AttributeType::BOOL; }

  protected function typeof_sortable () { return AttributeType::BOOL; }

  protected function typeof_crop () { return AttributeType::BOOL; }

  protected function typeof_imageWidth () { return AttributeType::NUM; }

  protected function typeof_imageHeight () { return AttributeType::NUM; }
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
    $this->page->enableFileUpload = true;
    $this->beginTag ('input');
    $this->addAttribute ('type', 'hidden');
    $this->addAttribute ('id', "{$this->attrs()->id}Field");
    if (isset($this->attrs ()->name))
      $this->addAttribute ('name', $this->attrs ()->name);
    else $this->addAttribute ('name', $this->attrs ()->id);
    $this->addAttribute ('value', $this->attrs ()->value);
    $this->endTag ();

    if (isset($this->attrs ()->value)) {
      $image = new Image($this->context, [
        'value' => $this->attrs ()->value,
        'class' => 'img-thumbnail'
      ], [
        'width'  => $this->attrs ()->imageWidth,
        'height' => $this->attrs ()->imageHeight,
        'crop'   => $this->attrs ()->getScalar ('crop')
      ]);
      $this->runPrivate ($image);
    }
    else $this->addTag ('div', [
      'class' => 'emptyImg',
      'style' => enum (';',
        "width:{$this->attrs()->imageWidth}px",
        isset($this->attrs ()->imageHeight) ? "height:{$this->attrs()->imageHeight}px" : ''
      )
    ]);

    $this->beginTag ('div');
    $this->addAttribute ('class', 'buttons');

    $this->beginTag ('div');
    $this->addAttribute ('class', 'fileBtn');
    $this->beginContent ();

    $button = new Button($this->context, [
      'disabled' => $this->attrs ()->disabled,
      'class'    => 'btn-default glyphicon glyphicon-picture'
    ]);
    $this->runPrivate ($button);

    $this->addTag ('input', [
      'id'        => "{$this->attrs()->id}File",
      'type'      => 'file',
      'class'     => 'fileBtn',
      'size'      => 1,
      'tabindex'  => -1,
      'onchange'  => "ImageField_onChange('{$this->attrs()->id}')",
      'name'      => isset($this->attrs ()->name) ? $this->attrs ()->name . '_file' : 'file',
      'hidefocus' => $this->page->browserIsIE ? 'true' : null
    ]);

    $this->endTag ();

    if (!$this->attrs ()->noClear) {
      $button = new Button($this->context, [
        'id'       => "{$this->attrs()->id}Clear",
        'script'   => "ImageField_clear('{$this->attrs()->id}')",
        'disabled' => $this->attrs ()->disabled || !isset($this->attrs ()->value),
        'class'    => 'btn-default glyphicon glyphicon-remove'
      ]);
      $this->runPrivate ($button);
    }
    if ($this->attrs ()->sortable) {
      $button = new Button($this->context, [
        'action'   => 'down',
        'param'    => $this->attrs ()->value,
        'disabled' => $this->attrs ()->disabled || !isset($this->attrs ()->value),
        'class'    => 'ImageField_next'
      ]);
      $this->runPrivate ($button);

      $button = new Button($this->context, [
        'action'   => 'up',
        'param'    => $this->attrs ()->value,
        'disabled' => $this->attrs ()->disabled || !isset($this->attrs ()->value),
        'class'    => 'ImageField_prev'
      ]);
      $this->runPrivate ($button);
    }
    echo '</div><div class="end"></div>';
  }
}

