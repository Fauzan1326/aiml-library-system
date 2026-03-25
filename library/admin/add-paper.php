<?php
session_start();
include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) {
    header('location:index.php');
}

if(isset($_POST['submit'])) {

    $title = $_POST['title'];
    $author = $_POST['author'];
    $type = $_POST['paper_type'];
    $year = $_POST['year'];
    $abstract = $_POST['abstract'];
    $keywords = $_POST['keywords'];

    $pdf = $_FILES['pdf']['name'];
    move_uploaded_file($_FILES['pdf']['tmp_name'], "uploads/".$pdf);

    $sql = "INSERT INTO tblpapers 
    (title, author, paper_type, year, abstract, keywords, pdf_file) 
    VALUES (:title,:author,:type,:year,:abstract,:keywords,:pdf)";

    $query = $dbh->prepare($sql);
    $query->bindParam(':title',$title);
    $query->bindParam(':author',$author);
    $query->bindParam(':type',$type);
    $query->bindParam(':year',$year);
    $query->bindParam(':abstract',$abstract);
    $query->bindParam(':keywords',$keywords);
    $query->bindParam(':pdf',$pdf);
    $query->execute();

    echo "<script>alert('Research Paper Added Successfully');</script>";
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Add Research Paper</title>
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
                <h4 class="header-line">ADD RESEARCH PAPER</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        Research Paper Details
                    </div>
                    <div class="panel-body">

                        <form method="post" enctype="multipart/form-data">

                            <div class="form-group">
                                <label>Paper Title</label>
                                <input type="text" name="title" class="form-control" required />
                            </div>

                            <div class="form-group">
                                <label>Author Name</label>
                                <input type="text" name="author" class="form-control" required />
                            </div>

                            <div class="form-group">
                                <label>Paper Type</label>
                                <select class="form-control" name="paper_type" required>
                                    <option value="">Select Type</option>
                                    <option>Research</option>
                                    <option>Review</option>
                                    <option>Technical</option>
                                    <option>Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="year" class="form-control" required />
                            </div>

                            <div class="form-group">
                                <label>Abstract</label>
                                <textarea name="abstract" class="form-control" rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Keywords</label>
                                <input type="text" name="keywords" class="form-control" />
                            </div>

                            <div class="form-group">
                                <label>Upload PDF</label>
                                <input type="file" name="pdf" class="form-control" required />
                            </div>

                            <button type="submit" name="submit" class="btn btn-success">
                                Upload Paper
                            </button>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
</body>
</html>
