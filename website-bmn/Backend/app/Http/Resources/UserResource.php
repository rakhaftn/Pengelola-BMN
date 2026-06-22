<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'nip' => $this->nip,
            'jabatan' => $this->jabatan,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Relations
            'unit_kerja' => $this->whenLoaded('unitKerja', function () {
                return $this->unitKerja ? [
                    'id' => $this->unitKerja->id,
                    'nama' => $this->unitKerja->nama,
                    'kode' => $this->unitKerja->kode,
                ] : null;
            }),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),

            // Role names (for convenience)
            'role' => $this->when($this->roles->isNotEmpty(), function () {
                return $this->roles->first()?->name;
            }),
        ];
    }
}
