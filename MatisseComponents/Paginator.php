<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;

class PaginatorProperties extends HtmlComponentProperties
{
  /**
   * @var int
   */
  public $page = 0;
  /**
   * @var int
   */
  public $pageCount = 0;
  /**
   * @var int
   */
  public $total = 0;
  /**
   * @var string
   */
  public $uri = '';
}

class Paginator extends HtmlComponent
{
  protected $containerTag = 'nav';

  /**
   * Returns the component's attributes.
   * @return PaginatorProperties
   */
  public function props ()
  {
    return $this->props;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return PaginatorProperties
   */
  public function newProperties ()
  {
    return new PaginatorProperties($this);
  }

  protected function postRender ()
  {
    if ($this->props ()->total > 1)
      parent::postRender ();
  }

  protected function preRender ()
  {
    if ($this->props ()->total > 1)
      parent::preRender ();
  }

  protected function render ()
  {
    $attr = $this->props ();

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

