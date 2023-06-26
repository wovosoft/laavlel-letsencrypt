<?php

namespace Wovosoft\LaravelLetsencryptCore\Data;

use Illuminate\Support\Collection;

class Order extends BaseData
{
    protected \DateTime $expiresAt;

    public function __construct(
        protected array  $domains,
        protected string $url,
        protected string $status,
        string           $expiresAt,
        protected array  $identifiers,
        protected array  $authorizations,
        protected string $finalizeURL
    )
    {
        //Handle the micro time date format
        if (str_contains($expiresAt, '.')) {
            $expiresAt = substr($expiresAt, 0, strpos($expiresAt, '.')) . 'Z';
        }
        $this->expiresAt = (new \DateTime())->setTimestamp(strtotime($expiresAt));
    }


    /**
     * Returns the order number
     * @return string
     */
    public function getId(): string
    {
        return basename($this->url);
    }

    /**
     * Return set of authorizations for the order
     * @return Collection
     */
    public function getAuthorizationURLs(): Collection
    {
        return collect($this->authorizations);
    }

    /**
     * Returns order status
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Returns expires at
     * @return \DateTime
     */
    public function getExpiresAt(): \DateTime
    {
        return $this->expiresAt;
    }

    /**
     * Returs domains as identifiers
     * @return array
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * Returns url
     * @return string
     */
    public function getFinalizeURL(): string
    {
        return $this->finalizeURL;
    }

    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns domains for the order
     * @return array
     */
    public function getDomains(): array
    {
        return $this->domains;
    }

    public function toArray(): array
    {
        return [
            "domains" => $this->domains,
            "url" => $this->url,
            "status" => $this->status,
            "expiresAt" => $this->expiresAt,
            "identifiers" => $this->identifiers,
            "authorizations" => $this->authorizations,
            "finalizeURL" => $this->finalizeURL,
        ];
    }

    public function isReady(): bool
    {
        return $this->getStatus() === 'ready';
    }
}
