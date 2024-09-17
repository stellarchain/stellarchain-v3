<?php
namespace App\Autocompleter;

use App\Entity\StellarHorizon\Asset;
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
        return Asset::class;
    }

    public function createFilteredQueryBuilder(EntityRepository $repository, string $query): QueryBuilder
    {
        return $repository
            ->createQueryBuilder('assets')
            ->andWhere('assets.asset_code LIKE :search')
            ->andWhere('assets.in_market = :inMarket')
            ->setParameter('search', '%'.$query.'%')
            ->setParameter('inMarket', true)

            // maybe do some custom filtering in all cases
            //->andWhere('food.isHealthy = :isHealthy')
            //->setParameter('isHealthy', true)
        ;
    }

    public function getLabel(object $entity): string
    {
        return $entity->getAssetCode();
    }

    public function getValue(object $entity): string
    {
        return $entity->getAssetCode();
    }

    public function isGranted(Security $security): bool
    {
        // see the "security" option for details
        return true;
    }

}
