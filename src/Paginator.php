<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\ComponentAttributes;
use Selene\Matisse\VisualComponent;

class PaginatorAttributes extends ComponentAttributes
{

  public $page;
  public $total;
  public $uri;
  public $page_count;

  protected function typeof_page () { return AttributeType::NUM; }

  protected function typeof_total () { return AttributeType::NUM; }

  protected function typeof_uri () { return AttributeType::TEXT; }

  protected function typeof_page_count () { return AttributeType::NUM; }
}

class Paginator extends VisualComponent
{
  protected $containerTag = 'nav';

  /**
   * Returns the component's attributes.
   * @return PaginatorAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return PaginatorAttributes
   */
  public function newAttributes ()
  {
    return new PaginatorAttributes($this);
  }

  protected function preRender ()
  {
    if ($this->attrs ()->total > 1)
      parent::preRender ();
  }

  protected function postRender ()
  {
    if ($this->attrs ()->total > 1)
      parent::postRender ();
  }

  protected function render ()
  {
    $SIZE  = floor (($this->attrs ()->page_count - 1) / 2);
    $page  = $this->attrs ()->page;
    $total = $this->attrs ()->total;
    if ($total < 2) return;
    $uri   = $this->attrs ()->uri;
    $start = $page - $SIZE;
    $end   = $start + 2 * $SIZE;
    if ($start < 1) {
      $d = -$start + 1;
      $start += $d;
      $end += $d;
    }
    if ($end > $total) {
      $d = $end - $total;
      $end -= $d;
      $start -= $d;
      if ($start < 1) $start = 1;
    }
    $this->beginTag ('ul');
    $this->addAttribute('class', 'pagination');
    if ($start > 1) {
      $st = $start - 1;
      if ($st < 1) $st = 1;
      $this->beginTag('li');
      $this->addTag ('a', ['href' => "$uri?p=$st", 'class' => 'prev'], '&laquo;');
      $this->endTag();
      $this->beginContent ();
    }
    for ($n = $start; $n <= $end; ++$n) {
      $this->beginTag('li', ['class' => $n == $page ? 'active' : '']);
      $this->addTag ('a', ['href' => "$uri?p=$n"], $n);
      $this->endTag();
    }
    if ($end < $total) {
      $this->beginContent ();
      $ed = $end + 1;
      if ($ed > $total) $ed = $total;
      $this->beginTag('li');
      $this->addTag ('a', ['href' => "$uri?p=$ed", 'class' => 'next'], '&raquo;');
      $this->endTag();
    }
    $this->endTag ();
  }

}

