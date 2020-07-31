<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "user_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute=true,
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"user_show", "users_list"})
 * )
 * @Hateoas\Relation(
 *      "list",
 *      href = @Hateoas\Route(
 *          "users_list",
 *          absolute=true,
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"user_show", "users_list"})
 * )
 * @Hateoas\Relation(
 *      "create_user",
 *      href = @Hateoas\Route(
 *          "user_new",
 *          absolute=true,
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"user_show", "users_list"})
 * )
 * @Hateoas\Relation(
 *      "edit_user",
 *      href = @Hateoas\Route(
 *          "user_edit",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute=true,
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"user_show", "users_list"})
 * )
 * @Hateoas\Relation(
 *      "delete_user",
 *      href = @Hateoas\Route(
 *          "user_delete",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute=true,
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"user_show", "users_list"})
 * )
 * @Hateoas\Relation(
 *     "customer",
 *     embedded = @Hateoas\Embedded("expr(object.getCustomer())")
 * )
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"users_list", "user_show"})
     */
    private $id;


    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @Groups({"users_list", "user_show"})
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="users")
     * @Groups({"user_show"})
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"users_list", "user_show"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"users_list", "user_show"})
     */
    private $lastname;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


}
