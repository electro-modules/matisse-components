<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Http\Lib\Http;
use Electro\Interfaces\Http\Shared\CurrentRequestInterface;
use Electro\Plugins\MatisseComponents\Config\MatisseComponentsModule;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Lib\JavascriptCodeGen;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\is;
use Matisse\Properties\TypeSystem\type;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
  public $maxFileSize = type::number;
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
  /**
   * @var string
   */
  public $value = '';
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
  /** @var CurrentRequestInterface */
  private $request;

  public function __construct (CurrentRequestInterface $currentRequest)
  {
    $this->request = $currentRequest;
    parent::__construct ();
  }

  /**
   * Handles file uploads.
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface      $response
   * @return ResponseInterface
   */
  static function dropzoneUpload ($request, $response)
  {
    return Http::jsonResponse ($response, []);
  }

  protected function init ()
  {
    parent::init ();
    $this->context->getAssetsService ()
                  ->addStylesheet ('lib/dropzone/dist/min/dropzone.min.css')
                  ->addScript ('lib/dropzone/dist/min/dropzone.min.js')
                  ->addInlineScript (self::CLIENT_SIDE_CODE, 'init-dropzone');
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
      'url'                          => $prop->url
        ?: enum ('/', $this->request->getAttribute ('appBaseUri'), MatisseComponentsModule::DROPZONE_UPLOAD_URL),
      //      'accept' =>                       getHandler ('onAccept'),
      'acceptedFiles'                => $prop->acceptedFiles,
      'maxFiles'                     => $prop->maxFiles,
      'maxFileSize'                  => $prop->maxFileSize,
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

