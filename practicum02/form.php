<?php
$errors =[];

if ($_SERVER['REQUEST_METHOD'] ==='POST'){
    $title = trim($_POST['title'] ?? '');
    $dueDate = $_POST['dueDate'] ?? '';
    $priority = $_POST['priority'] ?? '';
    //Перевіряємо назву
    if ($title === ''){
        $errors['title'] = 'Назва завдання є обов`язковою';
    }
    //Перевіряємо дату
    if ($dueDate === ''){
        $errors['dueDate'] = 'Оберіть дату виконання';
    }
    else{
        $date = DateTime::createFromFormat('Y-m-d', $dueDate);
        if (!$date || $date->format('Y-m-d') !== $dueDate){
            $errors['dueDate'] = 'Введіть коректну дату';
        }
    }
    //Перевіряємо пріоритет
        $allowedPriorities = ['Високий', 'Середній', 'Низький'];
        if (!in_array($priority, $allowedPriorities, true)){
            $errors['priority'] = 'Оберіть правильний пріоритет';
        }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Трекер завдань</title>
</head>
<body>
<h1>Нове завдання</h1>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<form method="post" action="form.php">
    <!----Робимо назву завдання-->
    <input type="text"
           id="title"
           name="title"
           value="<?= htmlspecialchars($title ?? '') ?>"
           required>
    <br><br>
    <!----Робимо дату виконання завдання-->
    <label for="dueDate">Дата виконання:</label>
    <input type="date"
           id="dueDate"
           name="dueDate"
           value="<?= htmlspecialchars($dueDate ?? '') ?>"
           required>
    <br><br>
    <!----Робимо пріорітет виконання завдання-->
    <label for="priority">Пріоритет:</label>
    <select name="priority" id="priority" required>
        <option value="">Оберіть пріоритет</option>
        <option value="Високий"
                <?= ($priority ?? '') === 'Високий' ? 'selected' : '' ?>>
            Високий
        </option>

        <option value="Середній"
                <?= ($priority ?? '') === 'Середній' ? 'selected' : '' ?>>
            Середній
        </option>

        <option value="Низький"
                <?= ($priority ?? '') === 'Низький' ? 'selected' : '' ?>>
            Низький
        </option>
    </select>
    <br><br>
    <button type="submit">Додати завдання</button>
</form>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)): ?>
    <h2>Завдання успішно додано!</h2>

    <p>Назва: <?= htmlspecialchars($title) ?></p>
    <p>Дата виконання: <?= htmlspecialchars($dueDate) ?></p>
    <p>Пріоритет: <?= htmlspecialchars($priority) ?></p>
<?php endif; ?>
<script>
    const form = document.querySelector('form');
    const titleInput = document.getElementById('title');
    const dueDateInput = document.getElementById('dueDate');
    const priorityInput = document.getElementById('priority');

    // Відновлюємо чернетку після завантаження сторінки
    const savedDraft = localStorage.getItem('taskDraft');

    if (savedDraft) {
        const draft = JSON.parse(savedDraft);

        titleInput.value = draft.title || '';
        dueDateInput.value = draft.dueDate || '';
        priorityInput.value = draft.priority || '';
    }
    // Зберігаємо чернетку
    function saveDraft() {
        const draft = {
            title: titleInput.value,
            dueDate: dueDateInput.value,
            priority: priorityInput.value
        };

        localStorage.setItem('taskDraft', JSON.stringify(draft));
    }
    titleInput.addEventListener('input', saveDraft);
    dueDateInput.addEventListener('change', saveDraft);
    priorityInput.addEventListener('change', saveDraft);
    // Перевірка перед відправленням форми
    form.addEventListener('submit', function(event) {
        if (titleInput.value.trim().length < 3) {
            event.preventDefault();
            alert('Назва завдання повинна містити щонайменше 3 символи');
            return;
        }
        // Після успішної перевірки видаляємо чернетку
        localStorage.removeItem('taskDraft');
    });
</script>
</body>
</html>

