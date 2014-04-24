<?php


namespace Wk\AfterBuyApi\Lib;

use GuzzleHttp\Client;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\Event;
use Wk\GuzzleCommandClient\Lib\GuzzleCommandClient;

/**
 * Class AfterBuyConnection
 * Implements the Singleton pattern
 */
class AfterBuyConnection extends GuzzleCommandClient
{

    const MAX_ATTEMPTS = 10;

    /** @var AfterBuyAdapter to generate the requests and responses out of the AfterBuyConnection */
    protected $adapter;

    protected static $instance = null;

    /** @var  \GuzzleHttp\Client */
    protected $guzzleClient;

    protected $partnerId;

    protected $partnerPassword;

    protected $userId;

    protected $userPassword;

    protected $detailLevel;

    protected $errorLanguage;

    protected $markerId;

    protected $checkVid;

    /** @var array */
    protected $apiUrls;

    /** @var \Monolog\Logger */
    private $logger;

    /**
     * List of error messages that are OK to accept the respond from AfterBuy
     */
    private $errorMessagesOk = array(
        'Diese Bestellung wurde bereits erfasst.',
        'Diese Bestellung wurde bereits erfasst, oder wird gerade bearbeitet.',
    );

    /**
     * Constructor for the class
     */
    public function __construct()
    {
        $this->adapter = new AfterBuyAdapter();
        $json = file_get_contents(__DIR__. "/../Resources/config/service.json");
        parent::__construct($json);
    }

    /**
     * @param Logger $logger
     */
    public function setLogger (Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param AfterBuyAdapter $adapter
     */
    public function setAdapter (AfterBuyAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return AfterBuyAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Setter for the urls
     * @param array $urls
     */
    public function setApiUrls(array $urls)
    {
        $this->apiUrls = $urls;
    }

    /**
     * Method to set the parameters for Afterbuy
     * @param array $params
     */
    public function setParams (array $params)
    {

        $this->partnerId = isset($params['partner_id']) ? $params['partner_id'] : null;
        $this->partnerPassword = isset($params['partner_pass']) ? $params['partner_pass'] : null;
        $this->userId = isset($params['user_id']) ? $params['user_id'] : null;
        $this->userPassword = isset($params['user_pass']) ? $params['user_pass'] : null;
        $this->detailLevel = isset($params['detail_level']) ? $params['detail_level'] : null;
        $this->errorLanguage = isset($params['error_language']) ? $params['error_language'] : null;
        $this->markerId = isset($params['marker_id']) ? $params['marker_id'] : null;
        $this->checkVid = isset($params['check_vid']) ? $params['check_vid'] : null;

    }

    /**
     * Instance of the AfterBuyConnection
     * @return AfterBuyConnection
     */
    public static function getInstance ()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param string $apiName
     *
     * @throws \RuntimeException
     */
    public function setBaseUrl ($apiName)
    {
        if (array_key_exists($apiName, $this->apiUrls)) {
            parent::setBaseUrl($this->apiUrls[$apiName]);
        } else {
            throw new \RuntimeException(sprintf('Api "%s" not defined for AfterBuy', $apiName));
        }

    }

    /**
     * Getter for the client
     * @return Client
     */
    public function getGuzzleClient()
    {
        return $this->client;
    }

    /**
     * Setter for the client
     * @param Client $client
     */
    public function setGuzzleClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Build the xml string for getting the AfterBuy time
     * @return string
     */
    public function getAfterBuyTimeRequest ()
    {
        return "<Request>" . $this->buildAfterbuyGlobalCredentials('GetAfterbuyTime') . "</Request>";
    }

    /**
     * Build the xml string for getting the AfterBuy sold items
     * @param array $params
     *
     * @return string
     */
    public function getAfterBuySoldItems (array $params)
    {

        $request = "<Request>" . $this->buildAfterbuyGlobalCredentials('GetSoldItems');
        $request .= $this->adapter->buildAfterBuySoldItemsXmlString($params);
        $request .= "</Request>";

        return $request;
    }

    /**
     * Build the xml string for updating the AfterBuy sold items
     * @param array $params
     *
     * @return string
     */
    public function updateAfterBuySoldItems (array $params)
    {

        $request = "<Request>" . $this->buildAfterbuyGlobalCredentials('UpdateSoldItems');
        $request .= $this->adapter->buildUpdateAfterBuySoldItemsXmlString($params);
        $request .= "</Request>";

        return $request;
    }

    /**
     * @param Event $event
     *
     * @return bool|void
     * @throws \Exception
     */
    public function onOrderCreation (Event $event)
    {
        $schutzklickVendorName = "R+V";

        // check if we have the Schutzclick flag
        if ($event->hasVendorProducts($schutzklickVendorName)) {
            // send the first notification to afterbuy with the product itself
            $status = $this->sendNotification($event->getBodyWithoutVendorProducts($schutzklickVendorName));

            foreach ($event->getBodyOnlyVendorProducts($schutzklickVendorName) as $body) {
                // send the notification with the insurance
                $status = $status && $this->sendNotification($body);
            }

        } else {
            // send the request to after buy and get the status
            $status = $this->sendNotification($event->getBody());
        }

        if (!$status) {
            throw new \Exception("Error when trying to create the order in AfterBuy. Not all the item could be notified to AfterBuy.");
        }

        return $status;
    }

    /**
     * Send the request to AfterBuy when a new order has been created and the webhook has been triggered
     * @param array $notification
     *
     * @return bool
     */
    public function sendNotification(array $notification)
    {

        if (empty ($notification)) {
            $this->logger->addError("\nEmpty order parameteres from Shopify");

            return false;
        }

        $params = array(
            'Partnerid' => $this->partnerId,
            'PartnerPass' => $this->partnerPassword,
            'Action' => "new",
            'UserID' => $this->userId,
            'MarkierungID' => $this->markerId,
            'CheckVID' => $this->checkVid,
        );

        // merge the configuration parameters with the ones from the order
        $params = array_merge($params, $this->adapter->parseOrder($notification));

        /*
        * $this->setBaseUrl('shop');
        * $output = "?" . implode('&', array_map(function ($v, $k) { return sprintf("%s=%s", $k, $v); }, $params, array_keys($params)));
        * $result = $this->executeCommand('createOrder', array("UriRequest" => $output) );
        * TODO: when working, change the executeCommand method in order to have the same structure for the response
        */

        // builds the GET query
        $this->initGuzzleClient($this->apiUrls['shop']);

        // start the log
        $this->logger->addInfo("\n\n".date(DATE_RFC822)."\nOrder ID: " . $params['VID']);
        //log the json from shopify
        $this->logger->addInfo("\nShopify JSON decoded: \n" . print_r($notification, true));

        $cont = 0;
        do {
            $cont++;

            $response = $this->guzzleClient->get('?' . http_build_query($params));

            $this->logger->addInfo("\n\nAfterBuy Response Data:\n".$response->getBody());
            $this->logger->addInfo("\nAfterBuy Response Status: ".$response->getStatusCode());
            $resp = $this->adapter->getResponse($response->getBody());

            if (($resp['success'] == true) || (in_array($resp['message'], $this->errorMessagesOk))) {
                return true;
            }

            sleep(0.5);
        } while (($response->getStatusCode() !== 200) && ($cont <= self::MAX_ATTEMPTS));

        return false;
    }

    /**
     * Build the xml string for the credentials
     * @param string $callName Name of the method to call in the api
     *
     * @return string
     */
    private function buildAfterbuyGlobalCredentials ($callName)
    {
        return "<AfterbuyGlobal>
                    <PartnerID>$this->partnerId</PartnerID>
                    <PartnerPassword>$this->partnerPassword</PartnerPassword>
                    <UserID>$this->userId</UserID>
                    <UserPassword>$this->userPassword</UserPassword>
                    <CallName>$callName</CallName>
                    <DetailLevel>$this->detailLevel</DetailLevel>
                    <ErrorLanguage>$this->errorLanguage</ErrorLanguage>
                </AfterbuyGlobal>";
    }

    /**
     * @param string $baseUrl
     *
     * @throws \Exception
     */
    private function initGuzzleClient($baseUrl)
    {
        $this->guzzleClient = new Client(array('base_url' => $baseUrl));
    }
}
