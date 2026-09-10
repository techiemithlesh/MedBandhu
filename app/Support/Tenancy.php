<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Hospital;

/**
 * Holds the tenant context for the current request / console command.
 *
 * Registered as a singleton (see AppServiceProvider). Populated by the
 * InitializeTenancy middleware for web requests, or manually in jobs/commands.
 */
class Tenancy
{
    protected ?Hospital $hospital = null;

    protected ?Branch $branch = null;

    /** True while running as the platform Super Admin (no hospital bound). */
    protected bool $superAdmin = false;

    public function setHospital(?Hospital $hospital): void
    {
        $this->hospital = $hospital;
    }

    public function hospital(): ?Hospital
    {
        return $this->hospital;
    }

    public function hospitalId(): ?int
    {
        return $this->hospital?->id;
    }

    public function hasHospital(): bool
    {
        return $this->hospital !== null;
    }

    public function setBranch(?Branch $branch): void
    {
        $this->branch = $branch;
    }

    public function branch(): ?Branch
    {
        return $this->branch;
    }

    public function branchId(): ?int
    {
        return $this->branch?->id;
    }

    public function setSuperAdmin(bool $value): void
    {
        $this->superAdmin = $value;
    }

    public function isSuperAdmin(): bool
    {
        return $this->superAdmin;
    }

    /**
     * Whether tenant scoping should currently be applied to queries.
     * Super Admin browsing the platform sees everything; once they pick a
     * hospital to work in, scoping switches back on.
     */
    public function shouldScope(): bool
    {
        return $this->hospital !== null;
    }

    public function forget(): void
    {
        $this->hospital = null;
        $this->branch = null;
        $this->superAdmin = false;
    }
}
