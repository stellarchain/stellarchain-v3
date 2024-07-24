<?php
declare(strict_types=1);

namespace App\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Twig\EasyAdminTwigExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Twig\Environment;

final class RefreshAdminContext implements EventSubscriberInterface
{
    public function __construct(private Environment $twig){

    }
    public static function getSubscribedEvents():array
    {
        return [
            ViewEvent::class => ['onKernelView',1]
        ];
    }

    public function onKernelView(ViewEvent $event):void
    {
        $extensionGlobals = $this->twig->getExtension(EasyAdminTwigExtension::class)->getGlobals();
        $twigGlobals = $this->twig->getGlobals();

        foreach ($extensionGlobals as $key=>$value){
            if(!isset($twigGlobals[$key]) || $twigGlobals[$key]===$value) {
                continue;
            }
            $this->twig->addGlobal($key,$value);
        }
    }

}
