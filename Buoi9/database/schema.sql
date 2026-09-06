CREATE DATABASE IF NOT EXISTS quan_ly_hoc_phan
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE quan_ly_hoc_phan;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS
    edit_requests,
    grades,
    registrations,
    class_schedules,
    classes,
    course_lecturers,
    course_semesters,
    course_faculties,
    courses,
    lecturers,
    students,
    semesters,
    faculties,
    users;

SET FOREIGN_KEY_CHECKS = 1;


-- =========================================================
-- 1. USERS
-- =========================================================

CREATE TABLE users (

    id INT AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(50) NOT NULL UNIQUE,

    password_hash VARCHAR(255) NOT NULL,

    full_name VARCHAR(100) NOT NULL,

    role ENUM('admin', 'teacher', 'student') NOT NULL,

    status TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 2. FACULTIES
-- =========================================================

CREATE TABLE faculties (

    id INT AUTO_INCREMENT PRIMARY KEY,

    code VARCHAR(20) NOT NULL UNIQUE,

    name VARCHAR(150) NOT NULL UNIQUE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 3. SEMESTERS
-- =========================================================

CREATE TABLE semesters (

    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    academic_year VARCHAR(9) NOT NULL,

    term ENUM('1', '2', '3') NOT NULL,

    study_start DATE NOT NULL,

    study_end DATE NOT NULL,

    registration_start DATE NOT NULL,

    registration_end DATE NOT NULL,

    registration_open TINYINT(1) NOT NULL DEFAULT 0,

    status TINYINT(1) NOT NULL DEFAULT 1,

    CHECK (study_start <= study_end),

    CHECK (registration_start <= registration_end)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 4. STUDENTS
-- =========================================================

CREATE TABLE students (

    user_id INT PRIMARY KEY,

    student_code VARCHAR(30) NOT NULL UNIQUE,

    faculty_id INT NOT NULL,

    class_name VARCHAR(80) NOT NULL,

    cohort VARCHAR(20) NOT NULL,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (faculty_id)
        REFERENCES faculties(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 5. LECTURERS
-- =========================================================

CREATE TABLE lecturers (

    user_id INT PRIMARY KEY,

    lecturer_code VARCHAR(30) NOT NULL UNIQUE,

    faculty_id INT NOT NULL,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (faculty_id)
        REFERENCES faculties(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 6. COURSES
-- =========================================================

CREATE TABLE courses (

    id INT AUTO_INCREMENT PRIMARY KEY,

    code VARCHAR(50) NOT NULL UNIQUE,

    name VARCHAR(150) NOT NULL,

    credits TINYINT UNSIGNED NOT NULL,

    description TEXT NULL,

    status TINYINT(1) NOT NULL DEFAULT 1,

    CHECK (credits BETWEEN 1 AND 10)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 7. COURSE - FACULTY
-- =========================================================

CREATE TABLE course_faculties (

    course_id INT NOT NULL,

    faculty_id INT NOT NULL,

    PRIMARY KEY (course_id, faculty_id),

    FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE,

    FOREIGN KEY (faculty_id)
        REFERENCES faculties(id)
        ON DELETE RESTRICT

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 8. COURSE - SEMESTER
-- =========================================================

CREATE TABLE course_semesters (

    course_id INT NOT NULL,

    semester_id INT NOT NULL,

    PRIMARY KEY (course_id, semester_id),

    FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE,

    FOREIGN KEY (semester_id)
        REFERENCES semesters(id)
        ON DELETE RESTRICT

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 9. COURSE - LECTURER
-- Một học phần có thể có nhiều giảng viên được phép dạy
-- =========================================================

CREATE TABLE course_lecturers (

    course_id INT NOT NULL,

    lecturer_id INT NOT NULL,

    PRIMARY KEY (course_id, lecturer_id),

    FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE,

    FOREIGN KEY (lecturer_id)
        REFERENCES lecturers(user_id)
        ON DELETE RESTRICT

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 10. CLASSES
-- Lớp học phần
-- =========================================================

CREATE TABLE classes (

    id INT AUTO_INCREMENT PRIMARY KEY,

    class_code VARCHAR(50) NOT NULL,

    course_id INT NOT NULL,

    semester_id INT NOT NULL,

    lecturer_id INT NOT NULL,

    max_students SMALLINT UNSIGNED NOT NULL,

    status TINYINT(1) NOT NULL DEFAULT 1,

    UNIQUE KEY uk_class_semester_code (
        semester_id,
        class_code
    ),

    KEY idx_classes_course (
        course_id
    ),

    KEY idx_classes_semester (
        semester_id
    ),

    KEY idx_classes_lecturer (
        lecturer_id
    ),

    FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (semester_id)
        REFERENCES semesters(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (lecturer_id)
        REFERENCES lecturers(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CHECK (max_students BETWEEN 1 AND 500)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 11. CLASS SCHEDULES
-- Một lớp học phần có thể có nhiều lịch học
-- =========================================================

CREATE TABLE class_schedules (

    id INT AUTO_INCREMENT PRIMARY KEY,

    class_id INT NOT NULL,

    weekday TINYINT UNSIGNED NOT NULL,

    start_period TINYINT UNSIGNED NOT NULL,

    end_period TINYINT UNSIGNED NOT NULL,

    start_date DATE NOT NULL,

    end_date DATE NOT NULL,

    room VARCHAR(100) NOT NULL,

    KEY idx_schedule_class (
        class_id
    ),

    KEY idx_schedule_conflict (
        weekday,
        start_period,
        end_period,
        start_date,
        end_date
    ),

    FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CHECK (weekday BETWEEN 2 AND 8),

    CHECK (
        start_period BETWEEN 1 AND 15
        AND end_period BETWEEN 1 AND 15
        AND start_period <= end_period
    ),

    CHECK (start_date <= end_date)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 12. REGISTRATIONS
-- Sinh viên đăng ký lớp học phần
-- =========================================================

CREATE TABLE registrations (

    id INT AUTO_INCREMENT PRIMARY KEY,

    student_id INT NOT NULL,

    class_id INT NOT NULL,

    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_student_class (
        student_id,
        class_id
    ),

    KEY idx_registration_student (
        student_id
    ),

    KEY idx_registration_class (
        class_id
    ),

    FOREIGN KEY (student_id)
        REFERENCES students(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 13. GRADES
-- =========================================================

CREATE TABLE grades (

    id INT AUTO_INCREMENT PRIMARY KEY,

    registration_id INT NOT NULL UNIQUE,

    midterm DECIMAL(4,2) NULL,

    final_exam DECIMAL(4,2) NULL,

    total DECIMAL(4,2) NULL,

    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (registration_id)
        REFERENCES registrations(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CHECK (
        midterm IS NULL
        OR midterm BETWEEN 0 AND 10
    ),

    CHECK (
        final_exam IS NULL
        OR final_exam BETWEEN 0 AND 10
    ),

    CHECK (
        total IS NULL
        OR total BETWEEN 0 AND 10
    )

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 14. EDIT REQUESTS
-- =========================================================

CREATE TABLE edit_requests (

    id INT AUTO_INCREMENT PRIMARY KEY,

    teacher_id INT NOT NULL,

    class_id INT NOT NULL,

    registration_id INT NOT NULL,

    content TEXT NOT NULL,

    status ENUM(
        'pending',
        'approved',
        'rejected'
    ) DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (teacher_id)
        REFERENCES lecturers(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (class_id)
        REFERENCES classes(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (registration_id)
        REFERENCES registrations(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;