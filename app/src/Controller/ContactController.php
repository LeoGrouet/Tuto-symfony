<?php

namespace App\Controller;

use App\Controller\DTO\ContactDTO;
use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $data = new ContactDTO();

        $data->name = 'Léo Grouet';
        $data->email = 'leo.grouet@gmail.com';
        $data->message = 'super site';
        $form = $this->createForm(ContactType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mail = (new TemplatedEmail())
                ->to('contact@demo.fr')
                ->from($data->email)
                ->htmlTemplate('mail/mail.html.twig')
                ->subject('Demande de contact')
                ->context(['data' => $data]);
            $mailer->send($mail);
            $this->addFlash('succes', 'Mail bien envoyé');
            $this->redirectToRoute('contact');
        };
        return $this->render('contact/contact.html.twig', [
            'form' => $form
        ]);
    }
}
