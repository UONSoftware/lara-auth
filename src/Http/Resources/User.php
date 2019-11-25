<?php

namespace UonSoftware\LaraAuth\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $fields = config('lara_auth.serialization_fields');
        
        $data = [];
        
        foreach($fields as $key => $value) {
            $data[$key] = $this->resource->{$value};
        }
        
        return $data;
    }
}
