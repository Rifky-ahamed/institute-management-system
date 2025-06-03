CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);


CREATE TABLE class (
    id SERIAL PRIMARY KEY,
    class VARCHAR(50) NOT NULL,
    year INT NOT NULL
);

CREATE TABLE students (
    student_code VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    dob DATE,
    institute_id INT,
    class_id INT,
    stupassword VARCHAR(255) NOT NULL,
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
    id INT PRIMARY KEY AUTO_INCREMENT,
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
    class_id INT,
    subject_id INT,
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

