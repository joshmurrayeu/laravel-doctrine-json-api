<?php

namespace JMWD\JsonApi\Database;

use App\Doctrine\Contracts\Entity;
use Closure;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Illuminate\Support\Str;
use JMWD\JsonApi\Error\Internal\InvalidAttributeGetter;
use JMWD\JsonApi\Error\Internal\InvalidAttributeSetter;
use JMWD\JsonApi\Error\Internal\MethodNotDefined;
use Tobyz\JsonApiServer\Adapter\AdapterInterface;
use Tobyz\JsonApiServer\Context;
use Tobyz\JsonApiServer\Schema\Attribute;
use Tobyz\JsonApiServer\Schema\HasMany;
use Tobyz\JsonApiServer\Schema\HasOne;
use Tobyz\JsonApiServer\Schema\Relationship;

abstract class Adapter implements AdapterInterface
{
    /**
     * @var ManagerRegistry $managerRegistry
     */
    protected ManagerRegistry $managerRegistry;

    /**
     * @return class-string
     */
    abstract public function adapterFor(): string;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return ManagerRegistry
     */
    protected function getManagerRegistry(): ManagerRegistry
    {
        return $this->managerRegistry;
    }

    /**
     * @return ObjectManager|EntityManager
     */
    public function getEntityManager(): ObjectManager|EntityManager
    {
        return $this->getManagerRegistry()->getManagerForClass(
            $this->adapterFor()
        );
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->getEntityManager()
            ->getRepository($this->adapterFor());
    }

    /**
     * @inheritDoc
     */
    public function query(): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('e');
    }

    /**
     * @param QueryBuilder $query
     * @param int[]        $ids
     *
     * @return void
     */
    public function filterByIds($query, array $ids): void
    {
        throw new MethodNotDefined(__FUNCTION__);
    }

    /**
     * @param QueryBuilder $query
     * @param Attribute    $attribute
     * @param mixed        $value
     * @param string       $operator
     *
     * @return void
     */
    public function filterByAttribute($query, Attribute $attribute, $value, string $operator = '='): void
    {
        $attributeName = $attribute->getName();

        $query->andWhere("e.{$attributeName} {$operator} :{$attributeName}")
            ->setParameter($attributeName, $value);
    }

    /**
     * @param QueryBuilder $query
     * @param Relationship $relationship
     * @param Closure      $scope
     *
     * @return void
     */
    public function filterByRelationship($query, Relationship $relationship, Closure $scope): void
    {
        throw new MethodNotDefined(__FUNCTION__);
    }

    /**
     * @param QueryBuilder $query
     * @param Attribute    $attribute
     * @param string       $direction
     *
     * @return void
     */
    public function sortByAttribute($query, Attribute $attribute, string $direction): void
    {
        throw new MethodNotDefined(__FUNCTION__);
    }

    /**
     * @param QueryBuilder $query
     * @param int          $limit
     * @param int          $offset
     *
     * @return void
     */
    public function paginate($query, int $limit, int $offset): void
    {
        $query->setMaxResults($limit)
            ->setFirstResult($offset);
    }

    /**
     * @param QueryBuilder $query
     * @param string       $id
     *
     * @return object|null
     */
    public function find($query, string $id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param QueryBuilder $query
     *
     * @return Entity[]
     */
    public function get($query): array
    {
        return $query->getQuery()
            ->getResult();
    }

    /**
     * @param QueryBuilder $query
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @return int
     *
     */
    public function count($query): int
    {
        $localQuery = clone $query;

        return $localQuery->select('count(e) as count')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Entity $model
     *
     * @return string
     */
    public function getId($model): string
    {
        return (string) ($model->getId() ?? '');
    }

    /**
     * @param Entity $model
     */
    public function getAttribute($model, Attribute $attribute)
    {
        $getter = $this->guessPropertyMethod($attribute->getName());

        if (method_exists($model, $getter)) {
            return $model->$getter();
        }

        throw new InvalidAttributeGetter($getter, get_class($model));
    }

    /**
     * @param Entity  $model
     * @param HasOne  $relationship
     * @param bool    $linkageOnly
     * @param Context $context
     *
     * @return Entity
     */
    public function getHasOne($model, HasOne $relationship, bool $linkageOnly, Context $context): Entity
    {
        $propertyName = $relationship->getName();

        // @phpstan-ignore-next-line
        if (($relationshipProperty = $relationship->getProperty()) && !empty($relationshipProperty)) {
            $propertyName = $relationshipProperty;
        }

        return $model->{$this->guessPropertyMethod($propertyName)}();
    }

    /**
     * @param Entity  $model
     * @param HasMany $relationship
     * @param bool    $linkageOnly
     * @param Context $context
     *
     * @return Entity[]
     */
    public function getHasMany($model, HasMany $relationship, bool $linkageOnly, Context $context): array
    {
        $propertyName = $relationship->getName();

        // @phpstan-ignore-next-line
        if (($relationshipProperty = $relationship->getProperty()) && !empty($relationshipProperty)) {
            $propertyName = $relationshipProperty;
        }

        $result = $model->{$this->guessPropertyMethod($propertyName)}();

        if ($result instanceof Collection) {
            $result = $result->toArray();
        }

        return $result;
    }

    /**
     * @param Entity $model
     *
     * @return bool
     */
    public function represents($model): bool
    {
        return false;
    }

    /**
     * @return Entity
     */
    public function model(): Entity
    {
        // @phpstan-ignore-next-line
        return new ($this->adapterFor())();
    }

    /**
     * @param Entity $model
     * @param string $id
     *
     * @return void
     */
    public function setId($model, string $id): void
    {
        throw new MethodNotDefined(__FUNCTION__);
    }

    /**
     * @param Entity    $model
     * @param Attribute $attribute
     * @param mixed     $value
     *
     * @return void
     */
    public function setAttribute($model, Attribute $attribute, mixed $value): void
    {
        $setter = $this->guessPropertyMethod($attribute->getName(), 'set');

        if (!method_exists($model, $setter)) {
            throw new InvalidAttributeSetter($setter, get_class($model));
        }

        $model->$setter($value);
    }

    /**
     * @param Entity       $model
     * @param HasOne       $relationship
     * @param Entity|mixed $related
     *
     * @return void
     */
    public function setHasOne($model, HasOne $relationship, mixed $related): void
    {
        $propertyName = $relationship->getName();

        if (($relationshipProperty = $relationship->getProperty())) {
            $propertyName = $relationshipProperty;
        }

        $setter = $this->guessPropertyMethod($propertyName, 'set');

        if (!method_exists($model, $setter)) {
            throw new InvalidAttributeSetter($setter, get_class($model));
        }

        $model->$setter($related);
    }

    /**
     * @param Entity $model
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @return void
     *
     */
    public function save($model): void
    {
        $ManagerRegistry = $this->getEntityManager();

        $ManagerRegistry->persist($model);
        $ManagerRegistry->flush();
    }

    /**
     * @param Entity   $model
     * @param HasMany  $relationship
     * @param Entity[] $related
     *
     * @return void
     */
    public function saveHasMany($model, HasMany $relationship, array $related): void
    {
        throw new MethodNotDefined(__FUNCTION__);
    }

    /**
     * @param Entity $model
     *
     * @return void
     */
    public function delete($model): void
    {
        throw new MethodNotDefined(__FUNCTION__);
    }

    /**
     * @param string $property
     * @param string $prefix
     *
     * @return string
     */
    protected function guessPropertyMethod(string $property, string $prefix = 'get'): string
    {
        return Str::camel(
            implode(' ', [$prefix, $property])
        );
    }
}
