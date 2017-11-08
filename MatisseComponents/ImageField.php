<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Interfaces\ContentRepositoryInterface;
use Electro\Plugins\MatisseComponents\Handlers\FileFieldHandler;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;

class ImageFieldProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $crop = true;
  /**
   * @var bool
   */
  public $disabled = false;
  /**
   * @var int
   */
  public $height = 120;
  /**
   * @var array
   */
  public $name = '';
  /**
   * @var bool
   */
  public $noClear = false; //allow 'field[]'
  /**
   * @var string
   */
  public $value = '';
  /**
   * @var int
   */
  public $width = 120;
  /**
   * @var string
   */
  public $fieldSuffix = FileFieldHandler::FILE_FIELD_SUFFIX;
}

class ImageField extends HtmlComponent
{
  const EMPTY_IMAGE       = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';

  const propertiesClass = ImageFieldProperties::class;

  /** @var ImageFieldProperties */
  public $props;

  protected $autoId = true;
  /** @var ContentRepositoryInterface */
  private $contentRepo;

  public function __construct (ContentRepositoryInterface $contentRepo)
  {
    parent::__construct ();
    $this->contentRepo = $contentRepo;
  }

  protected function init ()
  {
    parent::init ();

    $EMPTY = self::EMPTY_IMAGE;
    $js    = <<<JS
selenia.ext.imageField = {
  clear: function (id) {
    var e = $('#'+id);
    e.find('img').prop('src','$EMPTY').css('background-image','');
    e.find('input[type=hidden]').val('');
    e.find('span').text('');
    e.find('.clearBtn').hide();
  },
  onChange: function (id) {
    selenia.ext.imageField.clear(id);
    var e = $('#'+id);
    var input = e.find('input[type=file]');
    var name = input.val().replace(/^.*(\/|\\\)/, '');
    e.find('span').text(name);
    e.find('.clearBtn').show();
    if ('FileReader' in window) {
      var reader = new FileReader();
      reader.onload = function (ev) {
        e.find('img').css('background-image', 'url('+ev.target.result+')');
      };
      reader.readAsDataURL(input[0].files[0]);
    }
  }
};
JS;
    $this->context->getAssetsService ()->addInlineScript ($js, 'ImageFieldInit');
  }

  protected function render ()
  {
    $prop       = $this->props;
    $prop->name = $prop->name ?: $prop->id;

    $this->context->enableFileUpload ();
    $this->beginContent ();

    echo html ([
      h ("input#{$prop->id}Field", [
        'type'  => 'hidden',
        'name'  => $prop->name,
        'value' => $prop->value,
      ]),
      h ('.wrapper', [
        'style' => enum (';',
          isset($prop->width) ? "width:{$prop->width}px" : '',
          isset($prop->height) ? "height:{$prop->height}px" : ''
        ),
      ], [
        h ('img.Image', [
          'src' => $prop->value
            ? $this->contentRepo->getImageUrl ($prop->value, [
              'w'   => $prop->width,
              'h'   => $prop->height,
              'fit' => 'crop',
            ])
            : self::EMPTY_IMAGE,
        ]),
        h ('span'),
        h ("input", [
          'type'     => 'file',
          'name'     => $prop->name . $prop->fieldSuffix,
          'onchange' => "selenia.ext.imageField.onChange('{$prop->id}')",
          'disabled' => $prop->disabled,
        ]),
        when (!$prop->noClear,
          h ('button.clearBtn.fa.fa-times', [
            'type'     => 'button',
            'onclick'  => "selenia.ext.imageField.clear('{$prop->id}')",
            'disabled' => $prop->disabled,
            'style'    => when (!$prop->value, 'display:none'),
          ])),
      ]),
    ]);

  }

}
