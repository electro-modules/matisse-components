<?php
namespace Selenia\Plugins\MatisseWidgets;

use Selenia\Matisse\AttributeType;
use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\VisualComponent;
use Selenia\Media;

class FileUploadAttributes extends VisualComponentAttributes
{
  public $name;
  public $value;
  public $noClear          = false;
  public $disabled         = false;
  public $clearButtonClass = 'fa fa-times';

  protected function typeof_name () { return AttributeType::ID; }

  protected function typeof_value () { return AttributeType::TEXT; }

  protected function typeof_noClear () { return AttributeType::BOOL; }

  protected function typeof_disabled () { return AttributeType::BOOL; }

  protected function typeof_clearButtonClass () { return AttributeType::TEXT; }
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
    $id                           = $attr->id;
    $name                         = $attr->name;

    if ($this->autoId)
      $this->setAutoId ();
    $this->beginTag ('div');
    $this->addAttribute ('id', $id . (empty($value) ? 'File' : 'Text'));
    $this->addAttribute ('class', enum (' ',
      $this->className,
      $this->cssClassName,
      $attr->class,
      $attr->disabled ? 'disabled' : null,
      empty($value) ? '' : 'with-file'
    ));
    if (!empty($attr->htmlAttrs))
      echo ' ' . $attr->htmlAttrs;

    if (empty($value)) {
      // File doesn't exist

      $this->renderInputTypeFile ();
    }
    else {
      // File exists

      $this->beginTag ('input');
      $this->addAttribute ('class', $this->cssClassName);
      $this->addAttribute ('type', 'text');
      $this->addAttribute ('value', Media::getOriginalFileName ($value));
      $this->addAttribute ('readonly', "");

      $this->addTag ('button', [
        'class'   => "btn btn-default $attr->clearButtonClass",
        'onclick' => "$('#{$id}Field').val('');$(this).parent().removeClass('with-file')"
      ]);

      $this->renderInputTypeFile ();
    }
    $this->endTag (); // container div

    $this->beginTag ('input');
    $this->addAttribute ('type', 'hidden');
    $this->addAttribute ('id', "{$id}Field");
    if (isset($name))
      $this->addAttribute ('name', $name);
    else $this->addAttribute ('name', $id);
    $this->addAttribute ('value', $value);
    $this->endTag ();
  }

  private function renderInputTypeFile ()
  {
    $name = $this->attrs ()->name;
    $this->beginTag ('div');
    $this->addAttribute ('class', 'custom-input');

    $this->beginTag ('input');
    $this->addAttribute ('type', 'file');
    $this->addAttribute ('name', "{$name}_file");
    $this->addAttribute ('onchange', "$(this).parent().attr('data-file',$(this).val().split(/\\/|\\\\/).pop())");
    $this->endTag ();

    $this->endTag ();
  }
}
