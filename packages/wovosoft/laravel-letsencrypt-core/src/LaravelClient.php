<?php

namespace Wovosoft\LaravelLetsencryptCore;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Wovosoft\LaravelLetsencryptCore\Data\Directories;
use Wovosoft\LaravelLetsencryptCore\Ssl\ClientModes;


class LaravelClient
{
    const DIRECTORY_LIVE = 'https://acme-v02.api.letsencrypt.org/directory';
    const DIRECTORY_STAGING = 'https://acme-staging-v02.api.letsencrypt.org/directory';
    private ?Directories $directories = null;
    private string $disk = "local";
    private array|false $privateKeyDetails;

    public function __construct(
        public ClientModes $mode,
        public ?string     $sourceIp = null
    )
    {

    }

    public function getClient(): HttpClient
    {
        $config = [
            'base_uri' => ($this->mode === ClientModes::Live ? self::DIRECTORY_LIVE : self::DIRECTORY_STAGING),
        ];

        if ($this->sourceIp) {
            $config['curl.options']['CURLOPT_INTERFACE'] = $this->sourceIp;
        }
        return new HttpClient($config);
    }

    public function http(): PendingRequest
    {
        return Http::setClient(client: $this->getClient());
    }


    public function getDirectories(): Directories
    {
        if (is_null($this->directories)) {
            $this->directories = new Directories($this->http()->get('directory')->object());
        }
        return $this->directories;
    }


    protected function loadKeys(string $account): void
    {
        $accountDirectory = preg_replace('/[^a-z0-9]+/', '-', strtolower($account));
        if (!str_ends_with($accountDirectory, DIRECTORY_SEPARATOR)) {
            $accountDirectory .= DIRECTORY_SEPARATOR;
        }

        //Make sure a private key is in place
        if (!Storage::disk($this->disk)->exists($accountDirectory . 'account.pem')) {
            Storage::disk($this->disk)->put(
                path: $accountDirectory . 'account.pem',
                contents: Helper::getNewKey(4096)
            );
        }

        $privateKey = openssl_pkey_get_private(
            Storage::disk($this->disk)->get($accountDirectory . 'account.pem')
        );
        $this->privateKeyDetails = openssl_pkey_get_details($privateKey);
    }

    public function registerAccount()
    {

    }

    public function getAccount()
    {

    }

    public function deactivateAccount()
    {

    }

    public function accountOrders()
    {

    }

    public function orderCertificates()
    {

    }

    public function createOrder()
    {

    }

    public function authorizeOrder()
    {

    }

    public function isExpiredCertificate()
    {

    }
}
