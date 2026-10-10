(() => {
  'use strict';
  const root = document.getElementById('gor-app');
  if (!root) return;
  const config = JSON.parse(document.getElementById('gor-config').textContent);
  const ru = config.uiLanguage !== 'sk';
  const t = (r, s) => ru ? r : s;
  let state = config.state, busy = false, error = '', draft = null, previewed = null;
  const labels = {
    pending:t('Ожидает утверждения','Čaká na schválenie'), editing:t('Корректировка заказа','Úprava objednávky'),
    awaiting:t('Ожидаем согласия клиента','Čakáme na súhlas zákazníka'), agreed:t('Согласовано клиентом','Zákazník súhlasil'),
    executing:t('Выполняется утверждение','Prebieha schvaľovanie'), approved:t('Утверждён','Schválená'),
    cancelled:t('Отменён по согласованию','Zrušená po dohode'), error:t('Утверждение не завершено','Schválenie nebolo dokončené')
  };
  const esc = x => String(x ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const money = n => (n / 100).toLocaleString(ru?'ru-RU':'sk-SK',{minimumFractionDigits:2,maximumFractionDigits:2})+' €';
  const total = q => q.lines.reduce((n,l)=>n+l.total,0)+q.shipping+q.fees;
  const balance = () => total(state.quote)-state.paid-state.additional_paid+state.refunded;
  const button = (action,text,primary=false,disabled=false) => `<button type="button" data-action="${action}" ${busy||disabled?'disabled':''} class="${primary?'primary':''}">${text}</button>`;
  const input = (name,label,value='',type='text') => `<label><span class="gor-label">${label}</span><input name="${name}" type="${type}" value="${esc(value)}" ${type==='date'?`min="${config.today}"`:''}></label>`;
  function canApprove() {
    return ['pending','agreed'].includes(state.status) && state.quote.lines.every(l=>!l.weight_pending)
      && (!state.courier || !state.quote.lines.length || /^\d{4}-\d{2}-\d{2}$/.test(state.dispatch_date) && state.dispatch_date>=config.today)
      && (config.customer.cod || balance()<=0);
  }
  const table = q => `<table><thead><tr><th>${t('Товар','Tovar')}</th><th>${t('Количество / вес','Množstvo / hmotnosť')}</th><th>${t('Цена за ед.','Jednotková cena')}</th><th>${t('Стоимость','Cena')}</th></tr></thead><tbody>${q.lines.map(l=>`<tr><td>${esc(l.name)}${l.weight_pending?`<br><span class="gor-muted">${t('Нужно уточнить фактический вес','Treba upresniť skutočnú hmotnosť')}</span>`:''}</td><td>${l.qty/1000} ${l.unit==='kg'&&!l.weight_pending?t('кг','kg'):t('шт.','ks')}</td><td>${money(l.rate)}${l.unit==='kg'?t(' / кг',' / kg'):''}</td><td>${money(l.total)}</td></tr>`).join('')}</tbody></table><p style="text-align:right">${t('Доставка','Doprava')}: ${money(q.shipping)}${q.fees?` · ${t('Сборы','Poplatky')}: ${money(q.fees)}`:''}</p><p class="gor-total">${t('Итого','Spolu')}: ${money(total(q))}</p>`;
  function agreementBlock() {
    const entry = state.mail['proposal-'+state.revision];
    const sent = entry?.status==='sent';
    return `<section class="gor-edit"><h3>${t('Согласование с клиентом','Súhlas zákazníka')}</h3>
      <p>${sent ? (entry.approval_expires ? t('Письмо отправлено. Клиент подтверждает изменения по кнопке в письме.','E-mail bol odoslaný. Zákazník potvrdí zmeny tlačidlom v e-maile.') : t('Предыдущее письмо отправлено. Клиент может ответить письмом или по телефону.','Predchádzajúci e-mail bol odoslaný. Zákazník môže odpovedať e-mailom alebo telefonicky.')) : t('Письмо ещё не отправлено. Согласование недоступно.','E-mail ešte nebol odoslaný. Súhlas zatiaľ nemožno zaznamenať.')}</p>
      ${button('refresh',t('Обновить статус','Obnoviť stav'))} ${entry?.message?button('view_proposal',t('Посмотреть письмо','Zobraziť e-mail')):''}
      ${!sent?button('retry_proposal',t('Повторить отправку','Zopakovať odoslanie')):`<details><summary>${t('Клиент согласовал по письму или телефону','Zákazník súhlasil e-mailom alebo telefonicky')}</summary>
      <p>${t('Отмечайте только после явного согласия клиента с текущими изменениями. Время сохранится автоматически.','Zaznamenajte iba výslovný súhlas zákazníka s aktuálnymi zmenami. Čas sa uloží automaticky.')}</p>
      <label><span class="gor-label">${t('Как получено согласие','Spôsob získania súhlasu')}</span><select name="consent_channel"><option value="email">${t('По письму','E-mailom')}</option><option value="phone">${t('По телефону','Telefonicky')}</option></select></label>
      ${button('agree',t('Клиент согласовал','Zákazník súhlasil'),true)}</details>`}</section>`;
  }
  function render() {
    const c=config.customer, b=balance(), mail=state.mail.confirmation;
    const needsDate=state.courier&&state.quote.lines.length>0&&!['approved','cancelled'].includes(state.status);
    const validDate=/^\d{4}-\d{2}-\d{2}$/.test(state.dispatch_date)&&state.dispatch_date>=config.today;
    root.innerHTML=`<div role="alert" class="gor-error" ${error?'':'hidden'}>${esc(error)}</div>
      <div class="gor-grid"><section><h3>${t('Данные заказчика','Údaje zákazníka')}</h3><p><strong>${esc(c.full_name)}</strong></p><p>${esc(c.phone)}<br>${esc(c.email)}</p><p>${esc(c.address).replace(/\n/g,'<br>')}</p></section>
      <section><h3>${t('Оплата','Platba')}</h3><p>${esc(c.payment)}</p><p><strong>${state.paid+state.additional_paid>0?t('Оплачено: ','Zaplatené: ')+money(state.paid+state.additional_paid):t('Не оплачено','Nezaplatené')}</strong></p>
      ${b<0?`<p>${t('К возврату','Na vrátenie')}: <strong>${money(-b)}</strong></p>`:b>0?`<p>${c.cod?t('При получении','Pri prevzatí'):t('К оплате','Na zaplatenie')}: <strong>${money(b)}</strong></p>`:''}${state.refunded?`<p>${t('Возвращено','Vrátené')}: ${money(state.refunded)}</p>`:''}</section>
      <section><h3>${t('Доставка','Doprava')}</h3><p>${esc(c.shipping)}</p></section></div>
      <div class="gor-controls"><div><span class="gor-label">${t('Статус заказа','Stav objednávky')}</span><span class="gor-status ${state.status}" role="status">${labels[state.status]}</span></div>
      ${needsDate?input('dispatch_date',t('Дата передачи курьерской службе','Dátum odovzdania dopravcovi'),state.dispatch_date,'date'):''}
      ${button('approve',t('Утвердить заказ','Schváliť objednávku'),true,!canApprove())}
      ${needsDate&&!validDate?`<span id="gor-date-hint" class="gor-muted">${t('Укажите дату передачи курьеру','Zadajte dátum odovzdania dopravcovi')}</span>`:''}
      ${button('edit',t('Изменить','Upraviť'),false,!['pending','editing','awaiting','agreed'].includes(state.status))}
      ${state.status==='error'?button('retry',t('Повторить проверку','Zopakovať kontrolu')):''}</div>
      ${state.status==='agreed'?`<p class="gor-muted">${t('Согласие клиента записано','Súhlas zákazníka bol zaznamenaný')}${state.consent_at?' · '+esc(new Date(state.consent_at.replace(' ','T')+'Z').toLocaleString(ru?'ru-RU':'sk-SK')):''}. ${t('Проверьте заказ и нажмите «Утвердить заказ».','Skontrolujte objednávku a stlačte „Schváliť objednávku“.')}</p>`:''}
      ${state.status==='error'?`<p class="gor-error">${esc(state.effect?.reference||t('Ошибка выполнения','Chyba spracovania'))}</p>`:''}
      ${mail?`<p class="gor-muted">${mail.status==='sent'?t('Подтверждение отправлено: ','Potvrdenie odoslané: ')+esc(mail.at):t('Подтверждение не отправлено','Potvrdenie nebolo odoslané')} ${mail.status!=='sent'?button('retry_confirmation',t('Повторить отправку','Zopakovať odoslanie')):button('view_confirmation',t('Посмотреть письмо','Zobraziť e-mail'))}</p>`:''}
      ${state.status==='approved'&&state.dispatch_date?`<p>${t('Передача курьерской службе','Odovzdanie dopravcovi')}: <strong>${esc(state.dispatch_date.split('-').reverse().join('.'))}</strong></p>`:''}
      ${state.status==='editing'?editBlock():''}${state.status==='awaiting'?agreementBlock():''}${table(state.quote)}
      ${state.status==='pending'&&state.quote.lines.some(l=>l.unit==='kg')?`<div class="gor-controls">${state.quote.lines.map((l,i)=>l.unit==='kg'?input('actual_weight_'+i,t('Фактический вес, кг — ','Skutočná hmotnosť, kg — ')+esc(l.name)+'<br>'+t('Диапазон за 1 шт.: ','Rozsah na 1 ks: ')+esc(l.weight_min_kg)+'–'+esc(l.weight_max_kg)+t(' кг · Заказано: ',' kg · Objednané: ')+esc(l.ordered_units)+t(' шт.<br>Диапазон общего веса: ',' ks<br>Rozsah celkovej hmotnosti: ')+esc(l.weight_min_kg*l.ordered_units)+'–'+esc(l.weight_max_kg*l.ordered_units)+' kg',l.weight_pending?'':String(l.qty/1000)):'').join('')}${button('weight',t('Пересчитать по весу','Prepočítať podľa hmotnosti'))}${state.quote.lines.every(l=>!l.weight_pending)?button('preview_confirmation',t('Предпросмотр письма','Náhľad e-mailu')):''}</div>`:''}
      ${b<0?`<details class="gor-edit" ${state.status==='editing'?'open':''}><summary>${t('Реквизиты и ручной возврат','Údaje a ručné vrátenie peňazí')} ${money(-b)}</summary><div class="gor-controls">${input('iban',t('IBAN клиента','IBAN zákazníka'),state.iban)}${input('holder',t('Имя владельца счёта','Meno majiteľa účtu'),state.holder)}</div><label><input name="bank_consent" type="checkbox" ${state.bank_consent?'checked':''}> ${t('Клиент согласен на возврат банковским переводом','Zákazník súhlasí s vrátením peňazí bankovým prevodom')}</label>${button('bank',t('Сохранить реквизиты','Uložiť údaje'))}${['approved','cancelled'].includes(state.status)?`<div class="gor-controls">${input('refund_amount',t('Сумма выполненного перевода','Suma vykonaného prevodu'),(-b/100).toFixed(2))}${input('refund_reference',t('Банковская ссылка / номер операции','Banková referencia / číslo transakcie'))}${button('refund',t('Отметить выполненный возврат','Zaznamenať vykonané vrátenie'))}</div>`:''}</details>`:''}
      ${b>0&&!c.cod&&['pending','agreed'].includes(state.status)?`<details class="gor-edit"><summary>${t('Отметить поступление оплаты / доплаты','Zaznamenať prijatie platby / doplatku')}</summary><div class="gor-controls">${input('payment_amount',t('Полученная сумма','Prijatá suma'),(b/100).toFixed(2))}${input('payment_reference',t('Подтверждение банковского платежа','Potvrdenie bankovej platby'))}${button('payment',t('Оплата получена','Platba prijatá'))}</div></details>`:''}
      <dialog id="gor-preview"><h3 id="gor-subject"></h3><iframe title="${t('Письмо клиенту','E-mail zákazníkovi')}" sandbox=""></iframe><div class="gor-controls">${button('send_preview',t('Отправить изменения клиенту','Odoslať zmeny zákazníkovi'),true)}${button('close_preview',t('Закрыть','Zavrieť'))}</div></dialog>`;
    if(state.status==='editing')updateDraftTotal();
  }
  function editBlock() {
    if(!draft)draft={lines:state.quote.lines.map(l=>({...l,quantity:l.weight_pending?'':String(l.qty/1000),price:(l.rate/100).toFixed(2),weighted:l.unit==='kg',weight_confirmed:!l.weight_pending})),reason:state.reason,cause:state.cause||'absence',comment:state.comment};
    return `<section class="gor-edit"><h3>${t('Изменение состава','Úprava zloženia')}</h3><div id="gor-lines">${draft.lines.map((l,i)=>`<div class="gor-row" data-index="${i}"><label><span class="gor-label">${t('Товар','Tovar')}</span><strong>${esc(l.name)}</strong></label>${input('qty_'+i,l.weighted?t('Фактический вес, кг','Skutočná hmotnosť, kg'):t('Количество','Množstvo'),l.quantity)}${input('price_'+i,l.weighted?t('Цена за кг, €','Cena za kg, €'):t('Цена за ед., €','Cena za kus, €'),l.price)}<span class="gor-line-total"></span><button type="button" data-remove="${i}">${t('Удалить','Odstrániť')}</button>${l.weighted?`<label class="gor-weight"><input type="checkbox" name="weight_${i}" ${l.weight_confirmed?'checked':''}> ${t('Фактический вес подтверждён','Skutočná hmotnosť potvrdená')}</label>`:''}</div>`).join('')}</div>
      <div class="gor-inline">${input('search',t('Добавить товар на замену','Pridať náhradný produkt'))}${button('search',t('Найти товар','Nájsť produkt'))}</div><div class="gor-search-results"></div>
      <label><span class="gor-label">${t('Причина корректировки','Dôvod úpravy')}</span><select name="cause">${[['absence',t('Недостаточно товара','Nedostatok tovaru')],['customer',t('По просьбе клиента','Na žiadosť zákazníka')]].map(([value,label])=>`<option value="${value}" ${draft.cause===value?'selected':''}>${label}</option>`).join('')}</select></label>
      <label><span class="gor-label">${t('Комментарий клиенту — необязательно; язык: ','Komentár zákazníkovi — nepovinný; jazyk: ')}${state.language.toUpperCase()}</span><textarea name="comment">${esc(draft.comment)}</textarea></label>
      <p class="gor-summary" id="gor-draft-total"></p><div class="gor-controls">${button('save',t('Сохранить расчёт','Uložiť výpočet'))}${button('preview',t('Предпросмотр письма','Náhľad e-mailu'),true)}${button('cancel_edit',t('Отменить корректировку','Zrušiť úpravu'))}</div></section>`;
  }
  const val=n=>root.querySelector(`[name="${n}"]`)?.value||'';
  function readDraft() {
    if(!draft)return;
    draft.lines.forEach((l,i)=>{l.quantity=val('qty_'+i);l.price=val('price_'+i);if(l.weighted)l.weight_confirmed=!!root.querySelector(`[name="weight_${i}"]`)?.checked;});
    draft.reason=state.language==='ru'?'Изменение состава и стоимости заказа.':'Úprava zloženia a ceny objednávky.';
    draft.cause=val('cause');draft.comment=val('comment');
  }
  function updateDraftTotal() {
    readDraft();let sum=draft.lines.length?state.original.shipping+state.original.fees:0;
    draft.lines.forEach((l,i)=>{const qty=Math.round(Number(l.quantity.replace(',','.'))*1000),rate=Math.round(Number(l.price.replace(',','.'))*100);const amount=l.qty===qty&&l.rate===rate?l.total:Math.round(qty*rate/1000);sum+=amount;const node=root.querySelector(`[data-index="${i}"] .gor-line-total`);if(node)node.textContent=Number.isFinite(amount)?money(amount):'—';});
    const el=root.querySelector('#gor-draft-total');if(el)el.textContent=`${t('Было: ','Pôvodne: ')}${money(total(state.original))} → ${t('Предлагаемый итог: ','Navrhovaná suma: ')}${Number.isFinite(sum)?money(sum):'—'}. ${draft.lines.length?t('Доставка и сборы сохранены.','Doprava a poplatky sa nemenia.'):t('Предлагается отмена заказа без продажи и доставки.','Navrhujeme zrušiť objednávku bez predaja a dopravy.')}`;
  }
  async function api(action,data={}) {
    const payload={action,version:state.version,...data};
    const result=window.gorTransport?await window.gorTransport(payload):await fetch(config.endpoint,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':config.nonce},body:JSON.stringify(payload),cache:'no-store'}).then(async r=>{const j=await r.json();if(!r.ok)throw Error(j.message||t('Ошибка сервера','Chyba servera'));return j;});
    if(result.state)state=result.state;return result;
  }
  function showPreview(message,send) {
    const dialog=root.querySelector('dialog');dialog.querySelector('h3').textContent=message.subject;dialog.querySelector('iframe').dataset.html=message.html;dialog.querySelector('iframe').src='data:text/html;charset=utf-8,'+encodeURIComponent(message.html);dialog.querySelector('[data-action="send_preview"]').hidden=!send;dialog.showModal();
  }
  async function refresh() {
    if(busy||state.status==='editing'||root.querySelector('dialog')?.open)return;
    busy=true;const version=state.version,date=val('dispatch_date');
    try{await api('read');if(date)state.dispatch_date=date;if(state.version!==version){error='';busy=false;render();}}
    catch(ex){error=ex.message;busy=false;render();}finally{busy=false;root.querySelectorAll('[data-action="refresh"]').forEach(b=>b.disabled=false);}
  }
  root.addEventListener('input',e=>{
    if(e.target.name==='dispatch_date'){state.dispatch_date=e.target.value;root.querySelector('[data-action="approve"]').disabled=busy||!canApprove();const hint=root.querySelector('#gor-date-hint');if(hint)hint.hidden=/^\d{4}-\d{2}-\d{2}$/.test(e.target.value)&&e.target.value>=config.today;return;}
    if(e.target.closest('.gor-edit')&&state.status==='editing')updateDraftTotal();
  });
  // One read on returning to an awaiting order; no polling, page-wide observers or automatic business effects.
  window.addEventListener('focus',()=>{if(state.status==='awaiting')refresh();});
  root.addEventListener('click',async e=>{
    const el=e.target.closest('button');if(!el||busy)return;const action=el.dataset.action;
    if(el.dataset.remove!==undefined){readDraft();draft.lines.splice(Number(el.dataset.remove),1);render();return;}
    if(el.dataset.product){readDraft();const p=JSON.parse(el.dataset.product);draft.lines.push({product_id:p.id,name:p.name,quantity:'1',price:p.price,weighted:!!p.weighted,weight_confirmed:!p.weighted});render();return;}
    if(action==='refresh'){await refresh();return;}
    if(action==='close_preview'){root.querySelector('dialog').close();return;}
    if(action==='view_confirmation'||action==='view_proposal'){showPreview(state.mail[action==='view_confirmation'?'confirmation':'proposal-'+state.revision].message,false);return;}
    if(action==='search'){
      try{const products=window.gorProducts?await window.gorProducts(val('search')):await fetch(config.products+'?search='+encodeURIComponent(val('search')),{headers:{'X-WP-Nonce':config.nonce}}).then(r=>r.json());root.querySelector('.gor-search-results').innerHTML=products.map(p=>`<button type="button" data-product="${esc(JSON.stringify(p))}">${esc(p.name)} — ${esc(p.price)} €</button>`).join('')||t('Товары не найдены','Produkty sa nenašli');}catch(ex){error=ex.message;render();}return;
    }
    const dispatch=val('dispatch_date');let data={};
    if(action==='save'||action==='preview'){readDraft();data={quote:{lines:draft.lines},reason:draft.reason,cause:draft.cause,comment:draft.comment};}
    if(action==='agree')data.consent_channel=val('consent_channel');
    if(['weight','preview_confirmation','approve'].includes(action))data.dispatch_date=dispatch;
    if(action==='weight')data.weights=Object.fromEntries(state.quote.lines.map((l,i)=>[l.key,val('actual_weight_'+i)]).filter(([,v])=>v!==''));
    if(action==='bank')data={iban:val('iban'),holder:val('holder'),bank_consent:!!root.querySelector('[name="bank_consent"]')?.checked};
    if(action==='refund')data={amount:val('refund_amount'),reference:val('refund_reference')};
    if(action==='payment')data={amount:val('payment_amount'),reference:val('payment_reference')};
    if(action==='send_preview'&&previewed!==state.version){error=t('Расчёт изменился. Откройте предпросмотр заново.','Výpočet sa zmenil. Otvorte náhľad znova.');render();return;}
    busy=true;root.querySelectorAll('button').forEach(b=>b.disabled=true);error='';
    try{
      if(action==='preview_confirmation'){const result=await api('preview_confirmation',data);busy=false;render();showPreview(result.preview,false);return;}
      if(action==='preview'){await api('save',data);const result=await api('preview');busy=false;render();previewed=state.version;showPreview(result.preview,true);return;}
      if(action==='retry_confirmation'||action==='retry_proposal')await api('mail_retry',{key:action==='retry_confirmation'?'confirmation':'proposal-'+state.revision});
      else await api(action==='send_preview'?'proposal':action,data);
      if(!['save','bank'].includes(action))draft=null;
    }catch(ex){error=ex.message;}finally{busy=false;}
    render();
  });
  render();
})();
