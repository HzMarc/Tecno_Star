<?php

declare(strict_types=1);

final class CatalogValidationException extends DomainException
{
    public function __construct(public array $errors)
    {
        parent::__construct('Revisa los campos indicados.');
    }
}

final class CatalogAdminModel
{
    public function __construct(private PDO $database) {}

    public static function entity(string $kind): array
    {
        return match ($kind) {
            'categories' => ['tbcategorias', 'id_categoria', 'nombre_categoria', 'Categorías'],
            'brands' => ['tbmarcas', 'id_marca', 'nombre_marca', 'Marcas'],
            default => throw new InvalidArgumentException('Tipo de catálogo inválido.'),
        };
    }

    public function options(string $kind): array
    {
        [$table, $id, $name] = self::entity($kind);
        return $this->database->query("SELECT $id AS id, $name AS name, estado FROM $table ORDER BY $name")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $query = $this->database->prepare('SELECT * FROM tbproductos WHERE id_producto = ?');
        $query->execute([$id]);
        return $query->fetch() ?: null;
    }

    public function listing(array $input): array
    {
        $filters = [];
        foreach (['q', 'category', 'brand', 'status', 'page'] as $key) {
            if (isset($input[$key]) && !is_string($input[$key])) throw new CatalogValidationException(['filters' => 'Los filtros deben ser valores de texto.']);
            $filters[$key] = trim($input[$key] ?? '');
        }
        if (mb_strlen($filters['q']) > 150) throw new CatalogValidationException(['filters' => 'La búsqueda admite hasta 150 caracteres.']);
        $where = []; $values = [];
        if ($filters['q'] !== '') {
            $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']) . '%';
            $where[] = "(p.nombre_producto LIKE ? ESCAPE '!' OR p.codigo_producto LIKE ? ESCAPE '!')";
            array_push($values, $term, $term);
        }
        foreach (['category' => 'p.id_categoria', 'brand' => 'p.id_marca'] as $key => $column) {
            if ($filters[$key] !== '') {
                if (!ctype_digit($filters[$key]) || (int) $filters[$key] < 1) throw new CatalogValidationException(['filters' => 'Selecciona un filtro válido.']);
                $where[] = "$column = ?"; $values[] = (int) $filters[$key];
            }
        }
        if ($filters['status'] !== '') {
            if (!in_array($filters['status'], ['activo', 'inactivo'], true)) throw new CatalogValidationException(['filters' => 'Selecciona un estado válido.']);
            $where[] = 'p.estado = ?'; $values[] = $filters['status'];
        }
        $sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $count = $this->database->prepare('SELECT COUNT(*) FROM tbproductos p' . $sqlWhere);
        $count->execute($values); $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / 12));
        $page = min($pages, max(1, (int) $filters['page']));
        $offset = ($page - 1) * 12;
        $query = $this->database->prepare("SELECT p.*, c.nombre_categoria AS categoria, c.estado AS category_state,
            COALESCE(m.nombre_marca, 'Sin marca') AS marca, COALESCE(m.estado, 'activo') AS brand_state,
            COALESCE(i.stock_actual - i.stock_reservado, 0) AS stock
            FROM tbproductos p INNER JOIN tbcategorias c ON c.id_categoria = p.id_categoria
            LEFT JOIN tbmarcas m ON m.id_marca = p.id_marca
            LEFT JOIN tbinventario i ON i.id_producto = p.id_producto $sqlWhere
            ORDER BY p.id_producto DESC LIMIT 12 OFFSET $offset");
        $query->execute($values);
        return ['products' => $query->fetchAll(), 'filters' => $filters, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    public function validate(array $input): array
    {
        $data = []; $errors = [];
        foreach (['codigo_producto' => 50, 'nombre_producto' => 150, 'descripcion' => 10000, 'precio_compra' => 13, 'precio_venta' => 13, 'id_categoria' => 10, 'id_marca' => 10, 'estado' => 8] as $key => $max) {
            if (isset($input[$key]) && !is_string($input[$key])) { $errors[$key] = 'Introduce un valor válido.'; $data[$key] = ''; continue; }
            $data[$key] = trim($input[$key] ?? '');
            if (mb_strlen($data[$key]) > $max) $errors[$key] = "Admite hasta $max caracteres.";
        }
        $imageUrl = $input['imagen_url'] ?? '';
        if (!is_string($imageUrl)) { $errors['imagen_url'] = 'Introduce una URL válida.'; $imageUrl = ''; }
        $imageUrl = trim($imageUrl);
        if (mb_strlen($imageUrl) > 2048) $errors['imagen_url'] = 'La URL admite hasta 2048 caracteres.';
        if ($imageUrl !== '') {
            $parts = parse_url($imageUrl);
            if ($parts === false || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || filter_var($imageUrl, FILTER_VALIDATE_URL) === false) {
                $errors['imagen_url'] = 'Usa una URL completa y válida que comience con http:// o https://.';
            }
        }
        $data['imagen_url'] = $imageUrl;
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,49}$/D', $data['codigo_producto'])) $errors['codigo_producto'] = 'Usa de 1 a 50 letras, números, puntos, guiones o guiones bajos.';
        $data['codigo_producto'] = strtoupper($data['codigo_producto']);
        if ($data['nombre_producto'] === '') $errors['nombre_producto'] = 'El nombre es obligatorio.';
        foreach (['precio_compra', 'precio_venta'] as $key) {
            if (!preg_match('/^(?:0|[1-9][0-9]{0,9})(?:\.[0-9]{1,2})?$/D', $data[$key])) $errors[$key] = 'Introduce un importe entre 0 y 9999999999.99, con hasta dos decimales y punto decimal.';
        }
        foreach (['id_categoria', 'id_marca'] as $key) {
            if ($key === 'id_marca' && $data[$key] === '') { $data[$key] = null; continue; }
            if (!ctype_digit($data[$key]) || (int) $data[$key] < 1 || (int) $data[$key] > 2147483647) $errors[$key] = 'Selecciona una opción válida.';
            else $data[$key] = (int) $data[$key];
        }
        if (!in_array($data['estado'], ['activo', 'inactivo'], true)) $errors['estado'] = 'Selecciona un estado válido.';
        if ($errors) throw new CatalogValidationException($errors);
        return $data;
    }

    private function validateReferences(array $data): void
    {
        foreach (['categories' => 'id_categoria', 'brands' => 'id_marca'] as $kind => $key) {
            if ($data[$key] === null) continue;
            [$table, $id] = self::entity($kind);
            $query = $this->database->prepare("SELECT estado FROM $table WHERE $id = ? FOR UPDATE");
            $query->execute([$data[$key]]); $state = $query->fetchColumn();
            if ($state === false || ($data['estado'] === 'activo' && $state !== 'activo')) throw new CatalogValidationException([$key => 'Un producto activo necesita una opción activa.']);
        }
    }

    public function save(array $data, ?int $id, ?string $image, bool $removeImage = false): int
    {
        $this->database->beginTransaction();
        try {
            if ($id !== null) {
                $lock = $this->database->prepare('SELECT id_producto FROM tbproductos WHERE id_producto = ? FOR UPDATE');
                $lock->execute([$id]);
                if ($lock->fetchColumn() === false) throw new CatalogValidationException(['form' => 'El producto ya no existe.']);
                $sql = 'UPDATE tbproductos SET codigo_producto=:codigo_producto, nombre_producto=:nombre_producto, descripcion=:descripcion, precio_compra=:precio_compra, precio_venta=:precio_venta, id_categoria=:id_categoria, id_marca=:id_marca, estado=:estado';
                if ($image !== null || $removeImage) { $sql .= ', imagen=:imagen'; $data['imagen'] = $image; }
                $sql .= ' WHERE id_producto=:id'; $data['id'] = $id;
            } else {
                $sql = 'INSERT INTO tbproductos (codigo_producto,nombre_producto,descripcion,precio_compra,precio_venta,id_categoria,id_marca,estado,imagen) VALUES (:codigo_producto,:nombre_producto,:descripcion,:precio_compra,:precio_venta,:id_categoria,:id_marca,:estado,:imagen)';
                $data['imagen'] = $image;
            }
            $this->validateReferences($data);
            $this->database->prepare($sql)->execute($data);
            if ($id === null) {
                $id = (int) $this->database->lastInsertId();
                $this->database->prepare('INSERT INTO tbinventario (id_producto) VALUES (?)')->execute([$id]);
            }
            $this->database->commit();
            return $id;
        } catch (Throwable $exception) {
            $this->database->rollBack();
            if ($exception instanceof PDOException && (int) ($exception->errorInfo[1] ?? 0) === 1062) throw new CatalogValidationException(['codigo_producto' => 'Ese código ya está registrado, incluso si el producto está inactivo.']);
            throw $exception;
        }
    }

    public function setProductState(int $id, string $state): void
    {
        if (!in_array($state, ['activo','inactivo'], true)) throw new CatalogValidationException(['estado'=>'Estado inválido.']);
        // No reenviar datos de una lectura previa: únicamente se actualiza estado bajo bloqueo.
        $this->database->beginTransaction();
        try {
            $lock = $this->database->prepare('SELECT * FROM tbproductos WHERE id_producto = ? FOR UPDATE');
            $lock->execute([$id]); $current = $lock->fetch();
            if (!$current) throw new CatalogValidationException(['form' => 'Producto no encontrado.']);
            $current['estado'] = $state;
            $this->validateReferences($current);
            $this->database->prepare('UPDATE tbproductos SET estado=? WHERE id_producto=?')->execute([$state, $id]);
            $this->database->commit();
        } catch (Throwable $exception) { $this->database->rollBack(); throw $exception; }
    }

    public function saveEntity(string $kind, ?int $id, string $name, string $state): void
    {
        [$table, $idColumn, $nameColumn] = self::entity($kind);
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 100) throw new CatalogValidationException(['name' => 'El nombre debe tener entre 1 y 100 caracteres.']);
        if (!in_array($state, ['activo', 'inactivo'], true)) throw new CatalogValidationException(['estado' => 'Estado inválido.']);
        try {
            if ($id === null) $this->database->prepare("INSERT INTO $table ($nameColumn, estado) VALUES (?, ?)")->execute([$name, $state]);
            else {
                $exists = $this->database->prepare("SELECT $idColumn FROM $table WHERE $idColumn=?"); $exists->execute([$id]);
                if ($exists->fetchColumn() === false) throw new CatalogValidationException(['form' => 'Registro no encontrado.']);
                $this->database->prepare("UPDATE $table SET $nameColumn=?, estado=? WHERE $idColumn=?")->execute([$name, $state, $id]);
            }
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) throw new CatalogValidationException(['name' => 'Ese nombre ya está registrado. Puedes reactivar el registro existente.']);
            throw $exception;
        }
    }
}
