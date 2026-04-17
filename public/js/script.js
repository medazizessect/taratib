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
      wrapper.innerHTML = '<input type="text" class="form-control" name="membres_comite[]" placeholder="اسم العضو">' +
        '<button type="button" class="btn btn-outline-danger remove-member">حذف</button>';
      list.appendChild(wrapper);
    });
    list.addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-member')) {
        e.target.closest('.input-group').remove();
      }
    });
  }
})();
