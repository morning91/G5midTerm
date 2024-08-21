<?php  
require_once("../pdoConnect.php");

// 每頁筆數
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10; // 預設為 10
$startPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($startPage - 1) * $per_page;
$orderID = 'product_id ';
$orderValue = 'ASC';

//排序
if (isset($_GET['order'])) {
    $orderArray = explode(':', $_GET['order']);
    $orderID = $orderArray[0];
    $orderValue = $orderArray[1] == 'DESC' ? 'DESC' : 'ASC';
}

// 搜尋
$search = isset($_GET["search"]) ? $_GET["search"] : '';
$brand = isset($_GET["brand"]) ? $_GET["brand"] : ''; // 新增品牌變數
$category = isset($_GET["category"]) ? $_GET["category"] : ''; // 新增類別變數
$sub = isset($_GET["sub"]) ? $_GET["sub"] : ''; // 新增分類變數
$product_status = isset($_GET["product_status"]) ? $_GET["product_status"] : ''; // 新增狀態變數

$sql = "SELECT * FROM product
WHERE product_valid=0 AND product_status='已下架'";
if ($search) {
    $sql .= " AND product_name LIKE :search";
}
if ($brand) {
    $sql .= " AND product_brand = :brand"; // 根據品牌過濾
}
if ($category) {
    $sql .= " AND product_category_name = :category"; // 根據類別過濾
}
if ($sub) {
    $sql .= " AND product_sub_category = :sub"; // 根據分類過濾
}
if ($product_status) {
    $sql .= " AND product_status = :product_status"; // 根據狀態過濾
}
$sql .= " ORDER BY $orderID $orderValue LIMIT :limit OFFSET :offset";

$stmt = $dbHost->prepare($sql);

// 綁定參數做篩選搜尋
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
if ($brand) {
    $stmt->bindValue(':brand', $brand);
}
if ($category) {
    $stmt->bindValue(':category', $category);
}
if ($sub) {
    $stmt->bindValue(':sub', $sub);
}
if ($product_status) {
    $stmt->bindValue(':product_status', $product_status);
}

$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

// 總商品數顯示分頁
$countPage = "SELECT COUNT(*) FROM product WHERE product_valid=0 AND product_status='已下架'";
if ($search) {
    $countPage .= " AND product_name LIKE :search";
}
if ($brand) {
    $countPage .= " AND product_brand = :brand"; // 根據品牌過濾
}
if ($category) {
    $countPage .= " AND product_category_name = :category"; // 根據類別過濾
}
if ($sub) {
    $countPage .= " AND product_sub_category = :sub"; // 根據分類過濾
}
if ($product_status) {
    $countPage .= " AND product_status = :product_status"; // 根據狀態過濾
}


$countStmt = $dbHost->prepare($countPage);
if ($search) {
    $countStmt->bindValue(':search', '%' . $search . '%');
}
if ($brand) {
    $countStmt->bindValue(':brand', $brand);
}
if ($category) {
    $countStmt->bindValue(':category', $category);
}
if ($sub) {
    $countStmt->bindValue(':sub', $sub);
}
if ($product_status) {
    $countStmt->bindValue(':product_status', $product_status);
}

$countStmt->execute();
$productCount = $countStmt->fetchColumn();

// 計算總頁數
$totalPages = ceil($productCount / $per_page);

try {
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $productCount = count($rows); // 可以撈到商品資訊
} catch (PDOException $e) {
    echo "預處理陳述式執行失敗！ <br/>";
    echo "Error: " . $e->getMessage() . "<br/>";
    $db_host = NULL;
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>商品復原</title>
    <link rel="stylesheet" href="./css.css">
    <?php include("../headlink.php") ?>
</head>


<body>
    <script src="../assets/static/js/initTheme.js"></script>

    <div id="app">
        <?php include("../sidebar.php") ?>
        <div id="main" class='layout-navbar navbar-fixed'>
            <header>
            </header>
            <div id="main-content">
                <div class="page-heading">
                    <div class="page-title">
                        <div class="row">
                            <div class="col-12 col-md-6 order-md-1 order-last">
                                <h3>商品復原</h3>
                                <p class="text-subtitle text-muted"></p>
                            </div>
                            <div class="col-12 col-md-6 order-md-2 order-first">
                                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html"><i class="fa-solid fa-house"></i></a></li>
                                        <li class="breadcrumb-item active" aria-current="page">商品復原</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <section class="section">
                        <div class="card">
                            <div class="card-body">
                                <div class="dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns">
                                    <div class="dataTable-top">

                                        共計 <?= $productCount ?> 樣商品 
                                        <a class="btn btn-primary ms-2" href="ProductList.php"><i class="fa-solid fa-arrow-left"></i>返回</a>

                                    </div>
                                    <div>
                                        <?php if ($productCount > 0) : ?>
                                            <form class="mb-2" action="" method="get">
                                                <label class="ms-2">品牌</label>
                                                <div class="dataTable-dropdown">
                                                    <select name="brand" class="dataTable-selector form-select" onchange="this.form.submit()">
                                                        <option value="">選擇品牌</option>
                                                        <option value="木入森" <?= ($brand == "木入森") ? 'selected' : '' ?>>木入森</option>
                                                        <option value="水魔素" <?= ($brand == "水魔素") ? 'selected' : '' ?>>水魔素</option>
                                                        <option value="陪心" <?= ($brand == "陪心") ? 'selected' : '' ?>>陪心</option>
                                                        <option value="美喵" <?= ($brand == "美喵") ? 'selected' : '' ?>>美喵</option>
                                                    </select>
                                                </div>
                                                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                                <input type="hidden" name="page" value="<?= $startPage ?>"> <!-- 保留當前頁碼 -->
                                                <input type="hidden" name="per_page" value="<?= $per_page ?>">

                                                <label class="ms-2">類別</label>
                                                <div class="dataTable-dropdown">
                                                    <select name="category" class="dataTable-selector form-select" onchange="this.form.submit()">
                                                        <option value="">選擇類別</option>
                                                        <option value="犬貓通用" <?= ($category == "犬貓通用") ? 'selected' : '' ?>>犬貓通用</option>
                                                        <option value="犬寶保健" <?= ($category == "犬寶保健") ? 'selected' : '' ?>>犬寶保健</option>
                                                        <option value="貓皇保健" <?= ($category == "貓皇保健") ? 'selected' : '' ?>>貓皇保健</option>
                                                    </select>
                                                </div>

                                                <label class="ms-2">分類</label>
                                                <div class="dataTable-dropdown">
                                                    <select name="sub" class="dataTable-selector form-select" onchange="this.form.submit()">
                                                        <option value="">選擇分類</option>
                                                        <option value="魚油粉" <?= ($sub == "魚油粉") ? 'selected' : '' ?>>魚油粉</option>
                                                        <option value="鈣保健" <?= ($sub == "鈣保健") ? 'selected' : '' ?>>鈣保健</option>
                                                        <option value="腸胃保健" <?= ($sub == "腸胃保健") ? 'selected' : '' ?>>腸胃保健</option>
                                                        <option value="關節保健" <?= ($sub == "關節保健") ? 'selected' : '' ?>>關節保健</option>
                                                        <option value="口腔保健" <?= ($sub == "口腔保健") ? 'selected' : '' ?>>口腔保健</option>
                                                        <option value="心臟保健" <?= ($sub == "心臟保健") ? 'selected' : '' ?>>心臟保健</option>
                                                        <option value="皮膚保健" <?= ($sub == "皮膚保健") ? 'selected' : '' ?>>皮膚保健</option>
                                                        <option value="胰臟保健" <?= ($sub == "胰臟保健") ? 'selected' : '' ?>>胰臟保健</option>
                                                        <option value="眼睛保健" <?= ($sub == "眼睛保健") ? 'selected' : '' ?>>眼睛保健</option>
                                                    </select>
                                                </div>

                                                

                                                <div class="dataTable-search mt-2">
                                                    <form action="">
                                                        <div class="input-group">
                                                            <?php if (!empty($search)) : ?>
                                                                <a class="btn btn-primary" href="ProductList.php" title="回商品管理"><i class="fa-solid fa-left-long"></i> </a>
                                                            <?php endif; ?>
                                                            <input type="hidden" name="per_page" value="<?= $per_page ?>"> <!-- 在選擇筆數的時候搜尋會依照所選的筆數顯示 -->
                                                            <input type="search" class="form-control" value="<?php echo isset($_GET["search"]) ? $_GET["search"] : "" ?>" name="search" placeholder="搜尋商品">
                                                            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                                                        </div>
                                                    </form>
                                                </div>



                                            </form>



                                            <tbody>
                                                <!-- <?php foreach ($rows as $row) : ?>
                                                    <tr>
                                                        <td><?= $row["product_brand"] ?></td>
                                                    </tr>
                                                <?php endforeach; ?> -->
                                            </tbody>

                                    </div>
                                    <!-- 控制每頁筆數 -->
                                    <div class="d-flex justify-content-between">
                                        <form action="" method="get">
                                            <label class="ms-2">每頁</label>
                                            <div class="dataTable-dropdown">
                                                <select name="per_page" class="dataTable-selector form-select" onchange="this.form.submit()">
                                                    <option value="5" <?= ($per_page == 5) ? 'selected' : '' ?>>5</option>
                                                    <option value="10" <?= ($per_page == 10) ? 'selected' : '' ?>>10</option>
                                                    <option value="15" <?= ($per_page == 15) ? 'selected' : '' ?>>15</option>
                                                    <option value="20" <?= ($per_page == 20) ? 'selected' : '' ?>>20</option>
                                                    <option value="25" <?= ($per_page == 25) ? 'selected' : '' ?>>25</option>
                                                    <input type="hidden" name="brand" value="<?= $brand ?>">
                                                </select>
                                            </div>
                                            <label>筆</label>
                                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                            <input type="hidden" name="page" value="<?= $startPage ?>"> <!-- 保留當前頁碼 -->
                                        </form>

                                    </div>

                                    <div class="dataTable-container">

                                        <!-- 商品內容 -->
                                        <table class="table table-striped dataTable-table" id="table1">
                                            <thead>
                                                <tr>
                                                    <th data-sortable="" class="desc" aria-sort="descending"><a href="?page=<?= $startPage ?>&per_page=<?= $per_page ?>&order=product_id:<?= $orderValue === 'ASC' ? 'DESC' : 'ASC' ?>" class="dataTable-sorter">ID</a></th>
                                                    <th data-sortable=""><a href="?page=<?= $startPage ?>&per_page=<?= $per_page ?>&order=product_img:<?= $orderValue === 'ASC' ? 'DESC' : 'ASC' ?>" class="dataTable-sorter">圖片</a></th>
                                                    <th data-sortable=""><a href="?page=<?= $startPage ?>&per_page=<?= $per_page ?>&order=product_name:<?= $orderValue === 'ASC' ? 'DESC' : 'ASC' ?>" class="dataTable-sorter">名稱</a></th>
                                                    <th data-sortable=""><a href="?page=<?= $startPage ?>&per_page=<?= $per_page ?>&order=product_origin_price:<?= $orderValue === 'ASC' ? 'DESC' : 'ASC' ?>" class="dataTable-sorter">原價</a></th>
                                                    <th data-sortable=""><a href="?page=<?= $startPage ?>&per_page=<?= $per_page ?>&order=product_sale_price:<?= $orderValue === 'ASC' ? 'DESC' : 'ASC' ?>" class="dataTable-sorter">售價</a></th>
                                                    <th data-sortable=""><a href="?page=<?= $startPage ?>&per_page=<?= $per_page ?>&order=product_stock:<?= $orderValue === 'ASC' ? 'DESC' : 'ASC' ?>" class="dataTable-sorter">庫存</a></th>
                                                    <th data-sortable=""><a href="?page=<?= $startPage ?>&per_page=<?= $per_page ?>&order=product_update_date:<?= $orderValue === 'ASC' ? 'DESC' : 'ASC' ?>" class="dataTable-sorter">上次更新時間</a></th>
                                                    <th data-sortable=""><a href="#" class="dataTable-sorter ms-2">商品復原</a></th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php foreach ($rows as $row) : ?>
                                                    <tr>
                                                        <td><?= $row["product_id"] ?></td>
                                                        <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                                        <td>
                                                            <div class="ratio ratio-1x1">
                                                                <img class="object-fit-cover" src="./ProductPicUpLoad/<?= $row["product_img"] ?>" alt="<?= $row["product_name"] ?>">
                                                            </div>
                                                        </td>
                                                        <td><?= $row["product_name"] ?></td>
                                                        <td><?= number_format($row["product_origin_price"]) ?></td>
                                                        <td><?= number_format($row["product_sale_price"]) ?></td>
                                                        <td><?= $row["product_stock"] ?></td>
                                                        <td><?= $row["product_update_date"] ?></td>
                                                        <td>
                                                            
                                                            <!-- /ProductList.php?per_page=15&brand=木入森&search=&page=1 -->
                                                            <a class="ms-4" title="復原商品" href="ProductRepairAlert.php?product_id=<?= $row['product_id'] ?>&per_page=<?= $per_page ?>&brand=<?= $brand ?>&category=<?= $category ?>&sub=<?= $sub ?>&order=<?= $orderID ?>:<?= $orderValue ?>&page=<?= $startPage ?>"><i class="fa-solid fa-turn-up"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        <?php else : ?>
                                            <form class="mb-2" action="" method="get">
                                                <label class="ms-2">品牌</label>
                                                <div class="dataTable-dropdown">
                                                    <select name="brand" class="dataTable-selector form-select" onchange="this.form.submit()">
                                                        <option value="">選擇品牌</option>
                                                        <option value="木入森" <?= ($brand == "木入森") ? 'selected' : '' ?>>木入森</option>
                                                        <option value="水魔素" <?= ($brand == "水魔素") ? 'selected' : '' ?>>水魔素</option>
                                                        <option value="陪心" <?= ($brand == "陪心") ? 'selected' : '' ?>>陪心</option>
                                                        <option value="美喵" <?= ($brand == "美喵") ? 'selected' : '' ?>>美喵</option>
                                                    </select>
                                                </div>
                                                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                                <input type="hidden" name="page" value="<?= $startPage ?>"> <!-- 保留當前頁碼 -->
                                                <input type="hidden" name="per_page" value="<?= $per_page ?>">

                                                <label class="ms-2">類別</label>
                                                <div class="dataTable-dropdown">
                                                    <select name="category" class="dataTable-selector form-select" onchange="this.form.submit()">
                                                        <option value="">選擇類別</option>
                                                        <option value="犬貓通用" <?= ($category == "犬貓通用") ? 'selected' : '' ?>>犬貓通用</option>
                                                        <option value="犬寶保健" <?= ($category == "犬寶保健") ? 'selected' : '' ?>>犬寶保健</option>
                                                        <option value="貓皇保健" <?= ($category == "貓皇保健") ? 'selected' : '' ?>>貓皇保健</option>
                                                    </select>
                                                </div>

                                                <label class="ms-2">分類</label>
                                                <div class="dataTable-dropdown">
                                                    <select name="sub" class="dataTable-selector form-select" onchange="this.form.submit()">
                                                        <option value="">選擇分類</option>
                                                        <option value="魚油粉" <?= ($sub == "魚油粉") ? 'selected' : '' ?>>魚油粉</option>
                                                        <option value="鈣保健" <?= ($sub == "鈣保健") ? 'selected' : '' ?>>鈣保健</option>
                                                        <option value="腸胃保健" <?= ($sub == "腸胃保健") ? 'selected' : '' ?>>腸胃保健</option>
                                                        <option value="關節保健" <?= ($sub == "關節保健") ? 'selected' : '' ?>>關節保健</option>
                                                        <option value="口腔保健" <?= ($sub == "口腔保健") ? 'selected' : '' ?>>口腔保健</option>
                                                        <option value="心臟保健" <?= ($sub == "心臟保健") ? 'selected' : '' ?>>心臟保健</option>
                                                        <option value="皮膚保健" <?= ($sub == "皮膚保健") ? 'selected' : '' ?>>皮膚保健</option>
                                                        <option value="胰臟保健" <?= ($sub == "胰臟保健") ? 'selected' : '' ?>>胰臟保健</option>
                                                        <option value="眼睛保健" <?= ($sub == "眼睛保健") ? 'selected' : '' ?>>眼睛保健</option>
                                                    </select>
                                                </div>
                                                <div class="dataTable-search mt-2">
                                                    <form action="">
                                                        <div class="input-group">
                                                            <?php if (!empty($search)) : ?>
                                                                <a class="btn btn-primary" href="ProductList.php" title="回商品管理"><i class="fa-solid fa-left-long"></i> </a>
                                                            <?php endif; ?>
                                                            <input type="hidden" name="per_page" value="<?= $per_page ?>"> <!-- 在選擇筆數的時候搜尋會依照所選的筆數顯示 -->
                                                            <input type="search" class="form-control" value="<?php echo isset($_GET["search"]) ? $_GET["search"] : "" ?>" name="search" placeholder="搜尋商品">
                                                            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                        <form action="" method="get">
                                            <label class="ms-2">每頁</label>
                                            <div class="dataTable-dropdown">
                                                <select name="per_page" class="dataTable-selector form-select" onchange="this.form.submit()">
                                                    <option value="5" <?= ($per_page == 5) ? 'selected' : '' ?>>5</option>
                                                    <option value="10" <?= ($per_page == 10) ? 'selected' : '' ?>>10</option>
                                                    <option value="15" <?= ($per_page == 15) ? 'selected' : '' ?>>15</option>
                                                    <option value="20" <?= ($per_page == 20) ? 'selected' : '' ?>>20</option>
                                                    <option value="25" <?= ($per_page == 25) ? 'selected' : '' ?>>25</option>
                                                    <input type="hidden" name="brand" value="<?= $brand ?>">
                                                </select>
                                            </div>
                                            <label class="mb-3">筆</label>
                                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                            <input type="hidden" name="page" value="<?= $startPage ?>"> <!-- 保留當前頁碼 -->
                                        </form>

                                    </div>
                                            </form>
                                            <tr>
                                                <td>查無商品</td>
                                            </tr>
                                        <?php endif; ?>
                                        </table>
                                    </div>
                                    <!-- 下方顯示筆數 以及分頁變化 -->
                                    <?php $start_item = ($startPage - 1) * $per_page; ?>
                                    <div class="dataTable-bottom">
                                        <div class="dataTable-info">顯示 <?= $start_item + 1 ?> 到 <?= $start_item + $per_page ?>筆 共 <?= $productCount ?> 筆</div>
                                        <nav class="dataTable-pagination">
                                            <!-- 分頁 -->
                                            <ul class="dataTable-pagination-list pagination pagination-primary">

                                                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                                    <?php
                                                    // 當前頁是否是搜尋頁
                                                    $url = "?page=" . $i . "&per_page=" . $per_page; // 包含每頁筆數
                                                    if ($search) {
                                                        $url .= "&search=" . urlencode($search);
                                                    }
                                                    ?>
                                                    <li class="page-item <?= ($i == $startPage) ? 'active' : '' ?>">
                                                        <a class="page-link" href="<?= $url ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                    </div>
                    <div class="float-end">
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="../assets/static/js/components/dark.js"></script>
    <script src="../assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <?php include("../js.php") ?>
    <?php include("./product-js.php") ?>
    <script src="../assets/compiled/js/app.js"></script>


</body>

</html>