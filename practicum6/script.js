const search = document.getElementById('search');
const results = document.getElementById('results');
const addTaskForm = document.getElementById('addTaskForm');
const statusMessage = document.getElementById('ajax-status');

// Єдина точка входу API
const API_URL = 'api.php?resource=tasks';

let latestRequest = 0;

// Повідомлення про успіх або помилку
function showStatus(message, error = false) {
    statusMessage.textContent = message;
    statusMessage.style.color = error ? 'darkred' : 'green';
}

// Повідомлення всередині таблиці
function showTableMessage(message) {
    results.replaceChildren();

    const cell = results.insertRow().insertCell();
    cell.colSpan = 6;
    cell.textContent = message;
}

// Оформлення виконаного завдання
function paintRow(row, done) {
    row.classList.toggle('done', Number(done) === 1);
}

// Перевірка JSON-відповіді сервера
async function parseJson(response) {
    let result;

    try {
        result = await response.json();
    } catch {
        throw new Error('Сервер повернув некоректний JSON');
    }

    if (!result || typeof result !== 'object') {
        throw new Error('Некоректна відповідь сервера');
    }

    if (!response.ok || result.success !== true) {
        throw new Error(
            result.error || `Помилка HTTP ${response.status}`
        );
    }

    if (!Object.prototype.hasOwnProperty.call(result, 'data')) {
        throw new Error('У відповіді відсутнє поле data');
    }

    return result.data;
}

// Додавання рядка в таблицю
function appendTask(task) {
    const row = results.insertRow();

    // Ідентифікатор завдання
    row.insertCell().textContent = task.id;

    // Назва завдання
    const titleCell = row.insertCell();
    titleCell.textContent = task.title;
    titleCell.className = 'task-title';

    // Дата та пріоритет
    row.insertCell().textContent = task.due_date ?? '—';
    row.insertCell().textContent = task.priority;

    // Чекбокс виконання завдання
    const checkCell = row.insertCell();
    const checkbox = document.createElement('input');

    checkbox.type = 'checkbox';
    checkbox.checked = Number(task.done) === 1;
    checkbox.disabled = checkbox.checked;

    checkbox.setAttribute(
        'aria-label',
        `Виконано: ${task.title}`
    );

    // Виконання доменної дії complete через API
    checkbox.addEventListener('change', async () => {
        if (!checkbox.checked) {
            return;
        }

        checkbox.disabled = true;

        try {
            const response = await fetch(
                API_URL + '&action=complete',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: Number(task.id)
                    })
                }
            );

            const updatedTask = await parseJson(response);

            if (Number(updatedTask.done) !== 1) {
                throw new Error('Не вдалося оновити статус');
            }

            checkbox.checked = true;
            paintRow(row, updatedTask.done);

            showStatus('Завдання позначено виконаним');

        } catch (error) {
            checkbox.checked = false;
            checkbox.disabled = false;

            paintRow(row, 0);
            showStatus(error.message, true);
        }
    });

    checkCell.append(checkbox);
    paintRow(row, task.done);

    // Кнопки редагування та видалення
    const actions = row.insertCell();
    actions.className = 'actions';

    // Редагування завдання
    const edit = document.createElement('a');

    edit.href = 'edit.php?id=' +
        encodeURIComponent(task.id);

    edit.textContent = 'Редагувати';

    actions.append(edit, ' ');

    // Видалення завдання
    const form = document.createElement('form');

    form.method = 'POST';
    form.action = 'delete.php';

    const hidden = document.createElement('input');

    hidden.type = 'hidden';
    hidden.name = 'id';
    hidden.value = task.id;

    const remove = document.createElement('button');

    remove.type = 'submit';
    remove.textContent = 'Видалити';

    form.addEventListener('submit', event => {
        if (!confirm('Видалити завдання?')) {
            event.preventDefault();
        }
    });

    form.append(hidden, remove);
    actions.append(form);
}

// Завантаження списку завдань через API
async function loadTasks() {
    const requestNumber = ++latestRequest;

    try {
        const response = await fetch(
            API_URL + '&q=' +
            encodeURIComponent(search.value.trim()),
            {
                method: 'GET',
                cache: 'no-store'
            }
        );

        const tasks = await parseJson(response);

        // Ігнорування застарілих відповідей
        if (requestNumber !== latestRequest) {
            return false;
        }

        if (!Array.isArray(tasks)) {
            throw new Error('Некоректний формат списку завдань');
        }

        // Очищення попереднього вмісту таблиці
        results.replaceChildren();

        if (tasks.length === 0) {
            showTableMessage('Завдань не знайдено');
        } else {
            tasks.forEach(appendTask);
        }

        return true;

    } catch (error) {
        if (requestNumber !== latestRequest) {
            return false;
        }

        showTableMessage('Не вдалося завантажити завдання');
        showStatus(error.message, true);

        return false;
    }
}

// Живий пошук за пріоритетом
search.addEventListener('input', loadTasks);

// Додавання нового завдання через API
addTaskForm.addEventListener('submit', async event => {
    event.preventDefault();

    const button = addTaskForm.querySelector(
        'button[type="submit"]'
    );

    button.disabled = true;

    try {
        // Отримання даних форми
        const formData = new FormData(addTaskForm);

        const data = {
            title: formData.get('title'),
            due_date: formData.get('due_date') || null,
            priority: formData.get('priority')
        };

        // POST-запит до єдиної точки входу
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        await parseJson(response);

        // Очищення форми після створення завдання
        addTaskForm.reset();
        search.value = '';

        // Оновлення таблиці без перезавантаження сторінки
        if (await loadTasks()) {
            showStatus('Завдання успішно додано');
        }

    } catch (error) {
        showStatus(error.message, true);

    } finally {
        button.disabled = false;
    }
});

// Початкове завантаження завдань
loadTasks();
