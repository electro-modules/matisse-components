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
    $attr  = $this->attrs ();
    $value = $attr->get ('value', '');

    if ($this->autoId)
      $this->setAutoId ();
    $this->beginTag ('input');
    $this->addAttribute ('id', $attr->id . (empty($value) ? 'File' : 'Text'));
    $this->addAttribute ('class', enum (' ',
      $this->className,
      $this->cssClassName,
      $this->attrs ()->class,
      $this->attrs ()->css_class,
      $this->attrs ()->disabled ? 'disabled' : null
    ));
    if (!empty($this->attrs ()->html_attrs))
      echo ' ' . $this->attrs ()->html_attrs;

    if (!empty($value)) {
      // File exists

      $this->addAttribute ('type', 'text');
      $this->addAttribute ('value', Media::getOriginalFileName ($value));
      $this->addAttribute ('readonly', "");

      $this->beginTag ('input');
      $this->addAttribute ('type', 'file');
      $this->addAttribute('style', 'display: none');
      $this->addAttribute ('id', "{$attr->id}File");
      $this->endTag ();
      $this->addTag ('button', [
        'onclick' => "$('#{$attr->id}Field').val('');$('#{$attr->id}Text').hide();$('#{$attr->id}File').show();$(this).hide()"
      ], 'Clear');

    } else {
      // File doesn't exist

      $this->addAttribute ('type', 'file');
      $this->endTag ();
    }

    $this->beginTag ('input');
    $this->addAttribute ('type', 'hidden');
    $this->addAttribute ('id', "{$attr->id}Field");
    if (isset($attr->name))
      $this->addAttribute ('name', $attr->name);
    else $this->addAttribute ('name', $attr->id);
    $this->addAttribute ('value', $value);
    $this->endTag ();
    $this->handleFocus ();

  }
}
