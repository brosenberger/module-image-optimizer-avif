<?php

namespace BroCode\ImageAvifOptimizer\Model\Converter;

use BroCode\ImageAvifOptimizer\Api\Constants;
use BroCode\ImageOptimizer\Model\Converter\AbstractImageConverter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class AvifImageConverter extends AbstractImageConverter
{
    const CONVERTER_ID = 'avif';

    protected $imageQuality = null;

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($logger);
        $this->scopeConfig = $scopeConfig;
    }

    protected function getConversionImageExtension()
    {
        return '.' . self::CONVERTER_ID;
    }

    /**
     * @inheritDoc
     */
    public function getConverterId()
    {
        return self::CONVERTER_ID;
    }

    function doConvert($imagePath, $newFile)
    {
        if (!$this->supportsGd()) {
            $this->logger->warning('BroCode - ImageOptimizer: GD is not supported. Please install GD library to convert images to webp.');
            return false;
        }

        $imageData = file_get_contents($imagePath);
        try {
            $image = imagecreatefromstring($imageData);
            imagepalettetotruecolor($image);
        } catch (\Exception $ex) {
            $this->logger->info('BroCode - ImageOptimizer: Could not transform/load image ' . $imagePath . ': ' . $ex->getMessage());
            return false;
        }

        $converted = imageavif($image, $newFile, $this->getImageQuality());
        if (!$converted) {
            $this->logger->info('BroCode - ImageOptimizer: Could not convert image to avif: ' . $imagePath);
        }
        return $converted;
    }

    protected function supportsGd()
    {
        return function_exists('imageavif');
    }

    protected function getImageQuality()
    {
        if ($this->imageQuality === null) {
            $this->imageQuality = $this->scopeConfig->getValue(Constants::CONFIG_AVIF_QUALITY, 'store');
        }
        return $this->imageQuality;
    }

    protected function isEnabled()
    {
        return $this->scopeConfig->getValue(Constants::CONFIG_AVIF_ENABLED, 'store') == true;
    }
}
