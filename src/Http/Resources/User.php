<?php

namespace UonSoftware\LaraAuth\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        $fields = config('lara_auth.serialization_fields');

        $data = [];

        foreach ($fields as $key => $value) {
            if (
                is_array($value) &&
                isset($value['type']) &&
                $value['type'] === 'function' &&
                function_exists($value[$value['type']])
            ) {
                $date[$key] = $value($this->resource);
            } else {
                $data[$key] = $this->resource->{$value};
            }
        }

        return $data;
    }
}
