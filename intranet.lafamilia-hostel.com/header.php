<body>
    <div class="topbar">
        <div class="col-5 col-md-8 align-self-center">
            <h5><?php echo $title ?></h5>
        </div>
        <div id="header-role-DD" class="col-4 col-md-2 align-self-center">
<?php
        if ($_SESSION["role"]!=99) {
?>
            <select class="form-control" name="ses_role" required/>
                <option value="Role">Role</option>
            </select>
<?php
        }
?>
        </div>
            <div class="col-2 col-md-2 align-self-center">
    		    <img class="head-logo" src="https://www.lafamilia-hostel.com/wp/wp-content/uploads/2022/09/hostel1-01-scaled-e1662067915888.jpg" alt="" width="100">
            </div>
    </div>