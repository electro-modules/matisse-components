<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Application;
use Electro\Plugins\Matisse\Components\Base\HtmlComponent;
use Electro\Plugins\Matisse\Properties\Base\HtmlComponentProperties;

//Note that the file fckeditor/editor/fckeditor.html should be changed from the default to:  <body style="visibility:hidden">

class HtmlEditorProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $autofocus = false;
  /**
   * @var string
   */
  public $lang = '';
  /**
   * @var string
   */
  public $name = ''; //allow 'field[]'
  /**
   * @var string
   */
  public $value = '';
}

class HtmlEditor extends HtmlComponent
{
  const propertiesClass = HtmlEditorProperties::class;

  /** @var HtmlEditorProperties */
  public $props;

  protected $autoId = true;

  /**
   * @global Application $application
   */
  protected function render ()
  {
    global $application, $controller;
    $prop = $this->props;

    if (!isset($prop->name))
      $prop->name = $prop->id;
    $lang           = property ($prop, 'lang', $controller->lang);
    $lang           = $lang === 'pt' ? 'pt_pt' : $lang;
    $addonURI       = "$application->addonsPath/components/redactor";
    $autofocus      = $prop->autofocus ? 'true' : 'false';
    $scriptsBaseURI = $application->framework;
    $initCode       = <<<JAVASCRIPT
var redactorToolbar = ['html', 'formatting', 'bold', 'italic',
'unorderedlist', 'orderedlist', 'outdent', 'indent',
'image', 'video', 'file', 'table', 'link',
'fontcolor', 'backcolor',
'alignment',
'horizontalrule', 'fullscreen'];
JAVASCRIPT;
    $code           = <<<JAVASCRIPT
$(document).ready(
  function() {
    $('#{$prop->id}_field').redactor({
      buttons: redactorToolbar,
      lang: '{$lang}',
      focus: $autofocus,
      resize: false,
      autoresize: false,
      minHeight: 220,
      plugins: ['video', 'table', 'fullscreen', 'fontcolor', 'imagemanager', 'filemanager'],
      imageUpload: '$scriptsBaseURI/imageUpload.php',
      fileUpload: '$scriptsBaseURI/fileUpload.php',
      imageGetJson: '$scriptsBaseURI/gallery.php',
      imageManagerJson: '$scriptsBaseURI/gallery.php',
      imageInsertCallback: onInlineImageInsert
    });
  }
);
JAVASCRIPT;
    $this->context->getAssetsService ()->addScript ("$addonURI/redactor.min.js");
    $this->context->getAssetsService ()->addScript ("$addonURI/langs/$lang.js");
    $this->context->getAssetsService ()->addStylesheet ("$addonURI/css/redactor.css");
    $this->context->getAssetsService ()->addScript ("$addonURI/plugins/fontcolor.js");
    $this->context->getAssetsService ()->addScript ("$addonURI/plugins/video.js");
    $this->context->getAssetsService ()->addScript ("$addonURI/plugins/table.js");
    $this->context->getAssetsService ()->addScript ("$addonURI/plugins/fullscreen.js");
    $this->context->getAssetsService ()->addScript ("$addonURI/plugins/imagemanager.js");
    $this->context->getAssetsService ()->addInlineScript ($initCode, 'redactor');
    $this->context->getAssetsService ()->addInlineScript ($code);

    $this->tag ('textarea', [
      'id'   => $prop->id . "_field",
      'name' => $prop->name,
    ], $prop->value);
  }
}
