<?php

namespace Asb\ComposerCheck;

use Concrete\Package\ComposerCheck\App;

trait EntityBase
{

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param mixed    $id          The identifier
     * @param int      $lockMode    The lock mode
     * @param int|null $lockVersion The lock version
     *
     * @return static|null The entity instance or NULL if the entity can not be found
     */
    public static function getByID($id, $lockMode = null, $lockVersion = null)
    {
        return App::em()->getRepository(get_called_class())->find($id, $lockMode, $lockVersion);
    }

    public function save($flush = true, $merge = false)
    {
        $em = App::em();
        if ($merge) {
            $em->merge($this);
        } else {
            $em->persist($this);
        }

        if ($flush) {
            $em->flush();
        }
    }

    public function delete($flush = true)
    {
        $em = App::em();
        $em->remove($this);
        if ($flush) {
            $em->flush();
        }
    }

    public function setPropertiesFromArray($arr)
    {
        foreach ($arr as $key => $prop) {
            $setter = 'set'.ucfirst($key);
            // we prefer passing by setter method
            if (method_exists($this, $setter)) {
                call_user_func([$this, $setter], $prop);
            } else {
                $this->{$key} = $prop;
            }
        }
    }
}
