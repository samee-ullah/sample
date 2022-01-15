<?php
declare(strict_types=1);

namespace AjShopFinder\Storefront\Controller;

use AjShopFinder\Service\EmailService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class MailController extends StorefrontController
{
    private $genericPageLoader;
    private $emailService;

    public function __construct(GenericPageLoader $genericPageLoader, EmailService $emailService)
    {
        $this->genericPageLoader = $genericPageLoader;
        $this->emailService = $emailService;
    }

    /**
     * @RouteScope(scopes={"storefront"})
     * @Route("/send-mail", name="frontend.mail.send", methods={"GET"})
     */
    public function sendRegistrationMail(Request $request, SalesChannelContext $context): Response
    {
        $result = $this->notifyCustomer($context);
        if ($result) {
            $message = 'Mail has been sent!';
            $status = true;
        } else {
            $message = 'Failed';
            $status = false;
        }
        $page = $this->genericPageLoader->load($request, $context);
        return $this->renderStorefront('@AjShopFinder/storefront/page/mail/index.html.twig', [
            'page' => $page,
            'message' => $message,
            'status' => $status,
            'template' =>  $this->getTemplate()
        ]);
    }

    public function notifyCustomer(SalesChannelContext $salesChannelContext): bool
    {
        $email = 'sameeullah03@gmail.com';
        $message = $this->getTemplate();
        return $this->emailService->sendMail([$email => 'Dear Friend'], 'Picaldi', 'Voucher Activated', $message);
    }

    public function getTemplate(): string
    {
        return $this->renderView('@AjShopFinder/storefront/page/mail/templates/voucher_activation.index.twig', [
            'page' => [],
            'data' => [
                'salutation' => 'Mr',
                'firstName' => 'Kai',
                'lastName' => 'Muller',
                'vouchers' => [
                    [
                        'title' => '25€ voucher',
                        'code' => 'x67jhkj4',
                        'value' => 25,
                        'currencyFactor' => 1.73,
                        'currency.decimalPrecision' => 2,
                        'currency.symbol' => '€'
                    ]
                ]
            ]
        ]);
    }
}
