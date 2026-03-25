<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['login'])==0)
{
    header('location:index.php');
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title>Research Papers</title>

    <!-- BOOTSTRAP -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>

<body>

<!-- HEADER -->
<?php include('includes/header.php'); ?>

<!-- CONTENT -->
<div class="content-wrapper">
    <div class="container">

        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">Available Research Papers</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        Research Papers List
                    </div>

                    <div class="panel-body">
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
                                </tr>
                            </thead>
                            <tbody>

                            <?php
                            $sql = "SELECT * FROM tblpapers ORDER BY id DESC";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                            $cnt = 1;

                            if($query->rowCount() > 0)
                            {
                                foreach($results as $row)
                                {
                            ?>
                                <tr>
                                    <td><?php echo $cnt++; ?></td>
                                    <td><?php echo htmlentities($row->title); ?></td>
                                    <td><?php echo htmlentities($row->author); ?></td>
                                    <td><?php echo htmlentities($row->paper_type); ?></td>
                                    <td><?php echo htmlentities($row->year); ?></td>

                                    <td>
                                        <a href="admin/uploads/<?php echo $row->pdf_file; ?>" 
                                           target="_blank"
                                           class="btn btn-info btn-sm">
                                            View
                                        </a>
                                    </td>

                                    <td>
                                        <a href="admin/uploads/<?php echo $row->pdf_file; ?>" 
                                           download
                                           class="btn btn-success btn-sm">
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } 
                            else 
                            { 
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        No Research Papers Available
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

<!-- FOOTER -->
<?php include('includes/footer.php'); ?>

<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/custom.js"></script>

</body>
</html>
