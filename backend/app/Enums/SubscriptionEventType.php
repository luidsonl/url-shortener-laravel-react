<?php

namespace App\Enums;

enum SubscriptionEventType: string
{
    case CREATED = 'created';
    case RENEWED = 'renewed';
    case EXPIRED = 'expired';
    case PLAN_CHANGED = 'plan_changed';
    case CREDIT_APPLIED = 'credit_applied';
    case CANCELLED = 'cancelled';
}
