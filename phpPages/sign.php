<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../cssPages/style.css">
    <title>signUp</title>
</head>
<body>
    <div id="container">
        <div id="left">
            <div id="sub-left">
                <h1 class="text-heading">Create an account <br> <small>simplify how you manage</small> <br> <small>students and staff.</small></h1>
            </div>
        </div>
        <div id="right">
            <div id="sub-right">
                
                <form action="../phpPages/signUp.php" method="post" id="myForm-signup"  class="myForm">
                    <h1>Create an account</h1>
                    <label for="instituteName">Institute Name</label>
                    <input type="text" id="instituteName" name="instituteName" placeholder="ABC Institute">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="example77@gmail.com">
                    <label for="password">password (6 or more characters)</label>
                    <input type="password" id="password" name="password" placeholder="We@3ty">
                    <label for="confirmPassword">confirm password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="We@3ty">
                    <button type="submit">Sign Up</button>
                    <p>Do you already have an account? <a href="log.php">LogIn</a></p>
                </form>
            </div>
        </div>
    </div>

    
</body>
<script src="../jsPages/script.js"></script>
</html>