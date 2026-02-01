<?php
// admin/manage_content.php
session_start();
require_once '../api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$pdo = getDB();
$message = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM local_content WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: manage_content.php');
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $stmt = $pdo->prepare("UPDATE local_content SET title = ?, slug = ?, seo_title = ?, seo_description = ?, is_featured = ? WHERE id = ?");
    $stmt->execute([
        $_POST['title'],
        $_POST['slug'],
        $_POST['seo_title'],
        $_POST['seo_description'],
        isset($_POST['is_featured']) ? 1 : 0,
        $_POST['id']
    ]);
    $message = "Content Updated Successfully!";
}

// List Content
$content = $pdo->query("SELECT * FROM local_content ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Content - CMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .item-row { display: flex; background: #222; padding: 15px; border-bottom: 1px solid #333; gap: 20px; align-items: center; }
        .item-row:first-child { background: #333; font-weight: bold; border-radius: 8px 8px 0 0; }
        .col { flex: 1; }
        .col-sm { width: 100px; }
        .actions a { margin-right: 10px; color: #ccc; }
        .actions a:hover { color: white; }
        
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; padding: 20px; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .modal-content { background: #111; padding: 30px; border-radius: 8px; width: 100%; max-width: 600px; border: 1px solid #333; }
        
        label { display: block; margin-top: 15px; color: #bbb; margin-bottom: 5px; }
        input[type="text"], textarea { width: 100%; padding: 10px; background: #000; border: 1px solid #444; color: white; border-radius: 4px; }
    </style>
</head>
<body>
    <div style="max-width: 1000px; margin: 50px auto; padding: 0 20px;">
        <h1 style="border-bottom: 1px solid #333; padding-bottom: 20px;">Content Manager</h1>
        <a href="index.php" style="color: #ccc; margin-right: 20px;">&larr; Dashboard</a>
        <a href="import.php" class="btn btn-primary" style="float: right;">+ Import New</a>
        
        <?php if ($message): ?><p style="color: green; margin: 20px 0;"><?php echo $message; ?></p><?php endif; ?>

        <div style="margin-top: 30px;">
            <div class="item-row">
                <div class="col-sm">Poster</div>
                <div class="col">Title</div>
                <div class="col-sm">Type</div>
                <div class="col-sm">Featured</div>
                <div class="col-sm">Actions</div>
            </div>
            
            <?php foreach ($content as $item): 
                $img = $item['poster_path'] ? "https://image.tmdb.org/t/p/w92" . $item['poster_path'] : "";
            ?>
            <div class="item-row">
                <div class="col-sm"><img src="<?php echo $img; ?>" width="40" style="border-radius: 4px;"></div>
                <div class="col">
                    <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                    <div style="font-size: 0.8rem; color: #666;"><?php echo $item['slug']; ?></div>
                </div>
                <div class="col-sm"><?php echo strtoupper($item['type']); ?></div>
                <div class="col-sm"><?php echo $item['is_featured'] ? '<span style="color: green;">Yes</span>' : 'No'; ?></div>
                <div class="col-sm actions">
                    <a href="#" onclick='openEdit(<?php echo json_encode($item); ?>)'>Edit</a>
                    <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure?')" style="color: #d9534f;">Delete</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Edit SEO & Details</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                
                <label>Title (Override)</label>
                <input type="text" name="title" id="editTitle" required>
                
                <label>URL Slug (SEO)</label>
                <input type="text" name="slug" id="editSlug" required>
                
                <label>Meta Title (SEO)</label>
                <input type="text" name="seo_title" id="editSeoTitle">
                
                <label>Meta Description (SEO)</label>
                <textarea name="seo_description" id="editSeoDesc" rows="3"></textarea>
                
                <label style="margin-top: 20px; display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_featured" id="editFeatured"> Show in Home Slider
                </label>
                
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEdit(item) {
            document.getElementById('editId').value = item.id;
            document.getElementById('editTitle').value = item.title;
            document.getElementById('editSlug').value = item.slug;
            document.getElementById('editSeoTitle').value = item.seo_title;
            document.getElementById('editSeoDesc').value = item.seo_description;
            document.getElementById('editFeatured').checked = item.is_featured == 1;
            document.getElementById('editModal').style.display = 'flex';
        }
    </script>
</body>
</html>
