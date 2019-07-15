<?php

namespace App\Serializer\Normalizer;

use FOS\RestBundle\Serializer\Normalizer\ExceptionNormalizer as BaseNormalizer;

class ExceptionNormalizer extends BaseNormalizer
{
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];

        $data['error'] = $this->getExceptionMessage($object, isset($statusCode) ? $statusCode : null);

        return $data;
    }
}
