<?php
session_start();
error_reporting(0);
include('includes/config.php');
include('includes/ml_recommendations.php');

if(strlen($_SESSION['login']) == 0){
    header('location:index.php');
    exit;
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Dashboard | Online Library</title>

    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body>

<?php include('includes/header.php'); ?>

<div class="content-wrapper">
<div class="container">

<!-- DASHBOARD TITLE -->
<div class="row pad-botm">
    <div class="col-md-12">
        <h4 class="header-line">User Dashboard</h4>
    </div>
</div>

<div class="row">

<!-- BOOK COUNT -->
<a href="listed-books.php">
<div class="col-md-4 col-sm-6">
    <div class="alert alert-success text-center">
        <i class="fa fa-book fa-4x"></i>
        <?php
        $sql = "SELECT id FROM tblbooks";
        $query = $dbh->prepare($sql);
        $query->execute();
        ?>
        <h3><?php echo $query->rowCount(); ?></h3>
        Books Listed
    </div>
</div>
</a>

<!-- NOT RETURNED -->
<div class="col-md-4 col-sm-6">
    <div class="alert alert-warning text-center">
        <i class="fa fa-recycle fa-4x"></i>
        <?php
        $sid = $_SESSION['stdid'];
        $sql = "SELECT id FROM tblissuedbookdetails 
                WHERE StudentID=:sid AND (RetrunStatus IS NULL OR RetrunStatus=0)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':sid',$sid,PDO::PARAM_STR);
        $query->execute();
        ?>
        <h3><?php echo $query->rowCount(); ?></h3>
        Books Not Returned
    </div>
</div>

<!-- ISSUED -->
<a href="issued-books.php">
<div class="col-md-4 col-sm-6">
    <div class="alert alert-success text-center">
        <i class="fa fa-book fa-4x"></i>
        <?php
        $sql = "SELECT id FROM tblissuedbookdetails WHERE StudentID=:sid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':sid',$sid,PDO::PARAM_STR);
        $query->execute();
        ?>
        <h3><?php echo $query->rowCount(); ?></h3>
        Total Issued Books
    </div>
</div>
</a>

<!-- PAPERS -->
<a href="papers.php">
<div class="col-md-4 col-sm-6">
    <div class="alert alert-info text-center">
        <i class="fa fa-file-pdf-o fa-4x"></i>
        <h3>Papers</h3>
        View Research Papers
    </div>
</div>
</a>

</div>

<!-- ================= SMART BOOK RECOMMENDATIONS ================= -->

<div class="row pad-botm">
    <div class="col-md-12">
        <h4 class="header-line">📚 Smart Book Recommendations</h4>
    </div>
</div>

<?php
$sid = $_SESSION['stdid'];
$shownBooks = [];
?>

<div class="row">

<!-- ================= 1️⃣ LAST ISSUED BOOK ================= -->
<div class="col-md-4">
    <h5>📘 Based on Your Last Issued Book</h5>

    <?php
    $sql = "SELECT BookId FROM tblissuedbookdetails 
            WHERE StudentID = :sid 
            ORDER BY id DESC LIMIT 1";
    $q = $dbh->prepare($sql);
    $q->bindParam(':sid', $sid);
    $q->execute();

    if ($q->rowCount()) {

        $lastBook = $q->fetch(PDO::FETCH_OBJ)->BookId;

        $q2 = $dbh->prepare("SELECT CatId FROM tblbooks WHERE id = :id");
        $q2->bindParam(':id', $lastBook);
        $q2->execute();
        $catId = $q2->fetch(PDO::FETCH_OBJ)->CatId;

        $q3 = $dbh->prepare("
            SELECT * FROM tblbooks 
            WHERE CatId = :cat AND id != :bid 
            LIMIT 1
        ");
        $q3->bindParam(':cat', $catId);
        $q3->bindParam(':bid', $lastBook);
        $q3->execute();

        foreach ($q3->fetchAll(PDO::FETCH_OBJ) as $b) {
            $shownBooks[] = $b->id;
    ?>
        <div class="alert alert-info text-center" style="height:220px;">
            <i class="fa fa-book fa-3x"></i>
            <h4><?= htmlentities($b->BookName) ?></h4>
            <small>Similar category</small>
        </div>
    <?php
        }
    } else {
        echo "<p class='text-muted'>No history yet</p>";
    }
    ?>
</div>


<!-- ================= 2️⃣ FAVORITE CATEGORY ================= -->
<div class="col-md-4">
    <h5>📗 Your Favorite Category</h5>

    <?php
    $sql = "
        SELECT b.CatId, COUNT(*) AS total
        FROM tblissuedbookdetails i
        JOIN tblbooks b ON i.BookId = b.id
        WHERE i.StudentID = :sid
        GROUP BY b.CatId
        ORDER BY total DESC
        LIMIT 1
    ";
    $q = $dbh->prepare($sql);
    $q->bindParam(':sid', $sid);
    $q->execute();

    if ($q->rowCount()) {

        $favCat = $q->fetch(PDO::FETCH_OBJ)->CatId;

        $q2 = $dbh->prepare("
            SELECT * FROM tblbooks 
            WHERE CatId = :cat 
            AND id NOT IN (" . implode(',', $shownBooks ?: [0]) . ")
            LIMIT 1
        ");
        $q2->bindParam(':cat', $favCat);
        $q2->execute();

        foreach ($q2->fetchAll(PDO::FETCH_OBJ) as $b) {
            $shownBooks[] = $b->id;
    ?>
        <div class="alert alert-success text-center" style="height:220px;">
            <i class="fa fa-heart fa-3x"></i>
            <h4><?= htmlentities($b->BookName) ?></h4>
            <small>Your preferred category</small>
        </div>
    <?php
        }
    } else {
        echo "<p class='text-muted'>No preference yet</p>";
    }
    ?>
</div>


<!-- ================= 3️⃣ MOST POPULAR ================= -->
<div class="col-md-4">
    <h5>🔥 Most Popular Books</h5>

    <?php
    $sql = "
        SELECT b.id, b.BookName, COUNT(i.BookId) AS total
        FROM tblissuedbookdetails i
        JOIN tblbooks b ON i.BookId = b.id
        GROUP BY i.BookId
        ORDER BY total DESC
        LIMIT 1
    ";
    $q = $dbh->prepare($sql);
    $q->execute();

    foreach ($q->fetchAll(PDO::FETCH_OBJ) as $b) {
        if (in_array($b->id, $shownBooks)) continue;
    ?>
        <div class="alert alert-warning text-center" style="height:220px;">
            <i class="fa fa-fire fa-3x"></i>
            <h4><?= htmlentities($b->BookName) ?></h4>
            <small>Issued <?= $b->total ?> times</small><br><br>
            <a href="listed-books.php?popular=1" class="btn btn-warning btn-sm">
                View
            </a>
        </div>
    <?php } ?>
</div>

</div>

<!-- ================= TOP RATED BOOKS ================= -->

<hr>

<div class="row">
    <div class="col-md-12">
        <h5>⭐ Top Rated Books</h5>
    </div>

<?php
$sql = "
    SELECT b.BookName, ROUND(AVG(r.Rating),1) AS avg_rating
    FROM tblbookratings r
    JOIN tblbooks b ON r.BookId = b.id
    GROUP BY r.BookId
    ORDER BY avg_rating DESC
    LIMIT 4
";
$q = $dbh->prepare($sql);
$q->execute();

foreach ($q->fetchAll(PDO::FETCH_OBJ) as $row) {
?>
    <div class="col-md-3">
        <div class="alert alert-success text-center">
            <i class="fa fa-star fa-2x"></i>
            <h4><?= $row->BookName ?></h4>
            <p>⭐ <?= $row->avg_rating ?>/5</p>
        </div>
    </div>
<?php } ?>
</div>

<!-- ============================================================================
     ML-BASED INTELLIGENT RECOMMENDATION SYSTEM
     Content-Based Filtering + Collaborative Filtering + Clustering
     ============================================================================ -->

<hr style="margin-top: 40px; margin-bottom: 40px;">

<div class="row pad-botm">
    <div class="col-md-12">
        <h4 class="header-line"><i class="fa fa-brain"></i> AI-Powered Smart Recommendations</h4>
        <p style="color: #666; font-size: 13px;">Personalized recommendations using ML algorithms</p>
    </div>
</div>

<?php
$sid = $_SESSION['stdid'];

// Get student's cluster and summary
$student_summary = getStudentSummary($dbh, $sid);
$cluster = $student_summary['cluster'];
$cluster_label = $student_summary['cluster_label'];
$cluster_description = $student_summary['cluster_description'];

// Update student's cluster in database
updateStudentCluster($dbh, $sid);
?>

<!-- ============================================================================
     SECTION 1: STUDENT CLUSTER & PROFILE
     ============================================================================ -->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="fa fa-user-circle"></i> Your Reader Profile (ML Clustering)
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div style="padding: 20px; background: #f5f5f5; border-radius: 5px;">
                            <h3 style="color: #007bff; margin-top: 0;">📊 <?php echo htmlentities($cluster_label); ?></h3>
                            <p style="font-size: 16px; line-height: 1.6;">
                                <?php echo htmlentities($cluster_description); ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-condensed" style="background: #f9f9f9; border-radius: 5px;">
                            <tr>
                                <td><strong><i class="fa fa-book"></i> Books Issued:</strong></td>
                                <td><strong><?php echo htmlentities($student_summary['books_issued']); ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong><i class="fa fa-star"></i> Avg Rating Given:</strong></td>
                                <td><strong><?php echo htmlentities($student_summary['avg_rating']); ?>/5 ⭐</strong></td>
                            </tr>
                            <tr>
                                <td><strong><i class="fa fa-exclamation-triangle"></i> Late Returns:</strong></td>
                                <td><strong><?php echo htmlentities($student_summary['late_returns']); ?> times</strong></td>
                            </tr>
                            <tr>
                                <td><strong><i class="fa fa-cube"></i> Your Cluster:</strong></td>
                                <td><span class="label label-primary"><?php echo htmlentities($cluster); ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     SECTION 2: CONTENT-BASED FILTERING (Using Similarity Scoring)
     ============================================================================ -->

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <h5><i class="fa fa-filter"></i> <strong>Content-Based Filtering</strong> - Similar Books Based on Category, Author & Ratings</h5>
    </div>
</div>

<div class="row" style="margin-bottom: 30px;">
    <?php
    $content_recommendations = getContentBasedRecommendations($dbh, $sid, 3);
    
    if (!empty($content_recommendations)) {
        foreach ($content_recommendations as $book) {
            $book_rating = getAverageRating($dbh, $book->id);
            ?>
            <div class="col-md-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <strong><?php echo htmlentities(substr($book->BookName, 0, 40)); ?></strong>
                    </div>
                    <div class="panel-body text-center">
                        <i class="fa fa-book fa-4x" style="color: #0275d8;"></i>
                        <h5 style="margin-top: 10px;">Similarity Score</h5>
                        <h3 style="color: #0275d8; margin: 10px 0;"><?php echo htmlentities($book->similarity_score); ?>%</h3>
                        <p><strong>Rating:</strong> ⭐ <?php echo htmlentities(round($book_rating, 1)); ?>/5</p>
                        <p><strong>Category:</strong> 
                            <?php
                            $sql = "SELECT CategoryName FROM tblcategory WHERE id = :catid";
                            $q = $dbh->prepare($sql);
                            $q->bindParam(':catid', $book->CatId);
                            $q->execute();
                            $cat = $q->fetch(PDO::FETCH_OBJ);
                            echo htmlentities($cat->CategoryName ?? 'Unknown');
                            ?>
                        </p>
                        <a href="listed-books.php?search=<?php echo urlencode($book->BookName); ?>" class="btn btn-info btn-sm">
                            View Book
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-md-12"><p class="text-muted">Issue a book first to get personalized recommendations.</p></div>';
    }
    ?>
</div>

<!-- ============================================================================
     SECTION 3: COLLABORATIVE FILTERING (Co-Occurrence Based)
     ============================================================================ -->

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <h5><i class="fa fa-handshake-o"></i> <strong>Collaborative Filtering</strong> - "Users who read what you read also read..."</h5>
    </div>
</div>

<div class="row" style="margin-bottom: 30px;">
    <?php
    $collab_recommendations = getCollaborativeFilteringRecommendations($dbh, $sid, 3);
    
    if (!empty($collab_recommendations)) {
        foreach ($collab_recommendations as $book) {
            $book_rating = getAverageRating($dbh, $book->id);
            ?>
            <div class="col-md-4">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <strong><?php echo htmlentities(substr($book->BookName, 0, 40)); ?></strong>
                    </div>
                    <div class="panel-body text-center">
                        <i class="fa fa-users fa-4x" style="color: #28a745;"></i>
                        <h5 style="margin-top: 10px;">Co-Occurrence Score</h5>
                        <h3 style="color: #28a745; margin: 10px 0;"><?php echo htmlentities($book->co_occurrence_count); ?></h3>
                        <p><small>Users with similar interests also borrowed this</small></p>
                        <p><strong>Rating:</strong> ⭐ <?php echo htmlentities(round($book_rating, 1)); ?>/5</p>
                        <a href="listed-books.php?search=<?php echo urlencode($book->BookName); ?>" class="btn btn-success btn-sm">
                            View Book
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-md-12"><p class="text-muted">Collaborate recommendations will appear as more users interact with the system.</p></div>';
    }
    ?>
</div>

<!-- ============================================================================
     SECTION 4: CLUSTER-BASED RECOMMENDATIONS (Personalized by Reader Type)
     ============================================================================ -->

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <h5><i class="fa fa-cube"></i> <strong>Cluster-Based Recommendations for <?php echo htmlentities($cluster_label); ?>s</strong></h5>
    </div>
</div>

<div class="row" style="margin-bottom: 30px;">
    <?php
    $cluster_recommendations = getClusterBasedRecommendations($dbh, $cluster, 3);
    
    if (!empty($cluster_recommendations)) {
        foreach ($cluster_recommendations as $book) {
            $book_rating = getAverageRating($dbh, $book->id);
            ?>
            <div class="col-md-4">
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <strong><?php echo htmlentities(substr($book->BookName, 0, 40)); ?></strong>
                    </div>
                    <div class="panel-body text-center">
                        <i class="fa fa-lightbulb-o fa-4x" style="color: #ffc107;"></i>
                        <h5 style="margin-top: 10px;">Popular in Your Category</h5>
                        <p><strong>Rating:</strong> ⭐ <?php echo htmlentities(round($book_rating, 1)); ?>/5</p>
                        <p><strong>Popularity:</strong> <?php echo htmlentities($book->popularity ?? 0); ?> borrows</p>
                        <p><small>Recommended for <?php echo htmlentities($cluster_label); ?>s</small></p>
                        <a href="listed-books.php?search=<?php echo urlencode($book->BookName); ?>" class="btn btn-warning btn-sm">
                            View Book
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-md-12"><p class="text-muted">Cluster recommendations will appear soon.</p></div>';
    }
    ?>
</div>

<!-- ============================================================================
     SECTION 5: MODULE INFORMATION
     ============================================================================ -->

<div class="row" style="margin-top: 30px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-info-circle"></i> ML Recommendation System Info
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <h5><i class="fa fa-filter"></i> Content-Based Filtering</h5>
                        <p style="font-size: 12px;">Calculates similarity between books using category, author, rating, and popularity features. Weights: Category (3), Author (2), Rating (1), Popularity (1).</p>
                    </div>
                    <div class="col-md-4">
                        <h5><i class="fa fa-handshake-o"></i> Collaborative Filtering</h5>
                        <p style="font-size: 12px;">Finds users with similar borrowing patterns and recommends books they've borrowed. Based on co-occurrence frequency analysis.</p>
                    </div>
                    <div class="col-md-4">
                        <h5><i class="fa fa-cube"></i> K-Means Clustering</h5>
                        <p style="font-size: 12px;">Segments students into 3 clusters (Active, Average, Inactive) based on normalized features: books issued, avg rating, and late returns.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/custom.js"></script>
</body>
</html>
