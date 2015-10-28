<?php
namespace Selenia\Plugins\MatisseWidgets;

use Selenia\Core\Assembly\Services\ModuleServices;
use Selenia\Interfaces\ModuleInterface;

class MatisseWidgetsModule implements ModuleInterface
{
  function boot () { }

  function configure (ModuleServices $module)
  {
    $module
      ->publishPublicDirAs ('modules/selenia-plugins/matisse-components')
      ->registerComponents ([
        'Button'      => 'Selenia\Plugins\MatisseWidgets\Button',
        'Calendar'    => 'Selenia\Plugins\MatisseWidgets\Calendar',
        'Checkbox'    => 'Selenia\Plugins\MatisseWidgets\Checkbox',
        'DataGrid'    => 'Selenia\Plugins\MatisseWidgets\DataGrid',
        'Field'       => 'Selenia\Plugins\MatisseWidgets\Field',
        'FileUpload'  => 'Selenia\Plugins\MatisseWidgets\FileUpload',
        'HtmlRditor'  => 'Selenia\Plugins\MatisseWidgets\HtmlEditor',
        'Image'       => 'Selenia\Plugins\MatisseWidgets\Image',
        'ImageField'  => 'Selenia\Plugins\MatisseWidgets\ImageField',
        'Input'       => 'Selenia\Plugins\MatisseWidgets\Input',
        'Label'       => 'Selenia\Plugins\MatisseWidgets\Label',
        'Link'        => 'Selenia\Plugins\MatisseWidgets\Link',
        'MainMenu'    => 'Selenia\Plugins\MatisseWidgets\MainMenu',
        'Paginator'   => 'Selenia\Plugins\MatisseWidgets\Paginator',
        'RadioButton' => 'Selenia\Plugins\MatisseWidgets\Radiobutton',
        'Selector'    => 'Selenia\Plugins\MatisseWidgets\Selector',
        'Tab'         => 'Selenia\Plugins\MatisseWidgets\Tab',
        'TabPage'     => 'Selenia\Plugins\MatisseWidgets\TabPage',
        'Tabs'        => 'Selenia\Plugins\MatisseWidgets\Tabs',
      ]);
  }
}
