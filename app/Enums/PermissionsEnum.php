<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case CreateOrder = 'CreateOrder';
    case ApproveOrder = 'ApproveOrder';
    case ClaimOrder = 'ClaimOrder';
    case ViewOrder = 'ViewOrder';
    case TrackOrder = 'TrackOrder';
    case Manage  = 'Manage';
    case Delete = 'Delete';
    case None = 'None';
}