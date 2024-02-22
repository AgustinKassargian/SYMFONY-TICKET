<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Ticket;
use App\Api\TicketDTO;

class TicketController extends AbstractController
{
    private EntityManagerInterface $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

    }
    private function createDTO(Ticket $data)
    {
        $newTicketDTO = new TicketDTO();
        $newTicketDTO->id = $data->getId();
        $newTicketDTO->name = $data->getName();
        $newTicketDTO->description = $data->getDescription();
        $newTicketDTO->lvl = $data->getLvl();
        $newTicketDTO-> image= $data->getImage();
        $newTicketDTO->created_at = $data->getCreatedAt();
        $newTicketDTO->updated_at = $data->getUpdatedAt();
        $newTicketDTO->status = $data->getStatus();

        return $newTicketDTO;
    }

    private function setImageByLevel($lvl) {
        switch ($lvl) {
            case $lvl >= 3:
                return 'url_facil.jpg';
            default:
                return 'url_predeterminada.jpg';
        }
    }    

    #[Route('api/ticket/create', name: 'app_ticket', methods: ['POST'])]
    public function createTicket(Request $request): JsonResponse
    {
        $actualDate = new \DateTime();
        $data= json_decode($request->getContent(), true);
        $newTicket = new Ticket();
        try {
            $urlImage =  $this->setImageByLevel($data['lvl']);
        } catch (\Throwable $th) {
            dd('Murio aca');
        }
        $newTicket->setName($data['name']);
        $newTicket->setDescription($data['description']);
        $newTicket->setLvl($data['lvl']);
        $newTicket->setStatus('incomplete');
        
        $newTicket->setCreatedAt($actualDate);
        $newTicket->setUpdatedAt($actualDate);
        
        $newTicket->setImage($urlImage);

        $this->em->persist($newTicket);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Ticket creado con exito',
        ]);
    }

    #[Route('api/ticket/read', name: 'ticket_read', methods: ['GET'])]
      public function getTickets(): JsonResponse
    {
        $arrayTickets = $this->em->getRepository(Ticket::class)->findAll();
        $resultsDTO = [];
        foreach ($arrayTickets as $ticket) {
            $resultsDTO[] = $this->createDTO($ticket);
        }
        return new JsonResponse(['results'=>$resultsDTO], 200);
    }
}
