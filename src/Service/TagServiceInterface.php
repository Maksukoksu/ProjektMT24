<?php

namespace App\Service;

use App\Entity\Tag;

/**
 * Interface TagServiceInterface
 * @package App\Service
 */
interface TagServiceInterface
{
    /**
     * Create a new tag.
     *
     * @param Tag $tag
     * @return Tag
     */
    public function createTag(Tag $tag): Tag;

    /**
     * Update an existing tag.
     *
     * @param Tag $tag
     * @return Tag
     */
    public function updateTag(Tag $tag): Tag;

    /**
     * Delete a tag.
     *
     * @param Tag $tag
     * @return void
     */
    public function deleteTag(Tag $tag): void;

    /**
     * Get a tag by its ID.
     *
     * @param int $id
     * @return Tag|null
     */
    public function getTagById(int $id): ?Tag;

    /**
     * Get all tags.
     *
     * @return Tag[]
     */
    public function getAllTags(): array;

    /**
     * Find by title.
     *
     * @param string $title Tag title
     *
     * @return Tag|null Tag entity
     */
    public function findOneByTitle(string $title): ?Tag;

    public function save(Tag $tag);

}