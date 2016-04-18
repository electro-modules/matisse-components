<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Lib\JavascriptCodeGen;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;
use Selenia\Matisse\Properties\TypeSystem\type;

class DropzoneProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $acceptedFiles = '';
  /**
   * @var bool
   */
  public $autoProcessQueue = true;
  /**
   * @var int|null
   */
  public $maxFiles = type::number;
  /**
   * @var string
   */
  public $method = ['post', type::string, is::enum, ['post', 'put']];
  /**
   * @var string The field name under which a comma-separated list of temporary uploaded file paths will be submitted.
   */
  public $name = '';
  /**
   * @var int
   */
  public $parallelUploads = type::number;
  /**
   * @var string
   */
  public $url = '';
}

class Dropzone extends HtmlComponent
{
  const CLIENT_SIDE_CODE = <<<'JS'

    Dropzone.autoDiscover = false;

    Dropzone.prototype.addMock = function (name, metadata, thumbUrl) {
      // Create the mock file:
      var mockFile = { metadata: metadata, name: name };

      // Call the default addedfile event handler
      this.emit ("addedfile", mockFile);
      this.files.push (mockFile);

      // And optionally show the thumbnail of the file:
      if (thumbUrl)
        this.emit ("thumbnail", mockFile, thumbUrl);

      // Make sure that there is no progress bar, etc...
      this.emit ("complete", mockFile);

    };

    $('form').submit(function (ev) {
      var files = [];
      $('.Dropzone').each(function () {
        console.log(this.dropzone);
        this.dropzone.getAcceptedFiles().forEach (function (file) {
          if (file.status == 'success')
            files.push (file.xhr.responseText);
          else files.push ('');
        });

      });
      return false;
    });
JS;
  /** @var bool */
  const allowsChildren = true;
  
  const propertiesClass = DropzoneProperties::class;
  
  /** @var string */
  public $cssClassName = 'dropzone';
  /** @var DropzoneProperties */
  public $props;

  /** @var bool */
  protected $autoId = true;

  protected function init ()
  {
    parent::init ();
    $this->context->getAssetsService ()->addStylesheet ('lib/dropzone/dist/min/dropzone.min.css');
    $this->context->getAssetsService ()->addScript ('lib/dropzone/dist/min/dropzone.min.js');
    $this->context->getAssetsService ()->addInlineScript (self::CLIENT_SIDE_CODE, 'init-dropzone');
  }

  protected function postRender ()
  {
    parent::postRender ();
    echo html ([
      h ('input', [
        'type' => 'hidden',
        'name' => $this->props->id,
      ]),
    ]);
  }

  protected function render ()
  {
    $prop    = $this->props;
    $options = JavascriptCodeGen::makeOptions ([
      'url'                          => $prop->url,
      //      'accept' =>                       getHandler ('onAccept'),
      'acceptedFiles'                => $prop->acceptedFiles,
      'maxFiles'                     => $prop->maxFiles,
      'clickable'                    => true,
      'addRemoveLinks'               => true,
      'parallelUploads'              => $prop->parallelUploads,
      'method'                       => $prop->method,
      'autoProcessQueue'             => $prop->autoProcessQueue,
      'dictDefaultMessage'           => "Arraste ficheiros para aqui ou clique para escolher os ficheiros a enviar.",
      'dictInvalidFileType'          => 'Ficheiro inválido',
      'dictFileTooBig'               => 'Ficheiro demasiado grande',
      'dictResponseError'            => 'Erro ao enviar',
      'dictCancelUpload'             => 'Cancelar',
      'dictCancelUploadConfirmation' => 'Tem a certeza?',
      'dictRemoveFile'               => 'Apagar',
      'dictMaxFilesExceeded'         => 'Não pode inserir mais ficheiros',
    ], '  ');

    $this->context->getAssetsService ()->addInlineScript (<<<JS
(function(element){
  var dropzone = new Dropzone (element[0], $options);
}) ($('#$prop->id'));
JS
    );
  }

}

