"""Hard-terminate isolated PHP workers; verify OS/database lock release and exact recovery."""
import json, os, subprocess, sys, time
from pathlib import Path
root = Path(__file__).resolve().parents[2]
php = Path(r'C:\Users\ay\Documents\Latest\add-ons\.tools\php\php.exe')
wpcli = php.parent.parent/'wp-cli.phar'
site = Path(sys.argv[1]).resolve() if len(sys.argv)>1 else root/'.qa-runtime/wordpress-mysql'
extension = ['-d','extension=php_mysqli.dll'] if 'mysql' in site.name else []
control = Path(__file__).with_name('crash-control.php')
worker = Path(__file__).with_name('crash-worker.php')
def request(mode,job=''):
    env = dict(os.environ,SUITEEDGE_CRASH_MODE=mode,SUITEEDGE_CRASH_JOB=job)
    result = subprocess.run([str(php),*extension,str(wpcli),'--path='+str(site),'eval-file',str(control)],env=env,capture_output=True,text=True,timeout=45)
    assert result.returncode == 0, result.stdout+result.stderr
    return json.loads(next(line.split('CRASH_RESULT:',1)[1] for line in result.stdout.splitlines() if line.startswith('CRASH_RESULT:')))
checks = 0
for scenario in [('create',s) for s in ['lock','partial','written']] + [('create_saved',s) for s in ['lock','partial','written']]:
    mode, stage = scenario
    created = request(mode)
    marker = root/'.qa-runtime'/('crash-'+stage+'.marker')
    if marker.exists(): marker.unlink()
    process = subprocess.Popen([str(php),*extension,str(worker),str(site),created['job'],stage,str(marker)],stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    try:
        deadline = time.monotonic()+45
        while not marker.exists() and process.poll() is None and time.monotonic()<deadline: time.sleep(.1)
        assert marker.exists(), process.communicate(timeout=2)
        process.terminate(); process.communicate(timeout=10)
        if stage == 'lock':
            assert request('lock')['released']
            request('cancel',created['job'])
        else:
            result = request('recover',created['job'])
            assert result['content'] == 'Original crash text' and result['builder_data'] == '', result
            assert result['item'] == ('recovered' if stage=='partial' else 'restored'), result
        checks += 1
    finally:
        if process.poll() is None: process.kill(); process.communicate(timeout=10)
        if marker.exists(): marker.unlink()
(root/'.qa-runtime'/('crash-results-'+site.name+'.json')).write_text(json.dumps({'terminated_workers':checks,'stages':['lock','partial','written'],'paths':['direct conversion','saved output application'],'database':'MariaDB' if extension else 'SQLite'},indent=2))
print(f'PASS: {checks} hard-termination recovery scenarios on {site.name}')
