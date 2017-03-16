<?php

namespace Electro\Plugins\MatisseComponents\Config;

use Electro\ContentRepository\Config\ContentRepositorySettings;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\KernelInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Interfaces\ModuleInterface;
use Electro\Kernel\Lib\ModuleInfo;
use Electro\Plugins\MatisseComponents as C;
use Electro\Plugins\MatisseComponents\Handlers\FileFieldHandler;
use Electro\Plugins\MatisseComponents\Models\File;
use Electro\Profiles\WebProfile;
use Electro\ViewEngine\Config\ViewEngineSettings;
use Electro\ViewEngine\Services\AssetsService;
use League\Glide\Server;
use Matisse\Config\MatisseSettings;

class MatisseComponentsModule implements ModuleInterface
{
  static function getCompatibleProfiles ()
  {
    return [WebProfile::class];
  }

  static function startUp (KernelInterface $kernel, ModuleInfo $moduleInfo)
  {
    $kernel->onConfigure (
      function (MatisseSettings $matisseSettings, ModelControllerInterface $modelController,
                InjectorInterface $injector, ContentRepositorySettings $contentRepositorySettings,
                AssetsService $assetsService, ViewEngineSettings $engineSettings)
      use ($moduleInfo) {
        $engineSettings->registerViews ($moduleInfo);
        $matisseSettings
          ->registerMacros ($moduleInfo)
          ->registerComponents ([
            'Button'         => C\Button::class,
            'Checkbox'       => C\Checkbox::class,
            'DataGrid'       => C\DataGrid::class,
            'DocumentViewer' => C\DocumentViewer::class,
            'Dropzone'       => C\Dropzone::class,
            'Field'          => C\Field::class,
            'FileField'      => C\FileField::class,
            'HtmlEditor'     => C\HtmlEditor::class,
            'Image'          => C\Image::class,
            'ImageField'     => C\ImageField::class,
            'Input'          => C\Input::class,
            'Label'          => C\Label::class,
            'Link'           => C\Link::class,
            'MainMenu'       => C\MainMenu::class,
            'Modal'          => C\Modal::class,
            'NavigationPath' => C\NavigationPath::class,
            'Paginator'      => C\Paginator::class,
            'RadioButton'    => C\RadioButton::class,
            'Select'         => C\Select::class,
            'Switch'         => C\Switch_::class,
            'Tab'            => C\Tab::class,
            'TabPage'        => C\TabPage::class,
            'Tabs'           => C\Tabs::class,
          ]);
        $assetsService->registerAssets ($moduleInfo->name, [
          'dist/components.css',
        ]);

        $modelController
          ->registerExtension ($injector->makeFactory (FileFieldHandler::class));
      });
  }

}
