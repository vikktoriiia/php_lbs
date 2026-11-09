
const search = document.getElementById('search');
const results = document.getElementById('results');
const addTaskForm = document.getElementById('addTaskForm');

// Лічильник запитів для захисту від застарілих результатів
let latestRequest = 0;

// Виведення повідомлення в таблиці
function showMessage(message) {
    results.replaceChildren();

    const row = results.insertRow();
    const cell = row.insertCell();

    cell.colSpan = 5;
    cell.textContent = message;
}

// Візуальне оформлення виконаних завдань
function updateRowStyle(row, done) {
    const completed = Number(done) === 1;

    row.style.backgroundColor = completed ? '#e2f5e2' : '';
    row.cells[1].style.textDecoration = completed
        ? 'line-through'
        : '';
}

// Завантаження завдань через AJAX
async function loadTasks() {
    const requestNumber = ++latestRequest;

    try {
        const query = search.value.trim();

        const response = await fetch(
            'api_list.php?q=' + encodeURIComponent(query)
        );

        if (!response.ok) {
            throw new Error('Помилка завантаження завдань');
        }

        const tasks = await response.json();

        // Ігноруємо відповідь застарілого запиту
        if (requestNumber !== latestRequest) {
            return;
        }

        if (!Array.isArray(tasks)) {
            throw new Error('Некоректна відповідь сервера');
        }

        results.replaceChildren();

        if (tasks.length === 0) {
            showMessage('Завдань не знайдено');
            return;
        }

        // Відображення отриманих завдань
        tasks.forEach(task => {
            const row = results.insertRow();

            const values = [
                task.id,
                task.title,
                task.due_date ?? '—',
                task.priority
            ];

            values.forEach(value => {
                const cell = row.insertCell();
                cell.textContent = value;
            });

            // Створення чекбокса
            const statusCell = row.insertCell();
            const checkbox = document.createElement('input');

            checkbox.type = 'checkbox';
            checkbox.checked = Number(task.done) === 1;
            checkbox.setAttribute(
                'aria-label',
                'Виконано: ' + task.title
            );

            updateRowStyle(row, task.done);

            // Зміна статусу завдання через AJAX
            checkbox.addEventListener('change', async function () {
                const previousStatus = !checkbox.checked;
                const newStatus = checkbox.checked ? 1 : 0;

                checkbox.disabled = true;

                try {
                    const formData = new FormData();

                    formData.append('id', task.id);
                    formData.append('done', newStatus);

                    const response = await fetch('api_update.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('Помилка оновлення статусу');
                    }

                    const data = await response.json();

                    if (data.success !== true) {
                        throw new Error(
                            data.error || 'Не вдалося оновити статус'
                        );
                    }

                    // Оновлюємо вигляд без перезавантаження
                    checkbox.checked = Number(data.done) === 1;
                    updateRowStyle(row, data.done);

                } catch (error) {
                    // Повертаємо попередній стан
                    checkbox.checked = previousStatus;
                    updateRowStyle(row, previousStatus ? 1 : 0);

                    showStatusMessage(error.message, true);
                    console.error(error);

                } finally {
                    checkbox.disabled = false;
                }
            });

            statusCell.appendChild(checkbox);
        });

    } catch (error) {
        if (requestNumber !== latestRequest) {
            return;
        }

        showMessage('Помилка завантаження завдань');
        console.error(error);
    }
}

// Повідомлення про помилки та успішні дії
function showStatusMessage(message, isError = false) {
    let status = document.getElementById('ajax-status');

    if (!status) {
        status = document.createElement('p');
        status.id = 'ajax-status';
        results.closest('table').before(status);
    }

    status.textContent = message;
    status.style.color = isError ? 'red' : 'green';
}

// Живий пошук без перезавантаження
search.addEventListener('input', loadTasks);

// Додавання нового завдання через AJAX
addTaskForm.addEventListener('submit', async function (event) {
    event.preventDefault();

    const submitButton = addTaskForm.querySelector(
        'button[type="submit"]'
    );

    submitButton.disabled = true;

    try {
        const formData = new FormData(addTaskForm);

        const response = await fetch('api_add.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.error || 'Помилка додавання завдання'
            );
        }

        // Очищення форми після додавання
        addTaskForm.reset();

        // Скидання фільтра пошуку
        search.value = '';

        // Оновлення таблиці
        await loadTasks();

        showStatusMessage('Завдання успішно додано!');

    } catch (error) {
        showStatusMessage(error.message, true);
        console.error(error);

    } finally {
        submitButton.disabled = false;
    }
});

// Завантаження списку при відкритті сторінки
loadTasks();
