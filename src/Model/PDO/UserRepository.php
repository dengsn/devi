<?php
namespace Devi\Model\PDO;

use DateTime;
use Devi\Model\User;
use Devi\Model\UserRepositoryInterface;
use Devi\Serializer\DateTimeStrategy;
use Devi\Serializer\IntegerStrategy;
use Devi\Serializer\Serializer;
use PDO;

class UserRepository implements UserRepositoryInterface
{
  // Variables
  private $pdo;
  private $table;
  private $serializer;
  
  // Constructor
  public function __construct(PDO $pdo, $table)
  {
    $this->pdo = $pdo;
    $this->table = $table;
    $this->serializer = (new Serializer)
      ->withStrategy('id',new IntegerStrategy)
      ->withStrategy('date_created',new DateTimeStrategy)
      ->withStrategy('date_modified',new DateTimeStrategy);
  }
  
  // Gets a user from the repository
  public function find(int $id): User
  {
    $st = $this->pdo->prepare(
      "SELECT * FROM {$this->table}
        WHERE id = :id");
    $st->bindValue(':id',$id);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    
    // Return null if none found
    if (($result = $st->fetch()) === FALSE)
      return null;
    else
      return $this->serializer->deserialize($result,new User);
  }
  
  // Gets a user by name
  public function findByName(string $name): User
  {
    $st = $this->pdo->prepare(
      "SELECT * FROM {$this->table}
        WHERE name = :name");
    $st->bindValue(':name',$name);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    
    // Return null if none found
    if (($result = $st->fetch()) === FALSE)
      return null;
    else
      return $this->serializer->deserialize($result,new User);
  }
  
  // Gets a user by public key
  public function findByPublicKey(string $public_key): User
  {
    $st = $this->pdo->prepare(
      "SELECT * FROM {$this->table}
        WHERE public_key = :public_key");
    $st->bindValue(':public_key',$public_key);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    
    // Return null if none found
    if (($result = $st->fetch()) === FALSE)
      return null;
    else
      return $this->serializer->deserialize($result,new User);
  }
  
  // Gets all users
  public function findAll(): array
  {
    $st = $this->pdo->prepare(
      "SELECT * FROM {$this->table}
        ORDER BY date_modified DESC");
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    
    return array_map(function($el) {
      return $this->serializer->deserialize($el,new User);
    },$results);
  }
  
  // Puts a user into the repository
  public function create(User $user): void
  {
    $st = $this->pdo->prepare(
      "INSERT INTO {$this->table}
        (id, name, email, password, public_key, private_key, date_created, date_modified)
        VALUES NULL, :name, :email, :password, :public_key, :private_key, :date_created, :date_modified");
    $st->execute([
      ':name' => $user->getName(),
      ':email' => $user->getEmail(),
      ':password' => $user->getPassword(),
      ':public_key' => $user->getPublicKey(),
      ':private_key' => $user->getPrivateKey(),
      ':date_created' => $user->getDateCreated(),
      ':date_modified' => $user->getDateModified()
    ]);
  }
  
  // Patches a user in the repository
  public function update(User $user): void
  {
    $st = $this->pdo->prepare(
      "UPDATE {$this->table}
        SET name = :name, email = :email, password = :password, public_key = :public_key, private_key = :private_key, date_created = :date_created, date_modified = :date_modified
        WHERE id = :id");
    $st->execute([
      ':id' => $user->getId(),
      ':name' => $user->getName(),
      ':email' => $user->getEmail(),
      ':password' => $user->getPassword(),
      ':public_key' => $user->getPublicKey(),
      ':private_key' => $user->getPrivateKey(),
      ':date_created' => $user->getDateCreated(),
      ':date_modified' => $user->getDateModified()
    ]);
  }
  
  // Deletes a user from the repository
  public function delete(User $user): void
  {
    $st = $this->pdo->prepare(
      "DELETE FROM {$this->table}
        WHERE id = :id");
    $st->bindValue(':id',$user->getId());
    $st->execute();
  }
}
