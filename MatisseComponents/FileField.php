<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Interfaces\ContentRepositoryInterface;
use Electro\Plugins\MatisseComponents\Handlers\FileFieldHandler;
use Electro\Plugins\MatisseComponents\Models\File;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;

class FileFieldProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $browseButtonClass = 'fa-folder-open';
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
  public $downloadButtonClass = 'fa fa-download'; //allow 'field[]'
  /**
   * @var string
   */
  public $fileIsMissingText = '&lt; file is missing &gt;';
  /**
   * @var string
   */
  public $name = '';
  /**
   * @var bool
   */
  public $noClear = false;
  /**
   * @var string
   */
  public $value = '';
}

class FileField extends HtmlComponent
{
  const propertiesClass = FileFieldProperties::class;

  /** @var FileFieldProperties */
  public $props;

  protected $autoId = true;

  /** @var ContentRepositoryInterface */
  private $contentRepo;

  public function __construct (ContentRepositoryInterface $contentRepo)
  {
    parent::__construct ();
    $this->contentRepo = $contentRepo;
  }

  protected function preRender ()
  {
    $this->props->containerId = $this->props->id . 'Container';
    if ($this->props->value)
      $this->addClass ('with-file');
    parent::preRender ();
  }

  protected function render ()
  {
    $prop  = $this->props;
    $value = $prop->value ?: '';
    $id    = $prop->id;
    $name  = empty ($prop->name) ? $id : $prop->name;

    $this->context->enableFileUpload ();
    $this->beginContent ();

    if (!empty($value)) {
      /** @var File $file */
      $file     = File::where ('path', $value)->first ();
      $fileName = $file ? "$file->name.$file->ext" : $prop->fileIsMissingText;
    }
    else $fileName = null;

    echo html ([
      h ("input#{$id}", [
        'type'     => 'file',
        'name'     => $name . FileFieldHandler::FILE_FIELD_SUFFIX,
        'onchange' => "$(this).parent().addClass('with-file').find('.custom-input').attr('data-file',$(this).val().split(/\\/|\\\\/).pop())",
      ]),
      h (".custom-input", [
        'class'     => $prop->browseButtonClass,
        'data-file' => $fileName,
      ]),
      h ("a.btn.btn-default", [
        'type'  => 'button',
        'class' => $prop->downloadButtonClass,
        'href'  => $fileName
          ? sprintf ('%s?f=%s.%s', $this->contentRepo->getFileUrl ($value), urlencode ($file->name), $file->ext)
          : '',
        'style' => $value ? null : 'display:none',
      ]),
      h ("button.btn.btn-default", [
        'type'    => 'button',
        'class'   => $prop->clearButtonClass,
        'onclick' => "$(this).parent().removeClass('with-file').find('.custom-input').removeAttr('data-file').end().find('[type=hidden],[type=file]').val('')",
      ]),
      h ("input", [
        'type'  => 'hidden',
        'name'  => $name,
        'value' => $value,
      ]),
    ]);

  }

}
