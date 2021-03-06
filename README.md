# WkAfterbuyApiBundle

[![Build Status](https://travis-ci.org/asgoodasnu/afterbuyapi.png?branch=master)](https://travis-ci.org/asgoodasnu/afterbuyapi) [![Total Downloads](https://poser.pugx.org/asgoodasnu/afterbuyapi/d/total.png)](https://packagist.org/packages/asgoodasnu/afterbuyapi) [![Latest Stable Version](https://poser.pugx.org/asgoodasnu/afterbuyapi/v/stable.png)](https://packagist.org/packages/asgoodasnu/afterbuyapi) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/9a1fccba-a214-46bf-bd44-c6288a049a91/mini.png)](https://insight.sensiolabs.com/projects/9a1fccba-a214-46bf-bd44-c6288a049a91)

The WkAfterbuyApiBundle provides a Symfony2 service to interact with the [Afterbuy XML API](http://xmldoku.afterbuy.de/dokued/) using Guzzle.

Installation
----------------------------------------------------------------

Require the bundle and its dependencies with composer:

    $ composer require asgoodasnu/afterbuyapi
    
Register the bundle:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        new Wk\AfterbuyApiBundle\WkAfterbuyApiBundle(),
    );
}
```

Overwrite the parameters defined in `Wk\AfterbuyApiBundle\App\parameters.yml` with your own Afterbuy credentials in your project's `parameters.yml`:

```yaml
# parameters.yml
locale: en
afterbuy_partner_id: 123
afterbuy_partner_password: pass
afterbuy_user_id: user
afterbuy_user_password: pass
```
 
Usage
----------------------------------------------------------------
Interaction with the Afterbuy XML API is done via the service `wk_afterbuy_api.xml.client`.

#### Retrieving a list of sold items from Afterbuy:

```php
$client = $container->get('wk_afterbuy_api.xml.client');
$soldItems = $client->getSoldItems($filters, $orderDirection, $maxSoldItems, $detailLevel);
```

The response will be an instance of `Wk\AfterbuyApiBundle\Model\XmlApi\GetSoldItems\GetSoldItemsResponse` and provides methods to traverse the XML sent back from Afterbuy such as fetching the orders:

```php
$orders = $soldItems->getResult()->getOrders();
```

Provide an array of filters defined in Afterbuy, for example a DateFilter or a DefaultFilter. The models for these filters can be found in `Wk\AfterbuyApiBundle\Model\XmlApi\GetSoldItems\Filter`.

```php
$dateFilter = (new DateFilter(DateFilter::FILTER_AUCTION_END_DATE))
                ->setDateFrom(new DateTime('2000-01-01 00:00:00'))
                ->setDateTo(new DateTime('2000-01-10 00:00:00'));
            
$defaultFilter = new DefaultFilter(DefaultFilter::FILTER_COMPLETED_AUCTIONS);
```

#### Updating sold items on Afterbuy:

```php
$order = new \Wk\AfterbuyApiBundle\Model\XmlApi\UpdateSoldItems\Order();
$order->setOrderId(1234567890)
      ->setUserDefinedFlag(12345)
      ->setInvoiceMemo("You didn't read the memo? You are fired!");
$client = $container->get('wk_afterbuy_api.xml.client');
$client->updateSoldItems(array($orders));
```

The response will be an instance of `Wk\AfterbuyApiBundle\Model\XmlApi\UpdateSoldItems\UpdateSoldItemsResponse`.

Dependencies
----------------------------------------------------------------
* `jms/serializer` - Allows you to easily serialize, and deserialize data of any complexity
* `guzzlehttp/guzzle` - Guzzle is a PHP HTTP client library
* `symfony/yaml` - Symfony Yaml Component
* `symfony/monolog` - Symfony MonologBundle
* `symfony/framework-bundle` - Symfony FrameworkBundle

PHPUnit Tests
----------------------------------------------------------------
You can run the tests using the following command:

    $ vendor/bin/phpunit

Resources
----------------------------------------------------------------
Symfony 2
> [http://symfony.com](http://symfony.com)

Afterbuy XML Interface Documentation:
> [http://xmldoku.afterbuy.de/dokued/](http://xmldoku.afterbuy.de/dokued/)
