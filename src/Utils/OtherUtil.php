<?php

namespace Sae\Utils;

class OtherUtil {

    private function __construct(){
    }

    /**
     * Retourne une couleur plus foncée que celle passée en paramètre
     * @param string $colorHex Couleur en hexadécimal
     * @return string Couleur plus foncée
     */
    public static function darkerColor(string $colorHex) : string {
        $color = str_replace("#", "", $colorHex);
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));

        $r = max(0, $r - 50);
        $g = max(0, $g - 50);
        $b = max(0, $b - 50);

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

}