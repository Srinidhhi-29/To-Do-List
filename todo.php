<?php
$server = 'localhost:3307'; // Check if the port is correct
$username = 'root';
$password = '';
$database = 'todo_master';

// Create connection
$conn = mysqli_connect($server, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Initialize message variable
$message = '';

// Handle form submission for adding items
if (isset($_POST['add'])) {
    $item = $_POST['item'];
    if (!empty($item)) {
        $stmt = $conn->prepare("INSERT INTO todo (name) VALUES (?)");
        $stmt->bind_param("s", $item);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success" role="alert">Item added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger" role="alert">Error: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// Handle item deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['item'])) {
    $itemId = (int)$_GET['item'];
    $stmt = $conn->prepare("DELETE FROM todo WHERE id = ?");
    $stmt->bind_param("i", $itemId);

    if ($stmt->execute()) {
        $message = '<div class="alert alert-danger" role="alert">Item deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger" role="alert">Error: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// Handle marking item as done
if (isset($_GET['action']) && $_GET['action'] === 'done' && isset($_GET['item'])) {
    $itemId = (int)$_GET['item'];
    $stmt = $conn->prepare("UPDATE todo SET done = 1 WHERE id = ?");
    $stmt->bind_param("i", $itemId);

    if ($stmt->execute()) {
        $message = '<div class="alert alert-info" role="alert">Item marked as done!</div>';
    } else {
        $message = '<div class="alert alert-danger" role="alert">Error: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// Fetch todo items for display
$query = "SELECT * FROM todo";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            margin-top: 60px; /* Space for alert at the top */
        }
        .alert-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1050;
            text-align: center; /* Center text within the alert */
        }
        .alert {
            margin: 0;
            border-radius: 0;
        }
    </style>
</head>
<body>
    <div class="alert-container">
        <?php
            if ($message) {
                echo $message;
            }
        ?>
    </div>
    <main>
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-3"></div>
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <p>Todo List</p>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="item" placeholder="Add a Todo item" required>
                                </div>
                                <input type="submit" value="Add Item" class="btn btn-dark" name="add">
                            </form>
                            <div class="mt-5">
                                <?php
                                    if ($result->num_rows > 0) {
                                        $i = 1;
                                        while ($row = $result->fetch_assoc()) {
                                            $statusClass = $row['done'] ? 'text-decoration-line-through' : '';
                                            $statusText = $row['done'] ? 'Done' : 'Mark as done';
                                            $markButton = $row['done'] ? '' : '<a href="?action=done&item=' . $row['id'] . '" class="btn btn-outline-dark">' . $statusText . '</a>';
                                            echo '
                                                <div class="row mb-3">
                                                    <div class="col-sm-12 col-md-1"><h5>' . $i . '</h5></div>
                                                    <div class="col-sm-12 col-md-6"><h5 class="' . $statusClass . '">' . htmlspecialchars($row['name']) . '</h5></div>
                                                    <div class="col-sm-12 col-md-5">
                                                        ' . $markButton . '
                                                        <a href="?action=delete&item=' . $row['id'] . '" class="btn btn-outline-danger">Delete</a>
                                                    </div>
                                                </div>
                                            ';
                                            $i++;
                                        }
                                    } else {
                                        echo '
                                            <center>
                                                <img src="f2.png" width="50px" alt="emptylist"><br><span>Your list is empty</span>
                                            </center>
                                        ';
                                    }
                                ?>
                            </div>
                        </div>                  
                    </div>  
                </div>
            </div>
        </div>
    </main>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            // Fade out alerts after 4 seconds
            $(".alert").fadeTo(4000, 500).slideUp(500, function() {
                $(this).remove();
            });
        });
    </script>
</body>
</html>
