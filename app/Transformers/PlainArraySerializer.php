<?php

namespace App\Transformers;

use League\Fractal\Serializer\ArraySerializer;

/**
 * This is a customised serializer for fractal
 * false results in no wrapper array at all
 * null results in 'data'
 * anything else results in anything else
 * https://github.com/thephpleague/fractal/issues/90
 */

class PlainArraySerializer extends ArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        return $this->serializeArray($resourceKey, $data);
    }

    public function item($resourceKey, array $data)
    {
        return $this->serializeArray($resourceKey, $data);
    }

    protected function serializeArray($resourceKey, array $data)
    {
        if ($resourceKey === false) {
            return $data;
        }

        return array($resourceKey ?: 'data' => $data);
    }
}
