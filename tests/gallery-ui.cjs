// Run with PHP_BINARY and NODE_PATH pointing to the local PHP/Playwright runtimes.
const {chromium}=require('playwright');
const {execFileSync}=require('node:child_process');
const fs=require('node:fs');
const assert=require('node:assert/strict');
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});
 try {
 const page=await browser.newPage({viewport:{width:390,height:1000}});
 const image='data:image/png;base64,'+fs.readFileSync('wordpress/grafik-publicidad/assets/images/chapitas-catalogo.png').toString('base64');
 const gallery=execFileSync(process.env.PHP_BINARY,['tests/php/gallery-render.php'],{encoding:'utf8'}).replaceAll('__IMG__',image);
 const html=`<style>${fs.readFileSync('wordpress/grafik-publicidad/style.css','utf8')}</style><main id="chapitas" style="padding:16px"><div class="product-grid grafik-chapitas">${gallery}<form class="options grafik-chapitas-form"><label class="option">Tipo de chapita<select><option>Chapita destapador llavero</option><option>Chapita alfiler</option></select></label><div class="chapita-tiers"><p><span>10 a 100 unidades</span><strong data-regular-price>$500 c/u</strong></p><p><span>Desde 101 unidades</span><strong data-bulk-price>$400 c/u</strong></p></div></form></div></main>`;
 await page.setContent(html);
 assert.equal(await page.locator('.grafik-photo-slide:visible').count(),1);
 await page.addScriptTag({content:fs.readFileSync('wordpress/grafik-publicidad/assets/js/theme.js','utf8')});
 await page.mouse.move(389,999);
 await page.waitForFunction(()=>document.querySelector('[data-photo-count]').textContent==='2 / 7',{},{timeout:6500});
 await page.locator('[data-photo-index="0"]').click();
 await page.waitForFunction(()=>document.querySelector('[data-photo-index="0"]').getAttribute('aria-current')==='true');
 await page.waitForTimeout(450);
 const height=await page.locator('.grafik-photo-stage').evaluate(el=>el.getBoundingClientRect().height);
 await page.locator('[data-photo-next]').click();
 await page.waitForTimeout(100);
 const opacity=await page.locator('.grafik-photo-slide.is-active').evaluate(el=>Number(getComputedStyle(el).opacity));
 assert(opacity>0&&opacity<1,'Intermediate opacity during crossfade');
 await page.waitForTimeout(450);
 assert.equal(await page.locator('.grafik-photo-stage').evaluate(el=>el.getBoundingClientRect().height),height);
 await page.locator('[data-photo-index="6"]').click();
 await page.waitForFunction(()=>document.querySelector('[data-photo-count]').textContent==='7 / 7');
 await page.locator('[data-photo-next]').click();
 await page.waitForFunction(()=>document.querySelector('[data-photo-count]').textContent==='1 / 7');
 await page.locator('[data-photo-prev]').click();
 await page.waitForFunction(()=>document.querySelector('[data-photo-count]').textContent==='7 / 7');
 await page.emulateMedia({reducedMotion:'reduce'});
 for(const width of [320,390,750,1440]){
  await page.setViewportSize({width,height:1100});
  assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'No page overflow at '+width);
  const computed=await page.locator('[data-bulk-price]').evaluate(el=>({size:getComputedStyle(el).fontSize,weight:getComputedStyle(el).fontWeight,color:getComputedStyle(el).color}));
  assert.deepEqual(computed,{size:'28px',weight:'900',color:'rgb(255, 199, 46)'});
  assert.equal(await page.locator('[data-regular-price]').evaluate(el=>getComputedStyle(el).fontSize),'26px');
  assert.equal(await page.locator('.chapita-tiers p').first().evaluate(el=>getComputedStyle(el).fontSize),'24px');
  assert.match(await page.locator('select').evaluate(el=>getComputedStyle(el).backgroundImage),/svg/);
  await page.screenshot({path:`outputs/chapitas-ui-${width}.png`,fullPage:true});
 }
 assert.equal(await page.locator('.grafik-photo-slide.is-active').evaluate(el=>getComputedStyle(el).transitionDuration),'0s');
 console.log('PASS: real template, autoplay, crossfade, stable height, thumbnails, wrap, responsive prices, dropdown arrow and reduced motion.');
 } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
