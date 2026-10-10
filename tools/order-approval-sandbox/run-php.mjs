import { PHP, loadPHPRuntime } from '@php-wasm/universal';
import { getPHPLoaderModule } from '@php-wasm/node-8-3';
import fs from 'node:fs';
import path from 'node:path';
const root = path.resolve(import.meta.dirname, '../..');
const php = new PHP(await loadPHPRuntime(await getPHPLoaderModule(), {}));
const dirs = ['wordpress/wp-content/plugins/gastronom-order-review', 'tests/order-review', 'wordpress/wp-content/mu-plugins'];
for (const dir of dirs) {
  for (const f of fs.readdirSync(path.join(root,dir), {recursive:true})) {
    const local=path.join(root,dir,f);
    if (!fs.statSync(local).isFile()) continue;
    const dest='/app/'+dir+'/'+f;
    php.mkdir(path.posix.dirname(dest));php.writeFile(dest,fs.readFileSync(local));
  }
}
for(const file of ['gastronom-stock-fix/gastronom-stock-fix.php','gls-shipping-for-woocommerce/includes/api/class-gls-shipping-api-data.php','packeta/src/Packetery/Module/Order/Builder.php','packeta/src/Packetery/Module/Order/PacketSubmitter.php']) {
 const rel='wordpress/wp-content/plugins/'+file,dest='/app/'+rel;php.mkdir(path.posix.dirname(dest));php.writeFile(dest,fs.readFileSync(path.join(root,rel)));
}
const target=process.argv[2]||'tests/order-review/domain.php';
const result=await php.run({scriptPath:'/app/'+target});
process.stdout.write(result.text);
if(result.errors)process.stderr.write(result.errors);
process.exitCode=result.exitCode;
