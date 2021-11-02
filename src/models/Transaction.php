<?php

namespace sindibad\zaincash\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Admin
 *
 * @property-read int id
 * @property int transactionId
 * @property string status
 * @property string amount
 * @property string orderId
 * @property string operationId
 * @property string serviceType
 * @property string extras
 * @property string applicant_ip
 * @property string paid_at
 * @property-read string created_at
 * @property-read string updated_at
 * @property-read string deleted_at
 */

class Transaction extends Model
{

    const PENDING_STATUS        = "pending";
    const PAID_STATUS           = "paid";
    const FAILED_STATUS         = "failed";
    const REPETITIOUS_STATUS    = "repetitious";

    use HasFactory;
    use SoftDeletes;

    protected $table = "transaction_zaincash";

    /**
     * @var string[]
     */
    protected $fillable = [
        'amount',
        'transactionId',
        'orderId',
        'status',
        'serviceType',
        'extras',
        'applicant_ip',
        'operationId',
        'paid_at',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'extras' => 'array',
    ];
}
