<?php

namespace PriceChecker;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;


class REIChecker {

    const STORE_GARAGE = 'garage';
    const STORE_REI_DOT_COM = 'reidotcom';

    private $onlyShowCheaperItems;
    private $potentialSavings = 0;

    public function __construct($onlyShowCheaperItems = false, $shouldColorize = true) {
        $this->onlyShowCheaperItems = $onlyShowCheaperItems;
        $this->colorizer = new PriceColorizer($shouldColorize);
    }

    public function showAllPrices($inputFilePath) {
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($inputFilePath);

        $header = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 1) {
                    $header = array_map(function($column) {
                        return strtolower($column);
                    }, $row);
                } else {
                    $validRow = array_slice($row, 0, count($header));
                    $rowAsAssociativeArray = array_combine($header, $validRow);
                    $this->processRow($rowAsAssociativeArray);
                }
            }
        }
    }

    public function showPotentialSavings() {
        $string = " POTENTIAL SAVINGS: \${$this->potentialSavings} ";
        $stringLen = strlen($string);

        $formattedString = str_repeat('=', $stringLen) . "\n";
        $formattedString .= "$string\n";
        $formattedString .= str_repeat('=', $stringLen) . "\n";

        echo $this->colorizer->colorize($formattedString, Colorizer::GREEN);
    }


    private function processRow($row) {
        $currentPriceAndStore = $this->getCurrentPriceAndStore($row['url'], $row['sku']);
        $currentPrice = $currentPriceAndStore['price'];
        $currentStore = $currentPriceAndStore['store'];

        $isCheaper = ($currentPrice !== null && $currentPrice < $row['price']);
        if ($this->onlyShowCheaperItems && !$isCheaper) {
            return;
        }

        $colorizedCurrentPrice = $this->colorizer->colorizeCurrentPrice(floatval($currentPrice), floatval($row['price']));

        $formattedCurrentStore = ($currentStore === self::STORE_GARAGE) ? "REI Garage" : "REI.com";

        $formattedTitle = ($row['size']) ? "{$row['title']} ({$row['size']} - {$row['color']})" : "{$row['title']} ({$row['color']})";
        $formattedHeaderLength = max(strlen($formattedTitle), strlen($row['url']));

        echo str_repeat('-', $formattedHeaderLength) . "\n";
        echo "$formattedTitle\n";
        echo "{$row['url']}\n";
        echo str_repeat('-', $formattedHeaderLength) . "\n";
        echo "  => PAID: \$" . number_format($row['price'], 2) . "\n";
        echo "  => NOW: $colorizedCurrentPrice [on $formattedCurrentStore]\n\n";

        $this->potentialSavings += ($isCheaper) ? ($row['price'] - $currentPrice) : 0;
    }

    private function getCurrentPriceAndStore($url, $sku) {
        $context = stream_context_create([
            'http' => [
                'follow_location' => true,
            ],
        ]);
        $contents = file_get_contents($url, false, $context);

        $isREIGarage = (preg_match('/<title>.* - REI Garage<\/title>/m', $contents, $matches));
        if ($isREIGarage) {
            return [
                'price' => $this->getCurrentPriceForREIGarage($sku, $contents),
                'store' => self::STORE_GARAGE,
            ];
        } else {
            return [
                'price' => $this->getCurrentPriceForREIDotCom($sku, $contents),
                'store' => self::STORE_REI_DOT_COM,
            ];
        }
    }

    private function getCurrentPriceForREIGarage($sku, $contents) {
        $currentPrice = null;

        if (preg_match('/<script type="application\/json" id="page-data">([\s\S]+?)<\/script>/', $contents, $matches)) {
            $pageData = json_decode($matches[1], true);
            $variants = array_key_exists('variants', $pageData['product']) ? $pageData['product']['variants'] : [];

            foreach ($variants as $variant) {
                if (intval($variant['sku']) === $sku) {
                    $currentPrice = $variant['price'];
                    break;
                }
            }
        }

        return $currentPrice;
    }

    private function getCurrentPriceForREIDotCom($sku, $contents) {
        $currentPrice = null;

        if (preg_match('/<script type="application\/json" data-client-store="product-model-data">([\s\S]+?)<\/script>/', $contents, $matches)) {
            $productModelData = json_decode($matches[1], true);
            $variants = $productModelData['variantData'];

            foreach ($variants as $variant) {
                if (intval($variant['sku']) === $sku) {
                    $currentPrice = $variant['itemPrice'];
                    break;
                }
            }
        }

        return $currentPrice;
    }
}

?>