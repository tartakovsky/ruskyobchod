import fs from 'node:fs';
import assert from 'node:assert/strict';
import path from 'node:path';
import {createSandbox,root} from './runtime.mjs';
const source=fs.readFileSync(new URL('./build-short-email.mjs',import.meta.url),'utf8');
const sandbox=await createSandbox();
for(const match of source.matchAll(/files\["(\/app\/wordpress[^"]+)"\]=(.*);/g))sandbox.php.writeFile(match[1],JSON.parse(match[2]));
sandbox.php.writeFile('/four-mails.php',`<?php
require '/app/tests/order-review/wp-fixture.php';
use Gastronom\\OrderReview\\Review as R;
use Gastronom\\OrderReview\\Mail as M;
$line=static fn($id,$name,$qty,$rate,$unit='pcs',$pending=false)=>['key'=>'item-'.$id,'product_id'=>$id,'name'=>$name,'qty'=>$qty,'rate'=>$rate,'unit'=>$unit,'weight_pending'=>$pending,'total'=>(int)round($qty*$rate/1000)];
$result=[];
foreach(['ru','sk'] as $lang) {
 $ru=$lang==='ru';
 $dumplings=$line(1,$ru?'Пельмени «Домашние», 500 г':'Pelmene Domáce, 500 g',1000,600);
 $cheese=$line(4,$ru?'Сыр, на вес':'Syr, vážený',300,2000,'kg',true);
 $replacement=$line(3,$ru?'Пельмени «Сибирские», 500 г':'Pelmene Sibírske, 500 g',1000,750);
 foreach(['normal','replacement','weight','replacement_weight'] as $type) {
  $weighted=in_array($type,['weight','replacement_weight'],true);
  $changed=in_array($type,['replacement','replacement_weight'],true);
  $s=R::create(['lines'=>$weighted?[$dumplings,$cheese]:[$dumplings],'shipping'=>350,'fees'=>0],0,$lang,true);
  $s['cause']='absence';
  if($changed)$s['quote']['lines'][0]=$replacement;
  if($weighted){$s['quote']['lines'][1]['qty']=460;$s['quote']['lines'][1]['total']=920;$s['quote']['lines'][1]['weight_pending']=false;}
  $s['dispatch_date']='2026-09-15';
  $customer=['number'=>'12001','name'=>$ru?'Анна':'Anna','payment'=>$ru?'При получении':'Dobierka','shipping'=>'Packeta','cod'=>true];
  $message=M::build($s,$customer,$changed?'proposal':'confirmation');
  $final=M::build($s,$customer,'confirmation');
  $result[$lang][$type]=['variant'=>M::variant($s),'message'=>$message,'final'=>$final];
 }
 // Explicit metadata is the only source for Russian, including pre-existing review state.
 $o=new WC_Order(12002);$o->meta['_gastronom_lang']='';$o->lang='ru';
 $result['language_default']=gor_state($o)['language'];
 $o->meta['_gastronom_lang']='ru';$result['language_ru']=gor_state($o)['language'];
}
echo json_encode($result,JSON_UNESCAPED_UNICODE);
`);
const result=await sandbox.php.run({scriptPath:'/four-mails.php'});assert.equal(result.errors,'');const data=JSON.parse(result.text);
assert.equal(data.language_default,'sk');assert.equal(data.language_ru,'ru');
for(const lang of ['ru','sk'])for(const [type,item]of Object.entries(data[lang])){
 assert.equal(item.variant,type);assert.equal((item.message.html.match(/<table /g)||[]).length,1);
 assert.ok(item.final.html.includes('15.09.2026'));
 if(lang==='ru'){
  if(type==='normal')assert.ok(!item.message.html.includes('не хватает')&&!item.message.html.includes('фактический вес'));
  if(type==='weight')assert.ok(item.message.html.includes('фактический вес')&&!item.message.html.includes('после вашего согласия'));
  if(type.includes('replacement'))assert.ok(item.message.html.includes('не хватает')&&item.message.html.includes('после вашего согласия'));
  if(type==='replacement_weight')assert.ok(item.message.html.includes('Также мы уточнили'));
 }
}
const names={normal:'1. Обычное подтверждение',replacement:'2. Замена товара',weight:'3. Уточнение веса',replacement_weight:'4. Замена и уточнение веса'};
const escape=x=>x.replaceAll('&','&amp;').replaceAll('"','&quot;').replaceAll('<','&lt;');
let page='<!doctype html><html lang="ru"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Gastronom — четыре варианта письма</title><style>body{font:16px/1.5 Arial;background:#f3f5f7;color:#213544;margin:24px auto;max-width:1400px;padding:0 20px}main{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,500px),1fr));gap:24px}section{background:white;padding:20px;border-radius:10px}iframe{width:100%;height:850px;border:0}summary{cursor:pointer}button{padding:10px 20px;margin:0 10px 20px 0;cursor:pointer}</style><h1>Четыре варианта письма</h1><p>Тестовые примеры. Письма с заменой запрашивают согласие; обычное подтверждение и взвешивание — сообщают итог.</p><button onclick="choose(\'ru\')">Русский</button><button onclick="choose(\'sk\')">Slovensky</button>';
for(const lang of ['ru','sk']){page+=`<main data-lang="${lang}" ${lang==='sk'?'style="display:none"':''}>`;for(const [type,item]of Object.entries(data[lang]))page+=`<section><h2>${names[type]}</h2><iframe sandbox srcdoc="${escape(item.message.html)}"></iframe>${type.includes('replacement')?`<details><summary>Подтверждение после согласования</summary><iframe sandbox srcdoc="${escape(item.final.html)}"></iframe></details>`:''}</section>`;page+='</main>';}
page+='<script>function choose(lang){document.querySelectorAll("main").forEach(el=>el.style.display=el.dataset.lang===lang?"grid":"none")}</script></html>';
fs.writeFileSync(path.join(root,'docs/order-review/Gastronom-four-emails.html'),page);
console.log('PASS: all 4 variants in RU/SK, one composition, consent only for changes, courier date in confirmations, explicit language/default SK. Gallery generated.');
