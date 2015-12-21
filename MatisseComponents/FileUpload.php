<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\Types\type;
use Selenia\Media;

class FileUploadProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $clearButtonClass = 'fa fa-times';
  /**
   * @var bool
   */
  public $disabled = false;
  /**
   * @var string
   */
  public $name = type::id;
  /**
   * @var bool
   */
  public $noClear = false;
  /**
   * @var string
   */
  public $value = '';
}

class FileUpload extends HtmlComponent
{

  protected $autoId = true;

  /**
   * Returns the component's attributes.
   *
   * @return FileUploadProperties
   */
  public function props ()
  {
    return $this->props;
  }

  /**
   * Creates an instance of the component's attributes.
   *
   * @return FileUploadProperties
   */
  public function newProperties ()
  {
    return new FileUploadProperties($this);
  }

  protected function postRender ()
  {
  }

  protected function preRender ()
  {
  }

  protected function render ()
  {
    $attr  = $this->props ();
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
    $name = $this->props ()->name;
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
