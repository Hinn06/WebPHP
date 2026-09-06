document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('[data-admin-search]');

    if (!root) {
        return;
    }

    const input = root.querySelector('[data-search-input]');
    const clearButton = root.querySelector('[data-search-clear]');
    const tableBody = root.querySelector('[data-search-body]');
    const pagination = root.querySelector('[data-search-pagination]');
    const resultInfo = root.querySelector('[data-search-result]');

    const type = root.dataset.type || '';
    const endpoint = root.dataset.endpoint || '';
    const baseUrl = root.dataset.baseUrl || '';
    const csrfToken = root.dataset.csrf || '';

    if (!input || !tableBody) {
        console.error('Không tìm thấy thành phần tìm kiếm.');
        return;
    }

    let timer = null;
    let controller = null;
    let currentPage = 1;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function buildUrl(page) {
        const separator = endpoint.includes('?') ? '&' : '?';

        return endpoint
            + separator + 'type=' + encodeURIComponent(type)
            + '&q=' + encodeURIComponent(input.value.trim())
            + '&page=' + encodeURIComponent(page);
    }

    function setLoading(isLoading) {
        root.classList.toggle('is-loading', isLoading);
    }

    function showEmpty(message, colspan) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="${colspan}" class="empty-row">
                    ${escapeHtml(message)}
                </td>
            </tr>
        `;
    }

    function renderStatus(total, page, pages) {
        if (!resultInfo) {
            return;
        }

        const keyword = input.value.trim();

        if (keyword) {
            resultInfo.textContent =
                `Tìm thấy ${total} kết quả cho "${keyword}" — trang ${page}/${pages}.`;
        } else {
            resultInfo.textContent =
                `Tổng số: ${total} bản ghi — trang ${page}/${pages}.`;
        }
    }

    function renderPagination(page, pages) {
        if (!pagination) {
            return;
        }

        pagination.innerHTML = '';

        if (pages <= 1) {
            return;
        }

        const createPage = function (number) {
            const link = document.createElement('a');

            link.href = '#';
            link.textContent = String(number);

            if (number === page) {
                link.classList.add('active');
            }

            link.addEventListener('click', function (event) {
                event.preventDefault();

                if (number !== currentPage) {
                    loadData(number);
                }
            });

            return link;
        };

        const maxPages = 7;

        let start = Math.max(1, page - 3);
        let end = Math.min(pages, start + maxPages - 1);

        if (end - start + 1 < maxPages) {
            start = Math.max(1, end - maxPages + 1);
        }

        if (start > 1) {
            pagination.appendChild(createPage(1));

            if (start > 2) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                pagination.appendChild(dots);
            }
        }

        for (let i = start; i <= end; i++) {
            pagination.appendChild(createPage(i));
        }

        if (end < pages) {
            if (end < pages - 1) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                pagination.appendChild(dots);
            }

            pagination.appendChild(createPage(pages));
        }
    }

    function scheduleText(schedule) {
        const days = {
            2: 'Thứ 2',
            3: 'Thứ 3',
            4: 'Thứ 4',
            5: 'Thứ 5',
            6: 'Thứ 6',
            7: 'Thứ 7',
            8: 'Chủ nhật'
        };

        const weekday = Number(schedule.weekday || 0);
        const day = days[weekday] || ('Thứ ' + weekday);

        const start = schedule.start_period ?? '';
        const end = schedule.end_period ?? '';

        if (start !== '' && end !== '') {
            return `${day} · Tiết ${start}-${end}`;
        }

        return day;
    }

    function renderStudents(rows) {
        if (!rows.length) {
            showEmpty('Không tìm thấy sinh viên phù hợp.', 8);
            return;
        }

        tableBody.innerHTML = '';

        rows.forEach(function (row) {
            const id = Number(row.user_id || row.id || 0);

            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>${escapeHtml(row.student_code)}</td>
                <td>${escapeHtml(row.full_name)}</td>
                <td>${escapeHtml(row.username)}</td>
                <td>${escapeHtml(row.class_name)}</td>
                <td>${escapeHtml(row.faculty_name)}</td>
                <td>${escapeHtml(row.cohort)}</td>
                <td>
                    <span class="badge ${Number(row.status) ? '' : 'off'}">
                        ${Number(row.status) ? 'Hoạt động' : 'Khóa'}
                    </span>
                </td>
                <td class="actions">
                    <div class="action-row">
                        <a
                            class="icon-btn"
                            title="Sửa"
                            aria-label="Sửa"
                            href="${escapeHtml(
                                baseUrl + '?r=admin/student/edit&id=' + id
                            )}"
                        >✎</a>

                        <form
                            method="post"
                            action="${escapeHtml(
                                baseUrl + '?r=admin/student/delete'
                            )}"
                        >
                            <input
                                type="hidden"
                                name="_csrf"
                                value="${escapeHtml(csrfToken)}"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="${id}"
                            >

                            <button
                                class="icon-btn danger"
                                type="submit"
                                title="Xóa"
                                aria-label="Xóa"
                            >🗑</button>
                        </form>
                    </div>
                </td>
            `;

            const form = tr.querySelector('form');

            form.addEventListener('submit', function (event) {
                if (!confirm('Bạn có chắc muốn xóa sinh viên này?')) {
                    event.preventDefault();
                }
            });

            tableBody.appendChild(tr);
        });
    }

    function renderLecturers(rows) {
        if (!rows.length) {
            showEmpty('Không tìm thấy giảng viên phù hợp.', 6);
            return;
        }

        tableBody.innerHTML = '';

        rows.forEach(function (row) {
            const id = Number(row.user_id || row.id || 0);

            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>${escapeHtml(row.lecturer_code)}</td>
                <td>${escapeHtml(row.full_name)}</td>
                <td>${escapeHtml(row.username)}</td>
                <td>${escapeHtml(row.faculty_name)}</td>
                <td>
                    <span class="badge ${Number(row.status) ? '' : 'off'}">
                        ${Number(row.status) ? 'Hoạt động' : 'Khóa'}
                    </span>
                </td>
                <td class="actions">
                    <div class="action-row">
                        <a
                            class="icon-btn"
                            title="Sửa"
                            aria-label="Sửa"
                            href="${escapeHtml(
                                baseUrl + '?r=admin/lecturer/edit&id=' + id
                            )}"
                        >✎</a>

                        <form
                            method="post"
                            action="${escapeHtml(
                                baseUrl + '?r=admin/lecturer/delete'
                            )}"
                        >
                            <input
                                type="hidden"
                                name="_csrf"
                                value="${escapeHtml(csrfToken)}"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="${id}"
                            >

                            <button
                                class="icon-btn danger"
                                type="submit"
                                title="Xóa"
                                aria-label="Xóa"
                            >🗑</button>
                        </form>
                    </div>
                </td>
            `;

            const form = tr.querySelector('form');

            form.addEventListener('submit', function (event) {
                if (!confirm('Bạn có chắc muốn xóa giảng viên này?')) {
                    event.preventDefault();
                }
            });

            tableBody.appendChild(tr);
        });
    }

    function renderCourses(rows) {
        if (!rows.length) {
            showEmpty('Không tìm thấy học phần phù hợp.', 6);
            return;
        }

        tableBody.innerHTML = '';

        rows.forEach(function (row) {
            const id = Number(row.id || 0);

            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>
                    <span class="course-code">
                        ${escapeHtml(row.code)}
                    </span>
                </td>

                <td>
                    <span class="course-name">
                        ${escapeHtml(row.name)}
                    </span>
                </td>

                <td>${escapeHtml(row.credits)}</td>

                <td>
                    <div class="faculty-text">
                        ${escapeHtml(row.faculties || '')}
                    </div>
                </td>

                <td>
                    <span class="badge ${Number(row.status) ? '' : 'off'}">
                        ${Number(row.status) ? 'Hoạt động' : 'Khóa'}
                    </span>
                </td>

                <td class="actions">
                    <div class="action-row">
                        <a
                            class="icon-btn"
                            title="Sửa"
                            aria-label="Sửa"
                            href="${escapeHtml(
                                baseUrl + '?r=admin/course/edit&id=' + id
                            )}"
                        >✎</a>

                        <form
                            method="post"
                            action="${escapeHtml(
                                baseUrl + '?r=admin/course/delete'
                            )}"
                        >
                            <input
                                type="hidden"
                                name="_csrf"
                                value="${escapeHtml(csrfToken)}"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="${id}"
                            >

                            <button
                                class="icon-btn danger"
                                type="submit"
                                title="Xóa"
                                aria-label="Xóa"
                            >🗑</button>
                        </form>
                    </div>
                </td>
            `;

            const form = tr.querySelector('form');

            form.addEventListener('submit', function (event) {
                if (!confirm('Bạn có chắc muốn xóa học phần này?')) {
                    event.preventDefault();
                }
            });

            tableBody.appendChild(tr);
        });
    }

    function renderClasses(rows) {
        if (!rows.length) {
            showEmpty('Không tìm thấy lớp học phần phù hợp.', 9);
            return;
        }

        tableBody.innerHTML = '';

        rows.forEach(function (row) {
            const id = Number(row.id || 0);

            const schedules = Array.isArray(row.schedules)
                ? row.schedules
                : [];

            let dayHtml = '';
            let dateHtml = '';
            let roomHtml = '';

            if (!schedules.length) {
                dayHtml = '<span class="muted">Chưa có lịch</span>';
                dateHtml = '<span class="muted">—</span>';
                roomHtml = '<span class="muted">—</span>';
            } else {
                schedules.forEach(function (schedule) {
                    const dateStart = schedule.start_date || '';
                    const dateEnd = schedule.end_date || '';

                    let dateText = '—';

                    if (dateStart && dateEnd) {
                        dateText = `${dateStart} - ${dateEnd}`;
                    } else if (dateStart) {
                        dateText = dateStart;
                    } else if (dateEnd) {
                        dateText = dateEnd;
                    }

                    const room = schedule.room || '—';

                    dayHtml += `
                        <div class="schedule-line">
                            ${escapeHtml(scheduleText(schedule))}
                        </div>
                    `;

                    dateHtml += `
                        <div class="schedule-date">
                            ${escapeHtml(dateText)}
                        </div>
                    `;

                    roomHtml += `
                        <div class="schedule-room">
                            ${escapeHtml(room)}
                        </div>
                    `;
                });
            }

            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>${escapeHtml(row.class_code)}</td>

                <td>
                    ${escapeHtml(
                        (row.course_code || '') +
                        ' - ' +
                        (row.course_name || '')
                    )}
                </td>

                <td>${escapeHtml(row.semester_name)}</td>

                <td>
                    ${escapeHtml(
                        row.lecturer_name || 'Chưa phân công'
                    )}
                </td>

                <td>${dayHtml}</td>

                <td>${dateHtml}</td>

                <td>${roomHtml}</td>

                <td>
                    ${escapeHtml(
                        (row.registered_count ?? 0) +
                        '/' +
                        (row.max_students ?? 0)
                    )}
                </td>

                <td class="actions">
                    <div class="action-row">
                        <a
                            class="icon-btn"
                            title="Danh sách sinh viên"
                            aria-label="Danh sách sinh viên"
                            href="${escapeHtml(
                                baseUrl +
                                '?r=admin/class/detail&id=' +
                                id
                            )}"
                        >👥</a>

                        <a
                            class="icon-btn"
                            title="Sửa"
                            aria-label="Sửa"
                            href="${escapeHtml(
                                baseUrl +
                                '?r=admin/class/edit&id=' +
                                id
                            )}"
                        >✎</a>

                        <form
                            method="post"
                            action="${escapeHtml(
                                baseUrl + '?r=admin/class/delete'
                            )}"
                        >
                            <input
                                type="hidden"
                                name="_csrf"
                                value="${escapeHtml(csrfToken)}"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="${id}"
                            >

                            <button
                                class="icon-btn danger"
                                type="submit"
                                title="Xóa"
                                aria-label="Xóa"
                            >🗑</button>
                        </form>
                    </div>
                </td>
            `;

            const form = tr.querySelector('form');

            form.addEventListener('submit', function (event) {
                if (!confirm('Bạn có chắc muốn xóa lớp học phần này?')) {
                    event.preventDefault();
                }
            });

            tableBody.appendChild(tr);
        });
    }

    function renderRows(rows) {
        switch (type) {
            case 'students':
                renderStudents(rows);
                break;

            case 'lecturers':
                renderLecturers(rows);
                break;

            case 'courses':
                renderCourses(rows);
                break;

            case 'classes':
                renderClasses(rows);
                break;

            default:
                showEmpty('Loại dữ liệu tìm kiếm không hợp lệ.', 1);
        }
    }

    async function loadData(page) {
        if (!endpoint) {
            console.error('Chưa có endpoint tìm kiếm.');
            return;
        }

        if (controller) {
            controller.abort();
        }

        controller = new AbortController();

        setLoading(true);

        try {
            const response = await fetch(buildUrl(page), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal
            });

            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`
                );
            }

            const data = await response.json();

            if (!data || typeof data !== 'object') {
                throw new Error('API trả về dữ liệu không hợp lệ.');
            }

            if (data.success === false) {
                throw new Error(
                    data.message || 'Không thể tìm kiếm dữ liệu.'
                );
            }

            const rows = Array.isArray(data.rows)
                ? data.rows
                : Array.isArray(data.data)
                    ? data.data
                    : [];

            const total = Number(data.total ?? rows.length);

            const pages = Math.max(
                1,
                Number(data.pages ?? data.total_pages ?? 1)
            );

            currentPage = Math.max(
                1,
                Number(data.page ?? page)
            );

            renderRows(rows);
            renderPagination(currentPage, pages);
            renderStatus(total, currentPage, pages);

        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error('Lỗi tìm kiếm:', error);

            showEmpty(
                error.message || 'Không thể tải dữ liệu.',
                type === 'classes' ? 9 :
                type === 'students' ? 8 :
                type === 'lecturers' ? 6 :
                6
            );

            if (pagination) {
                pagination.innerHTML = '';
            }

        } finally {
            setLoading(false);
        }
    }

    function updateClearButton() {
        if (!clearButton) {
            return;
        }

        clearButton.style.display =
            input.value.trim() !== ''
                ? 'inline-flex'
                : 'none';
    }

    input.addEventListener('input', function () {
        updateClearButton();

        clearTimeout(timer);

        timer = setTimeout(function () {
            loadData(1);
        }, 350);
    });

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();

            clearTimeout(timer);

            loadData(1);
        }
    });

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            input.value = '';

            updateClearButton();

            input.focus();

            loadData(1);
        });
    }

    updateClearButton();
});