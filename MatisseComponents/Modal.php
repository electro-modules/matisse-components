<?php
namespace Electro\Plugins\MatisseComponents;

use Matisse\Components\Base\HtmlComponent;
use Matisse\Components\Metadata;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\type;

class ModalProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $closable = true;
  /**
   * @var Metadata
   */
  public $footer = type::content;
  /**
   * CSS class for defining the window size.
   *
   * <p>Valid values are:
   * <p>
   * <table>
   * <tr><td> ''         <td> medium size
   * <tr><td> 'modal-lg' <td> large size
   * <tr><td> 'modal-sm' <td> small size
   * </table>
   *
   * @var string
   */
  public $size = '';
  /**
   * @var string
   */
  public $title = '';
  /**
   * @var bool
   */
  public $withFooter = true;
  /**
   * @var bool
   */
  public $withHeader = true;
}

class Modal extends HtmlComponent
{
  const allowsChildren  = true;
  const propertiesClass = ModalProperties::class;

  public $autoId       = true;
  public $cssClassName = 'modal fade';
  /** @var ModalProperties */
  public $props;

  protected function render ()
  {
    $prop = $this->props;
    $id   = $prop->id;

    $header = null;
    if ($prop->withHeader)
      $header = h ('.modal-header', [
        when ($prop->closable, h ('button.close', ['data-dismiss' => 'modal', 'aria-label' => 'Close'], [
          h ('span', ['aria-hidden' => 'true'], '&times;'),
        ])),
        when ($prop->title, h ("h4#{$id}Title.modal-title", $prop->title)),
      ]);

    $footer = null;
    if ($prop->withFooter)
      $footer = h ('.modal-footer', $this->renderChildren ('footer'));

    $this->addAttrs ([
      'tabindex' => -1, 'role' => 'dialog', 'aria-labelledby' => when ($prop->withHeader, "{$id}Title"),
    ]);

    $this->setContent (html ([
      h ('.modal-dialog', ['class' => $prop->size, 'role' => 'document'], [
        h ('.modal-content', [
          $header,
          h ('.modal-body', $this->renderChildren ()),
          $footer,
        ]),
      ]),
    ]));
  }
}
