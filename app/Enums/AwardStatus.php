<?php

namespace App\Enums;

enum AwardStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
}
