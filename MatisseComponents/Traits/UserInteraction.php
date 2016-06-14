<?php
namespace Selenia\Plugins\MatisseComponents\Traits;

use Selenia\Plugins\Matisse\Components\Base\Component;
use Selenia\Traits\FluentTrait;

/**
 * @method $this|boolean enabled (boolean $v = null)
 */
trait UserInteraction
{
  use FluentTrait;

  private static $UIPROPS = [
    'animation'         => 'string',
    'cancelButtonText'  => 'string',
    'confirmButtonText' => 'string',
    'html'              => 'bool',
    'inputPlaceholder'  => 'string',
    'inputType'         => 'string',
    'inputValue'        => 'string',
    'message'           => 'string',
    'showCancelButton'  => 'bool',
    'showConfirmButton' => 'bool',
    'title'             => 'string',
    'type'              => 'string',
  ];

  /**
   * If set to false, the modal's animation will be disabled.
   *
   * <p>Possible (string) values : pop (default when animation set to true), slide-from-top, slide-from-bottom
   *
   * @var string|bool
   */
  private $animation;
  /**
   * Use this to change the text on the "Cancel"-button.
   *
   * @var string
   */
  private $cancelButtonText;
  /**
   * Use this to change the text on the "Confirm" button.
   *
   * <p>If showCancelButton is set as true, the confirm button will automatically show "Confirm" instead of "OK".
   *
   * @var string
   */
  private $confirmButtonText;
  /**
   * If set to true, will not escape title and text parameters.
   * Set to false if you're worried about XSS attacks.
   *
   * @var bool
   */
  private $html;
  /**
   * When using the input-type, you can specify a placeholder to help the user.
   *
   * @var string
   */
  private $inputPlaceholder;
  /**
   * Change the type of the input field when using type: "input".
   * > <p>This can be useful if you want users to type in their password for example.
   *
   * @var string
   */
  private $inputType;
  /**
   * Specify a default text value that you want your input to show when using type: "input".
   *
   * @var string
   */
  private $inputValue;
  /**
   * A description for the modal.
   *
   * @var string
   */
  private $message;
  /**
   * If set to true, a "Cancel" button will be shown, which the user can click on to dismiss the modal.
   *
   * @var bool
   */
  private $showCancelButton;
  /**
   * If set to false, the "OK/Confirm"-button will be hidden.
   *
   * <p>Make sure you set a timer or set allowOutsideClick to true when using this, in order not to annoy the user.
   *
   * @var bool
   */
  private $showConfirmButton;
  /**
   * The title of the modal.
   *
   * @var string
   */
  private $title;
  /**
   * The type of the modal.
   *
   * <p>There are 4 built-in types which will show a corresponding icon animation:
   * "warning", "error", "success" and "info".
   * <p>You can also set it as "input" to get a prompt modal.
   *
   * @var string
   */
  private $type;

  /**
   * Generates the interaction script and returns a javascript function call string that you can embed on the page (on
   * a click handler, for example).
   *
   * @return string
   */
  function get ()
  {
    $msg = str_encodeJavasciptStr ($prop->message, "'");
    $this->context->getAssetsService ()->addInlineScript ("function confirm_$prop->id()
{
  swal({
    title: '',
    text: $msg,
    type: 'warning',
    showCancelButton: true
  },
  function() {
    selenia.doAction('$action');
  });
}");
    return "confirm_$prop->id()";
  }

  function useInteraction ()
  {
    /** @var Component $this */
    $this->context->getAssetsService ()->addScript ('lib/bootstrap-sweetalert/lib/sweet-alert.min.js');
    $this->context->getAssetsService ()->addStylesheet ('lib/bootstrap-sweetalert/lib/sweet-alert.css');
  }

}
