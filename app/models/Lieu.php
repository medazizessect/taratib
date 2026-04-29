<?php
require_once __DIR__ . '/Model.php';

class Lieu extends Model
{
    protected string $table = 'lieux';
    protected array $allowedOrderColumns = ['id', 'adresse_libelle', 'code', 'created_at'];
}
