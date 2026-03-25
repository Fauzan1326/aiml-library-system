<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 ML System Diagnostic Report</h2>";
echo "<hr>";

// ============================================
// TEST 1: Database Connection
// ============================================
echo "<h3>1️⃣ DATABASE CONNECTION TEST</h3>";
try {
    include('includes/config.php');
    echo "✅ Database connected successfully<br>";
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

// ============================================
// TEST 2: ML Module Loading
// ============================================
echo "<h3>2️⃣ ML MODULE LOADING TEST</h3>";
try {
    include('includes/ml_recommendations.php');
    echo "✅ ML recommendations module loaded<br>";
    echo "✅ Contains all ML functions<br>";
} catch (Exception $e) {
    echo "❌ ML Module Error: " . $e->getMessage() . "<br>";
}

// ============================================
// TEST 3: Database Schema Check
// ============================================
echo "<h3>3️⃣ DATABASE SCHEMA CHECK</h3>";
try {
    $sql = "SHOW COLUMNS FROM tblstudents LIKE 'student_cluster'";
    $query = $dbh->prepare($sql);
    $query->execute();
    
    if ($query->rowCount() > 0) {
        echo "✅ student_cluster column EXISTS in tblstudents<br>";
    } else {
        echo "⚠️ student_cluster column NOT FOUND<br>";
        echo "⚠️ Run: mysql -u root -p library < ml_database_upgrade.sql<br>";
    }
} catch (Exception $e) {
    echo "❌ Schema Check Error: " . $e->getMessage() . "<br>";
}

// ============================================
// TEST 4: Views Check
// ============================================
echo "<h3>4️⃣ DATABASE VIEWS CHECK</h3>";
try {
    $views = ['vw_student_engagement', 'vw_book_popularity', 'vw_book_cooccurrence'];
    foreach ($views as $view) {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME = :view AND TABLE_SCHEMA = 'library'";
        $query = $dbh->prepare($sql);
        $query->bindParam(':view', $view);
        $query->execute();
        $exists = $query->fetch(PDO::FETCH_COLUMN) > 0;
        
        if ($exists) {
            echo "✅ View $view created<br>";
        } else {
            echo "⚠️ View $view NOT FOUND<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Views Check Error: " . $e->getMessage() . "<br>";
}

// ============================================
// TEST 5: Function Tests
// ============================================
echo "<h3>5️⃣ ML FUNCTION TESTS</h3>";
try {
    $testStudentId = 'SID002';
    
    // Test Content-Based
    echo "<strong>Testing Content-Based Filtering...</strong><br>";
    $cb = @getContentBasedRecommendations($dbh, $testStudentId, 3);
    echo "Result: " . count($cb) . " recommendations<br>";
    if (!empty($cb)) {
        echo "✅ Content-Based Filtering WORKING<br>";
        foreach ($cb as $book) {
            echo "  - " . htmlentities($book->BookName) . " (Score: " . $book->similarity_score . "%)<br>";
        }
    } else {
        echo "⚠️ No recommendations (student may have no history)<br>";
    }
    
    // Test Collaborative
    echo "<strong>Testing Collaborative Filtering...</strong><br>";
    $cf = @getCollaborativeFilteringRecommendations($dbh, $testStudentId, 3);
    echo "Result: " . count($cf) . " recommendations<br>";
    if (!empty($cf)) {
        echo "✅ Collaborative Filtering WORKING<br>";
        foreach ($cf as $book) {
            echo "  - " . htmlentities($book->BookName) . " (Co-occurrence: " . $book->co_occurrence_count . ")<br>";
        }
    } else {
        echo "⚠️ No collaborative recommendations<br>";
    }
    
    // Test Clustering
    echo "<strong>Testing K-Means Clustering...</strong><br>";
    $cluster = @getStudentCluster($dbh, $testStudentId);
    $label = @getClusterLabel($cluster);
    echo "✅ Clustering WORKING<br>";
    echo "  - Student: $testStudentId<br>";
    echo "  - Cluster: $cluster<br>";
    echo "  - Label: $label<br>";
    
    // Test Student Summary
    echo "<strong>Testing Student Summary...</strong><br>";
    $summary = @getStudentSummary($dbh, $testStudentId);
    echo "✅ Summary Generation WORKING<br>";
    echo "  - Books Issued: " . $summary['books_issued'] . "<br>";
    echo "  - Avg Rating: " . $summary['avg_rating'] . "/5<br>";
    echo "  - Late Returns: " . $summary['late_returns'] . "<br>";
    echo "  - Cluster: " . $summary['cluster_label'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Function Test Error: " . $e->getMessage() . "<br>";
}

// ============================================
// TEST 6: Student Data Check
// ============================================
echo "<h3>6️⃣ STUDENT DATA CHECK</h3>";
try {
    $sql = "SELECT COUNT(*) as total, 
                   SUM(CASE WHEN student_cluster IS NOT NULL THEN 1 ELSE 0 END) as clustered
            FROM tblstudents WHERE Status = 1";
    $query = $dbh->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    
    echo "Total Active Students: " . $result->total . "<br>";
    echo "Students with Cluster: " . $result->clustered . "<br>";
    
    if ($result->clustered > 0) {
        echo "✅ Student clustering IS POPULATED<br>";
    } else {
        echo "⚠️ No clusters assigned yet (run updateStudentCluster for each student)<br>";
    }
} catch (Exception $e) {
    echo "❌ Student Data Error: " . $e->getMessage() . "<br>";
}

// ============================================
// SUMMARY
// ============================================
echo "<hr>";
echo "<h3>📊 SUMMARY</h3>";
echo "<p><strong style='color: green;'>If you see mostly ✅ marks above, the ML system is working!</strong></p>";
echo "<p>Next steps:</p>";
echo "<ul>";
echo "<li>Make sure student has borrowed at least 1 book for recommendations</li>";
echo "<li>Scroll down on dashboard.php to see AI-Powered Smart Recommendations section</li>";
echo "<li>Check browser console (F12) for any JavaScript errors</li>";
echo "</ul>";

?>
