CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);


CREATE TABLE class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class VARCHAR(50) NOT NULL,
    year INT NOT NULL
);

ALTER TABLE class
ADD COLUMN institute_id INT;

ALTER TABLE class
ADD CONSTRAINT fk_class_institute
FOREIGN KEY (institute_id)
REFERENCES users(id)
ON DELETE CASCADE
ON UPDATE CASCADE;



CREATE TABLE student (
    student_code VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    dob DATE,
    institute_id INT,
    class_id INT NULL,
    stupassword VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (institute_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    FOREIGN KEY (class_id) REFERENCES class(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);



CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE subjects(
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(100) NOT NULL
);

INSERT INTO subjects (subject) VALUES ('Mathematics');
INSERT INTO subjects (subject) VALUES ('Science');
INSERT INTO subjects (subject) VALUES ('English');
INSERT INTO subjects (subject) VALUES ('Sinhala');
INSERT INTO subjects (subject) VALUES ('Tamil');
INSERT INTO subjects (subject) VALUES ('Islam');
INSERT INTO subjects (subject) VALUES ('GEO');
INSERT INTO subjects (subject) VALUES ('Civices');
INSERT INTO subjects (subject) VALUES ('History');



CREATE TABLE teachers (
    teacher_code VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    number VARCHAR(20),
    dob DATE,
    institute_id INT,
    class_id INT NULL,
    subject_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (institute_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    FOREIGN KEY (class_id) REFERENCES class(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);


CREATE TABLE schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class VARCHAR(50),
    year INT NOT NULL,
    subject VARCHAR(100),
    day VARCHAR(20),
    start_time TIME,
    end_time TIME,
    teacher_name VARCHAR(100)
);

ALTER TABLE schedule
ADD COLUMN institute_id INT;


ALTER TABLE schedule
ADD CONSTRAINT fk_institute
FOREIGN KEY (institute_id) REFERENCES users(id)
ON UPDATE CASCADE
ON DELETE CASCADE;



CREATE TABLE assignSubjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(50) NOT NULL,
    sub_id INT DEFAULT NULL,
    FOREIGN KEY (student_code) REFERENCES student(stupassword)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (sub_id) REFERENCES subjects(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

ALTER TABLE assignSubjects
ADD COLUMN institute_id INT(11);


ALTER TABLE assignSubjects
ADD CONSTRAINT fk_assignSubjects_institute
FOREIGN KEY (institute_id) REFERENCES users(id)
ON DELETE CASCADE
ON UPDATE CASCADE;


CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status ENUM('Present', 'Absent') NOT NULL,
    institute_id INT NOT NULL,
    student_code VARCHAR(50) NOT NULL,
    class VARCHAR(50) NOT NULL,
    year VARCHAR(10) NOT NULL,
    FOREIGN KEY (institute_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (student_code) REFERENCES student(stupassword)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

ALTER TABLE attendance
ADD COLUMN attendance_date DATE NOT NULL DEFAULT CURRENT_DATE;

ALTER TABLE attendance ADD COLUMN subject VARCHAR(255) NOT NULL;

CREATE TABLE classroom (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classroom VARCHAR(100) NOT NULL,
    description TEXT,
    institute_id INT NOT NULL,
    FOREIGN KEY (institute_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE classroom
ADD CONSTRAINT unique_institute_classroom UNIQUE (institute_id, classroom);

ALTER TABLE schedule
ADD hallNo VARCHAR(255);



ALTER TABLE schedule
ADD CONSTRAINT fk_schedule_hallNo
FOREIGN KEY (institute_id, hallNo)
REFERENCES classroom(institute_id, classroom)
ON DELETE SET NULL
ON UPDATE CASCADE;


ALTER TABLE teachers
DROP FOREIGN KEY teachers_ibfk_3;

ALTER TABLE teachers
DROP COLUMN subject_id;


CREATE TABLE assignSubjectsToTeacher (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_code VARCHAR(50) NOT NULL,
    sub_id INT DEFAULT NULL,
    FOREIGN KEY (teacher_code) REFERENCES teachers(teacher_code)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (sub_id) REFERENCES subjects(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

ALTER TABLE assignSubjectsToTeacher
ADD COLUMN institute_id INT(11);


ALTER TABLE assignSubjectsToTeacher
ADD CONSTRAINT fk_assignSubjectsToTeacher_institute
FOREIGN KEY (institute_id) REFERENCES users(id)
ON DELETE CASCADE
ON UPDATE CASCADE;