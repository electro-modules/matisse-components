<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Http\Lib\Http;
use Electro\Interfaces\ContentRepositoryInterface;
use Electro\Interfaces\Http\Shared\CurrentRequestInterface;
use Electro\Plugins\IlluminateDatabase\Services\ModelController;
use Electro\Plugins\MatisseComponents\Config\MatisseComponentsModule;
use Electro\Plugins\MatisseComponents\Models\File;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Lib\JavascriptCodeGen;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\is;
use Matisse\Properties\TypeSystem\type;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DropzoneProperties extends HtmlComponentProperties
{
  public $previewTemplate = <<<HTML
<div class="dz-preview dz-file-preview">\n  <div class="dz-image"><img data-dz-thumbnail /></div>\n  <div class="dz-details">\n    <div class="dz-size"><span data-dz-size></span></div>\n    <div class="dz-filename"><span data-dz-name></span></div>\n  </div>\n  <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>\n  <div class="dz-error-message"><span data-dz-errormessage></span></div>\n  <div class="dz-success-mark">\n    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">\n      <title>Check</title>\n      <defs></defs>\n      <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">\n        <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF" sketch:type="MSShapeGroup"></path>\n      </g>\n    </svg>\n  </div>\n  <div class="dz-error-mark">\n    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">\n      <title>Error</title>\n      <defs></defs>\n      <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">\n        <g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475">\n          <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup"></path>\n        </g>\n      </g>\n    </svg>\n  </div>\n<a style="display: none" data-dz-download class="dz-download" href="" title="Download"><img src="modules/electro-modules/matisse-components/i/download.png" alt="Download icon"/></a></div>
HTML;

  /**
   * @var string
   */
  public $dictDefaultMessage = "Arraste ficheiros para aqui ou clique para escolher os ficheiros a enviar.";
  /**
   * @var string
   */
  public $dictInvalidFileType = "Ficheiro inválido";
  /**
   * @var string
   */
  public $dictFileTooBig = "Ficheiro demasiado grande";
  /**
   * @var string
   */
  public $dictResponseError = "Erro ao enviar";
  /**
   * @var string
   */
  public $dictCancelUpload = "Cancelar";
  /**
   * @var string
   */
  public $dictCancelUploadConfirmation = "Tem a certeza?";
  /**
   * @var string
   */
  public $dictRemoveFile = "Apagar";
  /**
   * @var string
   */
  public $dictMaxFilesExceeded = "Não pode inserir mais ficheiros";
  /**
   * @var string
   */
  public $genericIconPath = 'modules/electro-modules/matisse-components/i/file.png';
  /**
   * @var string
   */
  public $acceptedFiles = '.pdf,.png,.jpg,.gif,.bmp,.jpeg';
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
  const CLIENT_SIDE_CODE       = <<<'JS'

    Dropzone.autoDiscover = false;

    Dropzone.prototype.addMock = function (name, metadata, thumbUrl, virtuaPath) {
      // Create the mock file:
      var mockFile = { metadata: metadata, name: name, path: virtuaPath };

      // Call the default addedfile event handler
      this.emit ("addedfile", mockFile);
      this.files.push (mockFile);

      // And optionally show the thumbnail of the file:
      if (thumbUrl)
        this.emit ("thumbnail", mockFile, thumbUrl);

      // Make sure that there is no progress bar, etc...
      this.emit ("complete", mockFile);

    };
JS;

  const GENERIC_ERROR_MESSAGE = "O upload do ficheiro falhou.";

  const TEMP_STORE_FOLDER_NAME = "dropzone-uploads";

  /** @var bool */
  const allowsChildren = true;

  const propertiesClass = DropzoneProperties::class;

  /** @var string */
  public $cssClassName = 'dropzone';

  /** @var DropzoneProperties */
  public $props;

  /** @var bool */
  protected $autoId = true;
  /**
   * @var ContentRepositoryInterface
   */
  private $contentRepository;
  /**
   * @var ModelController
   */
  private $modelController;
  /** @var CurrentRequestInterface */
  private $request;

  public function __construct (CurrentRequestInterface $currentRequest,ModelController $modelController,ContentRepositoryInterface $contentRepository)
  {
    $this->request = $currentRequest;
    parent::__construct ();
    $this->modelController = $modelController;
    $this->contentRepository = $contentRepository;
  }

  /**
   * Handles file uploads.
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface      $response
   * @return ResponseInterface
   * @internal param ContentRepositoryInterface $repository
   */
  static function dropzoneUpload ($request, $response)
  {
    $ds = DIRECTORY_SEPARATOR;
    $storeFolder = self::TEMP_STORE_FOLDER_NAME;
    $storePath = sys_get_temp_dir().$ds.$storeFolder;

    if (!is_dir($storePath))
      mkdir($storePath);

    $file = "";
    foreach ($request->getUploadedFiles() as $oFile)
    {
      $filename = $oFile->getClientFilename ();
      $filePath = $storePath.$ds.$filename;
      $oFile->moveTo($filePath);
      $file = $filePath;
    }
    return Http::jsonResponse ($response,$file);
  }

  protected function init ()
  {
    parent::init ();
    $this->context->getAssetsService ()
                  ->addStylesheet ('lib/dropzone/dist/min/dropzone.min.css')
                  ->addScript ('lib/dropzone/dist/min/dropzone.min.js')
                  ->addInlineScript (self::CLIENT_SIDE_CODE, 'init-dropzone');
  }

  /**
   * Private function to get value of this drozpone field
   * @return mixed
   */
  private function getFieldValue()
  {
    $model = $this->modelController->getModel();
    $fieldName = str_replace('model/','', $this->props->name);
    return $model->$fieldName;
  }

  /**
   * Private function to get all uploaded images in this dropzone
   * @param $value
   * @return array
   */
  private function getImagesInFieldValue($value)
  {
    $aImages = explode(',', $value);
    $arrImages = [];
    foreach ($aImages as $aImage)
    {
      $oImage = File::where('path',$aImage)->first();
      if (!$oImage) continue;
      $fileName = "$oImage->name.$oImage->ext";
      $arrImages[] = [
        'filename' => $fileName,
        'virtuaPath' => $oImage->path,
        'path' => $oImage->image ? $this->contentRepository->getImageUrl($oImage->path,[
          'w' => 120,
          'h' => 120,
          'fit' => 'crop',
        ]) : $this->contentRepository->getFileUrl($oImage->path),
        'pathDownload' => ($oImage->image ? $this->contentRepository->getImageUrl($oImage->path) : $this->contentRepository->getFileUrl($oImage->path))."?f=".$fileName,
        'icon' => !$oImage->image ? $this->props->genericIconPath : ''
      ];
    }
    return $arrImages;
  }

  protected function postRender ()
  {
    parent::postRender ();
    echo html ([
      h ('input', [
        'type' => 'hidden',
        'id' => $this->props->id,
        'name' => $this->props->name,
        'value' => $this->getFieldValue()
      ]),
    ]);
  }

  protected function render ()
  {
    $images = JavascriptCodeGen::makeOptions(['images' => $this->getImagesInFieldValue($this->getFieldValue())]);
    $prop    = $this->props;
    $options = JavascriptCodeGen::makeOptions ([
      'url'                          => $prop->url
        ?: enum ('/', $this->request->getAttribute ('appBaseUri'), MatisseComponentsModule::DROPZONE_UPLOAD_URL),
      'acceptedFiles'                => $prop->acceptedFiles,
      'maxFiles'                     => $prop->maxFiles,
      'maxFileSize'                  => $prop->maxFileSize,
      'clickable'                    => true,
      'addRemoveLinks'               => true,
      'parallelUploads'              => $prop->parallelUploads,
      'method'                       => $prop->method,
      'autoProcessQueue'             => $prop->autoProcessQueue,
      'dictDefaultMessage'           => $prop->dictDefaultMessage,
      'dictInvalidFileType'          => $prop->dictInvalidFileType,
      'dictFileTooBig'               => $prop->dictFileTooBig,
      'dictResponseError'            => $prop->dictResponseError,
      'dictCancelUpload'             => $prop->dictCancelUpload,
      'dictCancelUploadConfirmation' => $prop->dictCancelUploadConfirmation,
      'dictRemoveFile'               => $prop->dictRemoveFile,
      'dictMaxFilesExceeded'         => $prop->dictMaxFilesExceeded,
      'previewTemplate' => $prop->previewTemplate
    ], '  ');

    $this->context->getAssetsService ()->addInlineScript (<<<JS
(function(element)
{
  var dropzone = new Dropzone (element[0], $options);

  var oInput = $("input[name='$prop->name']");
  var arr = oInput.val().split(',');
  $.each($images.images,function(index,image) {
    dropzone.addMock(image.filename,'',image.icon ? image.icon : image.path,image.virtuaPath);
  });
  
  var id = oInput.attr('id');
  $.each($("#"+id+" a[data-dz-download]"),function(index,value)
  {
    var downloadLink = $images.images[index].pathDownload;
    $(this).attr('href',downloadLink).show();
    $(this).parent().find('.dz-remove').css('width','50%');
  });
  $('.dz-size span strong:contains("0")').parent().hide();

  dropzone.on("success", function(file, response)
  {
    file.path = response;
    var oInput = $("input[name='$prop->name']");
    if (oInput.val()=='')
      oInput.val(response);
    else 
      oInput.val(oInput.val()+","+response);
  });
  
  dropzone.on('error', function(file, response)
  {
    $(file.previewElement).find('.dz-error-message').text(response.error.info);
  });
  
  dropzone.on('removedfile',function(file,response)
  {
    var sPath = file.path;
    var oInput = $("input[name='$prop->name']");
    var sStr = oInput.val();
    var array = sStr.split(',');
    var iIndex = array.indexOf(sPath)
    array.splice(iIndex, 1);
    oInput.val(array.join());
  });
}) ($('#$prop->id'));
JS
    );
  }

}

