/* Real multipart HTTP uploads and streaming; isolated PHP doubles, no live store/email. */
const {chromium}=require('playwright');
const {spawn}=require('node:child_process');
const fs=require('node:fs');const path=require('node:path');const assert=require('node:assert/strict');
const root=path.resolve(__dirname,'..');
const testRoot=fs.mkdtempSync(path.join(root,'outputs/order-updates-http-'));
fs.mkdirSync(path.join(testRoot,'public'));
const php=process.env.GRAFIK_PHP||'php';
const server=spawn(php,['-d','extension_dir=C:/Users/Joaco/.cache/grafik-php/runtime/ext','-d','extension=fileinfo','-d','extension=mbstring','-d','upload_max_filesize=10M','-d','post_max_size=12M','-S','127.0.0.1:5192','-t',path.join(testRoot,'public'),path.join(__dirname,'php/order-updates-router.php')],{env:{...process.env,GRAFIK_MEDIA_TEST_ROOT:testRoot},windowsHide:true,stdio:'ignore'});
const png=Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9WQAAAAASUVORK5CYII=','base64');
const pdf=Buffer.from('%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF');
const base='http://127.0.0.1:5192/';
(async()=>{
 let browser;
 try{
  for(let i=0;i<40;i++){try{await fetch(base);break;}catch{await new Promise(r=>setTimeout(r,100));}}
  browser=await chromium.launch({channel:'msedge',headless:true});
  const page=await browser.newPage({viewport:{width:1000,height:900}});
  const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto(base);const zone=page.locator('.grafik-order-updates');
  const order=await zone.getAttribute('data-order'),nonce=await zone.getAttribute('data-nonce');
  async function api(fields,headers={}){return page.request.post(base+'admin-ajax.php',{multipart:{order,nonce,payload:'{}',operation:'save',...fields},headers});}
  let r=await api({nonce:'invalid'});assert.equal(r.status(),403);
  r=await api({}, {'X-Test-No-Permission':'1'});assert.equal(r.status(),403);
  await page.locator('[data-media-upload]').setInputFiles([{name:'produccion.png',mimeType:'image/png',buffer:png},{name:'comprobante.pdf',mimeType:'application/pdf',buffer:pdf}]);
  await page.getByText('Archivos subidos.',{exact:false}).waitFor();
  assert.equal(await page.locator('[data-media-id]').count(),2);
  const rows=page.locator('[data-media-id]');
  await rows.nth(0).getByLabel('Pedido listo',{exact:true}).check();
  await rows.nth(0).getByLabel('Pedido completado',{exact:true}).check();
  await rows.nth(1).getByLabel('Pedido enviado',{exact:true}).check();
  await page.locator('[data-tracking="carrier"]').selectOption('Starken');
  await page.locator('[data-tracking="code"]').fill('TRACK-29');
  await page.locator('[data-tracking="url"]').fill('https://example.test/tracking');
  await page.locator('[data-tracking="date"]').fill('2026-11-11');
  await page.getByRole('button',{name:'Actualizar pedido',exact:true}).click();
  assert.match(await page.locator('[data-media-status]').textContent(),/Primero pulsa Guardar/);
  await page.getByRole('button',{name:'Guardar fotos y seguimiento',exact:true}).click();
  await page.getByText('Fotos y seguimiento guardados. No se enviaron correos.',{exact:true}).waitFor();
  await page.reload();
  assert(await rows.nth(0).getByLabel('Pedido listo',{exact:true}).isChecked());
  assert(await rows.nth(1).getByLabel('Pedido enviado',{exact:true}).isChecked());
  for(const width of [390,1000]){
   await page.setViewportSize({width,height:900});
   assert(await zone.evaluate(e=>e.scrollWidth<=e.clientWidth));
   await zone.screenshot({path:path.join(root,'outputs/order-updates-admin-'+width+'.png')});
  }
  const id=await rows.nth(0).getAttribute('data-media-id');
  r=await page.request.get(base+'admin-post.php?action=grafik_order_media&order=29&file='+id,{headers:{'X-Test-No-Permission':'1'}});assert.equal(r.status(),404);
  r=await page.request.get(base+'?email=grafik_order_ready');const html=await r.text();
  assert.match(html,/produccion\.png/);assert(!html.includes('comprobante.pdf'));
  const signed=html.match(/src="([^"]+)"/)[1].replace(/&amp;/g,'&');
  r=await page.request.get(signed,{headers:{'X-Test-No-Permission':'1'}});assert.equal(r.status(),200);assert.equal(r.headers()['content-type'],'image/png');assert.equal(r.headers()['x-content-type-options'],'nosniff');assert.deepEqual(await r.body(),png);
  r=await page.request.get(signed.replace('order=29','order=30'),{headers:{'X-Test-No-Permission':'1'}});assert.equal(r.status(),404);
  r=await page.request.get(base+'?email=grafik_order_shipped');const shipped=await r.text();assert.match(shipped,/Ver comprobante/);assert(!shipped.includes('<img'));
  const pdfUrl=shipped.match(/href="([^"]+)"/)[1].replace(/&amp;/g,'&');
  r=await page.request.get(pdfUrl,{headers:{'X-Test-No-Permission':'1'}});assert.equal(r.status(),200);assert.equal(r.headers()['content-type'],'application/pdf');assert.deepEqual(await r.body(),pdf);
  r=await api({operation:'upload',file:{name:'fake.pdf',mimeType:'application/pdf',buffer:png}});assert.equal(r.status(),400);assert.match((await r.json()).data.message,/extensión/);
  // Simulate an independent editor changing the revision.
  const revision=await zone.getAttribute('data-revision');r=await api({payload:JSON.stringify({revision,selection:{},tracking:{}})});assert.equal(r.status(),200);
  await page.getByRole('button',{name:'Guardar fotos y seguimiento',exact:true}).click();await page.getByText('El pedido cambió en otra ventana.',{exact:false}).waitFor();
  await page.reload();await rows.nth(0).locator('[data-media-remove]').check();
  await page.getByRole('button',{name:'Guardar fotos y seguimiento',exact:true}).click();await page.getByText('Fotos y seguimiento guardados. No se enviaron correos.',{exact:true}).waitFor();
  r=await page.request.get(signed,{headers:{'X-Test-No-Permission':'1'}});assert.equal(r.status(),404);
  assert.deepEqual(errors,[]);
  console.log('PASS: real HTTP multipart uploads; admin persistence at 390/1000px; nonce/capability checks; actual signed image/PDF streaming; cross-order denial; MIME forgery; stale edits; deletion revocation; dirty-form guard.');
 }finally{
  if(browser)await browser.close();server.kill();await new Promise(r=>server.once('exit',r));
  if(!testRoot.startsWith(path.join(root,'outputs')+path.sep))throw Error('Unsafe cleanup path');
  fs.rmSync(testRoot,{recursive:true,force:true});
 }
})().catch(e=>{console.error(e);process.exitCode=1;});
