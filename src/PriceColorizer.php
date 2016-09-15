<?php

namespace PriceChecker;


class PriceColorizer extends Colorizer {

    public function colorizeCurrentPrice($currentPrice, $originalPrice) {
        $isStillAvailable = ($currentPrice !== 0.0);
        $isCheaper = ($isStillAvailable && $currentPrice < $originalPrice);
        $isSamePrice = ($isStillAvailable && $currentPrice === $originalPrice);

        if (!$isStillAvailable) {
            return $this->colorize('N/A', Colorizer::WHITE);
        }

        $formattedCurrentPrice = '$' . number_format($currentPrice, 2);

        if ($isCheaper) {
            return $this->colorize($formattedCurrentPrice, Colorizer::MAGENTA);
        } else if ($isSamePrice) {
            return $this->colorize($formattedCurrentPrice, Colorizer::WHITE);
        } else {
            return $this->colorize($formattedCurrentPrice, Colorizer::YELLOW);
        }
    }

}

?>