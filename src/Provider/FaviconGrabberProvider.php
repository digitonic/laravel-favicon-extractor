<?php

namespace StefanBauer\LaravelFaviconExtractor\Provider;

use GrahamCampbell\GuzzleFactory\GuzzleFactory;
use Illuminate\Support\Collection;
use StefanBauer\LaravelFaviconExtractor\Exception\NoIconsFoundException;

class FaviconGrabberProvider implements ProviderInterface
{
    public function fetchFromUrl(string $url): string
    {
        $url = parse_url($url);
        $client = GuzzleFactory::make();
        $response = $client->get($this->getUrl($url['path']));
        $icons = json_decode($response->getBody()->getContents(), true)['icons'];
        $collectedIcons = collect($icons);

        if ($icons === null) {
            throw new NoIconsFoundException('No icons could be found for this url', '404');
        }

        $filteredIcons = $collectedIcons->filter(function ($value, $key) {
            if (! empty($value['sizes'])) {
                return $value;
            }
        });

        if ($filteredIcons->isEmpty()) {
            return $icons[0]['src'];
        }

        return $this->findHighestQualityIcon($filteredIcons);
    }

    /**
     * @param Collection $icons
     *
     * @return string
     */
    private function findHighestQualityIcon(Collection $icons): string
    {
        $highestQualitySource = '';
        $largestSize = 0;

        $icons->each(function ($processedIcon) use (&$highestQualitySource, &$largestSize) {
            $iconSize = explode('x', $processedIcon['sizes'])[0];
            if ($iconSize > $largestSize ) {
                $highestQualitySource = $processedIcon['src'];
                return;
            }
        });

        return $highestQualitySource;
    }

    private function getUrl(string $url): string
    {
        return 'http://favicongrabber.com/api/grab/'.urlencode($url);
    }
}
