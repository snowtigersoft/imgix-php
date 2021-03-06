<?php

namespace Imgix;

class UrlBuilder {

    private $domains;
    private $useHttps;
    private $signKey;
    private $shardStrategy;

    private $shardCycleNextIndex = 0;

    public function __construct($domains, $useHttps = false, $signKey = "", $shardStrategy = ShardStrategy::CRC) {
        if (!is_array($domains)) {
            $this->domains = array($domains);
        } else {
            $this->domains = $domains;
        }

        if (sizeof($this->domains) === 0) {
            throw new \InvalidArgumentException("UrlBuilder requires at least one domain");
        }

        $this->useHttps = $useHttps;
        $this->signKey = $signKey;
        $this->shardStratgy = $shardStrategy;
    }

    public function setShardStrategy($start) {
        $this->shardStratgy = $start;
    }

    public function setSignKey($key) {
        $this->signKey = $key;
    }

    public function setUseHttps($useHttps) {
        $this->useHttps = $useHttps;
    }

    public function createURL($path, $params=array()) {
        $scheme = $this->useHttps ? "https" : "http";

        if ($this->shardStratgy === ShardStrategy::CRC) {
            $index = crc32($path) % sizeof($this->domains);
            $domain = $this->domains[$index];
        } else if ($this->shardStratgy === ShardStrategy::CYCLE) {
            $this->shardCycleNextIndex = ($this->shardCycleNextIndex + 1) % sizeof($this->domains);
            $domain = $this->domains[$this->shardCycleNextIndex];
        } else {
            $domain = $this->domains[0];
        }

        $uh = new UrlHelper($domain, $path, $scheme, $this->signKey, $params);

        return $uh->getURL();
    }
}
