<?php

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TagService
 * @package App\Service
 */
class TagService implements TagServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var TagRepository
     */
    private TagRepository $tagRepository;

    /**
     * TagService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TagRepository $tagRepository
     */
    public function __construct(EntityManagerInterface $entityManager, TagRepository $tagRepository)
    {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Create a new tag.
     *
     * @param Tag $tag
     * @return Tag
     */
    public function createTag(Tag $tag): Tag
    {
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $tag;
    }

    /**
     * Update an existing tag.
     *
     * @param Tag $tag
     * @return Tag
     */
    public function updateTag(Tag $tag): Tag
    {
        $this->entityManager->flush();

        return $tag;
    }

    /**
     * Delete a tag.
     *
     * @param Tag $tag
     * @return void
     */
    public function deleteTag(Tag $tag): void
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
    }

    /**
     * Get a tag by its ID.
     *
     * @param int $id
     * @return Tag|null
     */
    public function getTagById(int $id): ?Tag
    {
        return $this->tagRepository->find($id);
    }

    /**
     * Get all tags.
     *
     * @return Tag[]
     */
    public function getAllTags(): array
    {
        return $this->tagRepository->findAll();
    }
}