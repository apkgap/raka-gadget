(() => {
  'use strict';
  const products = [
    ['ATK Dragonfly A9 Air', 32065, 164493],
    ['ATK RS7 Air', 39605, 203174],
    ['แผ่นรองเมาส์ D-GLOW CLEAR (Lumi)', 18522, 95018],
    ['HM Lab 8K Hub (รุ่น Pro มีจอ)', 10600, 54378],
    ['สายสปริง HM HUB 8K', 5960, 30575],
    ['หูฟังเกมมิ่ง ATK Horizon Type-C', 23270, 119375],
    ['เมาส์ ATK Liekong Phantom', 43945, 225438],
    ['สายคีย์บอร์ด Hiomu Custom RGB', 18860, 96752],
    ['ZUOYA GMK26 Numpad', 16500, 84645],
    ['สายสั้น HM HUB C-C — เส้นที่ 1', 3166, 16242],
    ['สายสั้น HM HUB C-C — เส้นที่ 2', 3166, 16241],
    ['สายสั้น HM HUB USB-C — 1 เส้น', 3166, 16242],
    ['ตัวถ่วงน้ำหนักสายแม่เหล็ก', 1620, 8311],
  ];
  const form = document.getElementById('calculator');
  if (!form) return;
  const exportButton = document.getElementById('export-json');
  let calculatedData = null;
  const money = cents => (cents / 100).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const show = (id, value) => { document.getElementById(id).textContent = value; };
  const profit = (element, cents) => {
    element.textContent = (cents > 0 ? '+' : '') + money(cents);
    element.classList.toggle('positive', cents >= 0);
    element.classList.toggle('negative', cents < 0);
  };
  const tbody = document.getElementById('items');
  const fields = products.map(([name, yuan, cost], index) => {
    const row = document.createElement('tr');
    const heading = document.createElement('th');
    heading.scope = 'row';
    const number = document.createElement('span');
    number.className = 'number';
    number.textContent = String(index + 1).padStart(2, '0');
    heading.append(number, document.createTextNode(name));
    row.append(heading);
    for (const amount of [yuan, cost]) {
      const cell = document.createElement('td');
      cell.textContent = money(amount);
      row.append(cell);
    }
    const saleCell = document.createElement('td');
    const input = document.createElement('input');
    input.type = 'number';
    input.min = '0';
    input.max = '100000000';
    input.step = '0.01';
    input.required = true;
    input.inputMode = 'decimal';
    input.placeholder = 'กรอกราคาขาย';
    input.setAttribute('aria-label', `ราคาตั้งขาย ${name} (บาท)`);
    saleCell.append(input);
    const result = document.createElement('td');
    result.textContent = '—';
    row.append(saleCell, result);
    tbody.append(row);
    return { input, result, cost };
  });
  const totalCost = products.reduce((sum, item) => sum + item[2], 0);
  show('total-yuan', money(products.reduce((sum, item) => sum + item[1], 0)));
  show('total-cost', money(totalCost));
  show('cost-card', money(totalCost));
  form.addEventListener('submit', event => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    let totalSale = 0;
    for (const { input, result, cost } of fields) {
      const sale = Math.round(Number(input.value) * 100);
      totalSale += sale;
      profit(result, sale - cost);
    }
    show('total-sale', money(totalSale));
    show('sale-card', money(totalSale));
    profit(document.getElementById('total-profit'), totalSale - totalCost);
    profit(document.getElementById('profit-card'), totalSale - totalCost);
    calculatedData = {
      schema_version: 1,
      calculated_at: new Date().toISOString(),
      items: products.map(([name, yuan, cost], index) => {
        const sale = Math.round(Number(fields[index].input.value) * 100);
        return { item_number: index + 1, name, quantity: 1, cost_cny: yuan / 100,
          cost_thb: cost / 100, selling_price_thb: sale / 100, profit_thb: (sale - cost) / 100 };
      }),
      totals: { quantity: products.length,
        cost_cny: products.reduce((sum, item) => sum + item[1], 0) / 100,
        cost_thb: totalCost / 100, selling_price_thb: totalSale / 100,
        profit_thb: (totalSale - totalCost) / 100 },
    };
    exportButton.disabled = false;
    show('status', 'คำนวณครบทั้ง 13 ชิ้นแล้ว');
  });
  form.addEventListener('input', () => {
    calculatedData = null;
    exportButton.disabled = true;
    show('status', 'มีการแก้ไขราคา · กดคำนวณทั้งหมดเพื่ออัปเดตผล');
    for (const { result } of fields) { result.textContent = '—'; result.classList.remove('positive', 'negative'); }
    for (const id of ['total-sale', 'sale-card', 'total-profit', 'profit-card']) {
      const element = document.getElementById(id);
      element.textContent = '—';
      element.classList.remove('positive', 'negative');
    }
  });
  document.getElementById('clear').addEventListener('click', () => {
    form.reset();
    form.dispatchEvent(new Event('input'));
    show('status', 'ล้างราคาขายแล้ว · ยังไม่ได้คำนวณ');
    fields[0].input.focus();
  });
  exportButton.addEventListener('click', () => {
    if (!calculatedData) return;
    const blob = new Blob([JSON.stringify(calculatedData, null, 2)], { type: 'application/json;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `raka-cost-profit-${new Date().toISOString().replace(/[:.]/g, '-')}.json`;
    document.body.append(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
    show('status', 'ส่งออก JSON แล้ว');
  });
})();
