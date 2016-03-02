<?php
namespace Selenia\Plugins\MatisseComponents\Traits;

use Illuminate\Database\Eloquent\Model;
use Selenia\Plugins\MatisseComponents\Models\File;

trait FilesModelTrait
{
  /**
   * Get all the owned files.
   */
  public function files ()
  {
    /** @var Model $this */
    return $this->morphMany (File::class, 'owner');
  }

}
