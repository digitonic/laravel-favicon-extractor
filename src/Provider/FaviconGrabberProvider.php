<?php

namespace StefanBauer\LaravelFaviconExtractor\Provider;

use GrahamCampbell\GuzzleFactory\GuzzleFactory;
use StefanBauer\LaravelFaviconExtractor\Exception\NoIconsFoundException;

class FaviconGrabberProvider implements ProviderInterface
{
    public function fetchFromUrl(string $url): string
    {
        $url = parse_url($url);
        $client = GuzzleFactory::make();
        $response = $client->get($this->getUrl($url['host']));
        $icons = json_decode($response->getBody()->getContents(), true)['icons'];

        if ($icons === null) {
            throw new NoIconsFoundException('No icons could be found for this url', '404');
        }

        return $icons[0]['src'];
    }

    private function getUrl(string $url): string
    {
        return 'http://favicongrabber.com/api/grab/'.urlencode($url);
    }
}
