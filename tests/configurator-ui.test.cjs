const {chromium}=require('playwright');
const fs=require('node:fs');
const path=require('node:path');
const {execFileSync}=require('node:child_process');
const assert=require('node:assert/strict');
const root=path.resolve(__dirname,'..');
const read=file=>fs.readFileSync(path.join(root,file),'utf8');
function render(family){
 let html=execFileSync(process.env.GRAFIK_PHP||'php',[path.join(__dirname,'php/configurator-ui-render.php'),family],{encoding:'utf8'});
 html=html.replace(/https:\/\/example\.test\/theme\/assets\/images\/([^" ]+)/g,(_,name)=>'data:image/png;base64,'+fs.readFileSync(path.join(root,'wordpress/grafik-publicidad/assets/images',name)).toString('base64'));
 return html.replace('</head>','<style>'+read('wordpress/grafik-publicidad/style.css')+'\n'+read('wordpress/grafik-tyvek-configurator/assets/css/configurator.css')+'</style></head>');
}
const png=Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jQ1kAAAAASUVORK5CYII=','base64');
const file=(name='logo.png',buffer=png)=>({name,mimeType:'image/png',buffer});
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});
 try{
  const page=await browser.newPage();
  const errors=[];page.on('pageerror',error=>errors.push(error.message));
  const chapitas=render('chapitas');
  for(const width of [320,390,750,989,1440]){
   await page.setViewportSize({width,height:950});
   await page.setContent(chapitas);
   await page.addScriptTag({content:read('wordpress/grafik-tyvek-configurator/assets/js/chapitas.js')});
   const cart=page.getByRole('link',{name:'Ver carrito',exact:true});
   assert(await cart.isVisible(),'Cart hidden at '+width);
   assert.equal(Math.round((await cart.boundingBox()).width),52);
   assert(await cart.locator('svg').isVisible());
   assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Page overflow at '+width);
   for(const card of await page.locator('.chapita-tier').all()) assert(await card.evaluate(el=>el.scrollWidth<=el.clientWidth),'Price card overflow at '+width);
   assert.equal(await page.locator('[data-tier="regular"]').getAttribute('data-active'),'true');
   await page.locator('input[name="quantity"]').fill('101');
   assert.equal(await page.locator('[data-tier="bulk"]').getAttribute('data-active'),'true');
   assert.equal(await page.locator('[data-tier="regular"]').getAttribute('data-active'),'false');
   assert.equal(Number((await page.locator('[data-total]').textContent()).replace(/\D/g,'')),40400);
   if(width===390||width===989){
    await page.locator('.grafik-chapitas-form').screenshot({path:path.join(root,'outputs/dark-configurator-'+width+'.png')});
    await page.locator('.header').screenshot({path:path.join(root,'outputs/dark-header-'+width+'.png')});
   }
  }
  const input=page.locator('input[type="file"]');
  const amount=async selector=>Number((await page.locator(selector).textContent()).replace(/\D/g,''));
  for(const [kind,regular,bulk,threshold] of [['alfiler',500,400,101],['llavero',750,650,51],['destapador',950,860,51]]){
   await page.locator('select[name="kind"]').selectOption(kind);
   assert.equal(await amount('[data-regular-before]'),regular+100);
   assert.equal(await amount('[data-bulk-before]'),bulk+100);
   assert.equal(await amount('[data-regular-price]'),regular);
   assert.equal(await amount('[data-bulk-price]'),bulk);
   await page.locator('input[name="quantity"]').fill(String(threshold));
   assert.equal(await amount('[data-total]'),threshold*bulk);
   assert.equal(await page.locator('.chapita-offer-badge').count(),2);
  }
  const chooserPromise=page.waitForEvent('filechooser');
  await page.locator('.grafik-upload-zone').click();
  await (await chooserPromise).setFiles([file('logo-cliente.png')]);
  assert.equal(await page.locator('[data-upload-label]').textContent(),'1 archivo seleccionado');
  assert.match(await page.locator('[data-upload-feedback]').textContent(),/logo-cliente\.png/);
  assert(await input.evaluate(el=>el.checkValidity()));
  await input.setInputFiles([file('logo.png'),file('referencia.jpg'),{name:'detalle.pdf',mimeType:'application/pdf',buffer:Buffer.from('%PDF-1.4')}]);
  assert(await input.evaluate(el=>el.checkValidity()));
  assert.deepEqual(await page.locator('form.grafik-chapitas-form').evaluate(el=>new FormData(el).getAll('grafik_files[]').map(f=>f.name)),['logo.png','referencia.jpg','detalle.pdf']);
  await input.setInputFiles([file('1.png'),file('2.png'),file('3.png'),file('4.png')]);
  assert(await input.evaluate(el=>el.validity.customError));
  assert(await page.locator('.grafik-upload-zone').evaluate(el=>el.classList.contains('has-error')));
  await input.setInputFiles([{name:'no-permitido.svg',mimeType:'image/svg+xml',buffer:Buffer.from('<svg/>')}]);
  assert(await input.evaluate(el=>el.validity.customError));
  await input.setInputFiles([file('demasiado-grande.png',Buffer.alloc(10485761))]);
  assert(await input.evaluate(el=>el.validity.customError));
  await input.setInputFiles([file('logo-<img>.png')]);
  assert.equal(await page.locator('[data-upload-feedback]').textContent(),'logo-<img>.png');
  assert.equal(await page.locator('[data-upload-feedback] img').count(),0);
  await input.setInputFiles([]);
  assert(await input.evaluate(el=>el.checkValidity()));
  assert.equal(await page.locator('[data-upload-label]').textContent(),'Selecciona tus archivos');
  assert(await page.locator('[data-upload-feedback]').isHidden());
  await input.focus();
  assert.equal(await page.locator('.grafik-upload-zone').evaluate(el=>getComputedStyle(el).outlineStyle),'solid');
  await page.setContent(render('pulseras'));
  await page.addScriptTag({content:'window.jQuery=function(){}; window.grafikTyvek={maxFiles:3,fileTypes:["png","jpg","jpeg","pdf"],i18n:{fileLimit:"Puedes subir máximo 3 archivos.",fileType:"Usa solamente archivos PNG, JPG o PDF."}};'});
  await page.addScriptTag({content:read('wordpress/grafik-tyvek-configurator/assets/js/configurator.js')});
  await page.locator('input[type="file"]').setInputFiles([file('pulsera.png')]);
  assert.match(await page.locator('.grafik-file-list').textContent(),/pulsera.png/);
  assert(await page.locator('.grafik-file-list').isVisible());
  assert.deepEqual(errors,[]);
  console.log('PASS: real PHP templates at 320/390/750/989/1440px; visible SVG cart; price cards; native file chooser; 3-file/type/size validation; FormData; safe filenames; keyboard focus; Tyvek uploads.');
 }finally{await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
