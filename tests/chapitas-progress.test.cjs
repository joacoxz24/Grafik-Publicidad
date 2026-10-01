// Run with NODE_PATH pointing to the local Playwright runtime.
const {chromium}=require('playwright');
const fs=require('node:fs');
const assert=require('node:assert/strict');
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});
 try {
  const page=await browser.newPage({viewport:{width:390,height:750}});
  const rules={alfiler:{minimum:10,price:500,threshold:101,bulk:400},llavero:{minimum:5,price:750,threshold:51,bulk:650},destapador:{minimum:5,price:950,threshold:51,bulk:860},custom:{minimum:25,price:1200,threshold:25,bulk:1000}};
  await page.setContent(`<style>${fs.readFileSync('wordpress/grafik-publicidad/style.css','utf8')}</style><style>${fs.readFileSync('wordpress/grafik-tyvek-configurator/assets/css/configurator.css','utf8')}</style><main id="chapitas" style="padding:16px"><div class="grafik-chapitas" data-rules='${JSON.stringify(rules)}'><form class="options grafik-chapitas-form"><label class="option">Tipo de chapita<select name="kind">${Object.keys(rules).map(key=>`<option value="${key}">${key}</option>`).join('')}</select></label><label class="option">Cantidad<input name="quantity" type="number" value="10"><small class="chapita-minimum"></small></label><div class="chapita-bulk-progress"><progress data-bulk-progress></progress><small data-bulk-message></small></div><div class="chapita-tiers"><p><span data-regular-range></span><strong data-regular-price></strong></p><p><span data-bulk-range></span><strong data-bulk-price></strong></p></div><div class="price-card"><div><span>Precio por unidad</span><strong data-unit-price></strong></div><p data-bulk-saving class="chapita-saving" hidden></p><div class="total"><span>Total</span><strong data-total></strong></div></div><input type="file"><button type="submit">Agregar</button><p class="chapita-message"></p></form></div></main>`);
  await page.addScriptTag({content:fs.readFileSync('wordpress/grafik-tyvek-configurator/assets/js/chapitas.js','utf8')});
  async function check(kind,q,unit,total,save,remaining){
   await page.locator('select[name="kind"]').selectOption(kind);
   await page.locator('input[name="quantity"]').fill(String(q));
   const state=await page.evaluate(()=>{const root=document.querySelector('.grafik-chapitas');return {unit:root.querySelector('[data-unit-price]').textContent,total:root.querySelector('[data-total]').textContent,saving:root.querySelector('[data-bulk-saving]').textContent,hidden:root.querySelector('[data-bulk-saving]').hidden,remaining:root.querySelector('[data-bulk-message]').textContent,progress:Number(root.querySelector('progress').value),max:Number(root.querySelector('progress').max)}});
   const amount=text=>Number(text.replace(/[^0-9]/g,''));
   assert.equal(amount(state.unit),unit);assert.equal(amount(state.total),total);
   assert.equal(state.hidden,save===0);if(save)assert.equal(amount(state.saving),save);
   if(remaining===0){assert.equal(state.progress,state.max);assert.match(state.remaining,/Ya tienes/);}
   else{assert(state.progress<state.max);assert.match(state.remaining,new RegExp('Agrega '+remaining));}
  }
  await check('alfiler',10,500,5000,0,91);
  await check('alfiler',100,500,50000,0,1);
  await check('alfiler',101,400,40400,10100,0);
  await check('alfiler',150,400,60000,15000,0);
  await check('llavero',50,750,37500,0,1);
  await check('llavero',51,650,33150,5100,0);
  await check('destapador',51,860,43860,4590,0);
  await check('custom',25,1000,25000,5000,0);
  await page.locator('input[name="quantity"]').fill('0');
  assert.match(await page.locator('[data-bulk-message]').textContent(),/cantidad válida/);
  assert(await page.locator('button[type="submit"]').isDisabled());
  await check('alfiler',101,400,40400,10100,0);
  await page.waitForTimeout(300);
  await page.screenshot({path:'outputs/chapitas-progress-mobile.png'});
  console.log('PASS: 8 price thresholds, per-format savings, progress, invalid quantity and configured custom rule.');
 } finally{await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
