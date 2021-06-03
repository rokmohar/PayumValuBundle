<?php

namespace RokMohar\PayumValuBundle;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\ResponseInterface;

class ValuApi
{
    /** @var string */
    public const STATUS_PENDING = 'vobdelavi';

    /** @var string */
    public const STATUS_CONFIRMED = 'potrjeno';

    /** @var string */
    public const STATUS_REJECTED = 'zavrnjeno';

    /** @var string */
    public const STATUS_DISPLAYED = 'prikazano';

    /** @var int */
    public const DEFAULT_QUANTITY = 1;

    /** @var float */
    public const DEFAULT_VAT_RATE = 0;

    /** @var int */
    public const EXPIRED_COUNTER = 60;

    /** @var HttpClientInterface */
    protected $client;

    /** @var MessageFactory */
    protected $messageFactory;

    /** @var mixed[] */
    protected $options = [];

    /**
     * @param mixed[] $options
     * @param HttpClientInterface $client
     * @param MessageFactory $messageFactory
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param string $method
     * @param mixed[] $params
     * @return ResponseInterface
     */
    public function doRequest(string $method, array $params): ResponseInterface
    {
        $headers = [];
        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint(), $headers, http_build_query($params));
        $response = $this->client->send($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        if ($this->options['sandbox']) {
            return 'https://test-placilo.valu.si/placevanje/TarifficationE.dll';
        }

        return 'https://placilo.valu.si/te/TarifficationE.dll';
    }

    /**
     * @return string
     */
    public function getTarifficationId(): string
    {
        return $this->options['tarifficationId'];
    }
}
