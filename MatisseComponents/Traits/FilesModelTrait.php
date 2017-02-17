<?php

namespace Electro\Plugins\MatisseComponents\Traits;

use Electro\Plugins\MatisseComponents\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait FilesModelTrait
{
  /**
   * Registers an event handler that, when a model is deleted, deletes all of its files.
   */
  static public function bootFilesModelTrait ()
  {
    static::deleting (function ($model) {
      foreach ($model->files as $file)
        /** @var Model $file */
        $file->delete ();
    });
  }

  /**
   * Get all owned files.
   *
   * @return MorphMany
   */
  public function files ()
  {
    return $this->morphMany (File::class, 'owner');
  }

}
