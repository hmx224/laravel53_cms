<?php

namespace Modules\Activity\Web;

use App\Http\Controllers\BaseController;
use Modules\Activity\Models\ActivityData;
use Response;

class ActivityDataController extends BaseController
{
    protected $base_url = '/admin/activities';
    protected $view_path = 'activity.views';
    protected $module;

    public function show($activity_id)
    {
        return view($this->view_path .'.sign', compact('activity_id'));
    }

    public function table($activity_id)
    {
      return ActivityData::table($activity_id);
    }


}
