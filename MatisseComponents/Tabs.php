<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\AttributeType;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\VisualComponent;

class TabsAttributes extends VisualComponentAttributes
{
  public $containerCssClass = ''; //-1 to not preselect any tab
  public $data;
  public $disabled          = false;
  public $labelField        = 'label';
  public $lazyCreation      = false;
  public $pageTemplate;
  public $pages;
  public $selected_index    = 0;
  public $tabAlign          = 'left';
  public $value;
  public $valueField        = 'value';

  protected function typeof_containerCssClass () { return AttributeType::TEXT; }

  protected function typeof_data () { return AttributeType::DATA; }

  protected function typeof_disabled () { return AttributeType::BOOL; }

  protected function typeof_labelField () { return AttributeType::TEXT; }

  protected function typeof_lazyCreation () { return AttributeType::BOOL; }

  protected function typeof_pageTemplate () { return AttributeType::SRC; }

  protected function typeof_pages () { return AttributeType::SRC; }

  protected function typeof_selectedIndex () { return AttributeType::NUM; }

  protected function typeof_tabAlign () { return AttributeType::TEXT; }

  protected function typeof_value () { return AttributeType::TEXT; }

  protected function typeof_valueField () { return AttributeType::TEXT; }
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

class Tabs extends VisualComponent
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
   * @return TabsAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return TabsAttributes
   */
  public function newAttributes ()
  {
    return new TabsAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $this->selIdx = $attr->selected_index;
    $pages        = $this->getChildren ('pages');
    if (!empty($pages)) {
      //create data source for tabs from tab-pages defined on the source markup
      $data = [];
      foreach ($pages as $idx => $tabPage) {
        $t           = new TabsData();
        $t->id       = $tabPage->attrs ()->id;
        $t->value    = either ($tabPage->attrs ()->value, $idx);
        $t->label    = $tabPage->attrs ()->label;
        $t->icon     = $tabPage->attrs ()->icon;
        $t->inactive = $tabPage->inactive;
        $t->disabled = $tabPage->attrs ()->disabled;
        $t->url      = $tabPage->attrs ()->url;
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
          $pages[$this->selIdx]->attrs ()->selected = true;
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
          $child->attrs ()->selected = $s;
          if ($s) $selName = $child->attrs ()->id;
          $child->run ();
        }
      }
    }
    else {
      $selIdx = $this->selIdx;
      foreach ($this->getChildren () as $child)
        if ($child->className == 'Tab') {
          $s                         = $selIdx == $p++;
          $child->attrs ()->selected = $s;
          if ($s) $selName = $child->attrs ()->id;
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
          $child->attrs ()->selected = $s;
          if ($s) $sel = $child;
          $child->run ();
        }
      $this->end ();
      if (isset($sel))
        $this->tag ('script', null, "Tab_change(\$f('{$selName}Field'),'{$this->attrs()->id}')");
    }
  }

}

