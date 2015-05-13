<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\ComponentAttributes;
use Selene\Matisse\VisualComponent;
use Selene\Media;

class FileUploadAttributes extends ComponentAttributes
{
  public $name;
  public $value;
  public $no_clear = false;
  public $disabled = false;
  public $clear_button_class = 'fa fa-times';

  protected function typeof_name ()
  {
    return AttributeType::ID;
  }

  protected function typeof_value ()
  {
    return AttributeType::TEXT;
  }

  protected function typeof_no_clear ()
  {
    return AttributeType::BOOL;
  }

  protected function typeof_disabled ()
  {
    return AttributeType::BOOL;
  }

  protected function typeof_clear_button_class ()
  {
    return AttributeType::TEXT;
  }
}

class FileUpload extends VisualComponent
{

  protected $autoId = true;

  /**
   * Returns the component's attributes.
   *
   * @return FileUploadAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   *
   * @return FileUploadAttributes
   */
  public function newAttributes ()
  {
    return new FileUploadAttributes($this);
  }

  protected function preRender ()
  {
  }

  protected function postRender ()
  {
  }

  protected function render ()
  {
    $this->page->enableFileUpload = true;
    $attr                         = $this->attrs ();
    $value                        = $attr->get ('value', '');
    $id = $attr->id;
    $name = $attr->name;

    if ($this->autoId)
      $this->setAutoId ();
    $this->beginTag ('input');
    $this->addAttribute ('id', $id . (empty($value) ? 'File' : 'Text'));
    $this->addAttribute ('class', enum (' ',
      $this->className,
      $this->cssClassName,
      $this->attrs ()->class,
      $this->attrs ()->disabled ? 'disabled' : null
    ));
    if (!empty($this->attrs ()->html_attrs))
      echo ' ' . $this->attrs ()->html_attrs;

    if (empty($value)) {
      // File doesn't exist

      $this->addAttribute ('type', 'file');
      $this->addAttribute ('name', "{$name}_file");
      $this->endTag ();
    }
    else {
      // File exists

      $this->addAttribute ('type', 'text');
      $this->addAttribute ('value', Media::getOriginalFileName ($value));
      $this->addAttribute ('readonly', "");

      $this->addTag ('button', [
        'class' => "btn btn-default $attr->clear_button_class",
        'onclick' => "$('#{$id}Field').val('');var c=$('#{$id}Text').hide().attr('class');$('#{$id}File').show().attr('class',c);$(this).hide()"
      ]);

      $this->beginTag ('input');
      $this->addAttribute ('type', 'file');
      $this->addAttribute ('name', "{$name}_file");
      $this->addAttribute ('style', 'display: none');
      $this->addAttribute ('id', "{$id}File");
      $this->endTag ();
    }

    $this->beginTag ('input');
    $this->addAttribute ('type', 'hidden');
    $this->addAttribute ('id', "{$id}Field");
    if (isset($name))
      $this->addAttribute ('name', $name);
    else $this->addAttribute ('name', $id);
    $this->addAttribute ('value', $value);
    $this->endTag ();
    $this->handleFocus ();

  }
}
