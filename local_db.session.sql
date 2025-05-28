CREATE TABLE class (
    id SERIAL PRIMARY KEY,
    class VARCHAR(50) NOT NULL,
    year INT NOT NULL
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
    teacher_code INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    dob DATE NOT NULL,
    number VARCHAR(20),
    subject_id INT,
    class_id INT UNSIGNED,
    CONSTRAINT fk_subject FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_class FOREIGN KEY (class_id) REFERENCES class(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

ALTER TABLE teachers
ADD COLUMN institute_id INT,
ADD CONSTRAINT fk_institute FOREIGN KEY (institute_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE;
