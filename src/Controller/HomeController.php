<?php
/**
 * Created by PhpStorm.
 * User: m.ogeret
 * Date: 09/04/2018
 * Time: 13:07
 */

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Service\LinkManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    /**
     * @return Response
     */
    public function home()
    {
        $linkForm = $this->createForm(LinkType::class)->createView();
        return $this->render('home/homepage.html.twig', ['linkForm'=>$linkForm]);
    }

    public function handleHomeForm(Request $request, LinkManager $lm)
    {
        $em = $this->getDoctrine()->getManager();

        $ip = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent');

        //if spam and if non authenticated then too many links error
        if ($lm->spamProtection($ip, $userAgent, $em) && !$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse($this->render("home/link.html.twig",
                [
                    'status' => 'toomany',
                ]
            )->getContent());
        }

        $link = new Link($request);
        $linkForm = $this->createForm(LinkType::class, $link)->handleRequest($request);

        if ($linkForm->isSubmitted() && $linkForm->isValid()) {
            /** @var Link $link */
            $link = $linkForm->getData();
            $link->setUuid($lm->getUuid())
                 ->setUser($this->getUser());

             $em->persist($link);
             $em->flush();

             return new JsonResponse($this->render("home/link.html.twig",
                 [
                     'status' => 'ok',
                     'uuid' => $link->getUuid(),
                 ]
             )->getContent());

        }

        return new JsonResponse($this->render('home/homeForm.html.twig',
            [
                'linkForm'=>$linkForm->createView(),
            ]
        )->getContent());

    }

    public function conditionsOfUse() {
        return new JsonResponse($this->render('full page/cou.html.twig',
            [
                'version'=> exec('git describe --tags --abbrev=0')
            ]
        )->getContent());
    }

}
