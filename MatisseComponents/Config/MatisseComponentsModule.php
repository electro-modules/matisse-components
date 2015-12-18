<?php
namespace Selenia\Plugins\MatisseComponents\Config;

use Selenia\Core\Assembly\Services\ModuleServices;
use Selenia\Interfaces\ModuleInterface;
use Selenia\Plugins\MatisseComponents;

class MatisseComponentsModule implements ModuleInterface
{
  function boot () { }

  function configure (ModuleServices $module)
  {
    $module
      ->publishPublicDirAs ('modules/selenia-plugins/matisse-components')
      ->provideMacros ()
      ->registerComponents ([
        'Button'         => MatisseComponents\Button::class,
        'Calendar'       => MatisseComponents\Calendar::class,
        'Checkbox'       => MatisseComponents\Checkbox::class,
        'DataGrid'       => MatisseComponents\DataGrid::class,
        'Field'          => MatisseComponents\Field::class,
        'FileUpload'     => MatisseComponents\FileUpload::class,
        'HtmlEditor'     => MatisseComponents\HtmlEditor::class,
        'Image'          => MatisseComponents\Image::class,
        'ImageField'     => MatisseComponents\ImageField::class,
        'Input'          => MatisseComponents\Input::class,
        'Label'          => MatisseComponents\Label::class,
        'Link'           => MatisseComponents\Link::class,
        'MainMenu'       => MatisseComponents\MainMenu::class,
        'NavigationPath' => MatisseComponents\NavigationPath::class,
        'Paginator'      => MatisseComponents\Paginator::class,
        'RadioButton'    => MatisseComponents\Radiobutton::class,
        'Selector'       => MatisseComponents\Selector::class,
        'Tab'            => MatisseComponents\Tab::class,
        'TabPage'        => MatisseComponents\TabPage::class,
        'Tabs'           => MatisseComponents\Tabs::class,
      ]);
  }
}
