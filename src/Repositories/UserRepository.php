<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?array
    {
        // TODO B7-R1: SELECT id,name,email,password_hash,role theo email.
        $stmt = $this->pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        // TODO B7-R2: SELECT id,name,email,role theo id.
        $stmt = $this->pdo->prepare('SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }

    public function create(string $name, string $email, string $hash): void
    {
        // TODO B7-R3: INSERT user bằng prepared statement.
        // role CỐ ĐỊNH 'user' tại server; không lấy từ POST.
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, 'user')");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'hash' => $hash
        ]);
    }
}
