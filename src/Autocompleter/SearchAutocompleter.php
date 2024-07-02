<?php
namespace App\Autocompleter;

use App\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\UX\Autocomplete\EntityAutocompleterInterface;


#[AutoconfigureTag('ux.entity_autocompleter', ['alias' => 'search'])]
class SearchAutocompleter implements EntityAutocompleterInterface
{
    public function getEntityClass(): string
    {
        return Project::class;
    }

    public function createFilteredQueryBuilder(EntityRepository $repository, string $query): QueryBuilder
    {
        return $repository
            ->createQueryBuilder('project')
            ->andWhere('project.name LIKE :search OR project.description LIKE :search')
            ->setParameter('search', '%'.$query.'%')

            // maybe do some custom filtering in all cases
            //->andWhere('food.isHealthy = :isHealthy')
            //->setParameter('isHealthy', true)
        ;
    }

    public function getLabel(object $entity): string
    {
        return $entity->getName();
    }

    public function getValue(object $entity): string
    {
        return $entity->getId();
    }

    public function isGranted(Security $security): bool
    {
        // see the "security" option for details
        return true;
    }

}
