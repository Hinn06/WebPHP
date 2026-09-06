USE quan_ly_hoc_phan;

-- =========================================================
-- 1. USERS
-- =========================================================

INSERT INTO users
    (id, username, password_hash, full_name, role, status)
VALUES
    (1, 'admin',
     '$2y$12$eDI.Hak4yImuoiMuLaUoqe.MBCV7Q7lFMgliJZuA8J/e.NXItc6gC',
     'Quản trị viên', 'admin', 1),

    (2, 'teacher01',
     '$2y$12$eDI.Hak4yImuoiMuLaUoqe.MBCV7Q7lFMgliJZuA8J/e.NXItc6gC',
     'Nguyễn Văn An', 'teacher', 1),

    (3, 'student01',
     '$2y$12$eDI.Hak4yImuoiMuLaUoqe.MBCV7Q7lFMgliJZuA8J/e.NXItc6gC',
     'Nguyễn Thị Hoa', 'student', 1),

    (4, 'teacher02',
     '$2y$12$eDI.Hak4yImuoiMuLaUoqe.MBCV7Q7lFMgliJZuA8J/e.NXItc6gC',
     'Trần Văn Bình', 'teacher', 1),

    (5, 'student02',
     '$2y$12$eDI.Hak4yImuoiMuLaUoqe.MBCV7Q7lFMgliJZuA8J/e.NXItc6gC',
     'Lê Thị Mai', 'student', 1),

    (6, 'teacher03',
     '$2y$12$eDI.Hak4yImuoiMuLaUoqe.MBCV7Q7lFMgliJZuA8J/e.NXItc6gC',
     'Phạm Minh Đức', 'teacher', 1);


-- =========================================================
-- 2. FACULTIES
-- =========================================================

INSERT INTO faculties
    (id, code, name)
VALUES
    (1, 'CNTT', 'Khoa Công nghệ thông tin'),
    (2, 'KT', 'Khoa Kinh tế'),
    (3, 'NN', 'Khoa Ngoại ngữ'),
    (4, 'SP', 'Khoa Sư phạm');


-- =========================================================
-- 3. STUDENTS
-- =========================================================

INSERT INTO students
    (user_id, student_code, faculty_id, class_name, cohort)
VALUES
    (3, 'SV01', 1, 'CNTT-K21', 'K21'),
    (5, 'SV02', 2, 'KT-K21', 'K21');


-- =========================================================
-- 4. LECTURERS
-- =========================================================

INSERT INTO lecturers
    (user_id, lecturer_code, faculty_id)
VALUES
    (2, 'GV01', 1),
    (4, 'GV02', 1),
    (6, 'GV03', 1);


-- =========================================================
-- 5. SEMESTERS
-- =========================================================

INSERT INTO semesters
    (
        id,
        name,
        academic_year,
        term,
        study_start,
        study_end,
        registration_start,
        registration_end,
        registration_open,
        status
    )
VALUES
    (
        1,
        'Học kỳ 1 năm học 2026-2027',
        '2026-2027',
        '1',
        '2026-08-10',
        '2027-01-15',
        '2026-09-01',
        '2026-09-15',
        1,
        1
    ),

    (
        2,
        'Học kỳ 2 năm học 2026-2027',
        '2026-2027',
        '2',
        '2027-02-01',
        '2027-06-15',
        '2027-01-15',
        '2027-01-30',
        0,
        1
    );


-- =========================================================
-- 6. COURSES
-- =========================================================

INSERT INTO courses
    (id, code, name, credits, description, status)
VALUES
    (
        1,
        'WEB123',
        'Lập trình Web',
        3,
        'PHP, MySQL và phát triển ứng dụng Web.',
        1
    ),

    (
        2,
        'HTTT',
        'Hệ thống thông tin',
        3,
        'Phân tích và thiết kế hệ thống thông tin.',
        1
    ),

    (
        3,
        'CSDL',
        'Cơ sở dữ liệu',
        3,
        'Thiết kế và quản lý cơ sở dữ liệu.',
        1
    ),

    (
        4,
        'TA01',
        'Tiếng Anh 1',
        3,
        'Học phần tiếng Anh cơ bản dùng chung.',
        1
    );


-- =========================================================
-- 7. COURSE - FACULTY
-- Học phần được mở cho khoa nào
-- =========================================================

INSERT INTO course_faculties
    (course_id, faculty_id)
VALUES
    (1, 1), -- Lập trình Web - CNTT
    (2, 1), -- Hệ thống thông tin - CNTT
    (3, 1), -- Cơ sở dữ liệu - CNTT
    (4, 1), -- Tiếng Anh 1 - CNTT
    (4, 2), -- Tiếng Anh 1 - Kinh tế
    (4, 3); -- Tiếng Anh 1 - Ngoại ngữ


-- =========================================================
-- 8. COURSE - SEMESTER
-- Học phần được mở trong học kỳ nào
-- =========================================================

INSERT INTO course_semesters
    (course_id, semester_id)
VALUES
    (1, 1),
    (2, 1),
    (3, 1),
    (4, 1),

    (1, 2),
    (3, 2),
    (4, 2);


-- =========================================================
-- 9. COURSE - LECTURER
-- Giảng viên đủ điều kiện dạy học phần
-- =========================================================

INSERT INTO course_lecturers
    (course_id, lecturer_id)
VALUES
    (1, 2), -- WEB123 - GV001
    (1, 4), -- WEB123 - GV002

    (2, 2), -- HTTT - GV001

    (3, 2), -- CSDL - GV001
    (3, 4), -- CSDL - GV002
    (3, 6), -- CSDL - GV003

    (4, 2), -- TA01 - GV001
    (4, 4), -- TA01 - GV002
    (4, 6); -- TA01 - GV003


-- =========================================================
-- 10. CLASSES
-- Không lưu lịch học trực tiếp ở đây
-- Lịch học được lưu trong class_schedules
-- =========================================================

INSERT INTO classes
    (
        id,
        class_code,
        course_id,
        semester_id,
        lecturer_id,
        max_students,
        status
    )
VALUES
    (
        1,
        'WEB123-01',
        1,
        1,
        2,
        40,
        1
    ),

    (
        2,
        'WEB123-02',
        1,
        1,
        4,
        40,
        1
    ),

    (
        3,
        'HTTT-01',
        2,
        1,
        2,
        40,
        1
    ),

    (
        4,
        'CSDL-01',
        3,
        1,
        4,
        40,
        1
    ),

    (
        5,
        'TA01-01',
        4,
        1,
        6,
        50,
        1
    ),

    (
        6,
        'CSDL-01',
        3,
        2,
        2,
        40,
        1
    );

-- =========================================================
-- 11. CLASS SCHEDULES
-- =========================================================

INSERT INTO class_schedules
    (
        id,
        class_id,
        weekday,
        start_period,
        end_period,
        start_date,
        end_date,
        room
    )
VALUES

    -- WEB123-01
    (
        1,
        1,
        2,
        1,
        3,
        '2026-08-10',
        '2026-12-20',
        'A101'
    ),

    -- WEB123-02
    (
        2,
        2,
        3,
        4,
        6,
        '2026-08-10',
        '2026-12-20',
        'A102'
    ),

    -- HTTT-01
    (
        3,
        3,
        4,
        1,
        3,
        '2026-08-10',
        '2026-12-20',
        'A201'
    ),

    -- CSDL-01 học kỳ 1
    (
        4,
        4,
        5,
        4,
        6,
        '2026-08-10',
        '2026-12-20',
        'A202'
    ),

    -- TA01-01
    (
        5,
        5,
        6,
        1,
        3,
        '2026-08-10',
        '2026-12-20',
        'B101'
    ),

    -- CSDL-01 học kỳ 2
    (
        6,
        6,
        3,
        1,
        3,
        '2027-02-01',
        '2027-06-15',
        'A202'
    );


-- =========================================================
-- 12. REGISTRATIONS
-- Sinh viên đăng ký lớp học phần
-- =========================================================

INSERT INTO registrations
    (
        id,
        student_id,
        class_id
    )
VALUES
    (1, 3, 1),
    (2, 3, 3),
    (3, 5, 5);


-- =========================================================
-- 13. GRADES
-- Điểm của các đăng ký
-- =========================================================

INSERT INTO grades
    (
        registration_id,
        midterm,
        final_exam,
        total
    )
VALUES
    (1, 8.0, 8.5, 8.3),
    (2, 7.5, 8.0, 7.8);