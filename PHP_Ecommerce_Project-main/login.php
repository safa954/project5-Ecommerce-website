<?php

$connection = mysqli_connect("localhost", "root", "", "ecommerce");
session_start();

$imagePath = "";
$errors = [];
// Validation Function
function check($name, $email, $pass, $confirmPassword)
{
	global $errors;
	$regexName      = "/^[A-z ]{3,}$/";
	$regexEmail     = "/^[A-z0-9._-]+@(hotmail|gmail|yahoo).com$/";
	$regexPassword  = "/^(?=.*[A-Z])(?=.*[@$!%*#?&])(?=.*[a-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/";
	$state = true;
	// Validation
	if (empty($name) || trim($name) == "") {
		$errors[0] = "This field is required";
		$state     = false;
	} else if (!preg_match($regexName, $name)) {
		$errors[0] = "This field is not correct, only letters are allowed";
		$state     = false;
	}
	if (empty($email) || trim($email) == "") {
		$errors[1] = "This field is required";
		$state     = false;
	} else if (!preg_match($regexEmail, $email)) {
		$errors[1] = "This field is not correct";
		$state     = false;
	}
	if (empty($pass) || trim($pass) == "") {
		$errors[2] = "This field is required";
		$state     = false;
	} else if (!preg_match($regexPassword, $pass)) {
		$errors[2] = "The password is not correct, it must be at least 8 characters and must contains (upper case, lower case, numbers, special character, no spaces ";
		$state     = false;
	}
	if (empty($confirmPassword) || trim($confirmPassword) == "") {
		$errors[3] = "This field is required";
		$state     = false;
	} else if (!preg_match($regexPassword, $pass)) {
		$errors[3] = "The password are not match";
		$state     = false;
	}
	return $state;
}

// To add image
function imagePath()
{
	global $imagePath;
	$image     = $_FILES['image'] ?? null;
	$imagePath = "";
	if ($image && $image['tmp_name']) {
		$imagePath = 'IMG-' . uniqid() . "-" . $image['name'];
		move_uploaded_file($image['tmp_name'], "image/" . $imagePath);
	}
}

// Signup
if (isset($_POST['signupSubmit'])) {
	$state = check($_POST['name'], $_POST['email'], $_POST['password'], $_POST['confirmPassword']);
	if ($state == true) {
		// To add image
		imagePath();
		$sql        = "INSERT INTO users (user_name, user_email,user_password,user_img) VALUES ('{$_POST['name']}','{$_POST['email']}','{$_POST['password']}','{$imagePath}')";
		if (mysqli_query($connection, $sql)) {
			$_POST = array();
			$_SESSION['stateSignUp'] = true;
			header("Location: login.php?status=done");
			die();
		} else {
			$errors[1] = "The account is used";
		}
	}
}

// Login
if (isset($_POST['loginSubmit'])) {
	if (trim($_POST['email']) == "" || trim($_POST['password'] == "")) {
		$errors[4] = "The email or password is not correct";
	} else {
		// For Users
		$query  = "SELECT * FROM users where user_email = '{$_POST['email']}' AND user_password = '{$_POST['password']}'";
		$result = mysqli_query($connection, $query);
		$row    = mysqli_fetch_assoc($result);
		$count = mysqli_num_rows($result);
		if ($count == 1) {
			$sql = "UPDATE users SET login_date = NOW() where user_id = '{$row["user_id"]}' ";
			mysqli_query($connection, $sql);
			$_SESSION['userLogin'] = $row['user_id'];
			unset($_SESSION['adminLogin']);
			header("location:index.php");
			die();
		}
		$query  = "SELECT * FROM admins where admin_email = '{$_POST['email']}' AND admin_password = '{$_POST['password']}'";
		$result = mysqli_query($connection, $query);
		$row    = mysqli_fetch_assoc($result);
		$count = mysqli_num_rows($result);
		if ($count == 1) {
			$currentDate = date("Y-m-d");
			$sql = "UPDATE admins SET login_date = NOW() where admin_id = '{$row["admin_id"]}' ";
			mysqli_query($connection, $sql);
			$_SESSION['adminLogin'] = $row['admin_id'];
			header("location:index.php");
			die();
		}
		$errors[4] = "The email or password is not correct";
	}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Login | Page</title>
	<link rel="stylesheet" href="css/login.css">
	<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
	<!DOCTYPE html>
	<html>

	<body>
		<div class="main">
			<input type="checkbox" id="chk" aria-hidden="true">
			<div class="signup">
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data">
					<label for="chk" aria-hidden="true">Sign up</label>
					<?php if (isset($_GET['status']) && $_GET['status'] == 'done') { ?>
						<div class="alert alert-success" role="alert">
							Successfully Registered!
						</div>
					<?php	}					?>
					<div class="inputContainer">
						<input type="text" name="name" placeholder="Username" value="<?php echo $_POST['name'] ?? ""  ?>">
						<span class="error"><?php echo $errors[0] ?? "" ?></span>
					</div>
					<div class="inputContainer">
						<input type="text" name="email" placeholder="Email" value="<?php echo $_POST['email'] ?? ""  ?>">
						<span class="error"><?php echo $errors[1] ?? "" ?></span>
					</div>
					<div class="inputContainer">
						<input type="password" name="password" placeholder="Password">
						<span class="error"><?php echo $errors[2] ?? "" ?></span>
					</div>
					<div class="inputContainer">
						<input type="password" name="confirmPassword" placeholder="Confirm Password">
						<span class="error"><?php echo $errors[3] ?? "" ?></span>
					</div>
					<div class="inputContainer">
						<input class="form-control" type="file" name="image" id="formFile">
					</div>
					<button type="submit" name="signupSubmit">Sign up</button>
				</form>
			</div>

			<div class="login">
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
					<label for="chk" aria-hidden="true">Login</label>
					<div class="inputContainer">
						<input type="text" name="email" placeholder="Email">
					</div>
					<div class="inputContainer">
						<input type="password" name="password" placeholder="Password">
						<span class="error"><?php echo $errors[4] ?? "" ?></span>
					</div>
					<button type="submit" name="loginSubmit">Login</button>
				</form>
			</div>
		</div>
	</body>

	</html>
</body>

</html>