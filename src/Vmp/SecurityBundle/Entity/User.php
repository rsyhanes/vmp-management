<?php
/**
 * Created by PhpStorm.
 * User: Jose Antonio
 * Date: 8/14/2016
 * Time: 4:07 PM
 */

namespace Vmp\SecurityBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getRoleCount()
    {
        $roles = $this->getRoles();
        $count = count($roles);
        return $roles[$count - 1];
    }
}