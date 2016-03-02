<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;

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
  public $imageHeight = 120;
  /**
   * @var int
   */
  public $imageWidth = 120;
  /**
   * @var array
   */
  public $name = ''; //allow 'field[]'
  /**
   * @var bool
   */
  public $noClear = false;
  /**
   * @var bool
   */
  public $sortable = false;
  /**
   * @var string
   */
  public $value = '';
}

class ImageField extends HtmlComponent
{
  const EMPTY_IMAGE       = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
  const FILE_FIELD_SUFFIX = 'imageFieldFile';

  protected static $propertiesClass = ImageFieldProperties::class;

  /** @var ImageFieldProperties */
  public $props;

  protected $autoId = true;

  protected function init ()
  {
    parent::init ();

    $EMPTY = self::EMPTY_IMAGE;
    $js    = <<<JS
selenia.ext.imageField = {
  clear: function (id) {
    var e = $('#'+id);
    e.find('img').prop('src','$EMPTY');
    e.find('input[type=hidden]').val('');
    e.find('span').text('');
    e.find('.clearBtn').hide();
  },
  onChange: function (id) {
    var e = $('#'+id);
    var name = e.find('input[type=file]').val().replace(/^.*(\/|\\)/, '');
    e.find('span').text(name);
    e.find('.clearBtn').show();
  }
};
JS;
    $this->context->addInlineScript ($js, 'ImageFieldInit');
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
          isset($prop->imageWidth) ? "width:{$prop->imageWidth}px" : '',
          isset($prop->imageHeight) ? "height:{$prop->imageHeight}px" : ''
        ),
      ],[
          when ($prop->value,
            Image::_ ($this, [
              'value'  => $prop->value,
              'width'  => $prop->imageWidth,
              'height' => $prop->imageHeight,
              'fit'    => 'crop',
            ])),
          when (!$prop->value,
            h ('img.Image', [
                'src' => self::EMPTY_IMAGE,
              ]
            )),
          h ('span'),
          h ("input", [
            'type'     => 'file',
            'name'     => "{$prop->name}_" . self::FILE_FIELD_SUFFIX,
            'onchange' => "selenia.ext.imageField.onChange('{$prop->id}')",
            'disabled' => $prop->disabled,
          ]),
          when (!$prop->noClear,
            h ('button.clearBtn.fa.fa-trash', [
              'type'     => 'button',
              'onclick'  => "selenia.ext.imageField.clear('{$prop->id}')",
              'disabled' => $prop->disabled,
              'style'    => when (!$prop->value, 'display:none'),
            ])),
        ]),
    ]);

  }

}

