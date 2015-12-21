<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Components\Internal\ContentProperty;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\Types\is;
use Selenia\Matisse\Properties\Types\type;

class TabsProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $containerCssClass = '';
  /**
   * @var string
   */
  public $data = type::data;
  /**
   * @var bool
   */
  public $disabled = false;
  /**
   * @var string
   */
  public $labelField = 'label';
  /**
   * @var bool
   */
  public $lazyCreation = false;
  /**
   * @var ContentProperty|null
   */
  public $pageTemplate = type::content;
  /**
   * @var ContentProperty|null
   */
  public $pages = type::content;
  /**
   * > **Note:** -1 to not preselect any tab.
   * @var int
   */
  public $selected_index = 0;
  /**
   * @var string
   */
  public $tabAlign = ['left', is::enum, ['left', 'center', 'right']];
  /**
   * @var string
   */
  public $value = '';
  /**
   * @var string
   */
  public $valueField = 'value';
}

class TabsData
{
  public $disabled;
  public $icon;
  public $id;
  public $inactive;
  public $label;
  public $url;
  public $value;
}

class Tabs extends HtmlComponent
{
  protected $autoId = true;

  /**
   * Indicates if the component contains tab-pages.
   * @var boolean
   */
  protected $hasPages;

  private $count = 0;
  private $selIdx;

  /**
   * Returns the component's attributes.
   * @return TabsProperties
   */
  public function props ()
  {
    return $this->props;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return TabsProperties
   */
  public function newProperties ()
  {
    return new TabsProperties($this);
  }

  protected function render ()
  {
    $attr = $this->props ();

    $this->selIdx = $attr->selected_index;
    $pages        = $this->getChildren ('pages');
    if (!empty($pages)) {
      //create data source for tabs from tab-pages defined on the source markup
      $data = [];
      foreach ($pages as $idx => $tabPage) {
        $t           = new TabsData();
        $t->id       = $tabPage->props ()->id;
        $t->value    = either ($tabPage->props ()->value, $idx);
        $t->label    = $tabPage->props ()->label;
        $t->icon     = $tabPage->props ()->icon;
        $t->inactive = $tabPage->inactive;
        $t->disabled = $tabPage->props ()->disabled;
        $t->url      = $tabPage->props ()->url;
        $data[]      = $t;
      }
      $data                = new DataSet($data);
      $propagateDataSource = false;
    }
    else {
      $data                = $attr->data;
      $propagateDataSource = true;
    }
    if (!empty($data)) {
      $template = $attr->pageTemplate;
      if (isset($template)) {
        if (isset($pages))
          throw new ComponentException($this,
            "You may not define both the <b>p:page-template</b> and the <b>p:pages</p> parameters.");
        $this->hasPages = true;
      }
      if ($propagateDataSource)
        $this->contextualModel = $data;
      $value = either ($attr->value, $this->selIdx);
      foreach ($data as $idx => $record) {
        if (!get ($record, 'inactive')) {
          $isSel = get ($record, $attr->valueField) === $value;
          if ($isSel)
            $this->selIdx = $this->count;
          ++$this->count;
          //create tab
          $tab               = new Tab($this->context, [
            'id'       => $attr->id . 'Tab' . $idx,
            'name'     => $attr->id,
            'value'    => get ($record, $attr->valueField),
            'label'    => get ($record, $attr->labelField),
            'url'      => get ($record, 'url'),
            //'class'         => $this->style()->tab_class,
            //'css_class'     => $this->style()->tab_css_class,
            'disabled' => get ($record, 'disabled') || $attr->disabled,
            'selected' => false//$isSel
          ], [
            //'width'         => $this->style()->tab_width,
            //'height'        => $this->style()->tab_height,
            'icon' => get ($record, 'icon'),
            //'icon_align'    => $this->style()->tab_icon_align
          ]);
          $tab->container_id = $attr->id;
          $this->addChild ($tab);
          //create tab-page
          $newTemplate = isset($template) ? clone $template : null;
          if (isset($template)) {
            $page = new TabPage($this->context, [
              'id'            => get ($record, 'id', $attr->id . 'Page' . $idx),
              'label'         => get ($record, $attr->labelField),
              'icon'          => get ($record, 'icon'),
              'content'       => $newTemplate,
              'lazy_creation' => $attr->lazyCreation,
            ]);
            $newTemplate->attachTo ($page);
            $this->addChild ($page);
          }
        }
      }
      if (!empty($pages)) {
        $this->addChildren ($pages);
        if ($this->selIdx >= 0)
          $pages[$this->selIdx]->props ()->selected = true;
        $this->setupSet ($pages);
        $this->hasPages = true;
      }
    }

    //--------------------------------

    $this->begin ('fieldset', [
      'class' => enum (' ', 'tabGroup', $attr->tabAlign ? 'align_' . $attr->tabAlign : ''),
    ]);
    $this->beginContent ();
    $p = 0;
    if ($attr->tabAlign == 'right') {
      $selIdx   = $this->count - $this->selIdx - 1;
      $children = $this->getChildren ();
      for ($i = count ($children) - 1; $i >= 0; --$i) {
        $child = $children[$i];
        if ($child->className == 'Tab') {
          $s                         = $selIdx == $p++;
          $child->props ()->selected = $s;
          if ($s) $selName = $child->props ()->id;
          $child->run ();
        }
      }
    }
    else {
      $selIdx = $this->selIdx;
      foreach ($this->getChildren () as $child)
        if ($child->className == 'Tab') {
          $s                         = $selIdx == $p++;
          $child->props ()->selected = $s;
          if ($s) $selName = $child->props ()->id;
          $child->run ();
        }
    }
    $this->end ();

    if ($this->hasPages) {
      $this->begin ('div', [
        'id'    => $attr->id . 'Pages',
        'class' => enum (' ', 'TabsContainer', $attr->containerCssClass),
      ]);
      $this->beginContent ();
      $p      = 0;
      $selIdx = $this->selIdx;
      foreach ($this->getChildren () as $child)
        if ($child->className == 'TabPage') {
          $s                         = $selIdx == $p++;
          $child->props ()->selected = $s;
          if ($s) $sel = $child;
          $child->run ();
        }
      $this->end ();
      if (isset($sel))
        $this->tag ('script', null, "Tab_change(\$f('{$selName}Field'),'{$this->props()->id}')");
    }
  }

}

