<?php

namespace Rottenwood\KingdomBundle\Controller;

use Rottenwood\KingdomBundle\Entity\User;
use Rottenwood\KingdomBundle\Redis\RedisClientInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {

    /**
     * //TODO[Rottenwood]: Сделать главную страницу
     * @Route("/", name="index")
     * @return Response
     */
    public function indexAction() {
        if ($this->getUser()) {
            return $this->redirectToRoute('game_page');
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Основная страница игры
     * @Security("has_role('ROLE_USER')")
     * @Route("/game", name="game_page")
     * @param Request $request
     * @return Response
     */
    public function gamePageAction(Request $request) {
        $sessionId = $request->getSession()->getId();
        /** @var User $user */
        $user = $this->getUser();
        $userId =  $user->getId();

        /** @var RedisClientInterface $redis */
        $redis = $this->container->get('snc_redis.default');

        $redis->hset(RedisClientInterface::ID_USERNAME_HASH, $userId, $user->getName());
        $redis->hset(RedisClientInterface::ID_SESSION_HASH, $userId, $sessionId);
        $redis->hset(RedisClientInterface::SESSION_ID_HASH, $sessionId, $userId);

        return $this->render(
            'RottenwoodKingdomBundle:Default:game.html.twig',
            [
                'sessionId'    => $sessionId,
                'websocketUrl' => $this->getParameter('websocket_router_url'),
            ]
        );
    }
}
