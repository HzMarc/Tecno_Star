<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/CatalogAdminModel.php';
require_once BASE_PATH . '/core/CatalogImage.php';

final class CatalogAdminController
{
    private function authorize(): bool { return Auth::requireRole('Administrador') !== null; }
    private function model(): CatalogAdminModel { return new CatalogAdminModel(Database::connection()); }
    private function id(mixed $value): ?int
    {
        if ($value === null || $value === '') return null;
        if (!is_string($value) || !ctype_digit($value) || (int) $value < 1 || (int) $value > 2147483647) throw new CatalogValidationException(['form' => 'Identificador inválido.']);
        return (int) $value;
    }
    private function redirect(string $path, string $message): void
    {
        $_SESSION['catalog_notice'] = $message;
        header('Location: ' . app_url($path), true, 303);
    }
    private function notice(): ?string
    {
        $notice = $_SESSION['catalog_notice'] ?? null;
        unset($_SESSION['catalog_notice']);
        return is_string($notice) ? $notice : null;
    }
    public function products(): void
    {
        if (!$this->authorize()) return;
        $model = $this->model(); $errors = [];
        try { $listing = $model->listing($_GET); }
        catch (CatalogValidationException $exception) { http_response_code(422); $errors = $exception->errors; $listing = $model->listing([]); }
        extract($listing, EXTR_SKIP);
        $categories = $model->options('categories'); $brands = $model->options('brands');
        $notice = $this->notice();
        require BASE_PATH . '/app/Views/admin/products.php';
    }
    public function form(): void
    {
        if (!$this->authorize()) return;
        try { $id = $this->id($_GET['id'] ?? null); }
        catch (CatalogValidationException) { ErrorHandler::respond(404, 'Producto no encontrado.'); return; }
        $model = $this->model();
        $product = $id === null ? null : $model->find($id);
        if ($id !== null && $product === null) { ErrorHandler::respond(404, 'Producto no encontrado.'); return; }
        $values = $product ?? ['codigo_producto'=>'', 'nombre_producto'=>'', 'descripcion'=>'', 'precio_compra'=>'0.00', 'precio_venta'=>'', 'id_categoria'=>'', 'id_marca'=>'', 'estado'=>'activo', 'imagen'=>''];
        $values['imagen_url'] = '';
        $errors = [];
        $this->renderForm($id, $values, $errors);
    }
    private function renderForm(?int $id, array $values, array $errors): void
    {
        $categories = $this->model()->options('categories'); $brands = $this->model()->options('brands');
        require BASE_PATH . '/app/Views/admin/product-form.php';
    }
    public function save(): void
    {
        if (!$this->authorize()) return;
        $id = null; $image = null; $values = [];
        try {
            $id = $this->id($_POST['id'] ?? null);
            $old = $id === null ? null : $this->model()->find($id);
            if ($id !== null && $old === null) { ErrorHandler::respond(404, 'Producto no encontrado.'); return; }
            $values = array_filter($_POST, static fn ($value) => is_string($value));
            $values['imagen'] = $old['imagen'] ?? '';
            $data = $this->model()->validate($_POST);
            $imageUrl = $data['imagen_url'];
            unset($data['imagen_url']);
            if (isset($_FILES['image']) && !is_array($_FILES['image'])) throw new CatalogValidationException(['image' => 'La imagen no es válida.']);
            $remove = ($_POST['remove_image'] ?? '') === '1';
            $hasUpload = isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
            if (($remove && ($hasUpload || $imageUrl !== '')) || ($hasUpload && $imageUrl !== '')) throw new CatalogValidationException(['image' => 'Elige una sola opción: subir una imagen, pegar una URL o quitar la imagen.']);
            $image = $imageUrl !== '' ? $imageUrl : CatalogImage::store($_FILES['image'] ?? null);
            $this->model()->save($data, $id, $image, $remove);
            $this->redirect('/admin/products', $id === null ? 'Producto creado con stock en cero.' : 'Producto actualizado.');
        } catch (CatalogValidationException $exception) {
            CatalogImage::discard($image); http_response_code(422);
            $this->renderForm($id, $values, $exception->errors);
        } catch (Throwable $exception) { CatalogImage::discard($image); throw $exception; }
    }
    public function state(): void
    {
        if (!$this->authorize()) return;
        try {
            $id = $this->id($_POST['id'] ?? null); $state = $_POST['estado'] ?? null;
            if ($id === null || !is_string($state) || !in_array($state, ['activo','inactivo'], true)) throw new CatalogValidationException(['form'=>'Operación inválida.']);
            $this->model()->setProductState($id, $state);
            $this->redirect('/admin/products', $state === 'activo' ? 'Producto activado.' : 'Producto desactivado. Sus referencias y existencias se conservan.');
        } catch (CatalogValidationException $exception) { ErrorHandler::respond(422, implode(' ', $exception->errors)); }
    }
    public function entities(): void
    {
        if (!$this->authorize()) return;
        $this->renderEntities([], []);
    }
    public function saveEntity(): void
    {
        if (!$this->authorize()) return;
        try {
            $kind = $_POST['kind'] ?? '';
            if (!is_string($kind) || !in_array($kind, ['categories','brands'], true)) throw new CatalogValidationException(['form'=>'Catálogo inválido.']);
            $name = $_POST['name'] ?? null; $state = $_POST['estado'] ?? null;
            if (!is_string($name) || !is_string($state)) throw new CatalogValidationException(['name'=>'Introduce un nombre y estado válidos.']);
            $id = $this->id($_POST['id'] ?? null);
            $this->model()->saveEntity($kind, $id, $name, $state);
            $this->redirect('/admin/catalog?kind=' . $kind, ($kind === 'categories' ? 'Categoría' : 'Marca') . ($id === null ? ' creada.' : ' actualizada.'));
        } catch (CatalogValidationException $exception) {
            http_response_code(422); $this->renderEntities($exception->errors, array_filter($_POST, 'is_string'));
        }
    }
    private function renderEntities(array $errors, array $values): void
    {
        $kind = $values['kind'] ?? ($_GET['kind'] ?? 'categories');
        if (!is_string($kind) || !in_array($kind, ['categories','brands'], true)) { ErrorHandler::respond(422, 'Catálogo inválido.'); return; }
        [$table, $idColumn, $nameColumn, $title] = CatalogAdminModel::entity($kind);
        $q = $_GET['q'] ?? ''; $state = $_GET['status'] ?? ''; $requestedPage = $_GET['page'] ?? '1';
        if (!is_string($q) || mb_strlen($q) > 100 || !is_string($state) || !in_array($state, ['','activo','inactivo'], true) || !is_string($requestedPage)) { ErrorHandler::respond(422, 'Filtros inválidos.'); return; }
        $where = []; $params = [];
        if ($q !== '') { $where[] = "$nameColumn LIKE ? ESCAPE '!'"; $params[] = '%' . str_replace(['!','%','_'], ['!!','!%','!_'], $q) . '%'; }
        if ($state !== '') { $where[] = 'estado = ?'; $params[] = $state; }
        $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $db = Database::connection();
        $count = $db->prepare("SELECT COUNT(*) FROM $table $clause"); $count->execute($params); $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / 12)); $page = min($pages, max(1, (int) $requestedPage)); $offset = ($page - 1) * 12;
        $query = $db->prepare("SELECT $idColumn AS id, $nameColumn AS name, estado FROM $table $clause ORDER BY $nameColumn LIMIT 12 OFFSET $offset"); $query->execute($params); $entities = $query->fetchAll();
        $notice = $this->notice();
        require BASE_PATH . '/app/Views/admin/catalog.php';
    }
}
