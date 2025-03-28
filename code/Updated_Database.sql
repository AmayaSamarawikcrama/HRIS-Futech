-- CREATE DATABASE IF NOT EXISTS hris_db;
-- USE hris_db;

-- -- Department Table (Created first to avoid FK issues)
-- CREATE TABLE Department (
--     Department_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Department_Name VARCHAR(100) NOT NULL UNIQUE,
--     Location VARCHAR(255),
--     Manager_ID INT NULL,
--     FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL
-- );

-- -- Employee Table
-- CREATE TABLE Employee (
--     Employee_ID INT PRIMARY KEY AUTO_INCREMENT,
--     First_Name VARCHAR(50) NOT NULL,
--     Last_Name VARCHAR(50) NOT NULL,
--     Date_of_Birth DATE NOT NULL,
--     Gender ENUM('Male', 'Female') NOT NULL,
--     Address TEXT,
--     Contact_Number VARCHAR(15) UNIQUE CHECK (Contact_Number REGEXP '^[0-9]{10,15}$'),
--     Email VARCHAR(100) UNIQUE CHECK (Email LIKE '%@%._%'),
--     Qualification TEXT,
--     Insurance VARCHAR(50),
--     Blood_Type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') DEFAULT NULL,
--     Marital_Status ENUM('Single', 'Married', 'Divorced', 'Widowed') DEFAULT 'Single',
--     Hire_Date DATE NOT NULL,
--     Salary DECIMAL(10,2) NOT NULL CHECK (Salary >= 0),
--     Department_ID INT NULL,
--     Manager_ID INT NULL,
--     FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL,
--     FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL
-- );

-- -- Job Position Table
-- CREATE TABLE Job_Position (
--     Job_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Job_Title VARCHAR(100) NOT NULL UNIQUE,
--     Job_Description TEXT,
--     Salary_Range VARCHAR(50),
--     Job_Level ENUM('Junior', 'Mid', 'Senior', 'Lead', 'Executive') NOT NULL
-- );

-- -- Employee Performance Table
-- CREATE TABLE Employee_Performance (
--     Performance_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID INT NOT NULL,
--     Performance_Rating ENUM('Poor', 'Average', 'Good', 'Excellent') NOT NULL,
--     Strengths TEXT,
--     Recommendations TEXT,
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );

-- -- Attendance Table
-- CREATE TABLE Attendance (
--     Attendance_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID INT NOT NULL,
--     Date DATE NOT NULL,
--     Log_In_Time TIME NOT NULL,
--     Log_Out_Time TIME DEFAULT NULL CHECK (Log_Out_Time > Log_In_Time),
--     Work_Hours DECIMAL(5,2) GENERATED ALWAYS AS (TIMESTAMPDIFF(MINUTE, Log_In_Time, Log_Out_Time) / 60) STORED,
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );

-- -- Leave Management Table
-- CREATE TABLE Leave_Management (
--     Leave_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID INT NOT NULL,
--     Leave_Type ENUM('Sick Leave', 'Casual Leave', 'Annual Leave', 'Maternity Leave') NOT NULL,
--     Start_Date DATE NOT NULL,
--     End_Date DATE NOT NULL CHECK (End_Date > Start_Date),
--     Approval_Status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
--     Leave_Reason TEXT,
--     Duty_Covering VARCHAR(100),
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );

-- -- Payroll Table
-- CREATE TABLE Payroll (
--     Payroll_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID INT NOT NULL,
--     Base_Salary DECIMAL(10,2) NOT NULL CHECK (Base_Salary >= 0),
--     Deductions DECIMAL(10,2) DEFAULT 0 CHECK (Deductions >= 0),
--     Net_Salary DECIMAL(10,2) GENERATED ALWAYS AS (Base_Salary - Deductions) STORED,
--     Payment_Method ENUM('Bank Transfer', 'Cheque', 'Cash') NOT NULL,
--     Payment_Date DATE NOT NULL,
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );

-- -- User Accounts Table
-- CREATE TABLE User_Account (
--     User_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID INT UNIQUE NOT NULL,
--     Username VARCHAR(50) NOT NULL UNIQUE,
--     Password VARCHAR(255) NOT NULL,
--     User_Type ENUM('Admin', 'Manager', 'Employee') NOT NULL,
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );


CREATE DATABASE IF NOT EXISTS hris_db;
USE hris_db;

-- Department Table (without Manager_ID FK)
CREATE TABLE Department (
    Department_ID INT PRIMARY KEY AUTO_INCREMENT,
    Department_Name VARCHAR(100) NOT NULL UNIQUE,
    Location VARCHAR(255)
);

-- Add Department Manager_ID FK AFTER Employee table is created
ALTER TABLE Department 
ADD COLUMN Manager_ID INT NULL,
ADD CONSTRAINT FK_Department_Manager FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL;

-- Employee Table
CREATE TABLE Employee (
    Employee_ID VARCHAR(10) PRIMARY KEY, -- Format: HM0001 or EMP0001
    Password VARCHAR(255) NOT NULL,
    Employee_Type ENUM('HumanResource Manager', 'Employee') NOT NULL,
    First_Name VARCHAR(50) NOT NULL,
    Last_Name VARCHAR(50) NOT NULL,
    Date_of_Birth DATE NOT NULL,
    Gender ENUM('Male','Female') NOT NULL,
    Address TEXT,
    Contact_Number VARCHAR(15) UNIQUE CHECK (Contact_Number REGEXP '^[0-9]{10,15}$'),
    Email VARCHAR(100) UNIQUE CHECK (Email LIKE '%@%._%'),
    Qualification TEXT,
    Insurance VARCHAR(50),
    Blood_Type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') DEFAULT NULL,
    Marital_Status ENUM('Single', 'Married', 'Divorced', 'Widowed') DEFAULT 'Single',
    Hire_Date DATE NOT NULL,
    Salary DECIMAL(10,2) NOT NULL CHECK (Salary >= 0),
    Department_ID INT NULL,
    Manager_ID VARCHAR(10) NULL, -- Reference updated to match new Employee_ID format
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL,
    FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL
);

ALTER TABLE Employee ADD COLUMN Password VARCHAR(255) NOT NULL;






ALTER TABLE Employee ADD COLUMN file_name VARCHAR(255) NOT NULL; 
ALTER TABLE Employee ADD uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP; 








ALTER TABLE Employee MODIFY Employee_ID VARCHAR(10) NOT NULL;

ALTER TABLE Employee ADD COLUMN Employee_Type ENUM('HumanResource Manager', 'Employee') NOT NULL;

ALTER TABLE Employee ADD COLUMN Employee_Type VARCHAR(50) NOT NULL;



-- Create a sequence table to track the auto-increment value for Employee_ID
CREATE TABLE Employee_ID_Sequence (
    ID INT PRIMARY KEY AUTO_INCREMENT
);

-- Trigger to auto-generate Employee_ID with the required prefix
DELIMITER //
CREATE TRIGGER before_insert_employee
BEFORE INSERT ON Employee
FOR EACH ROW
BEGIN
    DECLARE new_id INT;
    DECLARE prefix VARCHAR(5);

    -- Determine prefix based on Employee_Type
    IF NEW.Employee_Type = 'HumanResource Manager' THEN
        SET prefix = 'HM';
    ELSE
        SET prefix = 'EMP';
    END IF;

    -- Get the next auto-increment value
    INSERT INTO Employee_ID_Sequence VALUES (NULL);
    SET new_id = LAST_INSERT_ID();

    -- Format Employee_ID as Prefix + Zero-Padded Number
    SET NEW.Employee_ID = CONCAT(prefix, LPAD(new_id, 4, '0'));
END;
//
DELIMITER ;


-- Add Employee Foreign Keys
ALTER TABLE Employee 
ADD CONSTRAINT FK_Employee_Department FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL,
ADD CONSTRAINT FK_Employee_Manager FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL;

-- Job Position Table
CREATE TABLE Job_Position (
    Job_ID INT PRIMARY KEY AUTO_INCREMENT,
    Job_Title VARCHAR(100) NOT NULL UNIQUE,
    Job_Description TEXT,
    Salary_Range VARCHAR(50),
    Job_Level ENUM('Junior', 'Mid', 'Senior', 'Lead', 'Executive') NOT NULL
);

-- Employee Performance Table
CREATE TABLE Employee_Performance (
    Performance_ID INT PRIMARY KEY AUTO_INCREMENT,
    Employee_ID VARCHAR(255) NOT NULL,
    Performance_Rating ENUM('Poor', 'Average', 'Good', 'Excellent') NOT NULL,
    Strengths TEXT,
    Recommendations TEXT
);

-- Attendance Table
CREATE TABLE Attendance (
    Attendance_ID INT PRIMARY KEY AUTO_INCREMENT,
    Employee_ID VARCHAR(255) NOT NULL,
    Date DATE NOT NULL,
    Log_In_Time TIME NOT NULL,
    Log_Out_Time TIME DEFAULT NULL,
    Work_Hours DECIMAL(5,2) GENERATED ALWAYS AS (
        IF(Log_Out_Time IS NOT NULL, TIMESTAMPDIFF(MINUTE, Log_In_Time, Log_Out_Time) / 60, NULL)
    ) STORED
);

-- Leave Management Table
CREATE TABLE Leave_Management (
    Leave_ID INT PRIMARY KEY AUTO_INCREMENT,
    Employee_ID VARCHAR(255) NOT NULL,
    Leave_Type ENUM('Sick Leave', 'Casual Leave', 'Annual Leave', 'Maternity Leave') NOT NULL,
    Start_Date DATE NOT NULL,
    End_Date DATE NOT NULL,
    Approval_Status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    Leave_Reason TEXT,
    Duty_Covering VARCHAR(100)
);

DELIMITER //
CREATE TRIGGER before_leave_insert
BEFORE INSERT ON Leave_Management
FOR EACH ROW
BEGIN
    IF NEW.End_Date <= NEW.Start_Date THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'End_Date must be later than Start_Date';
    END IF;
END;
//
DELIMITER ;

-- Payroll Table
CREATE TABLE Payroll (
    Payroll_ID INT PRIMARY KEY AUTO_INCREMENT,
    Employee_ID INT NOT NULL,
    Base_Salary DECIMAL(10,2) NOT NULL CHECK (Base_Salary >= 0),
    Deductions DECIMAL(10,2) DEFAULT 0 CHECK (Deductions >= 0),
    Net_Salary DECIMAL(10,2) GENERATED ALWAYS AS (Base_Salary - Deductions) STORED,
    Payment_Method ENUM('Bank Transfer', 'Cheque', 'Cash') NOT NULL,
    Payment_Date DATE NOT NULL
);

-- -- User Accounts Table
-- CREATE TABLE User_Account (
--     User_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID INT UNIQUE NOT NULL,
--     Username VARCHAR(50) NOT NULL UNIQUE,
--     Password VARCHAR(255) NOT NULL,
--     User_Type ENUM('Admin', 'Manager', 'Employee') NOT NULL
-- );


ALTER TABLE Employee_Performance 
ADD CONSTRAINT FK_Performance_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;

ALTER TABLE Attendance 
ADD CONSTRAINT FK_Attendance_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;

ALTER TABLE Leave_Management 
ADD CONSTRAINT FK_Leave_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;

ALTER TABLE Payroll 
ADD CONSTRAINT FK_Payroll_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;

ALTER TABLE User_Account 
ADD CONSTRAINT FK_User_Account_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;
