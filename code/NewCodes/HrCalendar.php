<?php
session_start();
$host = 'localhost';
$dbname = 'hris_db';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$success_message = '';
$error_message = '';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Get logged-in user data
$user_data = [];
$user_id = $_SESSION['user_id'];
$sql = "SELECT First_Name, Last_Name FROM Employee WHERE Employee_ID = '$user_id'";
$result = $conn->query($sql);
if($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    // If user data not found, use session data if available
    if(isset($_SESSION['First_Name']) && isset($_SESSION['Last_Name'])) {
        $user_data['First_Name'] = $_SESSION['First_Name'];
        $user_data['Last_Name'] = $_SESSION['Last_Name'];
    } else {
        $user_data['First_Name'] = 'Unknown';
        $user_data['Last_Name'] = 'User';
    }
}

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['add_event'])) {
        // Add event to database
        $event_name = $conn->real_escape_string($_POST['event_name']);
        $event_type = $conn->real_escape_string($_POST['event_type']);
        $event_description = $conn->real_escape_string($_POST['event_description']);
        $event_date = $conn->real_escape_string($_POST['event_date']);
        $event_time = $conn->real_escape_string($_POST['event_time']);
        $location = $conn->real_escape_string($_POST['location']);
        $organizer = $conn->real_escape_string($_POST['organizer']);
        
        $sql = "INSERT INTO Event (Event_Name, Event_Type, Event_Description, Event_Date, Event_Time, Location, Organizer) 
                VALUES ('$event_name', '$event_type', '$event_description', '$event_date', '$event_time', '$location', '$organizer')";
        
        if($conn->query($sql)) {
            $success_message = "Event added successfully!";
            // Refresh the page to show the new event
            header("Refresh:0");
        } else {
            $error_message = "Error adding event: " . $conn->error;
        }
    } elseif(isset($_POST['delete_event'])) {
        // Delete event from database
        $event_id = $conn->real_escape_string($_POST['event_id']);
        
        $sql = "DELETE FROM Event WHERE Event_ID = '$event_id'";
        
        if($conn->query($sql)) {
            $success_message = "Event deleted successfully!";
            // Refresh the page to update the calendar
            header("Refresh:0");
        } else {
            $error_message = "Error deleting event: " . $conn->error;
        }
    }
}

// Fetch events from database
$events = array();
$sql = "SELECT * FROM Event ORDER BY Event_Date, Event_Time";
$result = $conn->query($sql);

if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Team Calendar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
        }
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
        .event-list {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .event-card {
            margin-bottom: 10px;
            border-left: 4px solid #0d6efd;
        }
        .event-type {
            font-weight: bold;
            color: #0d6efd;
        }
        #calendar {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .fc-event {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="HrDashboard.php" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                    <a href="HrAddEmployee.php" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.php" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.php" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="HrProject.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="HrLeave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.php" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2">Company Details</a>
                    <a href="HrCalendar.php" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
                </div>
                <script>
                    function toggleMenu() {
                        const menuList = document.getElementById('menu-list');
                        menuList.classList.toggle('d-none');
                    }
                </script>
                <span class="employee-name ms-auto me-3">
                    <?php
                        echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']);
                    ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
            </div>
        </nav>

        <div class="container mt-4">
            <h2 class="text-center"><b>HR Team Calendar</b></h2>
            
            <!-- Display messages -->
            <?php if($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div id="calendar" class="mb-4"></div>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success w-100 mb-3" id="toggle-event-form-btn" onclick="toggleAddEventForm()">+ Add Event</button>
                    
                    <div id="add-event-form" class="d-none mb-4 p-3 border rounded">
                        <h4>Add New Event</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="event_name" class="form-label">Event Name</label>
                                <input type="text" class="form-control" id="event_name" name="event_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="event_type" class="form-label">Event Type</label>
                                <select class="form-select" id="event_type" name="event_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Training">Training</option>
                                    <option value="Workshop">Workshop</option>
                                    <option value="Seminar">Seminar</option>
                                    <option value="Conference">Conference</option>
                                    <option value="Team Building">Team Building</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="event_description" class="form-label">Description</label>
                                <textarea class="form-control" id="event_description" name="event_description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="event_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="event_time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="event_time" name="event_time" required>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location">
                            </div>
                            <div class="mb-3">
                                <label for="organizer" class="form-label">Organizer</label>
                                <input type="text" class="form-control" id="organizer" name="organizer">
                            </div>
                            <button type="submit" name="add_event" class="btn btn-primary">Submit</button>
                            <button type="button" class="btn btn-secondary" onclick="hideAddEventForm()">Cancel</button>
                        </form>
                    </div>
                    
                    <div class="d-none mb-4 p-3 border rounded" id="delete-event-form">
                        <h4>Delete Event</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="event_id" class="form-label">Select Event to Delete</label>
                                <select class="form-select" id="event_id" name="event_id" required>
                                    <option value="">Select Event</option>
                                    <?php foreach($events as $event): ?>
                                        <option value="<?php echo $event['Event_ID']; ?>">
                                            <?php echo htmlspecialchars($event['Event_Name'] . ' - ' . $event['Event_Date']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="delete_event" class="btn btn-danger">Delete</button>
                            <button type="button" class="btn btn-secondary" onclick="hideDeleteEventForm()">Cancel</button>
                        </form>
                    </div>
                    
                    <button class="btn btn-danger w-100 mb-3" id="toggle-delete-event-form-btn" onclick="toggleDeleteEventForm()">- Delete Event</button>
                    
                    <h4 class="mt-4">Upcoming Events</h4>
                    <div class="event-list">
                        <?php if(empty($events)): ?>
                            <div class="alert alert-info">No upcoming events scheduled.</div>
                        <?php else: ?>
                            <?php foreach($events as $event): ?>
                                <div class="card event-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($event['Event_Name']); ?></h5>
                                        <p class="card-text">
                                            <span class="event-type"><?php echo htmlspecialchars($event['Event_Type']); ?></span><br>
                                            <?php echo htmlspecialchars($event['Event_Description']); ?>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <strong>Date:</strong> <?php echo date('M j, Y', strtotime($event['Event_Date'])); ?><br>
                                                <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['Event_Time'])); ?><br>
                                                <?php if($event['Location']): ?>
                                                    <strong>Location:</strong> <?php echo htmlspecialchars($event['Location']); ?><br>
                                                <?php endif; ?>
                                                <?php if($event['Organizer']): ?>
                                                    <strong>Organizer:</strong> <?php echo htmlspecialchars($event['Organizer']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
        // Initialize FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    <?php foreach($events as $event): ?>
                    {
                        title: '<?php echo addslashes($event['Event_Name']); ?>',
                        start: '<?php echo $event['Event_Date'] . ($event['Event_Time'] ? 'T' . $event['Event_Time'] : ''); ?>',
                        description: '<?php echo addslashes($event['Event_Description']); ?>',
                        extendedProps: {
                            type: '<?php echo addslashes($event['Event_Type']); ?>',
                            location: '<?php echo addslashes($event['Location']); ?>',
                            organizer: '<?php echo addslashes($event['Organizer']); ?>'
                        },
                        color: getEventColor('<?php echo $event['Event_Type']; ?>')
                    },
                    <?php endforeach; ?>
                ],
                eventClick: function(info) {
                    // Display event details when clicked
                    alert(
                        'Event: ' + info.event.title + '\n' +
                        'Type: ' + info.event.extendedProps.type + '\n' +
                        'Description: ' + info.event.extendedProps.description + '\n' +
                        'Date: ' + info.event.start.toLocaleString() + '\n' +
                        'Location: ' + info.event.extendedProps.location + '\n' +
                        'Organizer: ' + info.event.extendedProps.organizer
                    );
                }
            });
            calendar.render();
            
            // Set default date for new events to today
            document.getElementById('event_date').valueAsDate = new Date();
            
            // Set default time for new events to current time + 1 hour
            const now = new Date();
            now.setHours(now.getHours() + 1);
            document.getElementById('event_time').value = now.toTimeString().substring(0, 5);
        });
        
        // Assign different colors based on event type
        function getEventColor(type) {
            switch(type) {
                case 'Meeting': return '#3788d8';
                case 'Training': return '#6c757d';
                case 'Workshop': return '#20c997';
                case 'Seminar': return '#6610f2';
                case 'Conference': return '#fd7e14';
                case 'Team Building': return '#dc3545';
                default: return '#6f42c1';
            }
        }

        // Toggle event forms
        function toggleAddEventForm() {
            const form = document.getElementById('add-event-form');
            const button = document.getElementById('toggle-event-form-btn');
            if (form.classList.contains('d-none')) {
                form.classList.remove('d-none');
                button.textContent = '- Close Form';
                // Hide delete form if open
                document.getElementById('delete-event-form').classList.add('d-none');
                document.getElementById('toggle-delete-event-form-btn').textContent = '- Delete Event';
            } else {
                form.classList.add('d-none');
                button.textContent = '+ Add Event';
            }
        }

        function toggleDeleteEventForm() {
            const form = document.getElementById('delete-event-form');
            const button = document.getElementById('toggle-delete-event-form-btn');
            if (form.classList.contains('d-none')) {
                form.classList.remove('d-none');
                button.textContent = '- Close Form';

                document.getElementById('add-event-form').classList.add('d-none');
                document.getElementById('toggle-event-form-btn').textContent = '+ Add Event';
            } else {
                form.classList.add('d-none');
                button.textContent = '- Delete Event';
            }
        }

        function hideAddEventForm() {
            document.getElementById('add-event-form').classList.add('d-none');
            document.getElementById('toggle-event-form-btn').textContent = '+ Add Event';
        }

        function hideDeleteEventForm() {
            document.getElementById('delete-event-form').classList.add('d-none');
            document.getElementById('toggle-delete-event-form-btn').textContent = '- Delete Event';
        }

        function logout() {
            // Implement logout functionality
            window.location.href = 'logout.php';
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>