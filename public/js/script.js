(function () {
  const exploited = document.getElementById('est_exploite');
  const exploitantRow = document.getElementById('exploitant_row');
  if (exploited && exploitantRow) {
    const sync = function () {
      exploitantRow.style.display = exploited.value === '1' ? '' : 'none';
    };
    exploited.addEventListener('change', sync);
    sync();
  }

  const addBtn = document.getElementById('add-member-btn');
  const list = document.getElementById('committee-members');
  if (addBtn && list) {
    addBtn.addEventListener('click', function () {
      const wrapper = document.createElement('div');
      wrapper.className = 'input-group mb-2';

      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'form-control';
      input.name = 'membres_comite[]';
      input.placeholder = 'اسم العضو';

      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'btn btn-outline-danger remove-member';
      button.textContent = 'حذف';

      wrapper.appendChild(input);
      wrapper.appendChild(button);
      list.appendChild(wrapper);
    });
    list.addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-member')) {
        e.target.closest('.input-group').remove();
      }
    });
  }
})();
