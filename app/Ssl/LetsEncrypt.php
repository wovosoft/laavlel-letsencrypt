<?php

namespace App\Ssl;

use Afosto\Acme\Data\Authorization;
use Afosto\Acme\Data\Certificate;
use Afosto\Acme\Data\Order;
use File;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;
use League\Flysystem\Filesystem;
use Afosto\Acme\Client;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Mockery\Exception;

/**
 * @link https://github.com/afosto/yaac/blob/master/README.md
 */
class LetsEncrypt
{
    private Client $client;
    private string $path;

    public function __construct(
        private readonly string      $username,
        private readonly ClientModes $mode = ClientModes::Staging,
        private readonly ?string     $basepath = "le",
        ?string                      $path = null,

    )
    {
        $this->path = $path ?: storage_path('app/ssl');
        File::ensureDirectoryExists($this->path);

        $adapter = new LocalFilesystemAdapter($this->path);
        $filesystem = new Filesystem($adapter);

        $this->client = new Client([
            'username' => $this->username,
            'fs' => $filesystem,
            'mode' => $this->mode->value,
            'basepath' => $this->basepath
        ]);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param array<string>|string $domains
     * @throws \Exception
     */
    public function createOrder(array|string $domains): Order
    {
        return $this->client->createOrder(
            is_string($domains) ? explode(",", $domains) : $domains
        );
    }

    public function getOrderId(Order $order): string
    {
        return $order->getId();
    }

    /**
     * @throws \Exception
     */
    public function getOrder(string|int $id): Order
    {
        return $this->client->getOrder($id);
    }

    /**
     * @param Order $order
     * @return array<Authorization>
     * @throws \Exception
     */
    public function authorize(Order $order): array
    {
        return $this->client->authorize($order);
    }

    /**
     * @param array<Authorization> $authorizations
     */
    public function storeHttpValidations(array $authorizations): void
    {
        foreach ($authorizations as $authorization) {
            $file = $authorization->getFile();
            file_put_contents($file->getFilename(), $file->getContents());
        }
    }

    /**
     * @param array $authorizations
     * @return void
     */
    public function storeDnsValidations(array $authorizations): void
    {
        foreach ($authorizations as $authorization) {
            $txtRecord = $authorization->getTxtRecord();

            //To get the name of the TXT record call:
            $txtRecord->getName();

            //To get the value of the TXT record call:
            $txtRecord->getValue();
        }
    }

    /**
     * @throws \Exception
     */
    public function selfTest(Authorization $authorization, ValidationTypes $type = ValidationTypes::Http): bool
    {
        if (!$this->client->selfTest($authorization, $type->value)) {
            throw new \Exception('Could not verify ownership via ' . $type->value);
        }

        if ($type === ValidationTypes::Dns) {
            sleep(30); // this further sleep is recommended, depending on your DNS provider, see below
        }
        return true;
    }

    /**
     * @param array<Authorization> $authorizations
     * @throws \Exception
     */
    public function requestHttpValidation(array $authorizations): bool
    {
        foreach ($authorizations as $authorization) {
            $this->client->validate($authorization->getHttpChallenge(), 15);
        }
        return true;
    }

    /**
     * @param array<Authorization> $authorizations
     * @throws \Exception
     */
    public function requestDnsValidation(array $authorizations): bool
    {
        foreach ($authorizations as $authorization) {
            $this->client->validate($authorization->getDnsChallenge(), 15);
        }
        return true;
    }

    /**
     * @throws \Exception
     */
    public function getCertificate(Order $order): Certificate
    {
        if ($this->client->isReady($order)) {
            return $this->client->getCertificate($order);
        }
        throw new \Exception("Validation is not succeeded");
    }

    public function getCert(Certificate $certificate): string
    {
        return $certificate->getCertificate();
    }

    public function getPrivateKey(Certificate $certificate): string
    {
        return $certificate->getPrivateKey();
    }

    public function getDomainCertificate(Certificate $certificate): string
    {
        return $certificate->getCertificate(false);
    }

    public function getIntermediateCertificate(Certificate $certificate): string
    {
        return $certificate->getIntermediate();
    }

    public function getCsr(Certificate $certificate): string
    {
        return $certificate->getCsr();
    }

    public function getCertificateExpiryDate(Certificate $certificate): \DateTime
    {
        return $certificate->getExpiryDate();
    }

    public function storeCertificate(Certificate $certificate, string $dir): true
    {
        if (!str($dir)->endsWith(DIRECTORY_SEPARATOR)) {
            $dir .= DIRECTORY_SEPARATOR;
        }

        File::put($dir . 'certificate.cert', $certificate->getCertificate());
        File::put($dir . 'private.key', $certificate->getPrivateKey());

        return true;
    }

    public function getPath(?string $path = null): string
    {
        $userDirectory = preg_replace('/[^a-z0-9]+/', '-', strtolower($this->username));

        return join(DIRECTORY_SEPARATOR, [
            $this->path,
            $this->basepath,
            $userDirectory,
            $path
        ]);
    }

    public function authorizationChallenges(Authorization $authorization): array
    {
        $data = [];
        foreach ($authorization->getChallenges() as $challenge) {
            $data[] = [
                "authorizationURL" => $challenge->getAuthorizationURL(),
                "url" => $challenge->getUrl(),
                "status" => $challenge->getStatus(),
                "type" => $challenge->getType(),
                "token" => $challenge->getToken(),
            ];
        }
        return $data;
    }

    /**
     * @param Order $order
     * @param array<Authorization> $authorizations
     * @return array
     */
    public function transformOrder(Order $order, array $authorizations): array
    {
        $output = [
            "id" => $order->getId(),
            "domains" => $order->getDomains(),
            "authorizations" => []
        ];

        foreach ($authorizations as $authorization) {
            $file = $authorization->getFile();
            $output["authorizations"][] = [
                "id" => $this->getAuthorizationId($authorization),
                "domain" => $authorization->getDomain(),
                "txt_record" => [
                    "name" => $authorization->getTxtRecord()->getName(),
                    "value" => $authorization->getTxtRecord()->getValue()
                ],
                "expires_at" => $authorization->getExpires(),
                "authorization_url" => $authorization?->getChallenges()[0]?->getAuthorizationURL(),
                "file" => [
                    "name" => $file->getFilename(),
                    "contents" => $file->getContents()
                ],
                "challenges" => $this->authorizationChallenges($authorization)
            ];
        }
        return $output;
    }

    protected function getHttpClient(?string $sourceIp = null): HttpClient
    {
        $config = [];

        if ($sourceIp) {
            $config['curl.options']['CURLOPT_INTERFACE'] = $sourceIp;
        }

        return new HttpClient($config);
    }

    /**
     * live: https://acme-v02.api.letsencrypt.org/acme/authz/{authorization_id}
     * staging: https:/acme-staging-v02.api.letsencrypt.org/acme/authz/{authorization_id}
     * @param string|int $authorizationId
     * @return bool
     * @throws GuzzleException
     * @throws \Exception
     */
    public function isOwnershipVerificationNeeded(string|int|Authorization $authorizationId): bool
    {
        if ($authorizationId instanceof Authorization) {
            $authorizationId = $this->getAuthorizationId($authorizationId);
        }

        $url = match ($this->mode) {
            ClientModes::Live => 'https://acme-v02.api.letsencrypt.org/acme/authz-v3' . DIRECTORY_SEPARATOR . $authorizationId,
            ClientModes::Staging => 'https://acme-staging-v02.api.letsencrypt.org/acme/authz-v3' . DIRECTORY_SEPARATOR . $authorizationId . '2',
        };

        $response = Http::get($url);
        if ($response->notFound()) {
            throw new \Exception("Authorization $authorizationId " . $response->reason());
        }
        if ($response->failed() || $response->status() !== 200) {
            throw new Exception($response->reason());
        }

        return $response->object()->status === "pending";
    }

    public function getAuthorizationId(Authorization $authorization): string
    {
        return basename($authorization->getChallenges()[0]->getAuthorizationURL());
    }

    public function renew()
    {

    }
}
