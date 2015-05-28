<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\ComponentAttributes;
use Selene\Matisse\VisualComponent;

class DataGridAttributes extends ComponentAttributes
{

  public $column;
  public $rowTemplate;
  public $noData;
  public $data;
  public $pagingType = 'simple_numbers';
  public $ajax       = false;
  public $action;
  public $detailUrl;
  public $clickable  = false;
  /** @var string Number o rows to display.
   * It may be a numeric constant or a javascript expression. */
  public $pageLength = '15';
  /** @var string A string representation of an array of number og rows to display. */
  public $lengthMenu   = '[10, 15, 20, 50, 100]';
  public $paging       = true;
  public $searching    = true;
  public $lengthChange = true;
  public $ordering     = true;
  public $info         = true;
  public $responsive   = true;
  public $lang         = 'en-US';

  /*
   * Attributes for each column:
   * - type="row-selector|action|input". Note: if set, clicks on the column have no effect.
   * - align="left|center|right"
   * - title="t" (t is text)
   * - width="n|n%" (n is a number)
   */
  protected function typeof_column () { return AttributeType::PARAMS; }

  protected function typeof_rowTemplate () { return AttributeType::SRC; }

  protected function typeof_noData () { return AttributeType::SRC; }

  protected function typeof_data () { return AttributeType::DATA; }

  protected function typeof_pagingType () { return AttributeType::TEXT; }

  protected function enum_pagingTtype () { return ['simple', 'simple_numbers', 'full', 'full_numbers']; }

  protected function typeof_ajax () { return AttributeType::BOOL; }

  protected function typeof_action () { return AttributeType::TEXT; }

  protected function typeof_detailUrl () { return AttributeType::TEXT; }

  protected function typeof_clickable () { return AttributeType::BOOL; }

  protected function typeof_pageLength () { return AttributeType::TEXT; }

  protected function typeof_lengthMenu () { return AttributeType::TEXT; }

  protected function typeof_paging () { return AttributeType::BOOL; }

  protected function typeof_searching () { return AttributeType::BOOL; }

  protected function typeof_lengthChange () { return AttributeType::BOOL; }

  protected function typeof_ordering () { return AttributeType::BOOL; }

  protected function typeof_info () { return AttributeType::BOOL; }

  protected function typeof_responsive () { return AttributeType::BOOL; }

  protected function typeof_lang () { return AttributeType::TEXT; }
}

class DataGrid extends VisualComponent
{
  const PUBLIC_URI = 'modules/admin';

  protected static $MIN_PAGE_ITEMS = [
    'simple'         => 0, // n/a
    'full'           => 0, // n/a
    'simple_numbers' => 3,
    'full_numbers'   => 5
  ];

  public    $cssClassName = 'box';
  protected $autoId       = true;

  private $enableRowClick = false;

  /**
   * Returns the component's attributes.
   * @return DataGridAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return DataGridAttributes
   */
  public function newAttributes ()
  {
    return new DataGridAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();
    $this->page->addInlineScript (<<<JAVASCRIPT
function check(ev,id,action) {
    action = action || 'check';
    ev.stopPropagation();
    $.post(location.href, { _action: action, id: id });
}
JAVASCRIPT
      , 'datagridInit');
    $id          = $attr->id;
    $minPagItems = self::$MIN_PAGE_ITEMS [$attr->pagingType];
    $PUBLIC_URI  = self::PUBLIC_URI;
    $language    = $attr->lang != 'en-US'
      ? "language:     { url: '$PUBLIC_URI/js/datatables/{$attr->lang}.json' }," : '';

    $this->setupColumns ($attr->column);
    $rowTemplate = $attr->rowTemplate;
    if (isset($rowTemplate)) {
      $this->enableRowClick    = $rowTemplate->isAttributeSet ('on_click')
                                 || $rowTemplate->isAttributeSet ('on_click_script');
      $this->defaultDataSource = $attr->data;
    }
    $paging       = boolToStr ($attr->paging);
    $searching    = boolToStr ($attr->searching);
    $ordering     = boolToStr ($attr->ordering);
    $info         = boolToStr ($attr->info);
    $responsive   = boolToStr ($attr->responsive);
    $lengthChange = boolToStr ($attr->lengthChange);

    // AJAX MODE

    if ($attr->ajax) {
      $url                  = $_SERVER['REQUEST_URI'];
      $action               = $attr->action;
      $detailUrl            = $attr->detailUrl;
      $this->enableRowClick = $attr->clickable;
      $this->page->addInlineDeferredScript (<<<JavaScript
$('#$id table').dataTable({
  serverSide:   true,
  paging:       $paging,
  lengthChange: $lengthChange,
  searching:    $searching,
  ordering:     $ordering,
  info:         $info,
  autoWidth:    false,
  responsive:   $responsive,
  pageLength:   $attr->pageLength,
  lengthMenu:   [10, 15, 20, 50, 100],
  $language
  ajax: {
     url: '$url',
     type: 'POST',
     data: {
        _action: '$action'
    }
   },
  initComplete: function() {
    $('#$id').show();
  },
  drawCallback: function() {
    $('#$id [data-nck]').on('click', function(ev) { ev.stopPropagation() });
  }
}).on ('length.dt', function (e,cfg,len) {
  mem.set ('prefs.rowsPerPage', len);
}).on ('click', 'tbody tr', function () {
    location.href = '$detailUrl' + $(this).attr('rowid');
});
JavaScript
      );
    }
    else {

      // IMMEDIATE MODE

      $this->page->addInlineDeferredScript (<<<JavaScript
$('#$id table').dataTable({
  paging:       $paging,
  lengthChange: $lengthChange,
  searching:    $searching,
  ordering:     $ordering,
  info:         $info,
  autoWidth:    false,
  responsive:   $responsive,
  pageLength:   $attr->pageLength,
  lengthMenu:   $attr->lengthMenu,
  pagingType:   '{$attr->pagingType}',
  $language
  initComplete: function() {
    $('#$id').show();
  },
  drawCallback: function() {
    $('#$id [data-nck]').on('click', function(ev) { ev.stopPropagation() });
    var p = $('#$id .pagination');
    p.css ('display', p.children().length <= $minPagItems ? 'none' : 'block');
  }
}).on ('length.dt', function (e,cfg,len) {
  mem.set ('prefs.rowsPerPage', len);
});
JavaScript
      );
      if (isset($this->defaultDataSource)) {
        $dataIter = $this->defaultDataSource->getIterator ();
        $dataIter->rewind ();
        $valid = $dataIter->valid ();
      }
      else $valid = true;
      if ($valid) {
        $columnsCfg = $attr->column;
        $this->beginTag ('table', [
          'class' => enum (' ', 'table table-striped', $this->enableRowClick ? 'table-clickable' : '')
        ]);
        $this->beginContent ();
        $this->renderHeader ($columnsCfg);
        if (!$attr->ajax) {
          $idx = 0;
          do {
            $this->renderRow ($idx++, $rowTemplate->children, $columnsCfg, $rowTemplate);
            $dataIter->next ();
          } while ($dataIter->valid ());
        }
        $this->endTag ();
      }
      else $this->renderSet ($this->getChildren ('no_data'));
    }
  }

  private function renderRow ($idx, array $columns, array $columnsCfg, Parameter $row)
  {
    $row->databind ();
    $this->beginTag ('tr');
    $this->addAttribute ('class', 'R' . ($idx % 2));
    if ($this->enableRowClick) {
      $onclick = property ($row->attrs (), 'on_click');
      if (isset($onclick))
        $onclick = "go('$onclick',event)";
      else $onclick = property ($row->attrs (), 'on_click_script');
      $this->addAttribute ('onclick', $onclick);
    }
    foreach ($columns as $k => $col) {
      $colCfg = get ($columnsCfg, $k);
      if (isset($colCfg)) {
        $colAttrs = $colCfg->attrs ();
        $colType  = property ($colAttrs, 'type', '');
        $al       = property ($colAttrs, 'align');;
        $isText = empty($colType);
        $this->beginTag ($colType == 'row-selector' ? 'th' : 'td');
        //if (isset($al))
        $this->addAttribute ('class', "ta-$al");
        if ($isText) {
          $this->beginContent ();
          $col->renderChildren ();
        }
        else {
          if ($this->enableRowClick)
            $this->addAttribute ('data-nck');
          $this->beginContent ();
          $col->renderChildren ();
        }
        $this->endTag ();
      }
    }
    $this->endTag ();
  }

  private function setupColumns (array $columns)
  {
    $id     = $this->attrs ()->id;
    $styles = '';
    foreach ($columns as $k => $col) {
      $al = $col->attrs ()->align;
      if (isset($al))
        $styles .= "#$id .c$k{text-align:$al}";
      $al = $col->attrs ()->header_align;
      if (isset($al))
        $styles .= "#$id .h$k{text-align:$al}";
    }
    $this->page->addInlineCss ($styles);
  }

  private function renderHeader (array $columns)
  {
    $id = $this->attrs ()->id;
    foreach ($columns as $k => $col) {
      $w = $col->attrs ()->width;
      if (strpos ($w, '%') === false && $this->page->browserIsIE)
        $w -= 3;
      $this->addTag ('col', isset($w) ? ['width' => $w] : null);
    }
    $this->beginTag ('thead');
    foreach ($columns as $k => $col) {
      $al = $col->attrs ()->get ('header_align', $col->attrs ()->align);
      if (isset($al))
        $this->page->addInlineCss ("#$id .h$k{text-align:$al}");
      $this->beginTag ('th');
      $this->setContent ($col->attrs ()->title);
      $this->endTag ();
    }
    $this->endTag ();
  }

}
