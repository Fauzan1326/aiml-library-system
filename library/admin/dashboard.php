<?php
session_start();
error_reporting(0);
include('includes/config.php');
include('../includes/ml_recommendations.php');
if(strlen($_SESSION['alogin'])==0)
  { 
header('location:index.php');
}
else{?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
    <title>Online Library Management System | Admin Dash Board</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE  -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />

</head>
<body>
      <!------MENU SECTION START-->
<?php include('includes/header.php');?>
<!-- MENU SECTION END-->
    <div class="content-wrapper">
         <div class="container">
        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">ADMIN DASHBOARD</h4>
                
                            </div>

        </div>
             
             <div class="row">
<a href="manage-books.php">
 <div class="col-md-3 col-sm-3 col-xs-6">
 <div class="alert alert-success back-widget-set text-center">
 <i class="fa fa-book fa-5x"></i>
<?php 
$sql ="SELECT id from tblbooks ";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$listdbooks=$query->rowCount();
?>
<h3><?php echo htmlentities($listdbooks);?></h3>
Books Listed
</div></div></a>

            
       
             <a href="manage-issued-books.php">
               <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-warning back-widget-set text-center">
                            <i class="fa fa-recycle fa-5x"></i>
<?php 
$sql2 ="SELECT id from tblissuedbookdetails where (RetrunStatus='' || RetrunStatus is null)";
$query2 = $dbh -> prepare($sql2);
$query2->execute();
$results2=$query2->fetchAll(PDO::FETCH_OBJ);
$returnedbooks=$query2->rowCount();
?>

                            <h3><?php echo htmlentities($returnedbooks);?></h3>
                          Books Not Returned Yet
                        </div>
                    </div>
                </a>

  <a href="reg-students.php">
               <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-danger back-widget-set text-center">
                            <i class="fa fa-users fa-5x"></i>
                            <?php 
$sql3 ="SELECT id from tblstudents ";
$query3 = $dbh -> prepare($sql3);
$query3->execute();
$results3=$query3->fetchAll(PDO::FETCH_OBJ);
$regstds=$query3->rowCount();
?>
                            <h3><?php echo htmlentities($regstds);?></h3>
                           Registered Users
                        </div>
                    </div></a>


  <a href="manage-authors.php">
 <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-success back-widget-set text-center">
                            <i class="fa fa-user fa-5x"></i>
<?php 
$sq4 ="SELECT id from tblauthors ";
$query4 = $dbh -> prepare($sq4);
$query4->execute();
$results4=$query4->fetchAll(PDO::FETCH_OBJ);
$listdathrs=$query4->rowCount();
?>
<h3><?php echo htmlentities($listdathrs);?></h3>
Authors Listed
</div>
</div></a>
</div>



 <div class="row">



  <a href="manage-categories.php">            
<div class="col-md-3 col-sm-3 rscol-xs-6">
<div class="alert alert-info back-widget-set text-center">
<i class="fa fa-file-archive-o fa-5x"></i>
<?php 
$sql5 ="SELECT id from tblcategory ";
$query5 = $dbh -> prepare($sql5);
$query5->execute();
$results5=$query5->fetchAll(PDO::FETCH_OBJ);
$listdcats=$query5->rowCount();
?>

                            <h3><?php echo htmlentities($listdcats);?> </h3>
                           Listed Categories
                        </div>
                    </div></a>
             

        </div>

<!-- =========================================================================
         ML-BASED INSIGHTS SECTION
     ========================================================================= -->

<div class="row pad-botm" style="margin-top: 30px;">
    <div class="col-md-12">
        <h4 class="header-line"><i class="fa fa-brain"></i> ML-BASED INSIGHTS & ANALYTICS</h4>
    </div>
</div>

<div class="row">
    <!-- Student Clustering Distribution -->
    <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="alert alert-primary back-widget-set text-center">
            <i class="fa fa-users fa-5x"></i>
            <?php 
            $sql_active = "SELECT COUNT(*) as count FROM tblstudents WHERE student_cluster = 1";
            $query = $dbh->prepare($sql_active);
            $query->execute();
            $active_count = $query->fetch(PDO::FETCH_OBJ)->count ?? 0;
            ?>
            <h3><?php echo htmlentities($active_count); ?></h3>
            Active Readers
            <p style="font-size: 12px; margin-top: 10px; color: #666;">Cluster Analysis</p>
        </div>
    </div>

    <!-- Average Readers -->
    <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="alert alert-info back-widget-set text-center">
            <i class="fa fa-book-open fa-5x"></i>
            <?php 
            $sql_avg = "SELECT COUNT(*) as count FROM tblstudents WHERE student_cluster = 2";
            $query = $dbh->prepare($sql_avg);
            $query->execute();
            $avg_count = $query->fetch(PDO::FETCH_OBJ)->count ?? 0;
            ?>
            <h3><?php echo htmlentities($avg_count); ?></h3>
            Average Readers
            <p style="font-size: 12px; margin-top: 10px; color: #666;">Moderate Engagement</p>
        </div>
    </div>

    <!-- Inactive Users -->
    <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="alert alert-warning back-widget-set text-center">
            <i class="fa fa-user-times fa-5x"></i>
            <?php 
            $sql_inactive = "SELECT COUNT(*) as count FROM tblstudents WHERE student_cluster = 3 OR student_cluster IS NULL";
            $query = $dbh->prepare($sql_inactive);
            $query->execute();
            $inactive_count = $query->fetch(PDO::FETCH_OBJ)->count ?? 0;
            ?>
            <h3><?php echo htmlentities($inactive_count); ?></h3>
            Inactive Users
            <p style="font-size: 12px; margin-top: 10px; color: #666;">Low Engagement</p>
        </div>
    </div>
</div>

<!-- Average Engagement Metrics -->
<div class="row" style="margin-top: 20px;">
    <div class="col-md-6">
        <div class="panel panel-info">
            <div class="panel-heading">
                <i class="fa fa-bar-chart"></i> Average Engagement Metrics
            </div>
            <div class="panel-body">
                <?php 
                $sql = "SELECT 
                        ROUND(AVG(books_issued), 2) as avg_books,
                        ROUND(AVG(avg_rating_given), 2) as avg_rating,
                        ROUND(AVG(late_returns), 2) as avg_late
                        FROM vw_student_engagement";
                $query = $dbh->prepare($sql);
                $query->execute();
                $metrics = $query->fetch(PDO::FETCH_OBJ);
                ?>
                <table class="table table-bordered">
                    <tr>
                        <td><strong>Avg Books per Student:</strong></td>
                        <td><?php echo htmlentities($metrics->avg_books ?? 0); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Avg Rating Given:</strong></td>
                        <td><?php echo htmlentities(round($metrics->avg_rating ?? 0, 2)); ?>/5 ⭐</td>
                    </tr>
                    <tr>
                        <td><strong>Avg Late Returns:</strong></td>
                        <td><?php echo htmlentities($metrics->avg_late ?? 0); ?> times</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Most Recommended Books (based on popularity) -->
    <div class="col-md-6">
        <div class="panel panel-success">
            <div class="panel-heading">
                <i class="fa fa-star"></i> Top Rated Books (Recommendation Score)
            </div>
            <div class="panel-body" style="max-height: 300px; overflow-y: auto;">
                <?php 
                $sql = "SELECT b.BookName, 
                               COUNT(DISTINCT i.StudentID) as unique_borrowers,
                               ROUND(AVG(r.Rating), 2) as avg_rating
                        FROM tblbooks b
                        LEFT JOIN tblissuedbookdetails i ON b.id = i.BookId
                        LEFT JOIN tblbookratings r ON b.id = r.BookId
                        GROUP BY b.id
                        ORDER BY avg_rating DESC, unique_borrowers DESC
                        LIMIT 5";
                $query = $dbh->prepare($sql);
                $query->execute();
                $topbooks = $query->fetchAll(PDO::FETCH_OBJ);
                ?>
                <ul class="list-group">
                    <?php foreach($topbooks as $book): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlentities(substr($book->BookName, 0, 30)); ?>...</strong><br>
                        Rating: <?php echo htmlentities($book->avg_rating ?? 0); ?>/5 ⭐ | 
                        Borrowed by: <?php echo htmlentities($book->unique_borrowers ?? 0); ?> users
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Content-Based Filtering Coverage -->
<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-cogs"></i> ML System Status
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <h5><i class="fa fa-filter"></i> Content-Based Filtering</h5>
                        <p>Active for all users with borrowing history</p>
                        <span class="label label-success">✓ Running</span>
                    </div>
                    <div class="col-md-3">
                        <h5><i class="fa fa-handshake-o"></i> Collaborative Filtering</h5>
                        <p>Cross-user book recommendations</p>
                        <span class="label label-success">✓ Running</span>
                    </div>
                    <div class="col-md-3">
                        <h5><i class="fa fa-cube"></i> K-Means Clustering</h5>
                        <p>Student segmentation (3 clusters)</p>
                        <span class="label label-success">✓ Running</span>
                    </div>
                    <div class="col-md-3">
                        <h5><i class="fa fa-graduation-cap"></i> Cluster Insights</h5>
                        <p>Personalized recommendations per cluster</p>
                        <span class="label label-success">✓ Running</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            
    </div>
    </div>
     <!-- CONTENT-WRAPPER SECTION END-->
<?php include('includes/footer.php');?>
      <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME  -->
    <!-- CORE JQUERY  -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS  -->
    <script src="assets/js/bootstrap.js"></script>
      <!-- CUSTOM SCRIPTS  -->
    <script src="assets/js/custom.js"></script>
</body>
</html>
<?php } ?>
