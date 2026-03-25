<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['login'])==0){
    header('location:index.php');
    exit();
}

$bookid = intval($_GET['bookid']);
$userid = $_SESSION['stdid'];

/* SUBMIT RATING */
if(isset($_POST['submit'])){

    $rating = $_POST['rating'];
    $review = $_POST['review'];

    $sql = "INSERT INTO tblbookratings(BookId, StudentId, Rating, Review)
            VALUES(:bid, :sid, :rating, :review)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bid', $bookid);
    $query->bindParam(':sid', $userid);
    $query->bindParam(':rating', $rating);
    $query->bindParam(':review', $review);
    $query->execute();

    echo "<script>alert('Thank you for rating this book!');</script>";
    echo "<script>window.location='listed-books.php';</script>";
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title>Rate Book</title>

    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>

<body>

<?php include('includes/header.php'); ?>

<div class="content-wrapper">
<div class="container">

<div class="row pad-botm">
    <div class="col-md-12">
        <h4 class="header-line">Rate This Book</h4>
    </div>
</div>

<div class="row">
<div class="col-md-8 col-md-offset-2">

<div class="panel panel-info">
    <div class="panel-heading">
        <i class="fa fa-star"></i> Book Rating
    </div>

    <div class="panel-body">

        <form method="post">

            <div class="form-group">
                <label>Rating (1 to 5)</label>
                <select name="rating" class="form-control" required>
                    <option value="">-- Select Rating --</option>
                    <option value="1">⭐ 1 - Poor</option>
                    <option value="2">⭐⭐ 2 - Fair</option>
                    <option value="3">⭐⭐⭐ 3 - Good</option>
                    <option value="4">⭐⭐⭐⭐ 4 - Very Good</option>
                    <option value="5">⭐⭐⭐⭐⭐ 5 - Excellent</option>
                </select>
            </div>

            <div class="form-group">
                <label>Review (Optional)</label>
                <textarea name="review" class="form-control" rows="4"
                          placeholder="Write your feedback here..."></textarea>
            </div>

            <div class="text-center">
                <button type="submit" name="submit" class="btn btn-success">
                    <i class="fa fa-check"></i> Submit Rating
                </button>

                <a href="listed-books.php" class="btn btn-default">
                    Cancel
                </a>
            </div>

        </form>

    </div>
</div>

</div>
</div>

</div>
</div>

<?php include('includes/footer.php'); ?>

<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>

</body>
</html>
