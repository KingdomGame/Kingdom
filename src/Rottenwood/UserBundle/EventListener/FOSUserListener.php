<?php

namespace Rottenwood\UserBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Rottenwood\KingdomBundle\Entity\Infrastructure\MoneyRepository;
use Rottenwood\KingdomBundle\Entity\Infrastructure\RoomRepository;
use Rottenwood\KingdomBundle\Entity\Money;
use Rottenwood\KingdomBundle\Entity\Room;
use Rottenwood\KingdomBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Слушатель событий FOSUserBundle
 * @package Rottenwood\BarchartBundle\Listener
 */
class FOSUserListener implements EventSubscriberInterface {

    /** @var UrlGeneratorInterface */
    private $router;
    /** @var RoomRepository */
    private $roomRepository;
    /** @var MoneyRepository */
    private $moneyRepository;
    /** @var string */
    private $avatarPath;

    /**
     * @param UrlGeneratorInterface $router
     * @param Kernel                $kernel
     * @param RoomRepository        $roomRepository
     * @param MoneyRepository       $moneyRepository
     */
    public function __construct(
        UrlGeneratorInterface $router,
        Kernel $kernel,
        RoomRepository $roomRepository,
        MoneyRepository $moneyRepository) {
        $this->router = $router;
        $this->roomRepository = $roomRepository;
        $this->avatarPath = $kernel->getRootDir() . '/../web/img/avatars/';
        $this->moneyRepository = $moneyRepository;
    }

    public static function getSubscribedEvents() {
        return [
            FOSUserEvents::REGISTRATION_SUCCESS   => 'onRegistrationSuccess',
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
        ];
    }

    public function onRegistrationCompleted(FilterUserResponseEvent $event) {
        $user = $event->getUser();
        $money = new Money($user);
        $this->moneyRepository->persist($money);
        $this->moneyRepository->flush($money);
    }

    public function onRegistrationSuccess(FormEvent $event) {
        /** @var User $user */
        $user = $event->getForm()->getData();
        /** @var Room $room */
        $room = $this->roomRepository->find(1);

        // Приведение имени к нужному виду
        $newName = explode(' ', $user->getUsernameCanonical())[0];
        $newName = $this->transliterate($newName);
        $newName = preg_replace('/[[:print:]]/', '', $newName);
        $newName = preg_replace('/№/', '', $newName);
        $newName = mb_convert_case($newName, MB_CASE_TITLE, 'UTF-8');

        if (!$newName) {
            $newName = $this->generateName();
        }

        $user->setAvatar($this->pickAvatar());

        $user->setName($newName);
        $user->setRoom($room);

        $url = $this->router->generate('game_page');
        $event->setResponse(new RedirectResponse($url));
    }

    /**
     * Транслитерация строк
     * @param string $string
     * @return string
     */
    private function transliterate($string) {
        return strtr($string, $this->getAlphabet());
    }

    /**
     * Массив соответствия русских букв латинским
     * @return string[]
     */
    private function getAlphabet() {
        return [
            'a' => 'а',
            'b' => 'б',
            'c' => 'ц',
            'd' => 'д',
            'e' => 'е',
            'f' => 'ф',
            'g' => 'г',
            'h' => 'х',
            'i' => 'ай',
            'j' => 'дж',
            'k' => 'к',
            'l' => 'л',
            'm' => 'м',
            'n' => 'н',
            'o' => 'о',
            'p' => 'п',
            'q' => 'к',
            'r' => 'р',
            's' => 'с',
            't' => 'т',
            'u' => 'ю',
            'v' => 'в',
            'w' => 'в',
            'x' => 'икс',
            'y' => 'й',
            'z' => 'з',
            'A' => 'А',
            'B' => 'Б',
            'C' => 'Ц',
            'D' => 'Д',
            'E' => 'Е',
            'F' => 'Ф',
            'G' => 'Г',
            'H' => 'Х',
            'I' => 'Ай',
            'J' => 'Дж',
            'K' => 'К',
            'L' => 'Л',
            'M' => 'М',
            'N' => 'Н',
            'O' => 'О',
            'P' => 'П',
            'Q' => 'К',
            'R' => 'Р',
            'S' => 'С',
            'T' => 'Т',
            'U' => 'Ю',
            'V' => 'В',
            'W' => 'В',
            'X' => 'Икс',
            'Y' => 'Й',
            'Z' => 'З',
        ];
    }

    /**
     * Генератор бессмысленных имен
     * @param int $lettersCount Количество символов в имени
     * @return string
     */
    private function generateName($lettersCount = 6) {
        $alphabet = $this->getAlphabet();

        $name = '';
        for ($i = 1; $i <= $lettersCount; $i++) {
            $randomLetterKey = array_rand($alphabet);
            $name .= $alphabet[$randomLetterKey];
            unset($alphabet[$randomLetterKey]);
        }

        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Установка рэндомного аватара
     * @return string
     */
    private function pickAvatar() {
        $finder = new Finder();

        $prefix = 'male';
        $avatarPath = $this->avatarPath . $prefix;

        $files = $finder->files()->in($avatarPath);

        $avatars = [];
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $avatars[] = $file->getBasename('.jpg');
        }

        $avatar = $prefix . '/' . $avatars[array_rand($avatars)];

        return $avatar;
    }
}
