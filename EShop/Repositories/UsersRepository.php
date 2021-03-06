<?php

namespace DF\Repositories;

use DF\App;
use DF\BindingModels\User\RegisterBindingModel;
use DF\Config\AppConfig;
use DF\Config\DatabaseConfig;
use DF\Models\Cart;
use DF\Models\User;
use DF\Models\UserProduct;
use DF\Services\RoleService;

class UsersRepository implements IRepository
{
    private $db;
    const TABLE_NAME = "users";

    public function __construct()
    {
        $this->db = \DF\Core\Database::getInstance(DatabaseConfig::DB_INSTANCE);
    }

    public function create(RegisterBindingModel $model)
    {
        if($model->getPassword() != $model->getConfirmPassword()) {
            throw new \Exception('Passwords does not match');
        }

        $this->db->beginTransaction();

        $statement = $this->db->prepare("
               INSERT INTO users (username, password_hash, email, register_date, cash)
               VALUE (?, ?, ?, NOW(), ?)
        ");

        $data = [
            $model->getUsername(),
            password_hash($model->getPassword(), AppConfig::PASSWORD_HASH_ALGORITHM),
            $model->getEmail(),
//            (new \DateTime())->format('Y-m-d H:i:s'),
            $model->getCash()
        ];

        if(!$statement->execute($data)) {
            echo $statement->errorInfo();
            $this->db->rollBack();
            return false;
        }

        $registeredUser = $this->findByUsername($model->getUsername());

        // Inserting user role
        $statement = $this->db->prepare("
            INSERT INTO user_roles (user_id, role_id)
            VALUES (?, ?)
        ");

        if(!$statement->execute([
            $registeredUser->getId(),
            App::$roles[AppConfig::DEFAULT_USER_ROLE]
        ])) {
            echo $statement->errorInfo();
            $this->db->rollBack();
            return false;
        }

        //making the user cart
        $statement = $this->db->prepare("
            INSERT INTO usercart (user_id)
            VALUES (?)
        ");

        if(!$statement->execute([$registeredUser->getId()])) {
            echo $statement->errorInfo();
            $this->db->rollBack();
            return false;
        }

        $this->db->commit();
        return true;
    }

    public function remove($id)
    {

    }

    public function findById($id)
    {
        $statement = $this->db->prepare("
            SELECT * FROM users WHERE id = ?
        ");

        $statement->execute([$id]);

        $data = $statement->fetch();

        $user = null;

        if($statement->rowCount() > 0) {
            $data['roles'] = RoleService::getUserRoles($data['id']);

            $user = new User($data);
        }

        return $user;
    }

    public function findByUsername($username)
    {
        $statement = $this->db->prepare("
            SELECT * FROM users WHERE username = ?
        ");

        $statement->execute([$username]);

        $data = $statement->fetch();

        $user = null;

        if($statement->rowCount() > 0) {
            $data['roles'] = RoleService::getUserRoles($data['id']);

            $user = new User($data);
        }

        return $user;
    }

    public function userExistsByUsername($username)
    {
        $statement = $this->db->prepare("
            SELECT id FROM users WHERE username = ?
        ");

        $statement->execute([$username]);

        if($statement->rowCount() > 0) {
            return true;
        }

        return false;
    }

    public function banIP($ipAddress)
    {
        $statement = $this->db->prepare("
            INSERT INTO blacklist (ip_address)
            VALUES (?)
        ");

        $statement->execute([$ipAddress]);

        if($statement->rowCount() > 0) {
            return true;
        }

        return false;
    }

    public function banByUsername($username)
    {
        $statement = $this->db->prepare("
            UPDATE users
            SET is_banned = TRUE
            WHERE username = ?
        ");

        $statement->execute([$username]);

        if(($statement->rowCount() > 0)) {
            //Successfull ban
            return true;
        }

        return false;
    }

    public function getUserCart($userId)
    {
        $statement = $this->db->prepare("
            SELECT p.name, p.price FROM usercart AS uc
            INNER JOIN cart_products AS cp ON cp.card_id = uc.id
            INNER JOIN products AS p ON p.id = cp.product_id
            WHERE uc.user_id = ?
        ");

        $statement->execute([$userId]);

        $data = $statement->fetch();
        return new Cart($data);
    }

    public function getUserProducts($userId)
    {
        $statement = $this->db->prepare("
            SELECT * FROM user_products
            WHERE user_id = ?
        ");

        $statement->execute([$userId]);

        $data = $statement->fetchAll();

        return $data;
    }

    public function getUserCartId($userId)
    {
        $statement = $this->db->prepare("
            SELECT id FROM usercart WHERE user_id = ?
        ");

        $statement->execute([$userId]);

        return $statement->fetch();
    }
}