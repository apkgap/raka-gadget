(() => {
  'use strict';
  const data = window.rakaReconciliation;
  if (!data || !document.getElementById('overview')) return;
  const cents = value => Math.round(value * 100);
  const money = value => (value / 100).toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2});
  const signed = value => (value > 0 ? '+' : '') + money(value);
  const sum = values => values.reduce((a,b) => a+b,0);
  const set = (id,value,isProfit = false) => {
    const el = document.getElementById(id);
    el.textContent = isProfit ? signed(value) : money(value);
    if (isProfit) el.classList.add(value < 0 ? 'negative' : 'positive');
  };
  const addRow = (id,values,profitIndex = -1) => {
    const row = document.createElement('tr');
    values.forEach((value,index) => {
      const cell = document.createElement(index === 0 ? 'th' : 'td');
      if (index === 0) cell.scope = 'row';
      cell.textContent = value;
      if(index === profitIndex) cell.classList.add(String(value).startsWith('-') ? 'negative' : 'positive');
      row.append(cell);
    });
    document.getElementById(id).append(row);
  };
  const definitions = [
    {label:'ATK Dragonfly A9 Air', items:[1], group:0},
    {label:'ATK RS7 Air (รวมมัดจำตามข้อมูลประกอบ)', items:[2], group:1},
    {label:'D-GLOW CLEAR (Lumi)', items:[3], group:2},
    {label:'HM Hub + สายสปริง (รับรวม 2 ชิ้น)', items:[4,5], group:3},
    {label:'ATK Horizon Type-C', items:[6], group:4},
    {label:'Hiomu Custom RGB', items:[8], group:9},
    {label:'ยอดผ่อนเทียบสินค้า 6 ชิ้น (สมมติฐาน รอยืนยัน)', items:[7,9,10,11,12,13], group:5},
  ];
  const receipts = group => sum(data.slips.filter(slip => slip.group === group).map(slip => slip.amount_cents));
  const totalCost = sum(data.items.map(item => cents(item.cost_thb)));
  const totalSale = sum(data.items.map(item => cents(item.selling_price_thb)));
  const all = sum(data.slips.map(slip => slip.amount_cents));
  const outside = sum(data.slips.filter(slip => slip.group === 7 || slip.group === 8).map(slip => slip.amount_cents));
  const pending = receipts(10);
  const received = all - outside;
  const profit = received - totalCost;
  set('cost',totalCost);set('received',received);set('profit',profit,true);
  [
    ['ยอดโอนทั้งหมด 16 สลิป',money(all)],
    ['หักเฉพาะค่าส่ง',money(outside)],
    ['ยอด X-raypad ที่รวมในยอดรับแล้ว',money(receipts(6))],
    ['ยอดรับรวมสายรอจับคู่',money(received)],
    ['ต้นทุนสินค้า 13 ชิ้น',money(totalCost)],
    ['ส่วนต่างยอดรับ − ต้นทุนที่ทราบ (ยังไม่มีต้นทุน X-raypad)',signed(profit)],
    ['ส่วนต่างหากไม่รวมสายรอจับคู่ (ยังไม่มีต้นทุน X-raypad)',signed(profit-pending)],
    ['ราคาตั้งขายรวม',money(totalSale)],
    ['กำไรหากขายได้ครบตามราคาตั้ง',signed(totalSale-totalCost)],
    ['ยอดรับรวม X-raypad เทียบราคาตั้ง 13 ชิ้น (ยังไม่มีราคาตั้ง X-raypad)',signed(received-totalSale)],
  ].forEach(values => addRow('overview',values));
  data.items.forEach(item => {
    const definition = definitions.find(group => group.items.includes(item.item_number));
    const receiptLabel = definition.items.length === 1 ? money(receipts(definition.group)) : definition.group === 3 ? 'รับรวมข้อ 4–5: ' + money(receipts(3)) : 'อยู่ในยอดผ่อนรวม ' + money(receipts(5)) + ' (รอยืนยัน)';
    addRow('items',[`${item.item_number}. ${item.name}`,money(cents(item.cost_cny)),money(cents(item.cost_thb)),money(cents(item.selling_price_thb)),signed(cents(item.selling_price_thb)-cents(item.cost_thb)),receiptLabel],4);
  });
  definitions.forEach(definition => {
    const items = data.items.filter(item => definition.items.includes(item.item_number));
    const cost = sum(items.map(item => cents(item.cost_thb)));
    const sale = sum(items.map(item => cents(item.selling_price_thb)));
    const receipt = receipts(definition.group);
    addRow('groups',[definition.label,money(cost),money(sale),money(receipt),signed(receipt-sale),signed(receipt-cost)],5);
  });
  addRow('groups',['สาย — ยอดปรับเพิ่มรอจับคู่ (ต้นทุนรวมอยู่ใน 13 ชิ้นแล้ว)','—','—',money(pending),'รอจับคู่','ยังไม่แบ่งกำไรรายชิ้น']);
  addRow('groups',['X-raypad — รวมยอดโอน 2 สลิป','ไม่มีข้อมูล','ไม่มีข้อมูล',money(receipts(6)),'ไม่มีราคาตั้ง','ยังสรุปกำไรไม่ได้']);
  set('yuan-total',sum(data.items.map(item => cents(item.cost_cny))));
  for(const id of ['cost-total','group-cost']) set(id,totalCost);
  for(const id of ['sale-total','group-sale']) set(id,totalSale);
  set('received-total',received-pending-receipts(6));
  set('group-received',received);
  set('planned-profit-total',totalSale-totalCost,true);
  set('group-difference',received-totalSale,true);
  set('group-profit',profit,true);
})();
