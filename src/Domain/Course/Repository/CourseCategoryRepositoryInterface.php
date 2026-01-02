<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\CourseCategory;

/**
 * Repository interface per CourseCategory
 *
 * Nota: Metodi standard (find, findBy, save, remove, etc.)
 * sono già forniti da ServiceEntityRepository
 */
interface CourseCategoryRepositoryInterface
{
    /**
     * Trova tutte le categorie ordinate per nome
     */
    public function findAllOrderedByName(): array;
}
