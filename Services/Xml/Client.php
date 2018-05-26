<?php

namespace Wk\AfterbuyApiBundle\Services\Xml;

use Doctrine\Common\Annotations\AnnotationRegistry;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use JMS\Serializer\Handler\ArrayCollectionHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\PhpCollectionHandler;
use JMS\Serializer\Handler\PropelCollectionHandler;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Wk\AfterbuyApiBundle\Model\XmlApi\AbstractRequest;
use Wk\AfterbuyApiBundle\Model\XmlApi\AbstractResponse;
use Wk\AfterbuyApiBundle\Model\XmlApi\AfterbuyGlobal;
use Wk\AfterbuyApiBundle\Model\XmlApi\GetShopProducts\GetShopProductsRequest;
use Wk\AfterbuyApiBundle\Model\XmlApi\GetShopProducts\GetShopProductsResponse;
use Wk\AfterbuyApiBundle\Model\XmlApi\GetSoldItems\Filter\AbstractFilter;
use Wk\AfterbuyApiBundle\Model\XmlApi\GetSoldItems\GetSoldItemsRequest;
use Wk\AfterbuyApiBundle\Model\XmlApi\GetSoldItems\GetSoldItemsResponse;
use Wk\AfterbuyApiBundle\Model\XmlApi\UpdateSoldItems\Order;
use Wk\AfterbuyApiBundle\Model\XmlApi\UpdateSoldItems\UpdateSoldItemsRequest;
use Wk\AfterbuyApiBundle\Model\XmlApi\UpdateSoldItems\UpdateSoldItemsResponse;
use Wk\AfterbuyApiBundle\Serializer\AfterbuyXmlDeserializationVisitor;
use Wk\AfterbuyApiBundle\Serializer\AfterbuyXmlSerializationVisitor;
use Wk\AfterbuyApiBundle\Serializer\DateHandler;

/**
 * Class Client
 */
class Client implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var AfterbuyGlobal
     */
    private $afterbuyGlobal;

    /**
     * @param string $userId
     * @param string $userPassword
     * @param int    $partnerId
     * @param string $partnerPassword
     * @param string $errorLanguage
     */
    public function __construct($userId, $userPassword, $partnerId, $partnerPassword, $errorLanguage)
    {
        AnnotationRegistry::registerLoader('class_exists');

        $baseUri = 'https://api.afterbuy.de/afterbuy/ABInterface.aspx';
        $this->afterbuyGlobal = new AfterbuyGlobal($userId, $userPassword, $partnerId, $partnerPassword, $errorLanguage);
        if (version_compare(\GuzzleHttp\Client::VERSION, '6.0.0', '<')) {
            $clientOptions = ['base_url' => $baseUri];
        } else {
            $clientOptions = ['base_uri' => $baseUri];
        }
        $this->client = new \GuzzleHttp\Client($clientOptions);

        $this->serializer = Client::getDefaultSerializer();
    }

    /**
     * @return Serializer
     */
    public static function getDefaultSerializer()
    {

        $deserializationVisitor = new AfterbuyXmlDeserializationVisitor();
        $deserializationVisitor->setDoctypeWhitelist(['<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">']);

        return SerializerBuilder::create()
            ->setSerializationVisitor('xml', new AfterbuyXmlSerializationVisitor())
            ->setDeserializationVisitor('xml', $deserializationVisitor )
            ->configureHandlers(self::getHandlerConfiguration())
            ->build();
    }

    /**
     * @return \Closure
     */
    public static function getHandlerConfiguration()
    {
        return function (HandlerRegistry $registry) {
            $registry->registerSubscribingHandler(new DateHandler());
            $registry->registerSubscribingHandler(new PhpCollectionHandler());
            $registry->registerSubscribingHandler(new ArrayCollectionHandler());
            $registry->registerSubscribingHandler(new PropelCollectionHandler());
        };
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param AbstractFilter[] $filters
     * @param bool             $orderDirection
     * @param int              $maxSoldItems
     * @param int              $detailLevel
     *
     * @return GetSoldItemsResponse|null
     */
    public function getSoldItems(array $filters = [], $orderDirection = false, $maxSoldItems = 250, $detailLevel = AfterbuyGlobal::DETAIL_LEVEL_PROCESS_DATA)
    {
        $request = (new GetSoldItemsRequest($this->afterbuyGlobal))
            ->setFilters($filters)
            ->setDetailLevel($detailLevel)
            ->setMaxSoldItems($maxSoldItems)
            ->setOrderDirection(intval($orderDirection));

        return $this->serializeAndSubmitRequest($request, GetSoldItemsResponse::class);
    }

    /**
     * @param AbstractFilter[] $filters
     * @param bool             $orderDirection
     * @param int              $maxSoldItems
     * @param int              $detailLevel
     *
     * @return GetShopProductsResponse|null
     */
    public function getShopProducts(array $filters = [], $orderDirection = false, $maxSoldItems = 250, $detailLevel = AfterbuyGlobal::DETAIL_LEVEL_PROCESS_DATA)
    {
        $request = (new GetShopProductsRequest($this->afterbuyGlobal))
            ->setFilters($filters)
            ->setDetailLevel($detailLevel)
            ->setMaxSoldItems($maxSoldItems);

        return $this->serializeAndSubmitRequest($request, GetShopProductsResponse::class);
    }

    /**
     * @param Order[] $orders
     * @param int     $detailLevel
     *
     * @return UpdateSoldItemsResponse|null
     */
    public function updateSoldItems(array $orders, $detailLevel = AfterbuyGlobal::DETAIL_LEVEL_PROCESS_DATA)
    {
        $request = (new UpdateSoldItemsRequest($this->afterbuyGlobal))
            ->setDetailLevel($detailLevel)
            ->setOrders($orders);

        return $this->serializeAndSubmitRequest($request, UpdateSoldItemsResponse::class);
    }

    /**
     * Logs to a logger, when given
     *
     * @param string|LogLevel  $level
     * @param string $message
     * @param array  $context
     */
    private function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * @param AbstractRequest $request
     * @param string          $type
     *
     * @return AbstractResponse|null
     */
    private function serializeAndSubmitRequest(AbstractRequest $request, $type)
    {
        $xml = $this->serializer->serialize($request, 'xml');
        $options = ['body' => $xml, 'headers' => ['Content-Type' => 'text/xml']];
        $this->log(LogLevel::DEBUG, 'Posted to Afterbuy with the following options: ', $options);

        try {
            $response = $this->client->post(null, $options);
            $this->log(LogLevel::DEBUG, sprintf('Afterbuy response: %s', $response->getBody()));
        } catch (BadResponseException $exception) {
            $this->log(LogLevel::ERROR, $exception->getMessage());

            return null;
        }

        if ($response->getStatusCode() != 200) {
            $this->log(LogLevel::ERROR, sprintf('Afterbuy responded with HTTP status code %d', $response->getStatusCode()));

            return null;
        }

        try {
            $object = $this->serializer->deserialize($response->getBody(), $type, 'xml');
        } catch (\Exception $exception) {
            $this->log(LogLevel::ERROR, $exception->getMessage());

            return null;
        }

        return $object;
    }
}
