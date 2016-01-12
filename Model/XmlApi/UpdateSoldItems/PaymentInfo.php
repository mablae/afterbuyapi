<?php

namespace Wk\AfterbuyApi\Model\XmlApi\UpdateSoldItems;

use JMS\Serializer\Annotation as Serializer;
use \DateTime;
use Wk\AfterbuyApi\Model\XmlApi\AbstractPaymentInfo;

/**
 * Class PaymentInfo
 */
class PaymentInfo extends AbstractPaymentInfo
{
    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("PaymentAadditionalCost")
     * @var float
     */
    private $paymentAdditionalCost;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Accessor(getter="getSendPaymentMailAsInteger", setter="setSendPaymentMailFromInteger")
     * @var bool
     */
    private $sendPaymentMail;

    /**
     * @return int
     */
    public function getSendPaymentMailAsInteger()
    {
        return $this->getBooleanAsInteger($this->sendPaymentMail);
    }

    /**
     * @param int $value
     */
    public function setSendPaymentMailFromInteger($value)
    {
        $this->sendPaymentMail = $this->setBooleanFromInteger($value);
    }

    /**
     * @param string $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @param DateTime $paymentDate
     *
     * @return $this
     */
    public function setPaymentDate(DateTime $paymentDate = null)
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    /**
     * @param float $alreadyPaid
     *
     * @return $this
     */
    public function setAlreadyPaid($alreadyPaid)
    {
        $this->alreadyPaid = $alreadyPaid;

        return $this;
    }

    /**
     * @return float
     */
    public function getPaymentAdditionalCost()
    {
        return $this->paymentAdditionalCost;
    }

    /**
     * @param float $paymentAdditionalCost
     *
     * @return $this
     */
    public function setPaymentAdditionalCost($paymentAdditionalCost)
    {
        $this->paymentAdditionalCost = $paymentAdditionalCost;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSendPaymentMail()
    {
        return $this->sendPaymentMail;
    }

    /**
     * @param bool $sendPaymentMail
     *
     * @return $this
     */
    public function setSendPaymentMail($sendPaymentMail)
    {
        $this->sendPaymentMail = $sendPaymentMail;

        return $this;
    }
}