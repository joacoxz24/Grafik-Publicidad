"use client";
import { useState } from "react";
import { CHAPITAS, money, type CartItem, type ChapitaKind } from "../lib/catalog";
export default function ChapitasConfigurator({ onAdd }: { onAdd: (item: CartItem) => void }) {
  const [kind, setKind] = useState<ChapitaKind>("alfiler");
  const [quantity, setQuantity] = useState("10");
  const [details, setDetails] = useState("");
  const [files, setFiles] = useState<string[]>([]);
  const rule = CHAPITAS[kind];
  const units = Number(quantity);
  const valid = Number.isInteger(units) && units >= rule.minimum && units <= 10000;
  const unitPrice = units >= rule.threshold ? rule.bulkPrice : rule.price;
  return <section className="shell configurator" id="chapitas"><div className="section-title"><span>Tu marca, contigo</span><h2>Chapitas publicitarias de 58 mm</h2><p>Elige el formato y la cantidad. El precio por unidad se ajusta automáticamente.</p></div><div className="product-grid">
    <div className="chapitas-photo"><img src="/assets/chapitas-catalogo.png" alt="Vista referencial de chapitas alfiler y llavero de 58 mm" width="1024" height="1024" loading="lazy" /><small>Imagen referencial de alfiler y llavero. El destapador llavero es una opción diferente.</small></div>
    <form className="options" onSubmit={e => { e.preventDefault(); if (valid) onAdd({ product:"chapita", variant:kind, quantity:units, color:"Diseño personalizado", files, designDetails:details.trim(), designLabel:"" }); }}>
      <label className="option">Tipo de chapita<select value={kind} onChange={e => { const k=e.target.value as ChapitaKind; setKind(k); setQuantity(String(Math.max(CHAPITAS[k].minimum, Number(quantity) || 0))); }}>{Object.entries(CHAPITAS).map(([key,r]) => <option key={key} value={key}>{r.name}</option>)}</select></label>
      <label className="option">Cantidad<input required type="number" min={rule.minimum} max="10000" step="1" value={quantity} onChange={e=>setQuantity(e.target.value)} /><small>Mínimo {rule.minimum} unidades. Puedes aumentar de una en una.</small></label>
      <div className="chapita-tiers"><p>{rule.minimum} a {rule.threshold-1} unidades <strong>{money(rule.price)} c/u</strong></p><p>Desde {rule.threshold} unidades <strong>{money(rule.bulkPrice)} c/u</strong></p></div>
      <label className="option">Detalles de tus chapitas<textarea maxLength={500} rows={4} value={details} onChange={e=>setDetails(e.target.value)} placeholder="Texto, colores, logo y detalles de tu diseño…" /></label>
      <label className="option">Logo o diseño de referencia<input type="file" accept=".png,.jpg,.jpeg,.pdf" multiple onChange={e=>{ const selected=Array.from(e.target.files || []); if(selected.length>3 || selected.some(f=>f.size>10*1024*1024 || !/\.(png|jpe?g|pdf)$/i.test(f.name))) {e.target.setCustomValidity("Selecciona hasta 3 archivos PNG, JPG o PDF, de máximo 10 MB cada uno."); setFiles([]);} else {e.target.setCustomValidity("");setFiles(selected.map(f=>f.name));} }} /><small>PNG, JPG o PDF · máximo 3 archivos · 10 MB por archivo.</small></label>
      <div className="price-card" aria-live="polite"><div><span>Precio por unidad</span><strong>{money(unitPrice)}</strong></div><div className="total"><span>Total</span><strong>{valid ? money(units*unitPrice) : "Revisa la cantidad"}</strong></div></div>
      <button className="cta full" type="submit" disabled={!valid}>Agregar al carrito <b aria-hidden="true">→</b></button>
    </form></div></section>;
}
