<?php

namespace App\EventListener;

use App\Domain\Shared\Trait\SoftDeletable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::onFlush, priority: 500)]
class SoftDeleteListener
{
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        // Intercetta le eliminazioni
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            // Se l'entity usa SoftDeletable, invece di eliminarla la marca come deleted
            if (in_array(SoftDeletable::class, class_uses($entity))) {
                $entity->softDelete();

                // Rimuovi dalla lista di eliminazioni
                $em->persist($entity);
                $uow->propertyChanged($entity, 'deletedAt', null, $entity->getDeletedAt());
                $uow->recomputeSingleEntityChangeSet(
                    $em->getClassMetadata(get_class($entity)),
                    $entity
                );
            }
        }
    }
}
