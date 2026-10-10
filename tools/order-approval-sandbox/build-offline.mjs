import {build} from 'esbuild';
import fs from 'node:fs';
import path from 'node:path';
import {root,html,products} from './runtime.mjs';
const base=path.join(root,'tools/order-approval-sandbox');
const files={};
for(const dir of ['wordpress/wp-content/plugins/gastronom-order-review','tests/order-review']) {
  for(const name of fs.readdirSync(path.join(root,dir),{recursive:true})) {
    if(!name.endsWith('.php'))continue;
    files['/app/'+dir+'/'+name]=fs.readFileSync(path.join(root,dir,name),'utf8');
  }
}
const ui=fs.readFileSync(path.join(root,'wordpress/wp-content/plugins/gastronom-order-review/assets/review.js'),'utf8');
const css=fs.readFileSync(path.join(root,'wordpress/wp-content/plugins/gastronom-order-review/assets/review.css'),'utf8');
const entry=`import {PHP,loadPHPRuntime} from '@php-wasm/universal';
import * as loader from './node_modules/@php-wasm/web-8-3/asyncify/php_8_3.js';
import binary from './node_modules/@php-wasm/web-8-3/asyncify/8_3_33/php_8_3.wasm';
(async()=>{
document.querySelector('#gor-app').textContent='Загрузка изолированного PHP…';
const php=new PHP(await loadPHPRuntime(loader,{wasmBinary:binary}));
const files=${JSON.stringify(files)};
for(const [name,text] of Object.entries(files)){php.mkdir(name.slice(0,name.lastIndexOf('/')));php.writeFile(name,text);}
let scenario='cod',queue=Promise.resolve();
window.gorTransport=payload=>{const task=queue.then(async()=>{php.writeFile('/request.json',JSON.stringify({...payload,scenario}));const result=await php.run({scriptPath:'/app/tests/order-review/browser-bridge.php'});const data=JSON.parse(result.text);if(data.error)throw Error(data.error);return data;});queue=task.catch(()=>{});return task;};
window.gorProducts=async search=>${JSON.stringify(products)}.filter(p=>p.name.toLowerCase().includes(search.toLowerCase()));
async function draw(reset=false){const config=await gorTransport({action:reset?'reset':'read'});document.querySelector('#gor-config').textContent=JSON.stringify(config);document.querySelector('#gor-app').outerHTML='<div id="gor-app"></div>';const script=document.createElement('script');script.textContent=${JSON.stringify(ui)};document.body.appendChild(script);script.remove();}
document.querySelector('#scenario').onchange=async e=>{scenario=e.target.value;document.querySelector('#fail-effect').checked=false;document.querySelector('#fail-mail').checked=false;await gorTransport({action:'fault',effect:false,mail:false});await draw();};
document.querySelector('#reset').onclick=async()=>{document.querySelector('#fail-effect').checked=false;document.querySelector('#fail-mail').checked=false;await draw(true);};
for(const id of ['fail-effect','fail-mail'])document.getElementById(id).onchange=()=>gorTransport({action:'fault',effect:document.getElementById('fail-effect').checked,mail:document.getElementById('fail-mail').checked});
await draw();window.gorSandboxReady=true;
})().catch(e=>{document.querySelector('#gor-app').textContent='Ошибка запуска: '+e.message;console.error(e);});`;
const result=await build({stdin:{contents:entry,resolveDir:base,sourcefile:'offline-entry.js'},bundle:true,write:false,external:['worker_threads','events'],format:'iife',platform:'browser',loader:{'.wasm':'binary'},minify:true,target:'es2022'});
const csp=`<meta http-equiv="Content-Security-Policy" content="default-src 'none'; script-src 'unsafe-inline' 'unsafe-eval' 'wasm-unsafe-eval'; style-src 'unsafe-inline'; img-src data:; frame-src about: data:; connect-src 'none'; form-action 'none'; base-uri 'none'">`;
let page=html({}).replace(/<link[^>]+>/,'<style>'+css+'</style>').replace('<meta charset="utf-8">','<meta charset="utf-8">'+csp).replace(/<script src="[^"]+"><\/script>/g,'');
page=page.replace('</html>',()=>'<script>'+result.outputFiles[0].text.replace(/<\/script/gi,'<\\/script')+'</script></html>');
const dest=path.join(root,'docs/order-review/Gastronom-review-offline.html');fs.writeFileSync(dest,page);console.log(dest+' ('+Math.round(page.length/1024/1024)+' MB)');
