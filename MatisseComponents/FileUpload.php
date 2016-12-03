<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Interfaces\ContentRepositoryInterface;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;

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
  public $name = ''; //allow 'field[]'
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
  const propertiesClass = FileUploadProperties::class;

  /** @var FileUploadProperties */
  public $props;

  protected $autoId = true;

  /** @var ContentRepositoryInterface */
  private $contentRepo;

  public function __construct (ContentRepositoryInterface $contentRepo)
  {
    parent::__construct ();
    $this->contentRepo = $contentRepo;
  }

  protected function render ()
  {
    $prop  = $this->props;
    $value = $prop->get ('value', '');
    $id    = $prop->id;
    $name  = $prop->name;

    $this->context->enableFileUpload ();

    $this->begin ('div');
    $this->attr ('id', $id . (empty($value) ? 'File' : 'Text'));
    $this->attr ('class', enum (' ',
      $this->className,
      $this->cssClassName,
      $prop->class,
      $prop->disabled ? 'disabled' : null,
      empty($value) ? '' : 'with-file'
    ));
    if (!empty($prop->htmlAttrs))
      echo ' ' . $prop->htmlAttrs;
    if ($this->htmlAttrs)
      foreach ($this->htmlAttrs as $k => $v)
        echo " $k=\"" . htmlspecialchars ($v) . '"';

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
        'class'   => "btn btn-default $prop->clearButtonClass",
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
    $name = $this->props->name;
    $this->begin ('input');
    $this->attr ('type', 'file');
    $this->attr ('name', "{$name}_file");
    $this->attr ('onchange',
      "$(this).parent().children(':nth-child(2)').attr('data-file',$(this).val().split(/\\/|\\\\/).pop())");
    $this->end ();

    $this->begin ('div');
    $this->attr ('class', 'custom-input');

    $this->end ();
  }
}
