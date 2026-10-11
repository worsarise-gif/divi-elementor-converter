"""HTTP integration tests against a disposable localhost QA WordPress site."""
import urllib.request, urllib.parse, urllib.error, http.cookiejar, re, json, html
from pathlib import Path
BASE = 'http://127.0.0.1:8097'
jar = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
checks = 0
def check(value, name):
 global checks
 if not value:
  Path('.qa-runtime/http-failure.html').write_text(globals().get('body',''),encoding='utf-8')
  raise AssertionError(name)
 checks += 1
def call(path, values=None, data=None, headers=None):
 if values is not None: data = urllib.parse.urlencode(values).encode()
 req = urllib.request.Request(path if path.startswith('http') else BASE+path, data=data, headers=headers or {})
 try:
  with client.open(req, timeout=45) as response:return response.status,response.read().decode(),response.headers
 except urllib.error.HTTPError as error:return error.code,error.read().decode(),error.headers
def nonce(body):
 result = re.search(r'name="_wpnonce" value="([^"]+)"',body)
 check(result is not None,'nonce present')
 return result.group(1)
call('/wp-login.php')
status,body,_ = call('/wp-login.php',{'log':'qa_admin','pwd':'local-qa-only','wp-submit':'Log In','testcookie':'1','redirect_to':BASE+'/wp-admin/'})
check(status == 200 and 'Dashboard' in body,'local administrator login')
status,body,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-import')
check(status == 200 and 'Import &amp; Scan Divi Exports' in body,'import screen')
import_nonce = nonce(body)
status,_,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-import',{'submit_upload':'Upload'})
check(status == 403,'upload CSRF rejected: '+str(status))
layout = '[et_pb_section][et_pb_row][et_pb_column type="4_4"][et_pb_text]HTTP QA page[/et_pb_text][et_pb_image src="https://example.com/image.png" align="right" align_tablet="center" align_phone="left" /][/et_pb_column][/et_pb_row][/et_pb_section]'
source = json.dumps({'context':'et_builder','data':{'10':{'post_title':'HTTP QA one','post_content':layout},'11':{'post_title':'HTTP QA two','post_content':layout}},'images':{'https://example.com/image.png':{'encoded':'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aJ1sAAAAASUVORK5CYII='}}})
boundary = 'SuiteEdgeQABoundary'
parts = []
for key,value in {'submit_upload':'Upload and Scan','_wpnonce':import_nonce}.items():
 parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n')
parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="divi_exports[]"; filename="http-qa.json"\r\nContent-Type: application/json\r\n\r\n{source}\r\n--{boundary}--\r\n')
status,body,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-import',data=''.join(parts).encode(),headers={'Content-Type':f'multipart/form-data; boundary={boundary}'})
check(status == 200 and '2 layouts ready' in body,'multipart import exposes both layouts')
status,body,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-analysis')
check(status == 200 and 'et_pb_text' in body and 'HTTP QA two' in body,'real scanned coverage')
status,body,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-mapping')
check('source_files[' not in body and 'Atomic output is unavailable' in body,'no arbitrary paths or Atomic option')
check('name="execution_mode" value="analyze"' in body and 'name="execution_mode" value="apply"' in body and '>Apply to pages</button>' in body,'visible analysis/apply buttons')
check('name="assets[]" value="import_external" checked' in body and 'name="assets[]" value="reuse_library" checked' in body,'recommended media defaults checked')
check('name="reviewed_warnings" value="1" checked' not in body,'published warning review remains explicit')
map_nonce = nonce(body)
status,_,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-jobs',{'submit_conversion':'Start','target_page[0]':'create_new','target_page[1]':'create_new'})
check(status == 403,'job creation CSRF rejected')
status,body,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-jobs',{'submit_conversion':'Start','_wpnonce':map_nonce,'target_page[0]':'create_new','target_page[1]':'create_new','execution_mode':'analyze','source_files[0]':'C:/Windows/win.ini'})
config = json.loads(re.search(r'const suiteedgeJobConfig=(.*?);</script>',body).group(1))
check(bool(config['active']),'persisted job creation')
job_id = config['active']
status,body,_ = call('/wp-admin/admin-ajax.php',{'action':'suiteedge_migrator_process_batch','job_id':job_id})
check(status == 403 and body.strip() == '-1','AJAX nonce rejected')
for action in ['suiteedge_migrator_apply_saved','suiteedge_migrator_clear_jobs']:
 status,body,_ = call('/wp-admin/admin-ajax.php',{'action':action,'job_id':job_id})
 check(status == 403 and body.strip() == '-1','new action CSRF rejected: '+action)
status,body,_ = call('/wp-admin/admin-ajax.php',{'action':'suiteedge_migrator_apply_saved','nonce':config['nonce'],'selection':json.dumps({job_id:[0]})})
check(status == 400 and not json.loads(body)['success'],'incomplete output cannot be queued through HTTP')
status,body,_ = call('/wp-admin/admin-ajax.php',{'action':'suiteedge_migrator_clear_jobs','nonce':config['nonce'],'jobs':'[[]]'})
check(status == 400 and not json.loads(body)['success'],'malformed clear selection rejected')
for _ in range(2):
 status,body,_ = call('/wp-admin/admin-ajax.php',{'action':'suiteedge_migrator_process_batch','job_id':job_id,'nonce':config['nonce'],'batch_size':'1'})
 result = json.loads(body)
 check(result['success'],'HTTP batch processing')
check(result['data']['status'] == 'completed' and result['data']['summary']['completed'] == 2,'two-layout dry run completion')
status,body,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-reports&job='+job_id)
check(status == 200 and 'analyzed' in body and '#1001' not in body and 'no page changes' in body,'real report page distinguishes analysis from page updates')
links = [html.unescape(link) for link in re.findall(r'href="([^"]*admin-post.php[^"]*)"',body)]
report_link = next(link for link in links if 'item=' not in link)
template_link = next(link for link in links if 'item=' in link)
status,body,headers = call(report_link)
check(status == 200 and json.loads(body)['summary']['completed'] == 2 and json.loads(body)['outcome']['converted_pages'] == 0 and 'attachment' in headers.get('Content-Disposition',''),'report download states zero converted pages')
status,body,_ = call(template_link)
check(status == 200 and json.loads(body)['version'] == '0.4' and json.loads(body)['content'][0]['elType'] == 'container','Elementor template download')
# Repeat the upload and perform an actual conversion to two new draft pages.
status,body,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-import',data=''.join(parts).encode(),headers={'Content-Type':f'multipart/form-data; boundary={boundary}'})
check(status == 200 and '2 layouts ready' in body,'second upload')
_,body,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-mapping')
map_nonce = nonce(body)
status,body,_ = call('/wp-admin/admin.php?page=suiteedge-migrator-jobs',{'submit_conversion':'Start','_wpnonce':map_nonce,'target_page[0]':'create_new','target_page[1]':'create_new','execution_mode':'apply','assets[]':'import_external'})
config = json.loads(re.search(r'const suiteedgeJobConfig=(.*?);</script>',body).group(1))
job_id = config['active']
status,body,_ = call('/wp-admin/admin-ajax.php',{'action':'suiteedge_migrator_process_batch','job_id':job_id,'nonce':config['nonce'],'batch_size':'5'})
result = json.loads(body)
check(result['success'] and result['data']['status'] == 'completed' and result['data']['summary']['completed'] == 2,'HTTP draft conversion')
status,body,_ = call('/wp-admin/admin-ajax.php',{'action':'suiteedge_migrator_rollback','job_id':job_id,'nonce':config['nonce']})
result = json.loads(body)
check(result['success'] and result['data']['restored_count'] == 2 and result['data']['status'] == 'rolled_back','HTTP rollback')
print(f'PASS: {checks} HTTP assertions (login, multipart upload, multi-layout scan, nonces, dry-run batches, reports, downloads)')
Path('.qa-runtime/http-results.json').write_text(json.dumps({'assertions':checks,'url':BASE,'browser_visual_qa':False},indent=2))
