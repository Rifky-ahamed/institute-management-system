<?php if (isset($_GET['error'])): ?>
     <script>
        <?php
        switch ($_GET['error']) {
            case 'invalid_password':
                echo 'alert("Incorrect password.");';
                break;
            case 'user_not_found':
                echo 'alert("User not found.");';
                break;
            default:
                echo 'alert("Unknown error occurred.");';
        }
        ?>
    </script>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../cssPages/style.css">
    <title>Login</title>
</head>
<body>
    <div id="container">
        <div id="left-second">
            <div id="sub-left-second">
                <form action="../phpPages/logIn.php" method="post" id="myForm-login" class="myForm">
                    <h1>Create an account</h1>
                    <label for="email">email</label>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com">
                    <label for="password">password (6 or more characters)</label>
                    <input type="password" id="password" name="password" placeholder="We@3ty">
                    <button type="submit">Log In</button>
                    <p>Don't have an account yet? <a href="sign.php">SignUp</a></p>
                </form>
            </div>
        </div>
        <div id="right-second">
            <div id="sub-right-second">
                
                <h1 id="text">Log in to<br> <small>simplify how you manage </small> <br> <small>students and staff.</small></h1>
            </div>
        </div>
    </div>
</body>
<script src="../jsPages/script.js"></script>
</html>