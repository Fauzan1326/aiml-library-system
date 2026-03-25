<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>ML System Quick Check</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        h2 { color: #333; }
    </style>
</head>
<body>

<h1>🔍 ML System Quick Diagnostic</h1>

<div class="box">
    <h2>1. Database Connection</h2>
    <?php
    try {
        include('includes/config.php');
        echo '<p class="ok">✅ Database Connected</p>';
    } catch (Exception $e) {
        echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
    }
    ?>
</div>

<div class="box">
    <h2>2. Student Cluster Column Check</h2>
    <?php
    try {
        $sql = "SHOW COLUMNS FROM tblstudents LIKE 'student_cluster'";
        $query = $dbh->prepare($sql);
        $query->execute();
        
        if ($query->rowCount() > 0) {
            echo '<p class="ok">✅ Column EXISTS!</p>';
            $col = $query->fetch(PDO::FETCH_OBJ);
            echo '<p>Type: ' . $col->Type . '</p>';
        } else {
            echo '<p class="error">❌ Column NOT FOUND - Run the SQL migration</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
    }
    ?>
</div>

<div class="box">
    <h2>3. Database Views Check</h2>
    <?php
    $views = ['vw_student_engagement', 'vw_book_popularity', 'vw_book_cooccurrence'];
    foreach ($views as $view) {
        try {
            $sql = "SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME = :view AND TABLE_SCHEMA = DATABASE()";
            $query = $dbh->prepare($sql);
            $query->bindParam(':view', $view);
            $query->execute();
            $exists = $query->fetch(PDO::FETCH_OBJ)->cnt > 0;
            
            if ($exists) {
                echo '<p class="ok">✅ ' . $view . ' EXISTS</p>';
            } else {
                echo '<p class="error">❌ ' . $view . ' NOT FOUND</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Error checking ' . $view . ': ' . $e->getMessage() . '</p>';
        }
    }
    ?>
</div>

<div class="box">
    <h2>4. Student Data Count</h2>
    <?php
    try {
        $sql = "SELECT COUNT(*) as total FROM tblstudents WHERE Status = 1";
        $query = $dbh->prepare($sql);
        $query->execute();
        $count = $query->fetch(PDO::FETCH_OBJ)->total;
        echo '<p>Active Students: <strong>' . $count . '</strong></p>';
    } catch (Exception $e) {
        echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
    }
    ?>
</div>

<div class="box">
    <h2>5. Books Data Count</h2>
    <?php
    try {
        $sql = "SELECT COUNT(*) as total FROM tblbooks";
        $query = $dbh->prepare($sql);
        $query->execute();
        $count = $query->fetch(PDO::FETCH_OBJ)->total;
        echo '<p>Total Books: <strong>' . $count . '</strong></p>';
    } catch (Exception $e) {
        echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
    }
    ?>
</div>

<div class="box">
    <h2>6. Issue Records Count</h2>
    <?php
    try {
        $sql = "SELECT COUNT(*) as total FROM tblissuedbookdetails";
        $query = $dbh->prepare($sql);
        $query->execute();
        $count = $query->fetch(PDO::FETCH_OBJ)->total;
        echo '<p>Total Issues: <strong>' . $count . '</strong></p>';
    } catch (Exception $e) {
        echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
    }
    ?>
</div>

<div class="box" style="background: #fffacd; border: 2px solid #ffa500;">
    <h2>✨ Summary</h2>
    <p>If you see mostly <span class="ok">✅</span> marks above, your database changes are applied and the website should show ML recommendations.</p>
    <p><strong>Next:</strong> Go to <a href="dashboard.php">dashboard.php</a> and scroll down to see AI-Powered Smart Recommendations</p>
</div>

</body>
</html>
<?php
?>
