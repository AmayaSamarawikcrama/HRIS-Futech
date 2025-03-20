<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Profile</title>
    <link rel="stylesheet" href="CSS/EmpProfile.css">
</head>
<body>
    <nav class="navbar">
        <div class="menu-icon">&#9776;</div>
        <button class="logout">Log Out</button>
        <img class="profile" src="profile.png" alt="Employee Details" width="40px" height="40px">
    </nav>
    <div class="container">
        <div class="sidebar">
            <h2>Profile</h2>
            <img class="profile-pic" src="profile.png" alt="Employee Details">
        </div>
        <main class="content">
            <form>
                <div class="profile-details">
                    <label for="emp_id">Employee ID</label> 
                    <input type="text" id="emp_id" name="emp_id" placeholder="Enter Employee ID" required>
                    
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="Enter First Name" required>
                    
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Enter Last Name" required>
                    
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                    
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                    
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" placeholder="Enter Address">
                    
                    <label for="contact_no">Phone Number</label>
                    <input type="tel" id="contact_no" name="contact_no" placeholder="Enter Phone Number">
                    
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter Email" required>
                    
                    <label for="marital_status">Marital Status</label>
                    <select id="marital_status" name="marital_status">
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                    
                    <label for="qualification">Qualification</label>
                    <input type="text" id="qualification" name="qualification" placeholder="Enter Qualification">
                    
                    <label for="experience">Experience (Years)</label>
                    <input type="number" id="experience" name="experience" placeholder="Enter Experience">
                    
                    <label for="blood_type">Blood Type</label>
                    <input type="text" id="blood_type" name="blood_type" placeholder="Enter Blood Type">
                    
                    <label for="insurance">Insurance</label>
                    <input type="text" id="insurance" name="insurance" placeholder="Enter Insurance Details">
                    
                    <label for="joining_date">Joining Date</label>
                    <input type="date" id="joining_date" name="joining_date" required>
                    
                    <label for="leave_balance">Leave Balance</label>
                    <input type="number" id="leave_balance" name="leave_balance" placeholder="Enter Leave Balance">
                    
                    <label for="department_id">Department ID</label>
                    <input type="text" id="department_id" name="department_id" placeholder="Enter Department ID">
                    
                    <label for="manager_id">Manager ID</label>
                    <input type="text" id="manager_id" name="manager_id" placeholder="Enter Manager ID">
                    
                    <button type="submit" class="submit-btn">Save Profile</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
