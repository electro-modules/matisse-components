<?php
namespace Selenia\Plugins\MatisseComponents\Config;

use League\Glide\Server;
use Selenia\Application;
use Selenia\Core\Assembly\Services\ModuleServices;
use Selenia\Interfaces\ModelControllerInterface;
use Selenia\Interfaces\ModuleInterface;
use Selenia\Plugins\MatisseComponents as C;
use Selenia\Plugins\MatisseComponents\Handlers\ImageFieldHandler;
use Selenia\Plugins\MatisseComponents\Models\File;

class MatisseComponentsModule implements ModuleInterface
{
  function boot (Application $app, ModelControllerInterface $modelController, Server $glideServer)
  {
    $modelController
      ->registerExtension (ImageFieldHandler::class);

    File::deleting (function (File $model) use ($app, $glideServer) {
      if (exists ($model->path)) {
        $path = "$app->fileArchivePath/$model->path";
        if (file_exists ($path))
          unlink ($path);
        $glideServer->deleteCache ($model->path);
      }
    });
  }

  function configure (ModuleServices $module)
  {
    $module
      ->publishPublicDirAs ('modules/selenia-plugins/matisse-components')
      ->provideMacros ()
      ->registerComponents ([
        'Button'         => C\Button::class,
        'Checkbox'       => C\Checkbox::class,
        'DataGrid'       => C\DataGrid::class,
        'Dropzone'       => C\Dropzone::class,
        'Field'          => C\Field::class,
        'FileUpload'     => C\FileUpload::class,
        'HtmlEditor'     => C\HtmlEditor::class,
        'Image'          => C\Image::class,
        'ImageField'     => C\ImageField::class,
        'Input'          => C\Input::class,
        'Label'          => C\Label::class,
        'Link'           => C\Link::class,
        'MainMenu'       => C\MainMenu::class,
        'NavigationPath' => C\NavigationPath::class,
        'Paginator'      => C\Paginator::class,
        'RadioButton'    => C\RadioButton::class,
        'Select'         => C\Select::class,
        'Switch'         => C\Switch_::class,
        'Tab'            => C\Tab::class,
        'TabPage'        => C\TabPage::class,
        'Tabs'           => C\Tabs::class,
      ])
      ->registerAssets ([
        'dist/components.css',
      ]);
  }
}
