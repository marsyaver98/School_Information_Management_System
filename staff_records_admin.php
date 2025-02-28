<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login_staff.php");
    exit();
}
// Connect to the database
$con = mysqli_connect("localhost", "root", "", "fyp_db");

// Check connection
if (!$con) {
    die("Connection failed: ". mysqli_connect_error());
}

// Get the logged-in user's email from the session
$user_email = $_SESSION['email'];

// Fetch first name from the database using email
$sql = "SELECT first_name FROM staffs WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($first_name);
$stmt->fetch();
$stmt->close();

// Get the number of students
$sql = "SELECT COUNT(*) FROM students";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_array($result, MYSQLI_NUM);
$parent_count = $row[0];

// Get the number of teachers
$sql = "SELECT COUNT(*) FROM teachers";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_array($result, MYSQLI_NUM);
$teacher_count = $row[0];

// Get the number of staffs
$sql = "SELECT COUNT(*) FROM staffs";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_array($result, MYSQLI_NUM);
$staff_count = $row[0];

// Close connection
mysqli_close($con);

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="staff_style.css">

	<title>IISSA CommWave</title>

	<style>
		.container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

	</style>
	</head>
	<body>
	<script>
		//Logout Popup
		function showLogoutModal() {
			document.getElementById('logoutModal').style.display = 'block';
		}

		function hideLogoutModal() {
			document.getElementById('logoutModal').style.display = 'none';
		}

		document.addEventListener('DOMContentLoaded', function() {
			document.getElementById('confirmLogoutBtn').addEventListener('click', function(event) {
				event.preventDefault(); // Prevent default form submission
				document.getElementById('logoutForm').submit(); // Submit the form
			});
		});
        //Delete Confirmation Popup
        document.addEventListener('DOMContentLoaded', function() 
        {
            // Add event listener for delete buttons
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const noticeId = this.getAttribute('data-id');
                    Confirm.open({
                        title: 'Confirm User Deletion',
                        message: 'Are you sure you want to delete this user?',
                        onok: () => {
                            location.href = 'staff_delete_admin.php?id=' + noticeId;
                        }
                    });
                });
            });
        });

        const Confirm = {
            open(options) {
                options = Object.assign({}, {
                    title: '',
                    message: '',
                    okText: 'Delete',
                    cancelText: 'Cancel',
                    onok: function() {},
                    oncancel: function() {}
                }, options);

                const html = `
                    <div class="confirm">
                        <div class="confirm__window">
                            <div class="confirm__titlebar">
                                <span class="confirm__title">${options.title}</span>
                                <button class="confirm__close">&times;</button>
                            </div>
                            <div class="confirm__content">${options.message}</div>
                            <div class="confirm__buttons">
                                <button class="confirm__button confirm__button--ok confirm__button--fill">${options.okText}</button>
                                <button class="confirm__button confirm__button--cancel">${options.cancelText}</button>
                            </div>
                        </div>
                    </div>
                `;

                const template = document.createElement('template');
                template.innerHTML = html;

                // Elements
                const confirmEl = template.content.querySelector('.confirm');
                const btnClose = template.content.querySelector('.confirm__close');
                const btnOk = template.content.querySelector('.confirm__button--ok');
                const btnCancel = template.content.querySelector('.confirm__button--cancel');

                confirmEl.addEventListener('click', e => {
                    if (e.target === confirmEl) {
                        options.oncancel();
                        this._close(confirmEl);
                    }
                });

                btnOk.addEventListener('click', () => {
                    options.onok();
                    this._close(confirmEl);
                });

                [btnCancel, btnClose].forEach(el => {
                    el.addEventListener('click', () => {
                        options.oncancel();
                        this._close(confirmEl);
                    });
                });

                document.body.appendChild(template.content);
            },

            _close(confirmEl) {
                confirmEl.classList.add('confirm--close');

                confirmEl.addEventListener('animationend', () => {
                    document.body.removeChild(confirmEl);
                });
            }
        };

		function togglePassword(element) {
			const input = element.previousElementSibling;
			if (input.type === "password") {
				input.type = "text";
				element.classList.remove('bx-show');
				element.classList.add('bx-hide');
			} else {
				input.type = "password";
				element.classList.remove('bx-hide');
				element.classList.add('bx-show');
			}
		}
    </script>

	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="#" class="brand">
			<i class='bx bx-hive'></i>
			<span class="text">IISSA CommWave</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="staff_index.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="staff_view_notice.php">
					<i class='bx bxs-megaphone'></i>
					<span class="text">School Notices</span>
				</a>
			</li>
			<li class="active">
				<a href="staff_user_index.php">
					<i class='bx bxs-group'></i>
					<span class="text">User Accounts</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="#" class="logout" onclick="showLogoutModal()">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
			</li>
		</ul>
	</section>
	<!-- SIDEBAR -->



	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu'></i>
			<a href="#" class="nav-link"></a>  

			<form action="#">
				
			</form>
			
			<div class="profile" id="profileDropdown">
                <i class='bx bxs-user-circle' style="margin-right: 10px;"></i>
				<span id="userName"><?php echo htmlspecialchars($first_name); ?></span>
				<i class='bx bx-chevron-down'></i>
				<div class="dropdown-menu" id="dropdownMenu">
					<div class="profile-info">
						<i class='bx bxs-user-circle'></i>
						<span><?php echo htmlspecialchars($first_name); ?></span>
					</div>
					<a href="parent_profile.php" class="dropdown-item">Profile</a>
				</div>
			</div>
			
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<!-- MAIN -->
					
					<ul class="breadcrumb">
						<li>
							<a class='active1' href="staff_index.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Users' Accounts</a>
						</li>
					</ul>
				</div>
				<!--
				<a href="#" class="btn-download">
					<i class='bx bxs-cloud-download' ></i>
					<span class="text">Download PDF</span>
				</a>
				-->
			</div>
		
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Staff User Records</h3>
					</div>
					<table>
						<thead>
							<tr>
								<th>ID</th>
								<th>First Name</th>
								<th>Last Name</th>
								<th>Phone Number</th>
								<th>Email</th>
								<th>Password</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php
							// Database connection
							$servername = "localhost";
							$username = "root";
							$password = "";
							$dbname = "fyp_db";

							// Create connection
							$conn = new mysqli($servername, $username, $password, $dbname);

							// Check connection
							if ($conn->connect_error) {
								die("Connection failed: " . $conn->connect_error);
							}

							// Fetch staff records
							$sql = "SELECT staff_id, first_name, last_name, phone_num, email, password FROM staffs";
							$result = $conn->query($sql);

							if ($result->num_rows > 0) {
								// Output data of each row
								while($row = $result->fetch_assoc()) {
									echo "<tr>
										<td>{$row['staff_id']}</td>
										<td>{$row['first_name']}</td>
										<td>{$row['last_name']}</td>
										<td>{$row['phone_num']}</td>
										<td>{$row['email']}</td>
										<td class='password-field'>
											<input type='password' value='{$row['password']}' readonly>
											<i class='bx bx-show password-toggle' onclick='togglePassword(this)'></i>
										</td>
										<td>
											<div class='btn-group'>
                                            	<button class='edit-btn' onclick=\"location.href='staff_admin_user.php?id={$row['staff_id']}'\">Edit</button>
                                            	<button class='delete-btn' data-id='{$row['staff_id']}'>Delete</button>
                                        	</div>
										</td>
									</tr>";
								}
							} else {
								echo "<tr><td colspan='7'>No records found</td></tr>";
							}

							$conn->close();
							?>
						</tbody>
					</table>
				</div>
			</div>
			
			
		
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->
	<form id="logoutForm" action="logout_users.php" method="post">
		<div id="logoutModal" class="modal">
		<div class="modal-content">
			<span class="close" onclick="hideLogoutModal()">&times;</span>
			<p>Are you sure you want to logout?</p>
			<button type="submit" id="confirmLogoutBtn">Logout</button>
		</div>
	</div>
	</form>

	<script src="script.js"></script>
</body>
</html>