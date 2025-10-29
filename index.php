<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Admin</title>
    <style>
        body {
            background-color: #121212;
            color: white;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h2 {
            color: #00c3ff;
        }
        a.logout {
            color: #00c3ff;
            text-decoration: none;
            float: right;
            background-color: #1e1e1e;
            padding: 5px 10px;
            border-radius: 5px;
        }
        a.logout:hover {
            background-color: #333;
        }
        form {
            background-color: #1e1e1e;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        input, textarea, select, button {
            padding: 8px;
            margin: 5px 0;
            width: 100%;
            border: none;
            border-radius: 5px;
        }
        input, textarea, select {
            background-color: #2c2c2c;
            color: white;
        }
        button {
            background-color: #00c3ff;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #009ed6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #00c3ff;
            color: black;
        }
        tr:nth-child(even) {
            background-color: #1e1e1e;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .edit-btn {
            background-color: #ffc107;
            color: black;
            padding: 5px 8px;
            border: none;
            border-radius: 4px;
        }
        .edit-btn:hover {
            background-color: #e0a800;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
            padding: 5px 8px;
            border: none;
            border-radius: 4px;
        }
        .delete-btn:hover {
            background-color: #b02a37;
        }
    </style>
    <script>
        function confirmDelete(id) {
            if (confirm("⚠️ Are you sure you want to delete this product?")) {
                window.location.href = "?delete=" + id;
            }
        }
    </script>
</head>
<body>

    <p style="text-align:right;">
        👋 Welcome, <b><?php echo $_SESSION['user']; ?></b> |
        <a class="logout" href="logout.php">Logout</a>
    </p>

    <h2><?php echo isset($_GET['edit']) ? "Edit Product" : "Add Product"; ?></h2>

    <?php
    if (isset($_GET['success']) && $_GET['success'] == 1) {
        echo "<p style='color:#00ff7f;'>✅ Product added successfully!</p>";
    }
    if (isset($_GET['updated']) && $_GET['updated'] == 1) {
        echo "<p style='color:#00ff7f;'>✏️ Product updated successfully!</p>";
    }
    if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
        echo "<p style='color:#00ff7f;'>🗑 Product deleted successfully!</p>";
    }

    $editData = null;
    if (isset($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $result = $conn->query("SELECT * FROM products WHERE id = $id");
        $editData = $result->fetch_assoc();
    }
    ?>

    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
        <input type="text" name="name" placeholder="Product Name" value="<?php echo $editData['name'] ?? ''; ?>" required>

        <select name="category" required>
            <option value="">Select Category</option>
            <?php
            $categories = ["Electronics","Clothing","Home & Kitchen","Sports","Books"];
            foreach ($categories as $cat) {
                $selected = ($editData && $editData['category'] == $cat) ? "selected" : "";
                echo "<option value='$cat' $selected>$cat</option>";
            }
            ?>
        </select>

        <input type="number" step="0.01" name="price" placeholder="Price" value="<?php echo $editData['price'] ?? ''; ?>" required>
        <input type="number" name="stock" placeholder="Stock" value="<?php echo $editData['stock'] ?? ''; ?>" required>
        <input type="text" name="supplier" placeholder="Enter supplier name" value="<?php echo $editData['supplier'] ?? ''; ?>">
        <textarea name="description" placeholder="Description"><?php echo $editData['description'] ?? ''; ?></textarea>

        <?php if ($editData): ?>
            <button type="submit" name="update_product">Update Product</button>
        <?php else: ?>
            <button type="submit" name="add_product">Add Product</button>
        <?php endif; ?>
    </form>

    <?php
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $supplier = $_POST['supplier'];
        $description = $_POST['description'];

        if (!empty($name) && !empty($category) && !empty($price) && !empty($stock)) {
            $stmt = $conn->prepare("INSERT INTO products (name, category, price, stock, supplier, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdss", $name, $category, $price, $stock, $supplier, $description);
            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                exit();
            } else {
                echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
            }
            $stmt->close();
        }
    }

    if (isset($_POST['update_product'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $supplier = $_POST['supplier'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, supplier=?, description=? WHERE id=?");
        $stmt->bind_param("sssdssi", $name, $category, $price, $stock, $supplier, $description, $id);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
            exit();
        } else {
            echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
        }
        $stmt->close();
    }

    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $conn->query("DELETE FROM products WHERE id = $id");
        header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=1");
        exit();
    }
    ?>

    <h2>Product List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price (₱)</th>
            <th>Stock</th>
            <th>Supplier</th>
            <th>Description</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['category']}</td>
                        <td>{$row['price']}</td>
                        <td>{$row['stock']}</td>
                        <td>{$row['supplier']}</td>
                        <td>{$row['description']}</td>
                        <td>{$row['created_at']}</td>
                        <td class='actions'>
                            <a href='?edit={$row['id']}' class='edit-btn'>Edit</a>
                            <button type='button' class='delete-btn' onclick='confirmDelete({$row['id']})'>Delete</button>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='9' style='text-align:center;'>No products found</td></tr>";
        }
        ?>
    </table>

</body>
</html>
