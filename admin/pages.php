<?php
// admin/pages.php
// session_start();
require_once '../api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$pdo = getDB();
$msg = '';

try {
    // Handle Delete
    if (isset($_GET['delete'])) {
        $pdo->prepare("DELETE FROM custom_pages WHERE id = ?")->execute([$_GET['delete']]);
        header('Location: pages.php');
        exit;
    }

    // Handle Save
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        if (!empty($_POST['custom_slug'])) $slug = $_POST['custom_slug'];
        
        $data = [
            $title,
            $slug,
            $_POST['content'],
            $_POST['seo_title'],
            $_POST['seo_description'],
            isset($_POST['id']) ? $_POST['id'] : null
        ];

        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $stmt = $pdo->prepare("UPDATE custom_pages SET title=?, slug=?, content=?, seo_title=?, seo_description=? WHERE id=?");
            $stmt->execute($data);
            $msg = "Page Updated";
        } else {
            // Insert
            array_pop($data); // Remove null ID
            $stmt = $pdo->prepare("INSERT INTO custom_pages (title, slug, content, seo_title, seo_description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($data);
            $msg = "Page Created";
        }
    }

    $pages = $pdo->query("SELECT * FROM custom_pages ORDER BY created_at DESC")->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Page Builder - CMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- TinyMCE CDN -->
    <script src="https://cdn.tiny.cloud/1/phkw2r1pek7jx50e7gutbshu6gvn9ekjvttj8ilctjm1iiyg/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: '#editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        skin: 'oxide-dark',
        content_css: 'dark'
      });
    </script>
    <style>
        .page-list { margin-top: 30px; }
        .page-item { background: #222; padding: 15px; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center; }
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; overflow-y: auto; padding: 20px; }
        .modal-content { background: #111; padding: 30px; border-radius: 8px; max-width: 900px; margin: 0 auto; border: 1px solid #333; }
        label { margin-top: 15px; display: block; color: #bbb; }
        input[type="text"], textarea { width: 100%; padding: 10px; background: #000; border: 1px solid #444; color: white; border-radius: 4px; }
    </style>
</head>
<body>
    <div style="max-width: 1000px; margin: 50px auto; padding: 0 20px;">
        <h1 style="border-bottom: 1px solid #333; padding-bottom: 20px;">Page Builder</h1>
        <a href="index.php" style="color: #ccc;">&larr; Dashboard</a>
        <a href="#" onclick="openEditor()" class="btn btn-primary" style="float: right;">+ New Page</a>

        <?php if ($msg): ?><p style="color: green;"><?php echo $msg; ?></p><?php endif; ?>

        <div class="page-list">
            <?php foreach ($pages as $p): ?>
            <div class="page-item">
                <div>
                    <strong><?php echo htmlspecialchars($p['title']); ?></strong>
                    <div style="font-size: 0.8rem; color: #666;">/<?php echo $p['slug']; ?></div>
                </div>
                <div>
                    <a href="../page.php?slug=<?php echo $p['slug']; ?>" target="_blank" style="margin-right: 10px; color: #4caf50;">View</a>
                    <a href="#" onclick='openEditor(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8"); ?>)' style="margin-right: 10px; color: #2196f3;">Edit</a>
                    <a href="?delete=<?php echo $p['id']; ?>" onclick="return confirm('Delete?')" style="color: #f44336;">Delete</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Page Editor Modal -->
    <div id="editorModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Create Page</h2>
            <form method="POST">
                <input type="hidden" name="id" id="pageId">
                
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label>Page Title</label>
                        <input type="text" name="title" id="pageTitle" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Custom Slug (Optional)</label>
                        <input type="text" name="custom_slug" id="pageSlug" placeholder="about-us">
                    </div>
                </div>

                <label>Content</label>
                <textarea name="content" id="editor"></textarea>

                <h3 style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px;">SEO Settings</h3>
                <label>Meta Title</label>
                <input type="text" name="seo_title" id="seoTitle">
                
                <label>Meta Description</label>
                <textarea name="seo_description" id="seoDesc" rows="2"></textarea>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" onclick="document.getElementById('editorModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Page</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditor(data = null) {
            document.getElementById('editorModal').style.display = 'block';
            if (data) {
                document.getElementById('modalTitle').innerText = 'Edit ' + data.title;
                document.getElementById('pageId').value = data.id;
                document.getElementById('pageTitle').value = data.title;
                document.getElementById('pageSlug').value = data.slug;
                document.getElementById('seoTitle').value = data.seo_title;
                document.getElementById('seoDesc').value = data.seo_description;
                tinymce.get('editor').setContent(data.content);
            } else {
                document.getElementById('modalTitle').innerText = 'Create New Page';
                document.getElementById('pageId').value = '';
                document.activeElement.blur(); // Reset focus
                document.getElementById('pageTitle').value = '';
                document.getElementById('pageSlug').value = '';
                tinymce.get('editor').setContent('');
            }
        }
    </script>
</body>
</html>
