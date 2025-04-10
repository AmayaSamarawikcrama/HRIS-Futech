-- You said:
-- CREATE DATABASE IF NOT EXISTS hris_db;
-- USE hris_db;

-- -- Department Table (without Manager_ID FK)
-- CREATE TABLE Department (
--     Department_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Department_Name VARCHAR(100) NOT NULL UNIQUE,
--     Location VARCHAR(255)
-- );

-- -- Add Department Manager_ID FK AFTER Employee table is created
-- ALTER TABLE Department 
-- ADD COLUMN Manager_ID INT NULL,
-- ADD CONSTRAINT FK_Department_Manager FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL;

-- -- Employee Table
-- CREATE TABLE Employee (
--     Employee_ID VARCHAR(10) PRIMARY KEY, -- Format: HM0001 or EMP0001
--     Password VARCHAR(255) NOT NULL,
--     Employee_Type ENUM('HumanResource Manager', 'Employee') NOT NULL,
--     First_Name VARCHAR(50) NOT NULL,
--     Last_Name VARCHAR(50) NOT NULL,
--     Date_of_Birth DATE NOT NULL,
--     Gender ENUM('Male','Female') NOT NULL,
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
--     Manager_ID VARCHAR(10) NULL, -- Reference updated to match new Employee_ID format
--     FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL,
--     FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL
-- );

-- ALTER TABLE Employee ADD COLUMN Password VARCHAR(255) NOT NULL;






-- ALTER TABLE Employee ADD COLUMN file_name VARCHAR(255) NOT NULL; 
-- ALTER TABLE Employee ADD uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP; 








-- ALTER TABLE Employee MODIFY Employee_ID VARCHAR(10) NOT NULL;

-- ALTER TABLE Employee ADD COLUMN Employee_Type ENUM('HumanResource Manager', 'Employee') NOT NULL;

-- ALTER TABLE Employee ADD COLUMN Employee_Type VARCHAR(50) NOT NULL;



-- -- Create a sequence table to track the auto-increment value for Employee_ID
-- CREATE TABLE Employee_ID_Sequence (
--     ID INT PRIMARY KEY AUTO_INCREMENT
-- );

-- -- Trigger to auto-generate Employee_ID with the required prefix
-- DELIMITER //
-- CREATE TRIGGER before_insert_employee
-- BEFORE INSERT ON Employee
-- FOR EACH ROW
-- BEGIN
--     DECLARE new_id INT;
--     DECLARE prefix VARCHAR(5);

--     -- Determine prefix based on Employee_Type
--     IF NEW.Employee_Type = 'HumanResource Manager' THEN
--         SET prefix = 'HM';
--     ELSE
--         SET prefix = 'EMP';
--     END IF;

--     -- Get the next auto-increment value
--     INSERT INTO Employee_ID_Sequence VALUES (NULL);
--     SET new_id = LAST_INSERT_ID();

--     -- Format Employee_ID as Prefix + Zero-Padded Number
--     SET NEW.Employee_ID = CONCAT(prefix, LPAD(new_id, 4, '0'));
-- END;
-- //
-- DELIMITER ;


-- -- Add Employee Foreign Keys
-- ALTER TABLE Employee 
-- ADD CONSTRAINT FK_Employee_Department FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL,
-- ADD CONSTRAINT FK_Employee_Manager FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL;

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
--     Employee_ID VARCHAR(255) NOT NULL,
--     Performance_Rating ENUM('Poor', 'Average', 'Good', 'Excellent') NOT NULL,
--     Strengths TEXT,
--     Recommendations TEXT
-- );

-- -- Attendance Table
-- CREATE TABLE Attendance (
--     Attendance_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID VARCHAR(255) NOT NULL,
--     Date DATE NOT NULL,
--     Log_In_Time TIME NOT NULL,
--     Log_Out_Time TIME DEFAULT NULL,
--     Work_Hours DECIMAL(5,2) GENERATED ALWAYS AS (
--         IF(Log_Out_Time IS NOT NULL, TIMESTAMPDIFF(MINUTE, Log_In_Time, Log_Out_Time) / 60, NULL)
--     ) STORED
-- );

-- -- Leave Management Table
-- CREATE TABLE Leave_Management (
--     Leave_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID VARCHAR(255) NOT NULL,
--     Leave_Type ENUM('Sick Leave', 'Casual Leave', 'Annual Leave', 'Maternity Leave') NOT NULL,
--     Start_Date DATE NOT NULL,
--     End_Date DATE NOT NULL,
--     Approval_Status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
--     Leave_Reason TEXT,
--     Duty_Covering VARCHAR(100)
-- );

-- DELIMITER //
-- CREATE TRIGGER before_leave_insert
-- BEFORE INSERT ON Leave_Management
-- FOR EACH ROW
-- BEGIN
--     IF NEW.End_Date <= NEW.Start_Date THEN
--         SIGNAL SQLSTATE '45000'
--         SET MESSAGE_TEXT = 'End_Date must be later than Start_Date';
--     END IF;
-- END;
-- //
-- DELIMITER ;

-- -- Payroll Table
-- CREATE TABLE Payroll (
--     Payroll_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID INT NOT NULL,
--     Base_Salary DECIMAL(10,2) NOT NULL CHECK (Base_Salary >= 0),
--     Deductions DECIMAL(10,2) DEFAULT 0 CHECK (Deductions >= 0),
--     Net_Salary DECIMAL(10,2) GENERATED ALWAYS AS (Base_Salary - Deductions) STORED,
--     Payment_Method ENUM('Bank Transfer', 'Cheque', 'Cash') NOT NULL,
--     Payment_Date DATE NOT NULL
-- );

-- -- -- User Accounts Table
-- -- CREATE TABLE User_Account (
-- --     User_ID INT PRIMARY KEY AUTO_INCREMENT,
-- --     Employee_ID INT UNIQUE NOT NULL,
-- --     Username VARCHAR(50) NOT NULL UNIQUE,
-- --     Password VARCHAR(255) NOT NULL,
-- --     User_Type ENUM('Admin', 'Manager', 'Employee') NOT NULL
-- -- );


-- ALTER TABLE Employee_Performance 
-- ADD CONSTRAINT FK_Performance_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;

-- ALTER TABLE Attendance 
-- ADD CONSTRAINT FK_Attendance_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;

-- ALTER TABLE Leave_Management 
-- ADD CONSTRAINT FK_Leave_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;

-- ALTER TABLE Payroll 
-- ADD CONSTRAINT FK_Payroll_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;

-- ALTER TABLE User_Account 
-- ADD CONSTRAINT FK_User_Account_Employee FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE;



-- -- Create Database
-- CREATE DATABASE IF NOT EXISTS hris_db;
-- USE hris_db;

-- -- Department Table (without Manager_ID FK initially)
-- CREATE TABLE Department (
--     Department_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Department_Name VARCHAR(100) NOT NULL UNIQUE,
--     Location VARCHAR(255)
-- );

-- -- Employee Table
-- CREATE TABLE Employee (
--     Employee_ID VARCHAR(10) PRIMARY KEY, -- Format: HM0001 or EMP0001
--     Password VARCHAR(255) NOT NULL,
--     Employee_Type ENUM('HumanResource Manager', 'Employee', 'Manager') NOT NULL,
--     First_Name VARCHAR(50) NOT NULL,
--     Last_Name VARCHAR(50) NOT NULL,
--     Date_of_Birth DATE NOT NULL,
--     Gender ENUM('Male','Female') NOT NULL,
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
--     Manager_ID VARCHAR(10) NULL,
--     file_name VARCHAR(255) NOT NULL,
--     uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL,
--     FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL
-- );

-- -- Manager Table
-- CREATE TABLE Manager (
--     Manager_ID VARCHAR(10) PRIMARY KEY,
--     Employee_ID VARCHAR(10) UNIQUE NOT NULL,
--     Department_ID INT NOT NULL,
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE,
--     FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE CASCADE
-- );

-- -- Add Department Manager_ID FK after Employee table creation
-- ALTER TABLE Department 
-- ADD COLUMN Manager_ID VARCHAR(10) NULL,
-- ADD CONSTRAINT FK_Department_Manager FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL;

-- -- Create a sequence table to track the auto-increment value for Employee_ID
-- CREATE TABLE Employee_ID_Sequence (
--     ID INT PRIMARY KEY AUTO_INCREMENT
-- );

-- -- Trigger to auto-generate Employee_ID
-- DELIMITER //
-- CREATE TRIGGER before_insert_employee
-- BEFORE INSERT ON Employee
-- FOR EACH ROW
-- BEGIN
--     DECLARE new_id INT;
--     DECLARE prefix VARCHAR(5);

--     IF NEW.Employee_Type = 'HumanResource Manager' THEN
--         SET prefix = 'HM';
--     ELSE IF NEW.Employee_Type = 'Manager' THEN
--         SET prefix = 'MAN';
--     ELSE
--         SET prefix = 'EMP';
--     END IF;

--     INSERT INTO Employee_ID_Sequence VALUES (NULL);
--     SET new_id = LAST_INSERT_ID();

--     SET NEW.Employee_ID = CONCAT(prefix, LPAD(new_id, 4, '0'));
-- END;
-- //
-- DELIMITER ;

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
--     Employee_ID VARCHAR(10) NOT NULL,
--     Performance_Rating ENUM('Poor', 'Average', 'Good', 'Excellent') NOT NULL,
--     Strengths TEXT,
--     Recommendations TEXT,
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );

-- -- Attendance Table
-- CREATE TABLE Attendance (
--     Attendance_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID VARCHAR(10) NOT NULL,
--     Date DATE NOT NULL,
--     Log_In_Time TIME NOT NULL,
--     Log_Out_Time TIME DEFAULT NULL,
--     Work_Hours DECIMAL(5,2) GENERATED ALWAYS AS (
--         IF(Log_Out_Time IS NOT NULL, TIMESTAMPDIFF(MINUTE, Log_In_Time, Log_Out_Time) / 60, NULL)
--     ) STORED,
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );

-- -- Leave Management Table
-- CREATE TABLE Leave_Management (
--     Leave_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID VARCHAR(10) NOT NULL,
--     Leave_Type ENUM('Sick Leave', 'Casual Leave', 'Annual Leave', 'Maternity Leave') NOT NULL,
--     Start_Date DATE NOT NULL,
--     End_Date DATE NOT NULL,
--     Approval_Status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
--     Leave_Reason TEXT,
--     Duty_Covering VARCHAR(100),
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );

-- -- Trigger to validate Leave Start_Date and End_Date
-- DELIMITER //
-- CREATE TRIGGER before_leave_insert
-- BEFORE INSERT ON Leave_Management
-- FOR EACH ROW
-- BEGIN
--     IF NEW.End_Date <= NEW.Start_Date THEN
--         SIGNAL SQLSTATE '45000'
--         SET MESSAGE_TEXT = 'End_Date must be later than Start_Date';
--     END IF;
-- END;
-- //
-- DELIMITER ;

-- -- Payroll Table
-- CREATE TABLE Payroll (
--     Payroll_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID VARCHAR(10) NOT NULL,
--     Base_Salary DECIMAL(10,2) NOT NULL CHECK (Base_Salary >= 0),
--     Deductions DECIMAL(10,2) DEFAULT 0 CHECK (Deductions >= 0),
--     Net_Salary DECIMAL(10,2) GENERATED ALWAYS AS (Base_Salary - Deductions) STORED,
--     Payment_Method ENUM('Bank Transfer', 'Cheque', 'Cash') NOT NULL,
--     Payment_Date DATE NOT NULL,
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );

-- CREATE TABLE User_Account (
--     User_ID INT PRIMARY KEY AUTO_INCREMENT,
--     Employee_ID VARCHAR(10) UNIQUE NOT NULL,
--     Username VARCHAR(50) NOT NULL UNIQUE,
--     Password VARCHAR(255) NOT NULL,
--     User_Type ENUM('Admin', 'Manager', 'Employee') NOT NULL,
--     FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
-- );

-- ALTER TABLE Employee 
-- MODIFY COLUMN Employee_Type ENUM('HumanResource Manager', 'Employee', 'Manager') NOT NULL;


-- Create Database
CREATE DATABASE IF NOT EXISTS hris_db;
USE hris_db;

-- Department Table
CREATE TABLE Department (
    Department_ID INT PRIMARY KEY AUTO_INCREMENT,
    Department_Name VARCHAR(100) NOT NULL UNIQUE,
    Location VARCHAR(255)
);

-- Employee Table
CREATE TABLE Employee (
    Employee_ID VARCHAR(10) PRIMARY KEY,
    Password VARCHAR(255) NOT NULL,
    Employee_Type ENUM('HumanResource Manager', 'Employee', 'Manager') NOT NULL,
    First_Name VARCHAR(50) NOT NULL,
    Last_Name VARCHAR(50) NOT NULL,
    Date_of_Birth DATE NOT NULL,
    Gender ENUM('Male','Female') NOT NULL,
    Address TEXT,
    Contact_Number VARCHAR(15) UNIQUE,
    Email VARCHAR(100) UNIQUE,
    Qualification TEXT,
    Insurance VARCHAR(50),
    Blood_Type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') DEFAULT NULL,
    Marital_Status ENUM('Single', 'Married', 'Divorced', 'Widowed') DEFAULT 'Single',
    Hire_Date DATE NOT NULL,
    Salary DECIMAL(10,2) NOT NULL CHECK (Salary >= 0),
    Department_ID INT NULL,
    Manager_ID VARCHAR(10) NULL,
    file_name VARCHAR(255) NOT NULL,
    uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL
);

-- Manager Table
CREATE TABLE Manager (
    Manager_ID VARCHAR(10) PRIMARY KEY,
    Employee_ID VARCHAR(10) UNIQUE NOT NULL,
    Department_ID INT NOT NULL,
    FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE,
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE CASCADE
);

-- Add Department Manager_ID FK
ALTER TABLE Department 
ADD COLUMN Manager_ID VARCHAR(10) NULL,
ADD CONSTRAINT FK_Department_Manager FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL;

-- Create sequence table for Employee_ID
CREATE TABLE Employee_ID_Sequence (
    ID INT PRIMARY KEY AUTO_INCREMENT
);

-- Trigger for auto-generating Employee_ID
DELIMITER //
CREATE TRIGGER before_insert_employee
BEFORE INSERT ON Employee
FOR EACH ROW
BEGIN
    DECLARE new_id INT;
    DECLARE prefix VARCHAR(5);

    IF NEW.Employee_Type = 'HumanResource Manager' THEN
        SET prefix = 'HM';
    ELSEIF NEW.Employee_Type = 'Manager' THEN
        SET prefix = 'MAN';
    ELSE
        SET prefix = 'EMP';
    END IF;
    
    INSERT INTO Employee_ID_Sequence VALUES (NULL);
    SET new_id = LAST_INSERT_ID();
    
    SET NEW.Employee_ID = CONCAT(prefix, LPAD(new_id, 4, '0'));
END;
//
DELIMITER ;

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
    Employee_ID VARCHAR(10) NOT NULL,
    Performance_Rating ENUM('Poor', 'Average', 'Good', 'Excellent') NOT NULL,
    Strengths TEXT,
    Recommendations TEXT,
    FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
);

-- Attendance Table
CREATE TABLE Attendance (
    Attendance_ID INT PRIMARY KEY AUTO_INCREMENT,
    Employee_ID VARCHAR(10) NOT NULL,
    Date DATE NOT NULL,
    Log_In_Time TIME NOT NULL,
    Log_Out_Time TIME DEFAULT NULL,
    Work_Hours DECIMAL(5,2) GENERATED ALWAYS AS (
        IF(Log_Out_Time IS NOT NULL, TIMESTAMPDIFF(MINUTE, Log_In_Time, Log_Out_Time) / 60, NULL)
    ) STORED,
    FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
);

CREATE TABLE Leave_Management (
    Leave_ID INT PRIMARY KEY AUTO_INCREMENT,
    Employee_ID VARCHAR(10) NOT NULL,
    Leave_Type ENUM('Sick Leave', 'Casual Leave', 'Annual Leave', 'Maternity Leave') NOT NULL,
    Start_Date DATE NOT NULL,
    End_Date DATE NOT NULL,
    Approval_Status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    Leave_Reason TEXT,
    Duty_Covering VARCHAR(100),
    FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
);

DELIMITER //
CREATE TRIGGER before_leave_insert
BEFORE INSERT ON Leave_Management
FOR EACH ROW
BEGIN
    IF NEW.End_Date IS NULL OR NEW.Start_Date IS NULL OR NEW.End_Date <= NEW.Start_Date THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'End_Date must be later than Start_Date';
    END IF;
END;
//
DELIMITER ;

CREATE TABLE Payroll (
    Payroll_ID INT PRIMARY KEY AUTO_INCREMENT,
    Employee_ID VARCHAR(10) NOT NULL,
    Base_Salary DECIMAL(10,2) NOT NULL CHECK (Base_Salary >= 0),
    Deductions DECIMAL(10,2) DEFAULT 0 CHECK (Deductions >= 0),
    -- Net_Salary DECIMAL(10,2) GENERATED ALWAYS AS (
    --     GREATEST(Base_Salary - Deductions, 0)
    -- ) STORED,
    Payment_Method ENUM('Bank Transfer', 'Cheque', 'Cash') NOT NULL,
    Payment_Date DATE NOT NULL,
    FOREIGN KEY (Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE CASCADE
);
-- Modify Payroll Table: Add missing columns
ALTER TABLE Payroll 
ADD COLUMN Fixed_Allowances DECIMAL(10,2) DEFAULT 0,
ADD COLUMN Overtime_Pay DECIMAL(10,2) DEFAULT 0,
ADD COLUMN Unpaid_Leave_Deductions DECIMAL(10,2) DEFAULT 0,
ADD COLUMN Loan_Recovery DECIMAL(10,2) DEFAULT 0,
ADD COLUMN PAYE_Tax DECIMAL(10,2) DEFAULT 0,
ADD COLUMN Employee_EPF DECIMAL(10,2) DEFAULT 0,
ADD COLUMN Employer_EPF DECIMAL(10,2) DEFAULT 0,
ADD COLUMN Employer_ETF DECIMAL(10,2) DEFAULT 0,
ADD COLUMN Gross_Salary DECIMAL(10,2) DEFAULT 0,
ADD COLUMN Total_Deductions DECIMAL(10,2) DEFAULT 0,
ADD COLUMN Net_Salary DECIMAL(10,2) DEFAULT 0;

-- Create a Trigger to Calculate Payroll Components
DELIMITER //
CREATE TRIGGER before_insert_payroll
BEFORE INSERT ON Payroll
FOR EACH ROW
BEGIN
    -- Calculate Employee EPF (8% of Base Salary)
    SET NEW.Employee_EPF = NEW.Base_Salary * 0.08;
    
    -- Calculate Employer EPF (12% of Base Salary)
    SET NEW.Employer_EPF = NEW.Base_Salary * 0.12;
    
    -- Calculate Employer ETF (3% of Base Salary)
    SET NEW.Employer_ETF = NEW.Base_Salary * 0.03;
    
    -- Calculate Gross Salary (Base Salary + Fixed Allowances + Overtime - Unpaid Leave)
    SET NEW.Gross_Salary = NEW.Base_Salary + NEW.Fixed_Allowances + NEW.Overtime_Pay - NEW.Unpaid_Leave_Deductions;
    
    -- Calculate Total Deductions (Employee EPF + PAYE Tax + Loan Recovery)
    SET NEW.Total_Deductions = NEW.Employee_EPF + NEW.PAYE_Tax + NEW.Loan_Recovery;
    
    -- Calculate Net Salary (Gross Salary - Total Deductions)
    SET NEW.Net_Salary = NEW.Gross_Salary - NEW.Total_Deductions;
END;
//
DELIMITER ;


-- Project Table (standalone)
CREATE TABLE Project (
    Project_ID INT PRIMARY KEY AUTO_INCREMENT,
    Project_Name VARCHAR(100) NOT NULL,
    Description TEXT,
    Start_Date DATE NOT NULL,
    End_Date DATE NOT NULL,
    Budget DECIMAL(15,2) CHECK (Budget >= 0),
    Status ENUM('Planning', 'In Progress', 'On Hold', 'Completed', 'Cancelled') DEFAULT 'Planning',
    Department_ID INT,
    Manager_ID VARCHAR(10),
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL,
    FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL
);







-- Event Table
CREATE TABLE Event (
    Event_ID INT PRIMARY KEY AUTO_INCREMENT,
    Event_Name VARCHAR(255) NOT NULL,
    Event_Type ENUM('Meeting', 'Training', 'Workshop', 'Seminar', 'Conference', 'Team Building', 'Other') NOT NULL,
    Event_Description TEXT,
    Event_Date DATE NOT NULL,
    Event_Time TIME NOT NULL,
    Location VARCHAR(255),
    Organizer VARCHAR(100),
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE Attendance 
ADD COLUMN Assigned_Task VARCHAR(255) NOT NULL,
ADD COLUMN Task_Completion DECIMAL(5,2) NOT NULL,
ADD COLUMN Comments TEXT,
MODIFY COLUMN Work_Hours DECIMAL(5,2) NOT NULL;