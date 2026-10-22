<?php
// VIEW: hiển thị HTML. $products được Controller (hoặc legacy entry) cung cấp.
$pageTitle = 'Danh sách sản phẩm';
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<?php // TODO B7-E2: chỉ hiển thị Create/Edit/Delete cho Admin (nhưng vẫn bắt buộc guard server). ?>
<div class="page-head">
 <div><h1>Danh sách sản phẩm</h1><p class="muted">Sản phẩm và danh mục được lấy từ MySQL.</p></div>
<?php if (isAdmin()): ?>
    <a class="btn" href="create-product.php">+ Thêm sản phẩm</a>
<?php endif; ?>
</div>
<?php if (isset($_GET['created']) || isset($_GET['updated']) || isset($_GET['deleted'])): ?>
<div class="notice success">Đã cập nhật dữ liệu sản phẩm.</div>
<?php endif; ?>
<div class="card table-scroll">
 <table class="data-table">
  <thead><tr><th>ID</th><th>Sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>Thao tác</th></tr></thead>
  <tbody>
  <?php foreach ($products as $product): ?>
  <tr>
   <td><?= (int)$product['id'] ?></td>
   <td><a href="product-detail.php?id=<?= (int)$product['id'] ?>"><?= e($product['name']) ?></a></td>
   <td><?= e($product['category_name']) ?></td>
   <td><?= number_format((float)$product['price'], 0, ',', '.') ?> đ</td>
   <td><span class="tag <?= (int)$product['stock'] > 0 ? '' : 'missing' ?>"><?= (int)$product['stock'] > 0 ? 'Còn ' . (int)$product['stock'] : 'Hết hàng' ?></span></td>
   <td><div class="actions">
      <?php if (isAdmin()): ?>
       <a class="btn btn-sm btn-outline" href="edit-product.php?id=<?= (int)$product['id'] ?>">Sửa</a>
      <?php endif; ?>

      <?php if (isAdmin()): ?>
      <form action="delete-product.php" method="post" onsubmit="return confirm('Xóa sản phẩm này?')">
       <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
       <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
       <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
      </form>
      <?php endif; ?>      
   </div></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$products): ?><tr><td colspan="6" class="muted">Chưa có sản phẩm.</td></tr><?php endif; ?>
  </tbody>
 </table>
</div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
