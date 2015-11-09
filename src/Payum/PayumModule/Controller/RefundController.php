<?php
namespace Payum\PayumModule\Controller;

use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Refund;
use Zend\Http\Response;

class RefundController extends PayumController
{
    public function doAction()
    {
        $token = $this->getHttpRequestVerifier()->verify($this);
        $this->getHttpRequestVerifier()->invalidate($token);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        try {
            $gateway->execute(new Refund($token));
        } catch (ReplyInterface $reply) {
            if ($reply instanceof HttpRedirect) {
                return $this->redirect()->toUrl($reply->getUrl());
            }

            if ($reply instanceof HttpResponse) {
                $this->getResponse()->setContent($reply->getContent());

                $response = new Response();
                $response->setStatusCode(200);
                $response->setContent($reply->getContent());

                return $response;
            }

            throw new \LogicException('Unsupported reply', null, $reply);
        }

        return $this->redirect()->toUrl($token->getAfterUrl());
    }
}
