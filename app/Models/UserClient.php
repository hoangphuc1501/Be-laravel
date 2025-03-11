<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;



class UserClient extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'users'; // Đảm bảo đúng tên bảng

    protected $fillable = [
        'fullname', 'email', 'password', 'address', 'phone',
        'otp', 'otpExpireAt', 'image', 'birthday', 'gender', 'status',
        'position', 'deleted'
    ];

    protected $hidden = [
        'password','otp'
    ];

    // Implement JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    // Kiểm tra OTP có hợp lệ không
    public function isOtpValid($otp)
    {
        return $this->otp && Hash::check($otp, $this->otp) && Carbon::parse($this->otpExpireAt)->isFuture();
    }

    public $timestamps = true;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';
}

