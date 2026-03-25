<?php
session_start();
include('includes/config.php');

if(strlen($_SESSION['alogin'])==0) {
    header('location:index.php');
    exit;
}

// DELETE PAPER
if(isset($_GET['del'])) {

    $id = $_GET['del'];

    // Get file name
    $sql = "SELECT pdf_file FROM tblpapers WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_OBJ);

    if($row){
        $file = "uploads/".$row->pdf_file;
        if(file_exists($file)){
            unlink($file);
        }
    }

    // Delete record
    $sql = "DELETE FROM tblpapers WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id);
    $query->execute();

    echo "<script>alert('Paper Deleted Successfully');</script>";
    echo "<script>window.location='manage-papers.php';</script>";
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Manage Research Papers</title>
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
                <h4 class="header-line">MANAGE RESEARCH PAPERS</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        Research Paper List
                    </div>

                    <div class="panel-body">
                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Type</th>
                                        <th>Year</th>
                                        <th>View</th>
                                        <th>Download</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                <?php
                                $sql = "SELECT * FROM tblpapers ORDER BY id DESC";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                $cnt = 1;

                                if($query->rowCount() > 0){
                                    foreach($results as $row){
                                ?>
                                    <tr>
                                        <td><?php echo $cnt++; ?></td>
                                        <td><?php echo htmlentities($row->title); ?></td>
                                        <td><?php echo htmlentities($row->author); ?></td>
                                        <td><?php echo htmlentities($row->paper_type); ?></td>
                                        <td><?php echo htmlentities($row->year); ?></td>

                                        <td>
                                            <a href="uploads/<?php echo $row->pdf_file; ?>" 
                                               target="_blank" 
                                               class="btn btn-info btn-xs">
                                               View
                                            </a>
                                        </td>

                                        <td>
                                            <a href="uploads/<?php echo $row->pdf_file; ?>" 
                                               download 
                                               class="btn btn-success btn-xs">
                                               Download
                                            </a>
                                        </td>

                                        <td>
                                            <a href="manage-papers.php?del=<?php echo $row->id; ?>"
                                               onclick="return confirm('Are you sure you want to delete this paper?');"
                                               class="btn btn-danger btn-xs">
                                               Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php } } else { ?>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            No Research Papers Found
                                        </td>
                                    </tr>
                                <?php } ?>

                                </tbody>
                            </table>

                        </div>
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
