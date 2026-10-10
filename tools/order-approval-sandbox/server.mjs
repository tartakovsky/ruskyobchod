import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import {createSandbox,root,html,products} from './runtime.mjs';
const sandbox=await createSandbox();
const port=Number(process.env.GASTRONOM_REVIEW_PORT||8787);
const scripts=`const scenario=new URLSearchParams(location.search).get('scenario')||'cod';document.querySelector('#scenario').value=scenario;document.querySelector('#scenario').onchange=e=>location.search='scenario='+e.target.value;window.gorTransport=async payload=>{const r=await fetch('/api',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({...payload,scenario})});const j=await r.json();if(!r.ok)throw Error(j.error);return j};window.gorProducts=async search=>(await fetch('/products').then(r=>r.json())).filter(p=>p.name.toLowerCase().includes(search.toLowerCase()));document.querySelector('#reset').onclick=async()=>{await gorTransport({action:'reset'});location.reload()};for(const id of ['fail-effect','fail-mail'])document.getElementById(id).onchange=()=>gorTransport({action:'fault',effect:document.getElementById('fail-effect').checked,mail:document.getElementById('fail-mail').checked});`;
http.createServer(async(req,res)=>{
  res.setHeader('Cache-Control','no-store');res.setHeader('X-Content-Type-Options','nosniff');
  try {
    const host=`127.0.0.1:${port}`;
    if(req.headers.host!==host||req.headers.origin&&req.headers.origin!==`http://${host}`){res.writeHead(403);res.end('Local sandbox only');return;}
    const url=new URL(req.url,`http://${host}`);
    if(req.method==='GET'&&url.pathname==='/'){
      const scenario=url.searchParams.get('scenario')||'cod';const config=await sandbox.request({action:'read',scenario});res.setHeader('Content-Type','text/html; charset=utf-8');res.end(html(config));return;
    }
    if(req.method==='POST'&&url.pathname==='/api'){
      let body='';for await(const chunk of req){body+=chunk;if(body.length>100000)throw Error('Payload too large');}
      res.setHeader('Content-Type','application/json; charset=utf-8');res.end(JSON.stringify(await sandbox.request(JSON.parse(body))));return;
    }
    if(url.pathname==='/products'){res.setHeader('Content-Type','application/json');res.end(JSON.stringify(products));return;}
    if(url.pathname==='/sandbox.js'){res.setHeader('Content-Type','text/javascript');res.end(scripts);return;}
    if(['/assets/review.css','/assets/review.js'].includes(url.pathname)){res.setHeader('Content-Type',url.pathname.endsWith('.css')?'text/css':'text/javascript');res.end(fs.readFileSync(path.join(root,'wordpress/wp-content/plugins/gastronom-order-review',url.pathname)));return;}
    res.writeHead(404);res.end('Not found');
  }catch(e){res.writeHead(409,{'Content-Type':'application/json'});res.end(JSON.stringify({error:e.message}));}
}).listen(port,'127.0.0.1',()=>console.log(`Protected review: http://127.0.0.1:${port}`));
