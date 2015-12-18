<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\AttributeType;
use Selenia\Matisse\VisualComponent;

class PaginatorAttributes extends VisualComponentAttributes
{

  public $page;
  public $pageCount;
  public $total;
  public $uri;

  protected function typeof_page () { return AttributeType::NUM; }

  protected function typeof_pageCount () { return AttributeType::NUM; }

  protected function typeof_total () { return AttributeType::NUM; }

  protected function typeof_uri () { return AttributeType::TEXT; }
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

  protected function postRender ()
  {
    if ($this->attrs ()->total > 1)
      parent::postRender ();
  }

  protected function preRender ()
  {
    if ($this->attrs ()->total > 1)
      parent::preRender ();
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $SIZE  = floor (($attr->pageCount - 1) / 2);
    $page  = $attr->page;
    $total = $attr->total;
    if ($total < 2) return;
    $uri   = $attr->uri;
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
    $this->begin ('ul');
    $this->attr ('class', 'pagination');
    if ($start > 1) {
      $st = $start - 1;
      if ($st < 1) $st = 1;
      $this->begin ('li');
      $this->tag ('a', ['href' => "$uri?p=$st", 'class' => 'prev'], '&laquo;');
      $this->end ();
      $this->beginContent ();
    }
    for ($n = $start; $n <= $end; ++$n) {
      $this->begin ('li', ['class' => $n == $page ? 'active' : '']);
      $this->tag ('a', ['href' => "$uri?p=$n"], $n);
      $this->end ();
    }
    if ($end < $total) {
      $this->beginContent ();
      $ed = $end + 1;
      if ($ed > $total) $ed = $total;
      $this->begin ('li');
      $this->tag ('a', ['href' => "$uri?p=$ed", 'class' => 'next'], '&raquo;');
      $this->end ();
    }
    $this->end ();
  }

}

