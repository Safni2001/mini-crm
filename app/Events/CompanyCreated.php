<?php

namespace App\Events;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompanyCreated
{
    use Dispatchable, SerializesModels;

    public Company $company;
    public User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Company $company, User $user)
    {
        $this->company = $company;
        $this->user = $user;
    }
}
