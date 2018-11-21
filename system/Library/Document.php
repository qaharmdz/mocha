<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha\System\Library;

class Document
{
    protected $data = [];

    public function setTitle(string $text, string $operate = '')
    {
        switch ($operate) {
            case 'prefix':
                $this->data['title'] = $text . $this->data['title'];
                break;

            case 'suffix':
                $this->data['title'] = $this->data['title'] . $text;
                break;

            default:
                $this->data['title'] = $text;
                break;
        }
    }

    public function getTitle()
    {
        return $this->data['title'] ?: '';
    }

    public function setMeta(string $attribute, string $value, string $content)
    {
        $this->data['meta'][] = [
            'attribute' => $attribute,
            'value'     => $value,
            'content'   => $content
        ];
    }

    public function getMeta()
    {
        return $this->data['meta'] ?: [];
    }

    public function setLink(string $rel, string $href, string $hreflang = '', string $type = '', string $media = '')
    {
        $this->data['link'][] = [
            'rel'       => $rel,
            'href'      => $href,
            'hreflang'  => $hreflang,
            'type'      => $type,
            'media'     => $media
        ];
    }

    public function getLink()
    {
        return $this->data['link'] ?: [];
    }

    public function setStyle($href, $media = 'all')
    {
        $this->data['style'][$href] = [
            'href'  => $href,
            'media' => $media
        ];
    }

    public function getStyle()
    {
        return $this->data['style'];
    }

    public function setScript($href)
    {
        $this->data['script'][$href] = $href;
    }

    public function getScript()
    {
        return $this->data['script'];
    }

    public function setAsset($name, array $asset)
    {
        $this->data['asset'][$name] = $asset;
    }

    public function getAsset($name)
    {
        if (!empty($this->data['asset'][$name])) {
            foreach ($this->data['asset'][$name] as $type => $assets) {
                if ($type == 'style') {
                    foreach ($assets as $asset) {
                        $this->setStyle($asset);
                    }
                }
                if ($type == 'script') {
                    foreach ($assets as $asset) {
                        $this->setScript($asset);
                    }
                }
            }
        }
    }

    public function setNode($name, $value)
    {
        $this->nodes[$name][] = $value;
    }

    public function getNodes($name)
    {
        return $this->nodes[$name] ?? [];
    }
}
