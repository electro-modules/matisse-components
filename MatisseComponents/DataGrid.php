<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\CompositeComponent;
use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Components\Internal\Metadata;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;
use Selenia\Matisse\Properties\TypeSystem\type;

/**
 * A dataGrid component, using the DataTables.net jQuery widget.
 *
 * Note: if responsive problems occur, try: $( $.fn.dataTable.tables(true) ).DataTable().responsive.recalc();
 */
class DataGridProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $action = '';
  /**
   * @var Metadata|null
   */
  public $actions = [type::content];
  /**
   * @var bool
   */
  public $ajax = false;
  /**
   * @var string
   */
  public $as = '';
  /**
   * @var bool
   */
  public $clickable = false;
  /**
   * Attributes for each column:
   * - type="row-selector|action|input". Note: if set, clicks on the column have no effect.
   * - align="left|center|right"
   * - title="t" (t is text)
   * - width="n|n%" (n is a number)
   *
   * @var Metadata[]
   */
  public $column = [type::collection, is::of, type::metadata];
  /**
   * @var mixed
   */
  public $data = type::data;
  /**
   * @var string
   */
  public $detailUrl = '';
  /**
   * @var bool
   */
  public $info = true;
  /**
   * @var string
   */
  public $initScript = '';
  /**
   * @var string
   */
  public $lang = 'en-US';
  /**
   * @var bool
   */
  public $lengthChange = true;
  /**
   * @var string
   */
  public $lengthChangeScript = '';
  /**
   * @var string A string representation of an array of number of rows to display.
   */
  public $lengthMenu = '[5,10,15,20,50,100]';
  /**
   * @var string
   */
  public $onClick = '';
  /**
   * @var string
   */
  public $onClickGoTo = '';
  /**
   * @var bool
   */
  public $ordering = true;
  /**
   * @var string Number of rows to display.
   * It may be a numeric constant or a javascript expression.
   */
  public $pageLength = '10';
  /**
   * @var bool
   */
  public $paging = true;
  /**
   * @var string
   */
  public $pagingType = ['simple_numbers', is::enum, ['simple', 'simple_numbers', 'full', 'full_numbers']];
  /**
   * @var Metadata|null
   */
  public $plugins = type::content;
  /**
   * @var string
   */
  public $responsive = 'false';
  /**
   * @var bool
   */
  public $searching = true;
  /**
   * One or more CSS classes to add to the rendered table.
   * > <p>**Note:** for Bootstrap, the following classes are supported: `table table-striped table-bordered`
   *
   * @var string
   */
  public $tableClass = 'table table-striped table-bordered';
}

class DataGrid extends HtmlComponent
{
  const PUBLIC_URI      = 'modules/selenia-plugins/matisse-components';
  const propertiesClass = DataGridProperties::class;
  protected static $MIN_PAGE_ITEMS = [
    'simple'         => 0, // n/a
    'full'           => 0, // n/a
    'simple_numbers' => 3,
    'full_numbers'   => 5,
  ];
  public           $cssClassName   = 'box';
  /** @var DataGridProperties */
  public $props;

  protected $autoId = true;

  private $enableRowClick = false;

  protected function init ()
  {
    parent::init ();
    $context = $this->context;
    $context->getAssetsService ()->addStylesheet ('lib/datatables.net-bs/css/dataTables.bootstrap.min.css');
    $context->getAssetsService ()->addStylesheet ('lib/datatables.net-responsive-bs/css/responsive.bootstrap.min.css');
    $context->getAssetsService ()->addStylesheet ('lib/datatables.net-buttons-bs/css/buttons.bootstrap.min.css');
    $context->getAssetsService ()->addScript ('lib/datatables.net/js/jquery.dataTables.min.js');
    $context->getAssetsService ()->addScript ('lib/datatables.net-bs/js/dataTables.bootstrap.min.js');
    $context->getAssetsService ()->addScript ('lib/datatables.net-responsive/js/dataTables.responsive.min.js');
    $context->getAssetsService ()->addScript ('lib/datatables.net-buttons/js/dataTables.buttons.min.js');
    $context->getAssetsService ()->addScript ('lib/datatables.net-buttons-bs/js/buttons.bootstrap.min.js');
  }

  protected function render ()
  {
    $prop    = $this->props;
    $context = $this->context;

    $context->getAssetsService ()->addInlineScript (<<<JS
function check(ev,id,action) {
  action = action || 'check';
  ev.stopPropagation();
  $.post(location.href, { _action: action, id: id });
}
$.extend(true, $.fn.dataTable.Buttons.defaults, {
  dom: {
    button: {
      className: 'btn'
    },
  }
});
JS
      , 'datagridInit');
    $id          = $prop->id;
    $minPagItems = self::$MIN_PAGE_ITEMS [$prop->pagingType];
    $PUBLIC_URI  = self::PUBLIC_URI;
    $language    = $prop->lang != 'en-US'
      ? "language:     { url: '$PUBLIC_URI/js/datatables/{$prop->lang}.json' }," : '';

    $this->setupColumns ($prop->column);
    $this->enableRowClick = $this->isPropertySet ('onClick') || $this->isPropertySet ('onClickGoTo');
    $paging               = boolToStr ($prop->paging);
    $searching            = boolToStr ($prop->searching);
    $ordering             = boolToStr ($prop->ordering);
    $info                 = boolToStr ($prop->info);
    $responsive           = $prop->responsive;
    $lengthChange         = boolToStr ($prop->lengthChange);

    ob_start ();
    $this->runChildren ('plugins');
    $plugins = ob_get_clean ();

    $this->beginContent ();

    $buttons = '';
    if ($prop->actions) {
      $btns = [
        "dom:\"<'row'<'col-sm-4'l><'col-sm-8'<'dataTables_buttons'B>f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>\",
buttons:[",
      ];
      $prop->actions->preRun();
      foreach ($prop->actions->getChildren () as $btn) {
        if (!$btn instanceof Button) {
          if ($btn instanceof CompositeComponent) {
            $btn->preRun ();
            $b = $btn->provideShadowDOM ()->getFirstChild ();
            if ($b instanceof Button) {
              $b->preRun ();
              $btn = $b;
              goto addBtn;
            }
          }
          throw new ComponentException($this, "Invalid content for the <kbd>actions</kbd> property.
<p>You can only use Button instances or components whose skin contains a button component as the first child");
        }
        addBtn:
        $bp = $btn->props;
        if ($bp->action) $action = "selenia.doAction('$bp->action')";
        elseif ($bp->script) $action = $bp->script;
        elseif ($v = $btn->getComputedPropValue ('url')) $action = "location.href='$v'";
        else $action = '';
        $class  = enum (' ', $bp->class,
          $bp->icon ? 'with-icon' : ''
        );
        $bLabel = $btn->getComputedPropValue ('label');
        $label  = $bp->icon ? "<i class=\"$bp->icon\"></i>$bLabel" : $bLabel;
        $btns[] = sprintf ("{className:'%s',text:'%s',action:function(e,dt,node,config){%s}}",
          $class, $label, $action);
      }
      $btns[]  = '],';
      $buttons = implode (',', $btns);
    }

    // AJAX MODE

    if ($prop->ajax) {
      $url                  = $_SERVER['REQUEST_URI'];
      $action               = $prop->action;
      $detailUrl            = $prop->detailUrl;
      $this->enableRowClick = $prop->clickable;
      $context->getAssetsService ()->addInlineScript (<<<JS
$('#$id table').dataTable({
  serverSide:   true,
  paging:       $paging,
  lengthChange: $lengthChange,
  searching:    $searching,
  ordering:     $ordering,
  info:         $info,
  autoWidth:    false,
  responsive:   $responsive,
  pageLength:   $prop->pageLength,
  lengthMenu:   $prop->lengthMenu,
  pagingType:   '$prop->pagingType',
  $language
  $plugins
  $buttons
  ajax: {
     url: '$url',
     type: 'POST',
     data: {
        _action: '$action'
    }
   },
  initComplete: function() {
    $prop->initScript
    $('#$id .col-sm-6').attr('class', 'col-xs-6');
    $('#$id').show();
  }
}).on ('length.dt', function (e,cfg,len) {
  $prop->lengthChangeScript
}).on ('click', 'tbody tr', function () {
    location.href = '$detailUrl' + $(this).attr('rowid');
});
JS
      );
    }
    else {

      // IMMEDIATE MODE

      $context->getAssetsService ()->addInlineScript (<<<JS
$('#$id table').dataTable({
  paging:       $paging,
  lengthChange: $lengthChange,
  searching:    $searching,
  ordering:     $ordering,
  info:         $info,
  autoWidth:    false,
  responsive:   $responsive,
  pageLength:   $prop->pageLength,
  lengthMenu:   $prop->lengthMenu,
  pagingType:   '$prop->pagingType',
  $language
  $plugins
  $buttons
  initComplete: function() {
    $prop->initScript
    $('#$id .col-sm-6').attr('class', 'col-xs-6');
    $('#$id').show();
  },
  drawCallback: function() {
    var p = $('#$id .pagination');
    p.css ('display', p.children().length <= $minPagItems ? 'none' : 'block');
  }
}).on ('length.dt', function (e,cfg,len) {
  $prop->lengthChangeScript
});
JS
      );
      if (isset($prop->data)) {
        /** @var \Iterator $dataIter */
        $dataIter = iterator ($prop->data);
        $dataIter->rewind ();
        $valid = $dataIter->valid ();
      }
      else $valid = false;
      if ($valid) {
        $this->parseIteratorExp ($prop->as, $idxVar, $itVar);
        $columnsCfg = $prop->column;
        $this->begin ('table', [
          'class' => enum (' ', $prop->tableClass, $this->enableRowClick ? 'table-clickable' : ''),
        ]);
        $this->beginContent ();
        $this->renderHeader ($columnsCfg);
        if (!$prop->ajax) {
          $idx = 0;
          /** @noinspection PhpUndefinedVariableInspection */
          foreach ($dataIter as $i => $v) {
            if ($idxVar)
              $this->viewModel->$idxVar = $i;
            $this->viewModel->$itVar = $v;
            $this->renderRow ($idx++, $columnsCfg);
          }
        }
        $this->end ();
      }
      else $this->runChildren ('no_data');
    }
  }

  protected function viewModel ()
  {
    $this->viewModel = $this->overlayViewModel();
  }

  private function renderHeader (array $columns)
  {
    $id = $this->props->id;
    foreach ($columns as $k => $col) {
      $w = $col->props->width;
      $this->tag ('col', isset($w) ? ['width' => $w] : null);
    }
    $this->begin ('thead');
    foreach ($columns as $k => $col) {
      $al = $col->props->get ('header_align', $col->props->align);
      if (isset($al))
        $this->context->getAssetsService ()->addInlineCss ("#$id .h$k{text-align:$al}");
      $this->begin ('th');
      $this->setContent ($col->props->title);
      $this->end ();
    }
    $this->end ();
  }

  /**
   * @param int        $idx
   * @param Metadata[] $columns
   * @throws \Selenia\Matisse\Exceptions\ComponentException
   */
  private function renderRow ($idx, array $columns)
  {
    $this->begin ('tr');
    $this->attr ('class', 'R' . ($idx % 2));
    if ($this->enableRowClick) {
      if ($this->isPropertySet ('onClickGoTo')) {
        $onclick = $this->getComputedPropValue ('onClickGoTo');
        $onclick = "selenia.go('$onclick',event)";
      }
      else $onclick = $this->getComputedPropValue ('onClick');
      $onclick = "if (!$(event.target).closest('[data-nck]').length) $onclick";
      $this->attr ('onclick', $onclick);
    }
    foreach ($columns as $k => $col) {
      $col->preRun ();
      $colAttrs = $col->props;
      $colType  = property ($colAttrs, 'type', '');
      $al       = property ($colAttrs, 'align');;
      $isText = empty($colType);
      $this->begin ('td');
      if ($colType != '')
        $this->attr ('class', enum (' ', "ta-$al",
          $colType == 'row-selector' ? 'rh' : '',
          $colType == 'field' ? 'field' : ''
        ));
      if ($isText) {
        $this->beginContent ();
        $col->runChildren ();
      }
      else {
        if ($this->enableRowClick)
          $this->attr ('data-nck');
        $this->beginContent ();
        $col->runChildren ();
      }
      $this->end ();
    }
    $this->end ();
  }

  private function setupColumns (array $columns)
  {
    $id     = $this->props->id;
    $styles = '';
    foreach ($columns as $k => $col) {
      $al = $col->props->align;
      if (isset($al))
        $styles .= "#$id .c$k{text-align:$al}";
      $al = $col->props->header_align;
      if (isset($al))
        $styles .= "#$id .h$k{text-align:$al}";
    }
    $this->context->getAssetsService ()->addInlineCss ($styles);
  }

}
