document.addEventListener('DOMContentLoaded', () => {
  // Mobile sidebar
  document.querySelector('.menu-btn')?.addEventListener('click', () => document.body.classList.toggle('nav-open'));

  // Confirm dialogs for destructive forms: <form data-confirm="Message">
  document.querySelectorAll('form[data-confirm]').forEach(f =>
    f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  // ---- Sale line editor (sales/create) ----
  const wrap = document.getElementById('lines');
  if (!wrap) return;
  const tpl = document.getElementById('line-template');
  let idx = wrap.querySelectorAll('.line').length;
  const peso = n => '₱' + Number(n || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  function recalc() {
    let total = 0;
    wrap.querySelectorAll('.line').forEach(row => {
      const sel = row.querySelector('select');
      const opt = sel.selectedOptions[0];
      const qty = parseFloat(row.querySelector('.qty').value) || 0;
      const price = parseFloat(row.querySelector('.price').value) || 0;
      const sub = Math.round(qty * price * 10000) / 10000;
      total += sub;
      row.querySelector('.sub').textContent = peso(sub);
      const tag = row.querySelector('.unit-tag');
      if (opt && opt.value) {
        const avail = parseFloat(opt.dataset.available);
        tag.textContent = `Available: ${avail.toLocaleString('en-PH', {minimumFractionDigits: 2})} ${opt.dataset.unit}`;
        tag.style.color = qty > avail ? 'var(--danger)' : '';
        row.querySelector('.unit-lbl').textContent = '(' + opt.dataset.unit + ')';
      } else { tag.textContent = ''; row.querySelector('.unit-lbl').textContent = ''; }
    });
    document.getElementById('grand-total').textContent = peso(total);
    const pay = parseFloat(document.getElementById('payment_amount')?.value) || 0;
    const bal = document.getElementById('balance-after');
    if (bal) bal.textContent = peso(total - pay);
  }

  // Disable harvests already chosen in other rows (same harvest only once per sale).
  function syncOptions() {
    const chosen = [...wrap.querySelectorAll('select')].map(s => s.value).filter(Boolean);
    wrap.querySelectorAll('select').forEach(s => [...s.options].forEach(o => {
      o.disabled = o.value && chosen.includes(o.value) && s.value !== o.value;
    }));
  }

  document.getElementById('add-line').addEventListener('click', () => {
    wrap.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__i__', idx++));
    syncOptions(); recalc();
  });
  wrap.addEventListener('click', e => {
    const btn = e.target.closest('.remove-line');
    if (btn && wrap.querySelectorAll('.line').length > 1) { btn.closest('.line').remove(); syncOptions(); recalc(); }
  });
  wrap.addEventListener('input', () => { syncOptions(); recalc(); });
  document.getElementById('payment_amount')?.addEventListener('input', recalc);
  syncOptions(); recalc();
});
