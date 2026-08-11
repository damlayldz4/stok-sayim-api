<?php

namespace App\Enums;

enum UserRole: string
{
    case SystemAdmin = 'system_admin';
    case CompanyAdmin = 'company_admin';
    case CountStaff = 'count_staff';
}