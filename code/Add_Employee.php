<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <link rel="stylesheet" href="CSS/Add_Employee.css">
</head>
<body>
    <div class="container">
        <nav>
            <div class="menu-icon">&#9776;</div>
            <button class="logout">Log Out</button>
            <img class="profile" src="assets/image.png" alt="Employee Details" width="40px" height="40px">

        </nav>
        <h2>Add Employee</h2>
        <form>
            <div class="left">
                <label for="name">First Name</label> 
                <input type="text" id="name" title="First Name" placeholder="First Name">
                <label for="name">Last Name</label> 
                <input type="text" id="name" title="Last Name" placeholder="Last Name">
                <label>Date of Birth</label> 
                <input type="date" title="Enter your date of birth" placeholder="Enter your date of birth">
                <label>Gender</label> 
                <input type="text" title="Enter your gender" placeholder="Enter your gender">
                <label>Address</label>
                 <input type="text" title="Enter your address" placeholder="Enter your address">
                <label>Phone Number</label>
                 <input type="tel" title="Enter your phone number" placeholder="Enter your phone number">
                 <label>Email</label> 
                <input type="text" title="Enter your Email" placeholder="Enter your Email">
                <label>Marital Status</label>
                <input type="text" title="Enter your Marital Status" placeholder="Enter your Marital Status">
                <label>Qualification</label>
                <input type="text" title="Enter your Qualification" placeholder="Enter your Qualification">
                <label>Experience</label>
                <input type="text" title="Enter your Experience" placeholder="Enter your Experience">
                <label>Blood Type</label>
                <input type="text" title="Enter your Blood Type" placeholder="Enter your Blood Type">
                <label>Insurance</label>
                <input type="text" title="Enter your Insurance" placeholder="Enter your Insurance">
                <label>Joining Date</label>
                <input type="date" title="Enter your Joining Date" placeholder="Enter your Joining Date">
                <label>Leave Balance</label>
                <input type="number" title="Enter your Leave Balance" placeholder="Enter your Leave Balance">
                <label>Department ID</label>
                <input type="text" title="Enter your Department ID" placeholder="Enter your Department ID">
                <label>Manager ID</label>
                <input type="text" title="Enter your Manager ID" placeholder="Enter your Manager ID">   
            </div>
            <div class="right">
                <label>Username</label> 
                <input type="text" title="Username" placeholder="Username">
                <label>Password</label> 
                <input type="password" title="Enter your password" placeholder="Enter your password">
            </div>
            <button type="submit" class="submit">SUBMIT</button>
        </form>
    </div>
</body>
</html>