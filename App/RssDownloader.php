<?php

namespace App;

use DiDom\Document;
use DiDom\Element;
use Zend\Feed\Reader\Reader;
use Zend\Feed\Reader\Exception\RuntimeException;

class RssDownloader {
    private $validArticleContainers = [
        "article",
        "div",
    ];
    private $validImageExtensions = [
        "jpg",
        "jpeg",
        "png",
        "gif",
        "bmp",
        "webp"
    ];
    private $selectorsToRemove = [
        'img[height="1"]', // remove any tracking pixel
        'head',
        'script',
        'noscript',
        'iframe',
        'style'
    ];

    private $providers = [];
    private $addedHTML = "";

    const PIXEL_1_1_LENGTH = 100;
    const METHOD_GET = "get";
    const METHOD_POST = "post";
    const METHOD_PUT = "put";

    public function __construct () {
        $this->providers = require(APP_ROOT . DIRECTORY_SEPARATOR . "providers.php");
        $this->addedHTML = file_get_contents(APP_ROOT . DIRECTORY_SEPARATOR . "header.html");
        $weeklyCacheFolder = $this->getCacheFolder("weekly");
        $dailyCacheFolder = $this->getCacheFolder("daily");

        if (!file_exists($weeklyCacheFolder)) {
            $this->createFolder($weeklyCacheFolder);
        }

        if (!file_exists($dailyCacheFolder)) {
            $this->createFolder($dailyCacheFolder);
        }
    }

    public function getFeedUrl ($url) {
        $content = $this->curl($url);
        $firstLine = strtok($content, "\n")[0];

        // it's not a feed
        if (strpos($firstLine, "<?xml") === false) {
            $document = new Document($content);
            $rss = $document->find('link[type="application/rss+xml"]');

            if (sizeof($rss) < 1) {
                return null;
            }

            $url = $rss[0]->attr("href");
        }

        return $url;
    }

    public function getTodayArticles ($userId) {
        $db = new Db($userId);
        $items = [];

        foreach ($db->list() as $url => $date) {
            $items = array_merge($items, $this->getTodayArticlesFrom($url));
        }

        return $items;
    }

    public function getTodayArticlesFrom ($url) {
        try {
            //$slashdotRss = Reader::import($url);
            $slashdotRss = Reader::importString($this->curl($url));
        } catch (RuntimeException $e) {
            return [];
        }

        $items = [];

        foreach ($slashdotRss as $item) {
            try {
                if($item->getDateModified()->format('Y-m-d') != date("Y-m-d")) {
                    continue;
                }
            } catch (\Exception $e) {
                // no date? no parsing
                continue;
            }

            $description = $item->getContent();

            // maybe it has a simple description
            if (empty($description)) {
                $description = $item->getDescription();
            }

            // cant do anything
            if (empty($description)) {
                continue;
            }
            $description = ltrim(explode("\n", $description)[0]);

            // get all text before the first link in the first line
            $pattern = '/<a[^>]+>.+/i';
            $replacement = '${1}';
            $description = preg_replace($pattern, $replacement, $description);
            $description = ltrim($description);
            $description = ltrim(substr($description, 0, -5));
            $link = $item->getLink();

            $entry = [
                'title' => $item->getTitle(),
                'url' => $link,
                'html' => $this->getFullArticle($link, $description),
             ];

            // failed to grab the entire article
            if (empty($entry["html"])) {
                continue;
            }

            $items[] = $entry;
        }

        return $items;
    }

    private function imagesTob64 ($html) {

        foreach ($html->find('picture') as &$pic) {
            $src = $pic->find("img")[0]->attr("src");

            if (empty($src)) {
                $pic->remove();
                continue;
            }

            // default image is a 1x1 pixel, usually for lazyloading
            // we will replace it using the first source picture has
            if (strpos($src, "data:image") !== false && strlen($src) < self::PIXEL_1_1_LENGTH) {
                $firstSource = $pic->find("source")[0];

                if (!empty($firstSource->attr("srcset"))) {
                    $src = $firstSource->attr("srcset");
                } else if (!empty($firstSource->attr("data-srcset"))) {
                    $src = $firstSource->attr("data-srcset");
                } else { // ran out of ideas
                    $pic->remove();
                    continue;
                }
            }

            $pic->replace(new Element('img', null, ["src" => $src]));
        }

        foreach ($html->find('img') as &$image) {
            $src = $image->attr("src");

            if (strpos($src, "http") === false) {
                continue;
            }
            $type = pathinfo($src, PATHINFO_EXTENSION);
            $image->attr("src", 'data:image/' . $type . ';base64,' . base64_encode($this->curl($src)));
        }

        return $html;
    }

    private function hasCustomSetting ($url, $settingType) {
        $host = parse_url($url, PHP_URL_HOST);
        return isset($this->providers[$host]) && isset($this->providers[$host][$settingType]);
    }

    private function getCustomSettings ($url) {
        return $this->providers[parse_url($url, PHP_URL_HOST)];
    }
    private function getCustomSetting ($url, $settingType) {
        return $this->providers[parse_url($url, PHP_URL_HOST)][$settingType];
    }

    private function removeProviderSelectors ($url, $html) {
        if ($this->hasCustomSetting($url, "remove")) {
            $selectors = array_merge($this->selectorsToRemove, $this->getCustomSetting($url, "remove"));
        } else {
            $selectors = $this->selectorsToRemove;
        }

        foreach ($selectors as $selector) {
            foreach ($html->find($selector) as $el) {
                $el->remove();
            }
        }

        if ($this->hasCustomSetting($url, "custom")) {
            $html = $this->getCustomSetting($url, "custom")($html);
        }

        return $html;
    }

    public function getFullArticle ($url, $firstLines) {
        $article = new Article($url);

        if ($article->exists()) {
            return $article->get();
        }

        $rawHtml = $this->curl($url);

        $html = new Document($rawHtml);

        if ($this->hasCustomSetting($url, "selector")) {
            $article = $html->find($this->getCustomSetting($url, "selector"));
        } else {
            $article = $html->find("article"); // sites that care on SEO will have this tag as article's container
        }

        if ($article && sizeof($article) > 0) {
            $html = new Document($article[0]->html()); // ugly didom hack, otherwise, any changes applied to article html arent saved
            $html = $this->removeProviderSelectors($url, $html);
            $html = $this->imagesTob64($html);

            return $this->addedHTML . $html->html();
        }
        return null;

        // @ToDo
        // thisis a work in progress part that attempts to find the article container searching by first article words
        if (substr($firstLines, 0, 1) == "<") { // looks like we have the parent html tag
            $chars = str_split($firstLines);
            $tagType = "";
            $parentTag = "";
            foreach ($chars as $char) {
                $parentTag .= $char;
                if ($char == ">") {
                    break;
                }
            }

            // <p style="text-align: justify;"> to <p
            if (strpos($parentTag, " ") !== false) {
                $tagType = explode(" ", $parentTag)[0];
            }

            // <div> to div
            $tagType = str_replace("<", "", $tagType);
            $tagType = str_replace(">", "", $tagType);

            // maybe article starts with an <image> or it's just a <p> from many that article may have
            if (!in_array($tagType, $this->validArticleContainers)) {
                exit;
            }

//            preg_match('%(<' . substr($firstLines, 1, 1) . '[^>]*>)' . stri$firstLines . '%i', $html, $regs);
        }
        preg_match('%(<[^>]+>' . $firstLines . ')%i', $html, $regs);
        pd($regs);
    }

    private function getCacheFolder ($type) {
        if ($type == "weekly") {
            $cT = "weekly" . DIRECTORY_SEPARATOR . date("Y_m_w");
        } else {
            $cT = "daily" . DIRECTORY_SEPARATOR . date("Y_m_d");
        }

        return  APP_ROOT . App::config("cache.folder") . DIRECTORY_SEPARATOR . $cT . DIRECTORY_SEPARATOR;
    }

    private function createFolder ($path, $recursive = false)
    {
        $oldmask = umask(0);
        mkdir($path, 0775, $recursive);
        umask($oldmask);
    }

    public function getCachePath ($url) {
         // articles may have website static images like icons, so we can recyle them to save bandwitdh
         if (in_array(pathinfo($url, PATHINFO_EXTENSION), $this->validImageExtensions)) {
             $cacheType = $this->getCacheFolder("weekly");
         } else {
             $cacheType = $this->getCacheFolder("daily");
         }

        return $cacheType . md5($url);
    }

    private function curl ($url, $method = "get", $data = []) {

        if (App::config("cache")) {
            $cachedFile = $this->getCachePath($url);

            if (file_exists($cachedFile)) {
                return file_get_contents($cachedFile);
            }
        }

        $ch = curl_init($url);
        $header = [];
        $header[0]  = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[]   = "Cache-Control: max-age=0";
        $header[]   = "Connection: keep-alive";
        $header[]   = "Keep-Alive: 300";
        $header[]   = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[]   = "Accept-Language: en-us,en;q=0.5";
        $header[]   = "Pragma: "; // browsers keep this blank.
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        switch ($method)
        {
            case self::METHOD_POST:
                curl_setopt($ch, CURLOPT_POST, true);
                if (sizeof($data) > 0) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                }
                break;
            case self::METHOD_PUT:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                if (sizeof($data) > 0) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                }
                break;
            default:
                if (sizeof($data) > 0) {
                    $url = $url . "?" . http_build_query($data);
                }
                break;
        }
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        $content = curl_exec($ch);

        if (App::config("cache")) {
            file_put_contents($cachedFile, $content);
        }

        return content;
    }
}
