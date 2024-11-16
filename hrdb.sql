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
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE stationtbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stationname VARCHAR(100) NOT NULL,
    stationcode VARCHAR(10) NOT NULL UNIQUE,
    stationtype ENUM('Hub', 'Spoke', 'International') DEFAULT 'Hub',
    operationtype ENUM('Full', 'Limited', 'Seasonal') DEFAULT 'Full',
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

-- Second-level tables (depend on first-level)
CREATE TABLE departmenttbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    businesscode VARCHAR(10),
    deptcode VARCHAR(10) NOT NULL UNIQUE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (businesscode) REFERENCES businessunittbl(businesscode)
) ENGINE=InnoDB;

-- Third-level tables (depend on second-level)
CREATE TABLE departmentunit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deptcode VARCHAR(10),
    deptunitname VARCHAR(100) NOT NULL,
    deptunitcode VARCHAR(10) NOT NULL UNIQUE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deptcode) REFERENCES departmenttbl(deptcode)
) ENGINE=InnoDB;

-- Fourth-level tables (depend on third-level)
CREATE TABLE staffheadcount (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deptunitcode VARCHAR(10),
    shcnostaff INT NOT NULL,
    shcwaiver VARCHAR(50),
    shctotal INT NOT NULL,
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deptunitcode) REFERENCES departmentunit(deptunitcode)
) ENGINE=InnoDB;

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

CREATE TABLE employeetbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deptunitcode VARCHAR(10),
    staffname VARCHAR(100) NOT NULL,
    staffid VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    createdby VARCHAR(100),
    dandt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deptunitcode) REFERENCES departmentunit(deptunitcode)
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
    status ENUM('pending', 'processed', 'draft') DEFAULT 'draft',
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
INSERT INTO businessunittbl (businessunit, businesscode, createdby) VALUES
('Operations', 'OPS', 'adewole.o@acn.aero'),
('Commercial', 'COM', 'adewole.o@acn.aero'),
('Finance', 'FIN', 'adewole.o@acn.aero');

INSERT INTO departments (departmentname, departmentcode, createdby) VALUES
('Flight Operations', 'FLT', 'adewole.o@acn.aero'),
('Ground Operations', 'GND', 'adewole.o@acn.aero'),
('Marketing', 'MKT', 'adewole.o@acn.aero');

INSERT INTO stationtbl (stationname, stationcode, stationtype, operationtype, createdby) VALUES
('Lagos', 'LOS', 'Hub', 'Full', 'adewole.o@acn.aero'),
('Abuja', 'ABV', 'Hub', 'Full', 'adewole.o@acn.aero'),
('Kano', 'KAN', 'Spoke', 'Limited', 'adewole.o@acn.aero');

INSERT INTO stafftype (stafftype, stprefix, createdby) VALUES
('Permanent', 'PER', 'adewole.o@acn.aero'),
('Contract', 'CON', 'adewole.o@acn.aero'),
('Temporary', 'TMP', 'adewole.o@acn.aero');

-- Second-level inserts
INSERT INTO departmenttbl (businesscode, deptcode, createdby) VALUES
('OPS', 'FLT', 'adewole.o@acn.aero'),
('OPS', 'GND', 'adewole.o@acn.aero'),
('COM', 'MKT', 'adewole.o@acn.aero'),
('OPS', 'ICT', 'adewole.o@acn.aero');

-- Third-level inserts
INSERT INTO departmentunit (deptcode, deptunitname, deptunitcode, createdby) VALUES
('FLT', 'Pilot Operations', 'PLT', 'adewole.o@acn.aero'),
('GND', 'Ramp Operations', 'RMP', 'adewole.o@acn.aero'),
('MKT', 'Digital Marketing', 'DGM', 'adewole.o@acn.aero'),
('ICT', 'Information Technology', 'ICT', 'adewole.o@acn.aero');

-- Fourth-level inserts
-- Note: shctotal is shcwaiver + shcnostaff
INSERT INTO staffheadcount (deptunitcode, shcnostaff, shcwaiver, shctotal, createdby) VALUES 
('PLT', 50, '0', 50, 'adewole.o@acn.aero'),
('RMP', 50, '0', 50, 'adewole.o@acn.aero'),
('DGM', 50, '0', 50, 'adewole.o@acn.aero'),
('ICT', 50, '0', 50, 'adewole.o@acn.aero');

INSERT INTO positiontbl (deptunitcode, poname, createdby) VALUES
('PLT', 'Captain', 'adewole.o@acn.aero'),
('RMP', 'Ramp Supervisor', 'adewole.o@acn.aero'),
('DGM', 'Digital Marketing Manager', 'adewole.o@acn.aero');

INSERT INTO jobtitletbl (deptunitcode, jdtitle, jddescription, eduqualification, jdposition, createdby) VALUES
('PLT', 'Senior Captain', 'Lead pilot position with extensive experience', 'ATPL License', 'Senior Management', 'adewole.o@acn.aero'),
('RMP', 'Ramp Operations Manager', 'Oversee all ramp operations', 'Bachelors Degree', 'Middle Management', 'adewole.o@acn.aero'),
('DGM', 'Digital Marketing Specialist', 'Handle digital marketing campaigns', 'Bachelors Degree', 'Officer', 'adewole.o@acn.aero');

-- Fifth-level inserts
INSERT INTO reportingline (rponame, linemanager, createdby) VALUES
('Captain', 'Chief Pilot', 'adewole.o@acn.aero'),
('Ramp Supervisor', 'Ground Operations Manager', 'adewole.o@acn.aero'),
('Digital Marketing Manager', 'Marketing Director', 'adewole.o@acn.aero');

INSERT INTO staffrequest (jdrequestid, jdtitle, novacpost, deptunitcode, status, createdby) VALUES
('REQ20240001', 'Senior Captain', 2, 'PLT', 'pending', 'adewole.o@acn.aero'),
('REQ20240002', 'Ramp Operations Manager', 3, 'RMP', 'pending', 'adewole.o@acn.aero'),
('REQ20240003', 'Digital Marketing Specialist', 1, 'DGM', 'pending', 'adewole.o@acn.aero');

-- Sixth-level inserts
INSERT INTO staffrequestperstation (jdrequestid, station, employmenttype, staffperstation, status, createdby) VALUES
('REQ20240001', 'LOS', 'Permanent', 1, 'pending', 'adewole.o@acn.aero'),
('REQ20240001', 'ABV', 'Permanent', 1, 'pending', 'adewole.o@acn.aero'),
('REQ20240002', 'LOS', 'Contract', 2, 'pending', 'adewole.o@acn.aero'),
('REQ20240002', 'KAN', 'Contract', 1, 'pending', 'adewole.o@acn.aero'),
('REQ20240003', 'LOS', 'Permanent', 1, 'pending', 'adewole.o@acn.aero');

-- Insert employees
INSERT INTO employeetbl (deptunitcode, staffname, staffid, status, createdby) VALUES
('PLT', 'John Smith', 'PL001', 'Active', 'adewole.o@acn.aero'),
('PLT', 'Sarah Johnson', 'PL002', 'Active', 'adewole.o@acn.aero'),
('RMP', 'Michael Brown', 'RM001', 'Active', 'adewole.o@acn.aero'),
('RMP', 'Jessica Williams', 'RM002', 'Active', 'adewole.o@acn.aero'),
('DGM', 'David Wilson', 'DM001', 'Active', 'adewole.o@acn.aero'),
('DGM', 'Emily Davis', 'DM002', 'Active', 'adewole.o@acn.aero');