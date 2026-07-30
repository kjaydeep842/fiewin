<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'referral_code',
        'referred_by',
        'avatar',
        'role',
        'status',
        'kyc_status',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referees()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function bets()
    {
        return $this->hasMany(GameBet::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function dailyRewards()
    {
        return $this->hasMany(DailyReward::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
