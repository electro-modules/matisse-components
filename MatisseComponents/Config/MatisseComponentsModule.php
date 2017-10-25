<?php

namespace Electro\Plugins\MatisseComponents\Config;

use Electro\Http\Lib\Http;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\Http\Shared\ApplicationRouterInterface;
use Electro\Interfaces\KernelInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Interfaces\ModuleInterface;
use Electro\Kernel\Lib\ModuleInfo;
use Electro\Localization\Config\LocalizationSettings;
use Electro\Plugins\MatisseComponents as C;
use Electro\Plugins\MatisseComponents\Dropzone;
use Electro\Plugins\MatisseComponents\Handlers\FileFieldHandler;
use Electro\Profiles\WebProfile;
use Electro\ViewEngine\Config\ViewEngineSettings;
use Electro\ViewEngine\Services\AssetsService;
use Matisse\Config\MatisseSettings;
use Psr\Log\LoggerInterface;

class MatisseComponentsModule implements ModuleInterface
{
  const DROPZONE_UPLOAD_URL = 'components/dropzone/upload';

  static function getCompatibleProfiles ()
  {
    return [WebProfile::class];
  }

  static function startUp (KernelInterface $kernel, ModuleInfo $moduleInfo)
  {
    $kernel->onConfigure (
      function (MatisseSettings $matisseSettings, ModelControllerInterface $modelController,
                InjectorInterface $injector, ApplicationRouterInterface $router,
                AssetsService $assetsService, ViewEngineSettings $engineSettings, LoggerInterface $logger, LocalizationSettings $localizationSettings)
      use ($moduleInfo) {
        $localizationSettings->registerTranslations($moduleInfo);
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

        $router->add ([
          route (self::DROPZONE_UPLOAD_URL,function($req,$res) use($logger)
          {
            try {
              return Dropzone::dropzoneUpload($req, $res);
            }
            catch (\Exception $e)
            {
              $logger->error($e->getMessage(),$e->getTrace());
              return Http::response($res,'$DROPZONE_GENERIC_ERROR_MESSAGE','application/json',500);
            }
            catch (\Error $e)
            {
              $logger->error($e->getMessage(),$e->getTrace());
              return Http::response($res,'$DROPZONE_GENERIC_ERROR_MESSAGE','application/json',500);
            }
          }),
        ]);
      });
  }

}
