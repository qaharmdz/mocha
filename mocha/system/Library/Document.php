<?php
/*
 * This file is part of Mocha package.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Full copyright and license see LICENSE file or visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

namespace Mocha\System\Library;

class Document
{
    protected $data = [];

    public function all()
    {
        return $this->data;
    }

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
        return $this->data['title'] ?? '';
    }

    public function addMeta(string $attribute, string $value, string $content)
    {
        $this->data['meta'][] = [
            'attribute' => $attribute,
            'value'     => $value,
            'content'   => $content
        ];
    }

    public function getMeta()
    {
        return $this->data['meta'] ?? [];
    }

    public function addLink(string $rel, string $href, string $hreflang = '', string $type = '', string $media = '')
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
        return $this->data['link'] ?? [];
    }

    public function addStyle(string $href, $media = 'all')
    {
        $this->data['style'][$href] = [
            'href'  => $href,
            'media' => $media
        ];
    }

    public function getStyle()
    {
        return $this->data['style'] ?? [];
    }

    public function addScript(string $href)
    {
        $this->data['script'][$href] = $href;
    }

    public function getScript()
    {
        return $this->data['script'] ?? [];
    }

    public function addAsset(string $name, array $asset)
    {
        $default = [
            'version' => '',
            'script'  => [],
            'style'   => []
        ];

        $this->data['asset'][$name] = array_replace_recursive($default, $asset);
    }

    public function getAsset(string $name)
    {
        return $this->data['asset'][$name] ?? [];
    }

    public function applyAsset(string $name)
    {
        if (!empty($this->data['asset'][$name])) {
            $version = '';

            foreach ($this->data['asset'][$name] as $type => $assets) {
                if (!$version) {
                    $version = !empty($this->data['assetVersion']) ? $this->data['assetVersion'] : ($type == 'version' ? '?v=' . $assets : '');
                }

                if ($type == 'style') {
                    foreach ($assets as $asset) {
                        $this->addStyle($asset . $version);
                    }
                }
                if ($type == 'script') {
                    foreach ($assets as $asset) {
                        $this->addScript($asset . $version);
                    }
                }
            }
        }
    }

    public function assetVersion(string $version)
    {
        $this->data['assetVersion'] = '?v=' . $version;
    }


    /**
     * Node is general purpose storage.
     */
    public function addNode(string $name, array $value)
    {
        $node = $this->data['node'][$name] ?? [];

        if (is_array($node)) {
            $this->data['node'][$name] = array_merge($node, $value);
        }
    }

    public function setNode(string $name, $value)
    {
        $this->data['node'][$name] = $value;
    }

    public function getNode(string $name, $default = null)
    {
        return $this->data['node'][$name] ?? $default;
    }
}
