const search = document.getElementById('search');
const results = document.getElementById('results');
const addTaskForm = document.getElementById('addTaskForm');
const statusMessage = document.getElementById('ajax-status');

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
    const data = await response.json();

    if (!response.ok) {
        throw new Error(
            data.error || `Помилка HTTP ${response.status}`
        );
    }

    return data;
}

// Додавання рядка в таблицю
function appendTask(task) {
    const row = results.insertRow();

    const idCell = row.insertCell();
    idCell.textContent = task.id;

    const titleCell = row.insertCell();
    titleCell.textContent = task.title;
    titleCell.className = 'task-title';

    row.insertCell().textContent = task.due_date ?? '—';
    row.insertCell().textContent = task.priority;

    // Чекбокс виконання
    const checkCell = row.insertCell();
    const checkbox = document.createElement('input');

    checkbox.type = 'checkbox';
    checkbox.checked = Number(task.done) === 1;
    checkbox.setAttribute(
        'aria-label',
        `Виконано: ${task.title}`
    );

    // Зміна статусу через AJAX
    checkbox.addEventListener('change', async () => {
        const oldValue = !checkbox.checked;
        checkbox.disabled = true;

        try {
            const data = new FormData();
            data.append('id', task.id);
            data.append('done', checkbox.checked ? '1' : '0');

            const response = await fetch('api_update.php', {
                method: 'POST',
                body: data
            });

            const result = await parseJson(response);

            if (result.success !== true) {
                throw new Error('Не вдалося оновити статус');
            }

            checkbox.checked = Number(result.done) === 1;
            paintRow(row, result.done);

            showStatus('Статус завдання оновлено');

        } catch (error) {
            checkbox.checked = oldValue;
            paintRow(row, oldValue ? 1 : 0);
            showStatus(error.message, true);

        } finally {
            checkbox.disabled = false;
        }
    });

    checkCell.append(checkbox);
    paintRow(row, task.done);

    // Кнопки редагування та видалення
    const actions = row.insertCell();
    actions.className = 'actions';

    const edit = document.createElement('a');
    edit.href = 'edit.php?id=' + encodeURIComponent(task.id);
    edit.textContent = 'Редагувати';

    actions.append(edit, ' ');

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

// Завантаження списку через AJAX
async function loadTasks() {
    const requestNumber = ++latestRequest;

    try {
        const response = await fetch(
            'api_list.php?q=' +
            encodeURIComponent(search.value.trim())
        );

        const tasks = await parseJson(response);

        if (requestNumber !== latestRequest) {
            return false;
        }

        if (!Array.isArray(tasks)) {
            throw new Error('Некоректна відповідь сервера');
        }

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

// Живий пошук
search.addEventListener('input', loadTasks);

// Додавання завдання через AJAX
addTaskForm.addEventListener('submit', async event => {
    event.preventDefault();

    const button = addTaskForm.querySelector(
        'button[type="submit"]'
    );

    button.disabled = true;

    try {
        const response = await fetch('api_add.php', {
            method: 'POST',
            body: new FormData(addTaskForm)
        });

        await parseJson(response);

        addTaskForm.reset();
        search.value = '';

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
