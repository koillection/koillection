<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Error;
use App\Enum\DatumTypeEnum;
use App\Form\Type\Entity\Admin\UserType;
use App\Repository\ErrorRepository;
use Doctrine\Common\Collections\Order;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ErrorController extends AbstractController
{
    #[Route(path: '/admin/errors', name: 'app_admin_error_index', methods: ['GET'])]
    public function index(ErrorRepository $errorRepository): Response
    {
        return $this->render('App/Admin/Error/index.html.twig', [
            'errors' => $errorRepository->findBy([], ['createdAt' => Order::Descending->value]),
        ]);
    }

    #[Route(path: '/admin/errors/{id}', name: 'app_admin_error_show', methods: ['GET'])]
    public function show(Error $error): Response {
        return $this->render('App/Admin/Error/show.html.twig', [
            'error' => $error
        ]);
    }
}
