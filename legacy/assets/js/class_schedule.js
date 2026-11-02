document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('schedule-container');

  container.addEventListener('click', function (e) {

    if (e.target && e.target.classList.contains('add-btn')) {
      const newRow = document.createElement('div');
      newRow.className = 'form-group schedule-row';
      newRow.innerHTML = `
          <div>
              <select name="class_day[]" required>
                  <option value="" disabled selected>Select Day</option>
                  <option value="Monday">Monday</option>
                  <option value="Tuesday">Tuesday</option>
                  <option value="Wednesday">Wednesday</option>
                  <option value="Thursday">Thursday</option>
                  <option value="Friday">Friday</option>
                  <option value="Saturday">Saturday</option>
              </select>
          </div>
          <div>
              <input type="time" name="start_time[]" required>
          </div>
          <div>
              <input type="time" name="end_time[]" required>
          </div>
          <div>
              <button type="button" class="add-btn">+</button>
              <button type="button" class="delete-btn">-</button>
          </div>
          `;
      container.appendChild(newRow);
    }

    if (e.target && e.target.classList.contains('delete-btn')) {
      const row = e.target.closest('.schedule-row');
      if (row) {
        row.remove();
      }
    }
  });
});