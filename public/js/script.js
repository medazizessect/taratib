(function () {
  const toggleExploitant = () => {
    const checkbox = document.getElementById('est_exploite');
    const block = document.getElementById('exploitant_block');
    if (!checkbox || !block) return;
    block.style.display = checkbox.checked ? 'block' : 'none';
  };

  const addMemberBtn = document.getElementById('add-member');
  const membersWrap = document.getElementById('members-wrap');

  if (addMemberBtn && membersWrap) {
    addMemberBtn.addEventListener('click', function () {
      const item = document.createElement('div');
      item.className = 'input-group mb-2';

      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'form-control';
      input.name = 'membres_comite[]';
      input.placeholder = 'Nom du membre';

      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'btn btn-outline-danger remove-member';
      button.textContent = 'Supprimer';

      item.appendChild(input);
      item.appendChild(button);
      membersWrap.appendChild(item);
    });

    membersWrap.addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-member')) {
        e.target.closest('.input-group')?.remove();
      }
    });
  }

  document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'est_exploite') toggleExploitant();
  });

  document.addEventListener('DOMContentLoaded', toggleExploitant);
})();
