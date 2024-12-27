<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Album;
use App\Form\Type\Entity\AlbumType;
use App\Form\Type\Entity\DisplayConfigurationType;
use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use App\Service\CachedValuesGetter;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AlbumController extends AbstractController
{
    #[Route(path: '/albums', name: 'app_album_index', methods: ['GET'])]
    #[Route(path: '/user/{username}/albums', name: 'app_shared_album_index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository, CachedValuesGetter $cachedValuesGetter): Response
    {
        dsfdsf;
        $this->denyAccessUnlessFeaturesEnabled(['albums']);
        $albums = $albumRepository->findBy(['parent' => null], ['title' => Criteria::ASC]);

        $albumsCounter = \count($albums);
        $photosCounter = 0;
        foreach ($albums as $album) {
            $albumsCounter += $cachedValuesGetter->getCachedValues($album)['counters']['children'] ?? 0;
            $photosCounter += $cachedValuesGetter->getCachedValues($album)['counters']['photos'] ?? 0;
        }

        return $this->render('App/Album/index.html.twig', [
            'albums' => $albums,
            'albumsCounter' => $albumsCounter,
            'photosCounter' => $photosCounter,
        ]);
    }

    #[Route(path: '/albums/edit', name: 'app_album_edit_index', methods: ['GET', 'POST'])]
    public function editIndex(
        Request $request,
        TranslatorInterface $translator,
        ManagerRegistry $managerRegistry
    ): Response {
        $form = $this->createForm(DisplayConfigurationType::class, $this->getUser()->getAlbumsDisplayConfiguration());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.album_index_edited'));

            return $this->redirectToRoute('app_album_index');
        }

        return $this->render('App/Album/edit_index.html.twig', [
            'form' => $form
        ]);
    }

    #[Route(path: '/albums/add', name: 'app_album_add', methods: ['GET', 'POST'])]
    public function add(Request $request, TranslatorInterface $translator, AlbumRepository $albumRepository, ManagerRegistry $managerRegistry): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        $album = new Album();
        if ($request->query->has('parent')) {
            $parent = $albumRepository->findOneBy([
                'id' => $request->query->get('parent'),
                'owner' => $this->getUser(),
            ]);
            $album
                ->setParent($parent)
                ->setVisibility($parent->getVisibility())
            ;
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->persist($album);
            $managerRegistry->getManager()->flush();

            $this->addFlash('notice', $translator->trans('message.album_added', ['album' => $album->getTitle()]));

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        return $this->render('App/Album/add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/albums/{id}/edit', name: 'app_album_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Album $album, TranslatorInterface $translator, ManagerRegistry $managerRegistry): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.album_edited', ['album' => $album->getTitle()]));

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        return $this->render('App/Album/edit.html.twig', [
            'form' => $form,
            'album' => $album,
        ]);
    }

    #[Route(path: '/albums/{id}/delete', name: 'app_album_delete', methods: ['POST'])]
    public function delete(Request $request, Album $album, TranslatorInterface $translator, ManagerRegistry $managerRegistry): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        $form = $this->createDeleteForm('app_album_delete', $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->remove($album);
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.album_deleted', ['album' => $album->getTitle()]));
        }

        return $this->redirectToRoute('app_album_index');
    }

    #[Route(path: '/albums/{id}', name: 'app_album_show', methods: ['GET'])]
    #[Route(path: '/user/{username}/albums/{id}', name: 'app_shared_album_show', methods: ['GET'])]
    public function show(Album $album, AlbumRepository $albumRepository, PhotoRepository $photoRepository): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        return $this->render('App/Album/show.html.twig', [
            'album' => $album,
            'children' => $albumRepository->findBy(['parent' => $album]),
            'photos' => $photoRepository->findBy(['album' => $album]),
        ]);
    }
}
