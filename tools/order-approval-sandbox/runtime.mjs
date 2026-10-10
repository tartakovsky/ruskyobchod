import { PHP, loadPHPRuntime } from '@php-wasm/universal';
import { getPHPLoaderModule } from '@php-wasm/node-8-3';
import fs from 'node:fs';
import path from 'node:path';
export const root = path.resolve(import.meta.dirname,'../..');
export async function createSandbox() {
  // No network proxy, socket, host filesystem mount or production credentials.
  const php = new PHP(await loadPHPRuntime(await getPHPLoaderModule(),{}));
  for(const dir of ['wordpress/wp-content/plugins/gastronom-order-review','tests/order-review']) {
    for(const f of fs.readdirSync(path.join(root,dir),{recursive:true})) {
      const source=path.join(root,dir,f);if(!fs.statSync(source).isFile())continue;
      const dest='/app/'+dir+'/'+f;php.mkdir(path.posix.dirname(dest));php.writeFile(dest,fs.readFileSync(source));
    }
  }
  let queue=Promise.resolve();
  function request(payload) {
    const task=queue.then(async()=>{
      php.writeFile('/request.json',JSON.stringify(payload));
      const result=await php.run({scriptPath:'/app/tests/order-review/browser-bridge.php'});
      if(result.errors)throw Error(result.errors);
      const body=JSON.parse(result.text);if(body.error)throw Error(body.error);return body;
    });
    queue=task.catch(()=>{});return task;
  }
  return {request,php};
}
export const products=[{id:1,name:'Пельмени «Домашние», 500 г',price:'6.00'},{id:2,name:'Продукты к заказу',price:'30.50'},{id:3,name:'Пельмени «Сибирские», 500 г',price:'7.50'},{id:4,name:'Сыр, на вес',price:'20.00',weighted:true}];
export function html(config) {
  return `<!doctype html><html lang="ru"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Gastronom — проверка заказа</title>
  <link rel="stylesheet" href="/assets/review.css"><style>body{margin:0;background:#f1f4f6;color:#213544;font:15px/1.5 Arial,sans-serif}header{background:#153e59;color:white;padding:18px 32px}main{max-width:1320px;margin:24px auto;padding:0 20px}.sandbox{padding:12px;background:#fff4da;border:1px solid #e7d2a0;margin-bottom:18px}.layout{display:grid;grid-template-columns:minmax(0,1fr) 270px;gap:20px}.card{background:white;padding:22px;border:1px solid #dce4e9;border-radius:8px}aside h3{margin:0 0 12px}aside{color:#516b7b}.toolbar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:10px}button,select{padding:7px;font:inherit}@media(max-width:900px){.layout{grid-template-columns:1fr}}</style>
  <header>GASTRONOM · Заказ №12001</header><main><div class="sandbox"><strong>Изолированная проверка.</strong> Только вымышленные данные. Письма сохраняются в тестовый журнал, касса и списание моделируются. Реальных операций нет.
  <div class="toolbar"><select id="scenario"><option value="cod">Оплата при получении</option><option value="prepaid">Предоплата 40 €</option><option value="weight">Весовой товар</option><option value="slovak">Письмо на словацком</option></select><button id="reset">Начать сценарий заново</button><label><input id="fail-effect" type="checkbox"> Сбой кассы</label><label><input id="fail-mail" type="checkbox"> Сбой письма</label></div></div>
  <div class="layout"><section class="card"><div id="gor-app"></div></section><aside class="card"><h3>Системы доставки</h3><p>На рабочем сайте здесь остаются существующие блоки Packeta / GLS.</p><p>Эта область показана схематично. Дата передачи и подтверждение клиенту находятся в основном блоке заказа.</p></aside></div></main>
  <script id="gor-config" type="application/json">${JSON.stringify(config).replace(/</g,'\\u003c')}</script><script src="/sandbox.js"></script><script src="/assets/review.js"></script></html>`;
}
