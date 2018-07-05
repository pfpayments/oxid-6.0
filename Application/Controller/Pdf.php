<?php
/**
 * PostFinanceCheckout OXID
 *
 * This OXID module enables to process payments with PostFinanceCheckout (https://www.postfinance.ch/).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */

namespace Pfc\PostFinanceCheckout\Application\Controller;


use PostFinanceCheckout\Sdk\Model\RenderedDocument;
use PostFinanceCheckout\Sdk\Service\TransactionService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class Webhook.
 */
class Pdf extends \OxidEsales\Eshop\Core\Controller\BaseController
{
    /**
     * @var \Pfc\PostFinanceCheckout\Extend\Application\Model\Order
     */
    private $order;
    /**
     * @var TransactionService
     */
    private $service;

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        $orderId = PostFinanceCheckoutModule::instance()->getRequestParameter('oxid');
        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        /* @var $order \Pfc\PostFinanceCheckout\Extend\Application\Model\Order */
        if (!$orderId || !$order->load($orderId)) {
            throw new \Exception("No order id supplied, or order could not be loaded: '$orderId'.");
        }
        if (!$order->isPfcOrder()) {
            throw new \Exception("Given order is not a PostFinance Checkout order: '$orderId'.");
        }
        $this->order = $order;
        $this->service = new TransactionService(PostFinanceCheckoutModule::instance()->getApiClient());
    }

    /**
     * @throws \Exception
     */
    private function verifyUser()
    {
        if ($this->getUser()->getId() !== $this->order->getOrderUser()->getId() && !$this->isAdmin()) {
            throw new \Exception("Attempting to download document from other user.");
        }
    }

    /**
     * @throws \Exception
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    public function packingSlip()
    {
        if (!PostFinanceCheckoutModule::settings()->isDownloadPackingEnabled()) {
            throw new \Exception("Packing slip download is not enabled.");
        }
        $this->verifyUser();

        $document = $this->service->getPackingSlip($this->order->getPostFinanceCheckoutTransaction()->getSpaceId(), $this->order->getPostFinanceCheckoutTransaction()->getTransactionId());

        $this->renderDocument($document);
    }

    /**
     * @throws \Exception
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    public function invoice()
    {
        if (!PostFinanceCheckoutModule::settings()->isDownloadInvoiceEnabled()) {
            throw new \Exception("Invoice download is not enabled.");
        }
        $this->verifyUser();

        $document = $this->service->getInvoiceDocument($this->order->getPostFinanceCheckoutTransaction()->getSpaceId(), $this->order->getPostFinanceCheckoutTransaction()->getTransactionId());

        $this->renderDocument($document);
    }

    /**
     * Outputs the given document.
     *
     * @param RenderedDocument $document
     */
    private function renderDocument(RenderedDocument $document)
    {
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $document->getTitle() . '.pdf"');
        header('Content-Description: ' . $document->getTitle());
        echo base64_decode($document->getData());
        exit();
    }
}