-- Create database if not exists
CREATE DATABASE IF NOT EXISTS hrdb;
USE hrdb;

-- createdby is the userid of the user who created the record make it: adewole.o@acn.aero
-- dandt should always be a varchar but date the values were created

-- First-level tables (no dependencies)
CREATE TABLE businessunittbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    businessunit VARCHAR(100) NOT NULL,
    businesscode VARCHAR(10) NOT NULL UNIQUE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departmentname VARCHAR(100) NOT NULL,
    departmentcode VARCHAR(10) NOT NULL UNIQUE,
    deptnostaff INT NOT NULL DEFAULT 0,
    deptwaiver INT NOT NULL DEFAULT 0,
    depttotal INT GENERATED ALWAYS AS (deptnostaff + deptwaiver) STORED,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE departmentunit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deptcode VARCHAR(10),
    deptunitname VARCHAR(100) NOT NULL,
    deptunitcode VARCHAR(10) NOT NULL UNIQUE,
    deptunitnostaff INT NOT NULL DEFAULT 0,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deptcode) REFERENCES departments(departmentcode)
) ENGINE=InnoDB;

CREATE TABLE subdeptunittbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deptunitcode VARCHAR(10),
    subdeptunit VARCHAR(100) NOT NULL,
    subdeptunitcode VARCHAR(10) NOT NULL UNIQUE,
    subdeptnostaff INT NOT NULL DEFAULT 0,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deptunitcode) REFERENCES departmentunit(deptunitcode)
) ENGINE=InnoDB;

-- Second-level tables (depend on first-level)
CREATE TABLE stationtbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stationname VARCHAR(100) NOT NULL,
    stationcode VARCHAR(10) NOT NULL UNIQUE,
    stationtype ENUM('Domestic','Regional','International') DEFAULT 'Domestic',
    operationtype ENUM('Fixed', 'Rotary') DEFAULT 'Fixed',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE stafftype (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stafftype VARCHAR(50) NOT NULL UNIQUE,
    stprefix VARCHAR(10) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Third-level tables (depend on second-level)
CREATE TABLE positiontbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deptunitcode VARCHAR(10),
    poname VARCHAR(100) NOT NULL UNIQUE,
    postatus ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deptunitcode) REFERENCES departmentunit(deptunitcode)
) ENGINE=InnoDB;

CREATE TABLE jobtitletbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deptunitcode VARCHAR(10),
    jdtitle VARCHAR(100) NOT NULL UNIQUE,
    jddescription TEXT,
    eduqualification VARCHAR(100),
    proqualification TEXT,
    workrelation TEXT,
    jdposition VARCHAR(100),
    jdcondition VARCHAR(100),
    agebracket VARCHAR(50),
    personspec TEXT,
    fuctiontech TEXT,
    managerial TEXT,
    behavioural TEXT,
    jdstatus ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deptunitcode) REFERENCES departmentunit(deptunitcode)
) ENGINE=InnoDB;

-- Create Sub Department Unit Table
CREATE TABLE employeetbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deptunitcode VARCHAR(10),
    subdeptunitcode VARCHAR(10),
    staffname VARCHAR(100) NOT NULL,
    staffid VARCHAR(20) NOT NULL UNIQUE,
    position VARCHAR(50),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deptunitcode) REFERENCES departmentunit(deptunitcode),
    FOREIGN KEY (subdeptunitcode) REFERENCES subdeptunittbl(subdeptunitcode),
    jdtitle VARCHAR(100),
    FOREIGN KEY (jdtitle) REFERENCES jobtitletbl(jdtitle)
) ENGINE=InnoDB;

-- Fifth-level tables (depend on fourth-level)
CREATE TABLE reportingline (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rponame VARCHAR(100),
    linemanager VARCHAR(100),
    postatus ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rponame) REFERENCES positiontbl(poname)
) ENGINE=InnoDB;

CREATE TABLE staffrequest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jdrequestid VARCHAR(20) UNIQUE NOT NULL,
    jdtitle VARCHAR(100) NOT NULL,
    novacpost INT NOT NULL,
    deptunitcode VARCHAR(10),
    status ENUM('draft', 'pending', 'processed') DEFAULT 'draft',
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdby VARCHAR(100),
    FOREIGN KEY (deptunitcode) REFERENCES departmentunit(deptunitcode),
    FOREIGN KEY (jdtitle) REFERENCES jobtitletbl(jdtitle)
) ENGINE=InnoDB;

-- Sixth-level tables (depend on fifth-level)
CREATE TABLE staffrequestperstation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jdrequestid VARCHAR(20),
    station VARCHAR(10),
    employmenttype VARCHAR(50),
    staffperstation INT NOT NULL,
    status ENUM('pending', 'approved', 'declined', '') DEFAULT 'pending',
    reason TEXT,
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createdby VARCHAR(100),
    FOREIGN KEY (jdrequestid) REFERENCES staffrequest(jdrequestid),
    FOREIGN KEY (station) REFERENCES stationtbl(stationcode),
    FOREIGN KEY (employmenttype) REFERENCES stafftype(stafftype)
) ENGINE=InnoDB;

-- First-level inserts (no dependencies)
INSERT INTO businessunittbl (businessunit, businesscode, status, createdby) VALUES
('Information Technology', 'ICT', 'Active', 'adewole.o@acn.aero'),
('Commercial', 'COM', 'Active', 'adewole.o@acn.aero'),
('Operations', 'OPS', 'Active', 'adewole.o@acn.aero');

INSERT INTO departments (departmentname, departmentcode, deptnostaff, deptwaiver, status, createdby) VALUES
('Information Technology', 'ICT', 100, 10, 'Active', 'adewole.o@acn.aero'),
('Commercial', 'COM', 150, 15, 'Active', 'adewole.o@acn.aero');

INSERT INTO stationtbl (stationname, stationcode, stationtype, operationtype, createdby) VALUES
('Lagos', 'LOS', 'Domestic', 'Fixed', 'adewole.o@acn.aero'),
('Abuja', 'ABV', 'Domestic', 'Fixed', 'adewole.o@acn.aero'),
('Kano', 'KAN', 'Domestic', 'Fixed', 'adewole.o@acn.aero'),
('United Kingdom', 'UK', 'International', 'Fixed', 'adewole.o@acn.aero');

INSERT INTO stafftype (stafftype, stprefix, createdby) VALUES
('Permanent', 'PER', 'adewole.o@acn.aero'),
('Contract', 'CON', 'adewole.o@acn.aero'),
('Temporary', 'TMP', 'adewole.o@acn.aero');

-- Second-level inserts
INSERT INTO departmentunit (deptcode, deptunitname, deptunitcode, deptunitnostaff, status, createdby) VALUES
-- ICT Department Units (no subunits)
('ICT', 'Development', 'DEV', 50, 'Active', 'adewole.o@acn.aero'),
('ICT', 'IT Support', 'ITS', 40, 'Active', 'adewole.o@acn.aero'),
-- Commercial Department Units (with subunits)
('COM', 'Sales', 'SLS', 60, 'Active', 'adewole.o@acn.aero'),
('COM', 'Marketing', 'MKT', 70, 'Active', 'adewole.o@acn.aero');

-- Third-level inserts
INSERT INTO positiontbl (deptunitcode, poname, createdby) VALUES
-- ICT Department positions
('DEV', 'Senior Developer', 'adewole.o@acn.aero'),
('DEV', 'Software Engineer', 'adewole.o@acn.aero'),
('ITS', 'IT Support Manager', 'adewole.o@acn.aero'),
('ITS', 'Support Engineer', 'adewole.o@acn.aero'),

-- Commercial Department positions
('SLS', 'Sales Team Lead', 'adewole.o@acn.aero'),
('SLS', 'Sales Officer', 'adewole.o@acn.aero'),
('MKT', 'Marketing Manager', 'adewole.o@acn.aero'),
('MKT', 'Digital Marketing Manager', 'adewole.o@acn.aero');

INSERT INTO jobtitletbl (deptunitcode, jdtitle, jddescription, eduqualification, jdposition, createdby) VALUES
-- ICT Department job titles
('DEV', 'Senior Developer', 'Lead development position', 'Bachelors Degree', 'Senior Management', 'adewole.o@acn.aero'),
('DEV', 'Software Engineer', 'Software development role', 'Bachelors Degree', 'Middle Management', 'adewole.o@acn.aero'),
('ITS', 'IT Support Manager', 'IT support leadership', 'Bachelors Degree', 'Middle Management', 'adewole.o@acn.aero'),
('ITS', 'IT Support Officer', 'IT support role', 'Bachelors Degree', 'Officer', 'adewole.o@acn.aero'),
('DEV', 'Senior Software Engineer', 'Senior development position', 'Bachelors Degree', 'Senior Management', 'adewole.o@acn.aero'),
('ITS', 'Systems Administrator', 'IT systems administration', 'Bachelors Degree', 'Middle Management', 'adewole.o@acn.aero'),
('MKT', 'Digital Marketing Specialist', 'Digital marketing role', 'Bachelors Degree', 'Officer', 'adewole.o@acn.aero'),

-- Commercial Department job titles
('SLS', 'Sales Team Lead', 'Sales team leadership', 'Bachelors Degree', 'TeamLead', 'adewole.o@acn.aero'),
('SLS', 'Sales Officer', 'Sales role', 'Bachelors Degree', 'Officer', 'adewole.o@acn.aero'),
('MKT', 'Marketing Manager', 'Marketing leadership', 'Bachelors Degree', 'Middle Management', 'adewole.o@acn.aero'),
('MKT', 'Digital Marketing Lead', 'Digital marketing leadership', 'Bachelors Degree', 'TeamLead', 'adewole.o@acn.aero');

-- Fifth-level inserts
INSERT INTO reportingline (rponame, linemanager, createdby) VALUES
('Senior Developer', 'IT Director', 'adewole.o@acn.aero'),
('IT Support Manager', 'IT Director', 'adewole.o@acn.aero'),
('Sales Team Lead', 'Commercial Director', 'adewole.o@acn.aero'),
('Digital Marketing Manager', 'Marketing Manager', 'adewole.o@acn.aero');

INSERT INTO staffrequest (jdrequestid, jdtitle, novacpost, deptunitcode, status, createdby) VALUES
('REQ20240001', 'Senior Software Engineer', 2, 'DEV', 'draft', 'adewole.o@acn.aero'),
('REQ20240002', 'Systems Administrator', 3, 'ITS', 'draft', 'adewole.o@acn.aero'),
('REQ20240003', 'Digital Marketing Specialist', 1, 'MKT', 'draft', 'adewole.o@acn.aero');


-- Sixth-level inserts
INSERT INTO staffrequestperstation (jdrequestid, station, employmenttype, staffperstation, status, createdby) VALUES
('REQ20240001', 'LOS', 'Permanent', 1, 'pending', 'adewole.o@acn.aero'),
('REQ20240001', 'ABV', 'Permanent', 1, 'pending', 'adewole.o@acn.aero'),
('REQ20240002', 'LOS', 'Contract', 2, 'pending', 'adewole.o@acn.aero'),
('REQ20240002', 'KAN', 'Contract', 1, 'pending', 'adewole.o@acn.aero'),
('REQ20240003', 'LOS', 'Permanent', 1, 'pending', 'adewole.o@acn.aero');

-- First, add all required sub-department units
INSERT INTO subdeptunittbl (deptunitcode, subdeptunit, subdeptunitcode, subdeptnostaff, status, createdby) VALUES
-- Commercial Subunits only
('SLS', 'Corporate Sales', 'CSLS', 30, 'Active', 'adewole.o@acn.aero'),
('SLS', 'Retail Sales', 'RSLS', 25, 'Active', 'adewole.o@acn.aero'),
('MKT', 'Digital Marketing', 'DMKT', 35, 'Active', 'adewole.o@acn.aero'),
('MKT', 'Brand Marketing', 'BMKT', 30, 'Active', 'adewole.o@acn.aero');

-- Add triggers to enforce headcount constraints
DELIMITER //

CREATE TRIGGER check_deptunit_headcount
BEFORE INSERT ON departmentunit
FOR EACH ROW
BEGIN
    DECLARE dept_total INT;
    SELECT depttotal INTO dept_total
    FROM departments 
    WHERE departmentcode = NEW.deptcode;
    
    IF NEW.deptunitnostaff > dept_total THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Department unit headcount cannot exceed department total';
    END IF;
END//

CREATE TRIGGER check_subdept_headcount
BEFORE INSERT ON subdeptunittbl
FOR EACH ROW
BEGIN
    DECLARE unit_total INT;
    SELECT deptunitnostaff INTO unit_total
    FROM departmentunit 
    WHERE deptunitcode = NEW.deptunitcode;
    
    IF NEW.subdeptnostaff > unit_total THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Subunit headcount cannot exceed department unit total';
    END IF;
END//

DELIMITER ;

-- First ensure we have all the job titles in jobtitletbl
INSERT INTO jobtitletbl (deptunitcode, jdtitle, jddescription, eduqualification, jdposition, createdby) VALUES
('DEV', 'IT Director', 'IT Department Head', 'Masters Degree', 'HOD', 'adewole.o@acn.aero'),
('SLS', 'Commercial Director', 'Commercial Department Head', 'Masters Degree', 'HOD', 'adewole.o@acn.aero');

-- Then insert employees with correct department unit codes
INSERT INTO employeetbl (deptunitcode, subdeptunitcode, staffname, staffid, position, jdtitle, status, createdby) VALUES
-- Department Heads (HOD) - must use existing department unit codes
('DEV', NULL, 'John Doe', 'ICT001', 'HOD', 'IT Director', 'Active', 'adewole.o@acn.aero'),
('SLS', NULL, 'Jane Smith', 'COM001', 'HOD', 'Commercial Director', 'Active', 'adewole.o@acn.aero'),

-- Department Unit Leads
('DEV', NULL, 'Bob Wilson', 'DEV001', 'DeptUnitLead', 'Senior Developer', 'Active', 'adewole.o@acn.aero'),
('ITS', NULL, 'Alice Brown', 'ITS001', 'DeptUnitLead', 'IT Support Manager', 'Active', 'adewole.o@acn.aero'),

-- Team Leads (for Commercial subunits)
('SLS', 'CSLS', 'Mike Johnson', 'CSLS001', 'TeamLead', 'Sales Team Lead', 'Active', 'adewole.o@acn.aero'),
('MKT', 'DMKT', 'Sarah Davis', 'DMKT001', 'TeamLead', 'Digital Marketing Lead', 'Active', 'adewole.o@acn.aero');


-- First add the HR and Executive departments
INSERT INTO departments (departmentname, departmentcode, deptnostaff, deptwaiver, status, createdby) VALUES
('Human Resources', 'HRD', 50, 5, 'Active', 'adewole.o@acn.aero'),
('Executive Management', 'EXE', 10, 0, 'Active', 'adewole.o@acn.aero');

-- Then add their department units
INSERT INTO departmentunit (deptcode, deptunitname, deptunitcode, deptunitnostaff, status, createdby) VALUES
('HRD', 'HR Operations', 'HRD', 30, 'Active', 'adewole.o@acn.aero'),
('EXE', 'Executive Office', 'EXE', 10, 'Active', 'adewole.o@acn.aero');

-- Add job titles for HR and Executive positions
INSERT INTO jobtitletbl (deptunitcode, jdtitle, jddescription, eduqualification, jdposition, createdby) VALUES
('HRD', 'Head of HR', 'HR Department Head', 'Masters Degree', 'HOD', 'adewole.o@acn.aero'),
('HRD', 'HR Manager', 'HR Operations Manager', 'Masters Degree', 'DeptUnitLead', 'adewole.o@acn.aero'),
('EXE', 'Chief Financial Officer', 'Financial Operations Head', 'Masters Degree', 'CFO', 'adewole.o@acn.aero'),
('EXE', 'Chief Executive Officer', 'Company Head', 'Masters Degree', 'CEO', 'adewole.o@acn.aero');

-- Add executive level employees
INSERT INTO employeetbl (deptunitcode, subdeptunitcode, staffname, staffid, position, jdtitle, status, createdby) VALUES
-- HR Department
('HRD', NULL, 'Sarah Wilson', 'HR001', 'HOD', 'Head of HR', 'Active', 'adewole.o@acn.aero'),
('HRD', NULL, 'James Brown', 'HR002', 'DeptUnitLead', 'HR Manager', 'Active', 'adewole.o@acn.aero'),

-- Executive Management
('EXE', NULL, 'Michael Chen', 'CFO001', 'CFO', 'Chief Financial Officer', 'Active', 'adewole.o@acn.aero'),
('EXE', NULL, 'Elizabeth Taylor', 'CEO001', 'CEO', 'Chief Executive Officer', 'Active', 'adewole.o@acn.aero');

-- Create approval table
CREATE TABLE approvaltbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jdrequestid VARCHAR(20),
    jdtitle VARCHAR(100),
    approverstaffid VARCHAR(20),
    approvallevel ENUM('TeamLead', 'DeptUnitLead', 'HOD', 'HR', 'HeadOfHR', 'CFO', 'CEO'),
    status ENUM('draft', 'pending', 'approved', 'declined') DEFAULT 'draft',
    comments TEXT,
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jdrequestid) REFERENCES staffrequest(jdrequestid),
    FOREIGN KEY (jdtitle) REFERENCES jobtitletbl(jdtitle),
    FOREIGN KEY (approverstaffid) REFERENCES employeetbl(staffid)
) ENGINE=InnoDB;

-- Create trigger to populate approval levels when staff request is created
DELIMITER //

CREATE TRIGGER create_approval_levels
AFTER INSERT ON staffrequest
FOR EACH ROW
BEGIN
    DECLARE deptheadid VARCHAR(20);
    DECLARE deptunitleadid VARCHAR(20);
    
    -- Get relevant approvers based on department
    SELECT staffid INTO deptheadid
    FROM employeetbl
    WHERE deptunitcode = NEW.deptunitcode AND position = 'HOD';
    
    SELECT staffid INTO deptunitleadid
    FROM employeetbl
    WHERE deptunitcode = NEW.deptunitcode AND position = 'DeptUnitLead';

    -- Insert approval levels
    IF deptunitleadid IS NOT NULL THEN
        INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
        VALUES (NEW.jdrequestid, NEW.jdtitle, deptunitleadid, 'DeptUnitLead', 'pending', NEW.createdby);
    END IF;

    -- HOD approval
    INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
    VALUES (NEW.jdrequestid, NEW.jdtitle, deptheadid, 'HOD', 'draft', NEW.createdby);

    -- HR approvals
    INSERT INTO approvaltbl (jdrequestid, jdtitle, approverstaffid, approvallevel, status, createdby)
    VALUES 
    (NEW.jdrequestid, NEW.jdtitle, 'HR002', 'HR', 'draft', NEW.createdby),
    (NEW.jdrequestid, NEW.jdtitle, 'HR001', 'HeadOfHR', 'draft', NEW.createdby),
    (NEW.jdrequestid, NEW.jdtitle, 'CFO001', 'CFO', 'draft', NEW.createdby),
    (NEW.jdrequestid, NEW.jdtitle, 'CEO001', 'CEO', 'draft', NEW.createdby);
END//

DELIMITER ;
