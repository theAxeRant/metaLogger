<?php

namespace Theaxerant\Metalogger\Util\Helper;

class IpHelper {

    /**
     * Should the provided Ip address be filtered out by the provided network mask
     *
     * @param string $IpAddress
     * @param string $netMask
     * @return bool
     */
    public static function filterFromMask(string $IpAddress, string $netMask): bool
    {

        $intAddress = ip2long($IpAddress);

        list($sIp, $iNetmask) = explode('/',$netMask,2);
        $iIp = ip2long($sIp);
        $dMaks = pow(2, (32-$iNetmask)) - 1;
        $iNetmask = ~ $dMaks;
        $bResult = (($intAddress & $iNetmask) == ($iIp & $iNetmask));

        return !$bResult;
    }
}