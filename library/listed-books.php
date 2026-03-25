<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['login'])==0){
    header('location:index.php');
    exit();
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title>Online Library Management System | Books</title>

    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>

<body>

<?php include('includes/header.php'); ?>

<div class="content-wrapper">
<div class="container">

<!-- HEADER -->
<div class="row pad-botm">
    <div class="col-md-12">
        <h4 class="header-line">
            <?php echo isset($_GET['popular']) ? "🔥 Most Popular Books" : "📚 All Books"; ?>
        </h4>
    </div>
</div>

<div class="row">
<div class="col-md-12">
<div class="panel panel-default">
<div class="panel-heading">
    <?php echo isset($_GET['popular']) ? "Popular Books" : "Available Books"; ?>
</div>

<div class="panel-body">

<?php
/* ============================
   QUERY SECTION
============================= */

if(isset($_GET['popular'])){

    // MOST POPULAR BOOKS
    $sql = "
        SELECT 
            tblbooks.*,
            tblauthors.AuthorName,
            COUNT(tblissuedbookdetails.BookId) AS totalIssued
        FROM tblissuedbookdetails
        JOIN tblbooks ON tblissuedbookdetails.BookId = tblbooks.id
        LEFT JOIN tblauthors ON tblauthors.id = tblbooks.AuthorId
        GROUP BY tblissuedbookdetails.BookId
        ORDER BY totalIssued DESC
    ";

} else {

    // NORMAL LIST
    $sql = "
        SELECT 
            tblbooks.*,
            tblauthors.AuthorName,
            tblcategory.CategoryName,
            COUNT(tblissuedbookdetails.id) AS issuedBooks,
            COUNT(tblissuedbookdetails.RetrunStatus) AS returnedbook
        FROM tblbooks
        LEFT JOIN tblissuedbookdetails ON tblissuedbookdetails.BookId = tblbooks.id
        LEFT JOIN tblauthors ON tblauthors.id = tblbooks.AuthorId
        LEFT JOIN tblcategory ON tblcategory.id = tblbooks.CatId
        GROUP BY tblbooks.id
    ";
}

$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if($query->rowCount() > 0){
foreach($results as $row){
?>

<!-- BOOK CARD -->
<div class="col-md-4" style="height:380px;">
<table class="table table-bordered">

<tr>
    <td rowspan="2">
        <img src="admin/bookimg/<?php echo htmlentities($row->bookImage); ?>" width="120">
    </td>
    <th>Book</th>
    <td><?php echo htmlentities($row->BookName); ?></td>
</tr>

<tr>
    <th>Author</th>
    <td><?php echo htmlentities($row->AuthorName ?? 'N/A'); ?></td>
</tr>

<tr>
    <th>ISBN</th>
    <td colspan="2"><?php echo htmlentities($row->ISBNNumber); ?></td>
</tr>

<tr>
    <th>Quantity</th>
    <td colspan="2"><?php echo htmlentities($row->bookQty); ?></td>
</tr>

<tr>
    <th>Available</th>
    <td colspan="2">
        <?php
        if(isset($row->issuedBooks)){
            echo $row->bookQty - ($row->issuedBooks - $row->returnedbook);
        } else {
            echo "—";
        }
        ?>
    </td>
</tr>

<?php if(isset($_GET['popular'])){ ?>
<tr>
    <th>Issued</th>
    <td colspan="2"><?php echo $row->totalIssued; ?> times</td>
</tr>
<?php } ?>

<tr>
    <td colspan="3" class="text-center">
        <!-- RATE BUTTON -->
        <a href="rate-book.php?bookid=<?php echo $row->id; ?>"
           class="btn btn-primary btn-sm">
            ⭐ Rate Book
        </a>
    </td>
</tr>

</table>
</div>

<?php }} else { ?>

<p class="text-center text-muted">No books found.</p>

<?php } ?>

</div>
</div>
</div>
</div>
</div>

<?php include('includes/footer.php'); ?>

<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/dataTables/jquery.dataTables.js"></script>
<script src="assets/js/dataTables/dataTables.bootstrap.js"></script>

</body>
</html>
