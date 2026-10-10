#!/usr/bin/env python3
"""Build a reviewable, non-deploying archive; reject source changes after verification."""
import hashlib,json,pathlib,subprocess,zipfile,datetime,argparse
root=pathlib.Path(__file__).resolve().parents[2]
report=json.loads((root/'docs/order-review/verification.json').read_text())
assert report.get('sourceHashes') and all(r['exitCode']==0 for r in report['report']), 'Passing verification required'
for name,expected in report['sourceHashes'].items():
    assert hashlib.sha256((root/name).read_bytes()).hexdigest()==expected, 'Changed after verification: '+name
base='d5a339a1'
parser=argparse.ArgumentParser()
parser.add_argument('--baseline-json',default='docs/order-review/deployment-20261003/preflight.json')
args=parser.parse_args()
preflight=json.loads((root/args.baseline_json).read_text())
current_baselines={'wordpress/'+e['path']:e['actual'] for e in preflight['files']}
files={p.relative_to(root).as_posix() for p in (root/'wordpress/wp-content/plugins/gastronom-order-review').rglob('*') if p.is_file()}
files.update(subprocess.check_output(['git','diff','--name-only',base,'--','wordpress'],cwd=root,text=True).splitlines())
files.add('wordpress/wp-content/mu-plugins/rusky-order-review-guard.php')
entries=[]
for name in sorted(files):
    data=(root/name).read_bytes()
    before=subprocess.run(['git','show',base+':'+name],cwd=root,capture_output=True)
    baseline_data=before.stdout
    # Live GLS differs from Git baseline only by the terminal newline (read-only preflight 2026-09-14).
    if name=='wordpress/wp-content/plugins/gls-shipping-for-woocommerce/includes/api/class-gls-shipping-api-data.php' and not baseline_data.endswith(b'\n'):
        baseline_data+=b'\n'
    entries.append({'path':name,'sha256':hashlib.sha256(data).hexdigest(),'baseline_sha256':current_baselines[name]})
manifest={'schema':1,'created':datetime.datetime.now(datetime.timezone.utc).isoformat(),'baseline':base,'production_baseline_at':preflight['at'],'verification_date':report['date'],'production_changed':False,'files':entries}
(root/'docs/order-review/release-manifest.json').write_text(json.dumps(manifest,indent=2)+'\n')
archive=root/'docs/order-review/gastronom-order-review-candidate.zip'
with zipfile.ZipFile(archive,'w',zipfile.ZIP_DEFLATED) as z:
    for entry in entries:z.write(root/entry['path'],entry['path'])
    for doc in ['README.md','IMPLEMENTATION-PLAN.md','RELEASE-RUNBOOK.md','TEST-REPORT.md','verification.json','release-manifest.json']:
        z.write(root/'docs/order-review'/doc,'docs/order-review/'+doc)
print(f'Prepared {archive.name}: {len(entries)} source files, {archive.stat().st_size} bytes; no deployment performed.')
