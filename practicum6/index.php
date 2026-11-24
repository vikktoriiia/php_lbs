<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Трекер завдань — Практична робота №5</title>
    <style>
        body {
            font: 16px/1.5 Arial, sans-serif;
            max-width: 1000px;
            margin: 28px auto;
            padding: 0 16px;
        }

        input, select, button {
            font: inherit;
            margin: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        th, td {
            border: 1px solid #bbb;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #eee;
        }

        .done {
            background: #e2f5e2;
        }

        .done .task-title {
            text-decoration: line-through;
        }

        #ajax-status {
            min-height: 1.3em;
        }

        .actions {
            white-space: nowrap;
        }

        .actions form {
            display: inline;
        }
    </style>
</head>
<body>

<h1>Трекер завдань</h1>

<h2>Додати нове завдання</h2>

<form id="addTaskForm">
    <label>
        Назва:
        <input name="title" type="text" maxlength="255" required>
    </label>

    <label>
        Термін:
        <input name="due_date" type="date">
    </label>

    <label>
        Пріоритет:
        <select name="priority">
            <option>Низький</option>
            <option>Середній</option>
            <option>Високий</option>
        </select>
    </label>

    <button type="submit">Додати завдання</button>
</form>

<hr>

<h2>Список завдань через AJAX</h2>

<label for="search">Живий пошук за пріоритетом:</label>

<input
        id="search"
        type="text"
        placeholder="Введіть пріоритет"
>

<p id="ajax-status" role="status" aria-live="polite"></p>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Назва завдання</th>
        <th>Термін виконання</th>
        <th>Пріоритет</th>
        <th>Виконано</th>
        <th>Дії</th>
    </tr>
    </thead>

    <tbody id="results">
    <tr>
        <td colspan="6">Завантаження…</td>
    </tr>
    </tbody>
</table>

<script src="script.js"></script>

</body>
</html>
