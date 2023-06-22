<?php

namespace Wovosoft\LaravelLetsencryptCore;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\FilesystemException;
use OpenSSLAsymmetricKey;
use Wovosoft\LaravelLetsencryptCore\Data\Account;
use Wovosoft\LaravelLetsencryptCore\Data\Authorization;
use Wovosoft\LaravelLetsencryptCore\Data\Certificate;
use Wovosoft\LaravelLetsencryptCore\Data\Challenge;
use Wovosoft\LaravelLetsencryptCore\Data\Order;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * Live url
     */
    const DIRECTORY_LIVE = 'https://acme-v02.api.letsencrypt.org/directory';

    /**
     * Staging url
     */
    const DIRECTORY_STAGING = 'https://acme-staging-v02.api.letsencrypt.org/directory';

    /**
     * Flag for production
     */
    const MODE_LIVE = 'live';

    /**
     * Flag for staging
     */
    const MODE_STAGING = 'staging';

    /**
     * New account directory
     */
    const DIRECTORY_NEW_ACCOUNT = 'newAccount';

    /**
     * Nonce directory
     */
    const DIRECTORY_NEW_NONCE = 'newNonce';

    /**
     * Order certificate directory
     */
    const DIRECTORY_NEW_ORDER = 'newOrder';

    /**
     * Http validation
     */
    const VALIDATION_HTTP = 'http-01';

    /**
     * DNS validation
     */
    const VALIDATION_DNS = 'dns-01';

    protected ?string $nonce = null;

    protected Account $account;

    protected array $privateKeyDetails;

    protected ?OpenSSLAsymmetricKey $accountKey = null;

    protected Filesystem $filesystem;

    protected array $directories = [];

    protected array $header = [];

    protected ?string $digest = null;

    protected ?HttpClient $httpClient = null;

    protected array $config;

    /**
     * Client constructor.
     *
     * @param array $config
     *
     * @type string $mode The mode for ACME (production / staging)
     * @type Filesystem $fs Filesystem for storage of static data
     * @type string $basePath The base path for the filesystem (used to store account information and csr / keys
     * @type string $username The acme username
     * @type string $source_ip The source IP for Guzzle (via curl. Options) to bind to (defaults to 0.0.0.0 [OS default])
     * }
     * @throws Exception|GuzzleException
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        if ($this->getOption('fs', false)) {
            $this->filesystem = $this->getOption('fs');
        } else {
            throw new \LogicException('No filesystem option supplied');
        }

        if ($this->getOption('username', false) === false) {
            throw new \LogicException('Username not provided');
        }

        $this->init();
    }

    /**
     * Get an existing order by ID
     *
     * @param $id
     * @return Order
     * @throws Exception
     * @throws GuzzleException
     * @throws FilesystemException
     */
    public function getOrder($id): Order
    {
        $url = str_replace('new-order', 'order', $this->getUrl(self::DIRECTORY_NEW_ORDER));
        $url = $url . '/' . $this->getAccount()->getId() . '/' . $id;
        $response = $this->request($url, $this->signPayloadKid(null, $url));
        $data = json_decode((string)$response->getBody(), true);

        $domains = [];
        foreach ($data['identifiers'] as $identifier) {
            $domains[] = $identifier['value'];
        }

        return new Order(
            $domains,
            $url,
            $data['status'],
            $data['expires'],
            $data['identifiers'],
            $data['authorizations'],
            $data['finalize']
        );
    }

    /**
     * Get ready status for order
     *
     * @param Order $order
     * @return bool
     * @throws Exception
     */
    public function isReady(Order $order): bool
    {
        try {
            $order = $this->getOrder($order->getId());
        } catch (GuzzleException|FilesystemException $e) {
        }
        return $order->getStatus() == 'ready';
    }


    /**
     * Create a new order
     *
     * @param array $domains
     * @return Order
     * @throws Exception
     * @throws GuzzleException
     * @throws FilesystemException
     */
    public function createOrder(array $domains): Order
    {
        $identifiers = [];
        foreach ($domains as $domain) {
            $identifiers[] =
                [
                    'type'  => 'dns',
                    'value' => $domain,
                ];
        }

        $url = $this->getUrl(self::DIRECTORY_NEW_ORDER);
        $response = $this->request($url, $this->signPayloadKid(
            [
                'identifiers' => $identifiers,
            ],
            $url
        ));

        $data = json_decode((string)$response->getBody(), true);
        return new Order(
            $domains,
            $response->getHeaderLine('location'),
            $data['status'],
            $data['expires'],
            $data['identifiers'],
            $data['authorizations'],
            $data['finalize']
        );
    }

    /**
     * Obtain authorizations
     *
     * @param Order $order
     * @return array|Authorization[]
     * @throws Exception
     * @throws GuzzleException
     * @throws FilesystemException
     */
    public function authorize(Order $order): array
    {
        $authorizations = [];
        foreach ($order->getAuthorizationURLs() as $authorizationURL) {
            $response = $this->request(
                $authorizationURL,
                $this->signPayloadKid(null, $authorizationURL)
            );
            $data = json_decode((string)$response->getBody(), true);
            $authorization = new Authorization($data['identifier']['value'], $data['expires'], $this->getDigest());

            foreach ($data['challenges'] as $challengeData) {
                $challenge = new Challenge(
                    $authorizationURL,
                    $challengeData['type'],
                    $challengeData['status'],
                    $challengeData['url'],
                    $challengeData['token']
                );
                $authorization->addChallenge($challenge);
            }
            $authorizations[] = $authorization;
        }

        return $authorizations;
    }

    /**
     * Run a self-test for the authorization
     * @param Authorization $authorization
     * @param string $type
     * @param int $maxAttempts
     * @return bool
     * @throws GuzzleException
     */
    public function selfTest(Authorization $authorization, string $type = self::VALIDATION_HTTP, int $maxAttempts = 15): bool
    {
        if ($type == self::VALIDATION_HTTP) {
            return $this->selfHttpTest($authorization, $maxAttempts);
        } elseif ($type == self::VALIDATION_DNS) {
            return $this->selfDNSTest($authorization, $maxAttempts);
        }
        return false;
    }

    /**
     * Validate a challenge
     *
     * @param Challenge $challenge
     * @param int $maxAttempts
     * @return bool
     * @throws Exception
     * @throws GuzzleException|FilesystemException
     */
    public function validate(Challenge $challenge, int $maxAttempts = 15): bool
    {
        try {
            $this->request(
                $challenge->getUrl(),
                $this->signPayloadKid([
                    'keyAuthorization' => $challenge->getToken() . '.' . $this->getDigest(),
                ], $challenge->getUrl())
            );
        } catch (GuzzleException|FilesystemException $e) {
        }

        $data = [];
        do {
            $response = $this->request(
                $challenge->getAuthorizationURL(),
                $this->signPayloadKid(null, $challenge->getAuthorizationURL())
            );
            $data = json_decode((string)$response->getBody(), true);
            if ($maxAttempts > 1 && $data['status'] != 'valid') {
                sleep(ceil(15 / $maxAttempts));
            }
            $maxAttempts--;
        } while ($maxAttempts > 0 && $data['status'] != 'valid');

        return (isset($data['status']) && $data['status'] == 'valid');
    }

    /**
     * Return a certificate
     *
     * @param Order $order
     * @return Certificate
     * @throws Exception
     * @throws GuzzleException
     * @throws FilesystemException
     */
    public function getCertificate(Order $order): Certificate
    {
        $privateKey = Helper::getNewKey($this->getOption('key_length', 4096));
        $csr = Helper::getCsr($order->getDomains(), $privateKey);
        $der = Helper::toDer($csr);

        $response = $this->request(
            $order->getFinalizeURL(),
            $this->signPayloadKid(
                ['csr' => Helper::toSafeString($der)],
                $order->getFinalizeURL()
            )
        );

        $data = json_decode((string)$response->getBody(), true);
        $certificateResponse = $this->request(
            $data['certificate'],
            $this->signPayloadKid(null, $data['certificate'])
        );
        $chain = preg_replace('/^[ \t]*[\r\n]+/m', '', (string)$certificateResponse->getBody());
        return new Certificate($privateKey, $csr, $chain);
    }


    /**
     * Return LE account information
     *
     * @return Account
     * @throws Exception|GuzzleException
     */
    public function getAccount(): Account
    {
        $response = $this->request(
            $this->getUrl(self::DIRECTORY_NEW_ACCOUNT),
            $this->signPayloadJWK(
                [
                    'onlyReturnExisting' => true,
                ],
                $this->getUrl(self::DIRECTORY_NEW_ACCOUNT)
            )
        );

        $data = json_decode((string)$response->getBody(), true);
        $accountURL = $response->getHeaderLine('Location');
        $date = (new \DateTime())->setTimestamp(strtotime($data['createdAt']));
        return new Account($data['contact'], $date, ($data['status'] == 'valid'), $data['initialIp'], $accountURL);
    }

    /**
     * Returns the ACME api configured Guzzle Client
     * @return HttpClient
     */
    protected function getHttpClient(): HttpClient
    {
        if ($this->httpClient === null) {
            $config = [
                'base_uri' => (
                ($this->getOption('mode', self::MODE_LIVE) == self::MODE_LIVE) ?
                    self::DIRECTORY_LIVE : self::DIRECTORY_STAGING),
            ];
            if ($this->getOption('source_ip', false) !== false) {
                $config['curl.options']['CURLOPT_INTERFACE'] = $this->getOption('source_ip');
            }
            $this->httpClient = new HttpClient($config);
        }
        return $this->httpClient;
    }

    /**
     * Returns a Guzzle Client configured for self test
     * @return HttpClient
     */
    protected function getSelfTestClient(): HttpClient
    {
        return new HttpClient([
            'verify'          => false,
            'timeout'         => 10,
            'connect_timeout' => 3,
            'allow_redirects' => true,
        ]);
    }

    /**
     * Self HTTP test
     * @param Authorization $authorization
     * @param $maxAttempts
     * @return bool
     */
    protected function selfHttpTest(Authorization $authorization, $maxAttempts): bool
    {
        do {
            $maxAttempts--;
            try {
                $response = $this->getSelfTestClient()->request(
                    'GET',
                    'http://' . $authorization->getDomain() . '/.well-known/acme-challenge/' .
                    $authorization->getFile()->getFilename()
                );
                $contents = (string)$response->getBody();
                if ($contents == $authorization->getFile()->getContents()) {
                    return true;
                }
            } catch (RequestException $e) {
            }
        } while ($maxAttempts > 0);

        return false;
    }

    /**
     * Self DNS test client that uses Cloudflare's DNS API
     * @param Authorization $authorization
     * @param $maxAttempts
     * @return bool
     * @throws GuzzleException
     */
    protected function selfDNSTest(Authorization $authorization, $maxAttempts): bool
    {
        do {
            $response = $this->getSelfTestDNSClient()->get(
                '/dns-query',
                [
                    'query' => [
                        'name' => $authorization->getTxtRecord()->getName(),
                        'type' => 'TXT',
                    ],
                ]
            );
            $data = json_decode((string)$response->getBody(), true);
            if (isset($data['Answer'])) {
                foreach ($data['Answer'] as $result) {
                    if (trim($result['data'], "\"") == $authorization->getTxtRecord()->getValue()) {
                        return true;
                    }
                }
            }
            if ($maxAttempts > 1) {
                sleep(ceil(45 / $maxAttempts));
            }
            $maxAttempts--;
        } while ($maxAttempts > 0);

        return false;
    }

    /**
     * Return the preconfigured client to call Cloudflare's DNS API
     * @return HttpClient
     */
    protected function getSelfTestDNSClient(): HttpClient
    {
        return new HttpClient([
            'base_uri'        => 'https://cloudflare-dns.com',
            'connect_timeout' => 10,
            'headers'         => [
                'Accept' => 'application/dns-json',
            ],
        ]);
    }

    /**
     * Initialize the client
     * @throws Exception
     * @throws GuzzleException
     */
    protected function init(): void
    {
        //Load the directories from the LE api
        $response = $this->getHttpClient()->get('/directory');
        $result = json_decode((string)$response->getBody(), true);
        $this->directories = $result;

        //Prepare LE account
        $this->loadKeys();
        $this->tosAgree();
        $this->account = $this->getAccount();
    }

    protected function loadKeys(): void
    {
        //Make sure a private key is in place
        if ($this->getFilesystem()->has($this->getPath('account.pem')) === false) {
            $this->getFilesystem()->write(
                $this->getPath('account.pem'),
                Helper::getNewKey($this->getOption('key_length', 4096))
            );
        }
        $privateKey = $this->getFilesystem()->read($this->getPath('account.pem'));
        $privateKey = openssl_pkey_get_private($privateKey);
        $this->privateKeyDetails = openssl_pkey_get_details($privateKey);
    }

    /**
     * Agree to the terms of service
     *
     * @throws Exception
     * @throws GuzzleException
     */
    protected function tosAgree(): void
    {
        $this->request(
            $this->getUrl(self::DIRECTORY_NEW_ACCOUNT),
            $this->signPayloadJWK(
                [
                    'contact'              => [
                        'mailto:' . $this->getOption('username'),
                    ],
                    'termsOfServiceAgreed' => true,
                ],
                $this->getUrl(self::DIRECTORY_NEW_ACCOUNT)
            )
        );
    }

    /**
     * Get a formatted path
     *
     * @param null $path
     * @return string
     */
    protected function getPath($path = null): string
    {
        $userDirectory = preg_replace('/[^a-z0-9]+/', '-', strtolower($this->getOption('username')));

        return $this->getOption(
                'basePath',
                'le'
            ) . DIRECTORY_SEPARATOR . $userDirectory . ($path === null ? '' : DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Return the Flysystem filesystem
     * @return Filesystem
     */
    protected function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Get a defined option
     *
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    protected function getOption($key, $default = null): mixed
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Get key fingerprint
     *
     * @return string
     * @throws Exception|FilesystemException
     */
    protected function getDigest(): string
    {
        if ($this->digest === null) {
            $this->digest = Helper::toSafeString(hash('sha256', json_encode($this->getJWKHeader()), true));
        }

        return $this->digest;
    }

    /**
     * Send a request to the LE API
     *
     * @param $url
     * @param array $payload
     * @param string $method
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function request($url, array $payload = [], string $method = 'POST'): ResponseInterface
    {
        $response = $this->getHttpClient()->request($method, $url, [
            'json'    => $payload,
            'headers' => [
                'Content-Type' => 'application/jose+json',
            ],
        ]);
        $this->nonce = $response->getHeaderLine('replay-nonce');

        return $response;
    }

    /**
     * Get the LE directory path
     *
     * @param $directory
     *
     * @return mixed
     * @throws Exception
     */
    protected function getUrl($directory): string
    {
        if (isset($this->directories[$directory])) {
            return $this->directories[$directory];
        }

        throw new Exception('Invalid directory: ' . $directory . ' not listed');
    }


    /**
     * @throws FilesystemException
     * @throws Exception
     */
    protected function getAccountKey(): bool|OpenSSLAsymmetricKey
    {
        if ($this->accountKey === null) {
            $this->accountKey = openssl_pkey_get_private($this->getFilesystem()
                ->read($this->getPath('account.pem')));
        }

        if ($this->accountKey === false) {
            throw new Exception('Invalid account key');
        }

        return $this->accountKey;
    }

    /**
     * Get the header
     *
     * @return array
     * @throws Exception
     * @throws FilesystemException
     */
    protected function getJWKHeader(): array
    {
        return [
            'e'   => Helper::toSafeString(Helper::getKeyDetails($this->getAccountKey())['rsa']['e']),
            'kty' => 'RSA',
            'n'   => Helper::toSafeString(Helper::getKeyDetails($this->getAccountKey())['rsa']['n']),
        ];
    }

    /**
     * Get JWK envelope
     *
     * @param $url
     * @return array
     * @throws Exception
     * @throws FilesystemException
     * @throws GuzzleException
     */
    protected function getJWK($url): array
    {
        //Require a nonce to be available
        if ($this->nonce === null) {
            $response = $this->getHttpClient()->head($this->directories[self::DIRECTORY_NEW_NONCE]);
            $this->nonce = $response->getHeaderLine('replay-nonce');
        }
        return [
            'alg'   => 'RS256',
            'jwk'   => $this->getJWKHeader(),
            'nonce' => $this->nonce,
            'url'   => $url,
        ];
    }

    /**
     * Get KID envelope
     *
     * @param $url
     * @return array
     * @throws GuzzleException
     */
    protected function getKID($url): array
    {
        $response = $this->getHttpClient()->head($this->directories[self::DIRECTORY_NEW_NONCE]);
        $nonce = $response->getHeaderLine('replay-nonce');

        return [
            "alg"   => "RS256",
            "kid"   => $this->account->getAccountURL(),
            "nonce" => $nonce,
            "url"   => $url,
        ];
    }

    /**
     * Transform the payload to the JWS format
     *
     * @param $payload
     * @param $url
     * @return array
     * @throws Exception
     */
    protected function signPayloadJWK($payload, $url): array
    {
        $payload = is_array($payload) ? str_replace('\\/', '/', json_encode($payload)) : '';
        $payload = Helper::toSafeString($payload);
        $protected = Helper::toSafeString(json_encode($this->getJWK($url)));

        $result = openssl_sign($protected . '.' . $payload, $signature, $this->getAccountKey(), "SHA256");

        if ($result === false) {
            throw new Exception('Could not sign');
        }

        return [
            'protected' => $protected,
            'payload'   => $payload,
            'signature' => Helper::toSafeString($signature),
        ];
    }

    /**
     * Transform the payload to the KID format
     *
     * @param $payload
     * @param $url
     * @return array
     * @throws Exception
     * @throws FilesystemException
     * @throws GuzzleException
     */
    protected function signPayloadKid($payload, $url): array
    {
        $payload = is_array($payload) ? str_replace('\\/', '/', json_encode($payload)) : '';
        $payload = Helper::toSafeString($payload);
        $protected = Helper::toSafeString(json_encode($this->getKID($url)));

        $result = openssl_sign($protected . '.' . $payload, $signature, $this->getAccountKey(), "SHA256");
        if ($result === false) {
            throw new Exception('Could not sign');
        }

        return [
            'protected' => $protected,
            'payload'   => $payload,
            'signature' => Helper::toSafeString($signature),
        ];
    }
}
