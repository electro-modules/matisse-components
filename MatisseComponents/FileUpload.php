<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\AttributeType;
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
    $attr  = $this->attrs ();
    $value = $attr->get ('value', '');
    $id    = $attr->id;
    $name  = $attr->name;

    $this->page->enableFileUpload = true;

    if ($this->autoId)
      $this->setAutoId ();
    $this->begin ('div');
    $this->attr ('id', $id . (empty($value) ? 'File' : 'Text'));
    $this->attr ('class', enum (' ',
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

      $this->begin ('input');
      $this->attr ('class', $this->cssClassName);
      $this->attr ('type', 'text');
      $this->attr ('value', Media::getOriginalFileName ($value));
      $this->attr ('readonly', "");

      $this->tag ('button', [
        'class'   => "btn btn-default $attr->clearButtonClass",
        'onclick' => "$('#{$id}Field').val('');$(this).parent().removeClass('with-file')",
      ]);

      $this->renderInputTypeFile ();
    }
    $this->end (); // container div

    $this->begin ('input');
    $this->attr ('type', 'hidden');
    $this->attr ('id', "{$id}Field");
    if (isset($name))
      $this->attr ('name', $name);
    else $this->attr ('name', $id);
    $this->attr ('value', $value);
    $this->end ();
  }

  private function renderInputTypeFile ()
  {
    $name = $this->attrs ()->name;
    $this->begin ('div');
    $this->attr ('class', 'custom-input');

    $this->begin ('input');
    $this->attr ('type', 'file');
    $this->attr ('name', "{$name}_file");
    $this->attr ('onchange', "$(this).parent().attr('data-file',$(this).val().split(/\\/|\\\\/).pop())");
    $this->end ();

    $this->end ();
  }
}
