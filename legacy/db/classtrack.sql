CREATE TABLE instructors (
    employee_id INT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    department VARCHAR(100),
    position VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE students (
    student_number INT PRIMARY KEY,
    student_type ENUM('Regular', 'Irregular') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    section VARCHAR(50),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE classes (
    class_id INT PRIMARY KEY AUTO_INCREMENT,
    course_title VARCHAR(100),
    course_code VARCHAR(50) NOT NULL,
    section VARCHAR(50) NOT NULL,
    class_schedule VARCHAR(100)
);

CREATE TABLE student_classes (
    student_number INT NOT NULL,
    class_id INT NOT NULL,
    PRIMARY KEY (student_number, class_id),
    FOREIGN KEY (student_number) REFERENCES students(student_number) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE
);

CREATE TABLE instructor_classes (
    employee_id INT NOT NULL,
    class_id INT NOT NULL,
    PRIMARY KEY (employee_id, class_id),
    FOREIGN KEY (employee_id) REFERENCES instructors(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE
);

CREATE TABLE attendance_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE
);

CREATE TABLE absence_reports (
    report_id INT PRIMARY KEY AUTO_INCREMENT,
    remarks ENUM('Pending', 'Validated', 'Rejected'),
    reason TEXT,
    file_upload VARCHAR(255),
    feedback TEXT,
    class_id INT NOT NULL,
    student_number INT NOT NULL,
    record_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (student_number) REFERENCES students(student_number) ON DELETE CASCADE,
    FOREIGN KEY (record_id) REFERENCES attendance_records(record_id) ON DELETE CASCADE
);

CREATE TABLE student_attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    status ENUM('Present', 'Absent', 'Late'),
    class_id INT NOT NULL,
    student_number INT NOT NULL,
    record_id INT NOT NULL,
    report_id INT,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (student_number) REFERENCES students(student_number) ON DELETE CASCADE,
    FOREIGN KEY (record_id) REFERENCES attendance_records(record_id) ON DELETE CASCADE,
    FOREIGN KEY (report_id) REFERENCES absence_reports(report_id) ON DELETE SET NULL
);