<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceSantander extends Model
{
    use HasFactory;

    protected $table = 'workspaces_santander';

    protected $fillable = [
        'status',
        'parametros_bancos_id',
        'type',
        'description',
        'covenant_code',
        'bank_slip_billing_webhook_active',
        'pix_billing_webhook_active',
        'parametros_bancos_id',
        'id_remoto',
        'webhookurl'
    ];
}
