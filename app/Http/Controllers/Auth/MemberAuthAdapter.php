<?php

namespace App\Http\Controllers\Auth;

use App\Models\Member;
use Tymon\JWTAuth\Providers\Auth\AuthInterface;

class MemberAuthAdapter implements AuthInterface
{
    protected $user;

    public function byCredentials(array $credentials = [])
    {
        return false;
    }

    public function byId($id)
    {
        $this->user = Member::find($id);
        return !empty($this->user);
    }

    public function user()
    {
        return $this->user;
    }
}
