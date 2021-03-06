<?php

namespace SAFETECHio\FIDO2\Tools;

trait EnumType
{
    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function All()
    {
        $tbs = new static;
        $reflectionClass = new \ReflectionClass($tbs);
        return $reflectionClass->getConstants();
    }

    /**
     * @param string $needle
     * @return bool
     * @throws \ReflectionException
     */
    public static function Has($needle): bool
    {
        return in_array($needle, static::All());
    }
}