<!-- ===== HEADER START ===== -->
<div style="
    width:100%;
    background:linear-gradient(90deg,#0b2c5d,#1c3f75);
    padding:22px 40px;
    display:flex;
    align-items:center;
    gap:30px;
    box-shadow:0 4px 10px rgba(0,0,0,0.15);
">

    <!-- LOGO -->
    <img src="assets/img/cover.png"
         style="height:130px; width:auto; background:white; padding:8px; border-radius:6px;">

    <!-- TEXT -->
    <div style="color:white;">

        <h1 style="
    font-family: Georgia, 'Times New Roman', serif;
    font-weight: bold;
    color: #ffffff;
    font-size: 34px;
    margin: 0;
">
    Computer Science – AIML Department
       </h1>


        <p style="
            margin:6px 0 0;
            font-size:17px;
            font-style:italic;
            font-weight:500;
            color:#f2f2f2;
        ">
            Anjuman-I-Islam’s M.H. Saboo Siddik College of Engineering
        </p>

        <p style="
            margin-top:6px;
            font-size:14px;
            color:#d6d6d6;
            letter-spacing:0.5px;
        ">
            Online Library Management System
        </p>
    </div>
</div>

<!-- BOTTOM STRIP -->
<div style="height:6px; background:#f4b400;"></div>
<!-- ===== HEADER END ===== -->


<?php if($_SESSION['login'])
{
?> 
            <div class="right-div">
                <a href="logout.php" class="btn btn-danger pull-right">LOG ME OUT</a>
            </div>
            <?php }?>
        </div>
    </div>
    <!-- LOGO HEADER END-->
<?php if($_SESSION['login'])
{
?>    
<section class="menu-section">
        <div class="container">
            <div class="row ">
                <div class="col-md-12">
                    <div class="navbar-collapse collapse ">
                        <ul id="menu-top" class="nav navbar-nav navbar-right">
                            <li><a href="dashboard.php" class="menu-top-active">DASHBOARD</a></li>
                            <li><a href="issued-books.php">Issued Books</a></li>
                             <li>
                                <a href="#" class="dropdown-toggle" id="ddlmenuItem" data-toggle="dropdown"> Account <i class="fa fa-angle-down"></i></a>
                                <ul class="dropdown-menu" role="menu" aria-labelledby="ddlmenuItem">
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="my-profile.php">My Profile</a></li>
                                     <li role="presentation"><a role="menuitem" tabindex="-1" href="change-password.php">Change Password</a></li>
                                </ul>
                            </li>

                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <?php } else { ?>
        <section class="menu-section">
        <div class="container">
            <div class="row ">
                <div class="col-md-12">
                    <div class="navbar-collapse collapse ">
                        <ul id="menu-top" class="nav navbar-nav navbar-right">                        
                          
      <li><a href="index.php">Home</a></li>
      <li><a href="index.php#ulogin">User Login</a></li>
                            <li><a href="signup.php">User Signup</a></li>
                         
                            <li><a href="adminlogin.php">Admin Login</a></li>

                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php } ?>