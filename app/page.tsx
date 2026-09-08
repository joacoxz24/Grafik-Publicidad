"use client";

import { FormEvent, useEffect, useMemo, useState } from "react";
import ProductCarousel from "./components/ProductCarousel";
import ChapitasConfigurator from "./components/ChapitasConfigurator";
import { itemTotal, itemName, validCartItem, type CartItem } from "./lib/catalog";

const PRICE = 10500;
const INSTAGRAM = "https://www.instagram.com/grafikpublicidad.cl";
const money = (value: number) =>
  new Intl.NumberFormat("es-CL", {
    style: "currency",
    currency: "CLP",
    maximumFractionDigits: 0,
  }).format(value);



const COLORS = [
  "Sin color específico",
  "Blanco",
  "Negro",
  "Rojo",
  "Rosado",
  "Celeste",
  "Amarillo",
  "Naranjo",
  "Azul",
  "Verde",
  "Fucsia",
];

const colorClass = (value: string) =>
  value === "Sin color específico"
    ? "sin-color"
    : value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

export default function Home() {
  const [quantity, setQuantity] = useState(100);
  const [color, setColor] = useState("Sin color específico");
  const [files, setFiles] = useState<string[]>([]);
  const [designDetails, setDesignDetails] = useState("");
  const [cart, setCart] = useState<CartItem[]>([]);
  const [cartOpen, setCartOpen] = useState(false);
  const [contactSent, setContactSent] = useState(false);

  const price = useMemo(() => {
    const subtotal = (quantity / 100) * PRICE;
    const discount = quantity >= 1000 ? subtotal * 0.2 : 0;
    return { subtotal, discount, total: subtotal - discount };
  }, [quantity]);

  const [cartReady, setCartReady] = useState(false);
  useEffect(() => {
    try { const saved = JSON.parse(sessionStorage.getItem("grafik-cart") || "[]"); if (Array.isArray(saved)) setCart(saved.filter(validCartItem)); } catch { /* Empty cart when storage is unavailable. */ }
    setCartReady(true);
  }, []);
  useEffect(() => { if (cartReady) { try { sessionStorage.setItem("grafik-cart", JSON.stringify(cart)); } catch { /* Checkout retries storage explicitly. */ } } }, [cart, cartReady]);
  const cartTotal = cart.reduce((total, item) => total + itemTotal(item), 0);
  const addChapitas = (item: CartItem) => { setCart(items => [...items, {...item, designLabel: `Diseño ${items.length + 1}`}]); setCartOpen(true); };

  const addToCart = () => {
    setCart((items) => [
      ...items,
      {
        quantity,
        color,
        files,
        designDetails: designDetails.trim(),
        designLabel: `Diseño ${items.length + 1}`,
      },
    ]);
    setCartOpen(true);
  };

  const goToCheckout = () => {
    window.sessionStorage.setItem("grafik-cart", JSON.stringify(cart));
    window.location.assign("/checkout");
  };

  return (
    <main>
      <header className="header">
        <a className="logo" href="#inicio">
          <i><span /></i><span>Grafik <b>Publicidad</b></span>
        </a>
        <nav>
          <a href="#inicio">Inicio</a><a href="#productos">Productos</a><a href="#personaliza">Pulseras</a><a href="#chapitas">Chapitas</a>
          <a href="#comprar">Cómo comprar</a><a href="#contacto">Contacto</a>
        </nav>
        <div className="header-actions">
          <a href={INSTAGRAM} target="_blank" rel="noreferrer">Instagram</a>
          <button onClick={() => setCartOpen(true)} aria-label="Abrir carrito">◫ <b>{cart.length}</b></button>
        </div>
      </header>

      <ProductCarousel />

      <div className="strip">
        <span>✦ Impresión personalizada</span><span>✦ Control de acceso</span>
        <span>✦ Despacho nacional</span><span>✦ Atención directa</span>
      </div>

      <section className="shell catalog-families" id="productos">
        <div className="section-title"><span>Elige tu producto</span><h2>Tu idea, en el formato que quieras</h2><p>Productos personalizados para eventos, marcas y promociones.</p></div>
        <div className="family-grid">
          <a className="family-card" href="#personaliza"><img src="/assets/pulseras-tyvek-reales.png" alt="Pulseras Tyvek de colores" width="1024" height="1024" loading="lazy" /><div><span className="kicker">Eventos y accesos</span><h3>Pulseras Tyvek</h3><p>Desde 100 unidades. Elige tu color y adjunta tu diseño.</p><strong>100 unidades · $10.500</strong><span className="family-link">Personalizar pulseras →</span></div></a>
          <a className="family-card" href="#chapitas"><img src="/assets/chapitas-catalogo.png" alt="Chapitas personalizadas con alfiler y llavero" width="1024" height="1024" loading="lazy" /><div><span className="kicker">Marcas y promociones</span><h3>Chapitas publicitarias</h3><p>58 mm. Alfiler, llavero y destapador llavero.</p><strong>Desde $400 c/u · alfiler, 101+ unidades</strong><span className="family-link">Personalizar chapitas →</span></div></a>
        </div>
      </section>

      <section className="shell configurator" id="personaliza">
        <div className="section-title">
          <span>Tu pulsera, a tu manera</span>
          <h2>Personaliza y cotiza al instante</h2>
          <p>Selecciona cantidad, color y adjunta tu logo o diseño de referencia.</p>
        </div>
        <div className="product-grid">
          <div className="preview">
            <div className={`flat-band ${colorClass(color)}`}>
              <i />
            </div>
            <small>✦ Vista referencial únicamente del color base</small>
          </div>
          <div className="options">
            <div className="option">
              <label>Cantidad</label>
              <div className="counter">
                <button onClick={() => setQuantity(Math.max(100, quantity - 100))}>−</button>
                <strong>{quantity.toLocaleString("es-CL")} unidades</strong>
                <button onClick={() => setQuantity(Math.min(10000, quantity + 100))}>+</button>
              </div>
              <input type="range" min="100" max="3000" step="100" value={quantity}
                onChange={(event) => setQuantity(Number(event.target.value))} />
              <small className={quantity >= 1000 ? "success" : ""}>
                {quantity >= 1000 ? "20% de descuento aplicado." :
                  `Agrega ${(1000 - quantity).toLocaleString("es-CL")} unidades para obtener 20% de descuento.`}
              </small>
            </div>
            <div className="option">
              <label>Color base <span className="optional">(Opcional)</span></label>
              <div className="swatches">
                {COLORS.map((item) => (
                  <button key={item} type="button" aria-label={item}
                    title={item}
                    className={`${colorClass(item)} ${color === item ? "active" : ""}`}
                    onClick={() => setColor(item)}>
                    {item === "Sin color específico" ? "×" : ""}
                  </button>
                ))}
              </div>
              <small className="color-choice">{color}</small>
            </div>
            <div className="option">
              <label>Escribe los detalles de la pulsera</label>
              <textarea className="design-details" value={designDetails} maxLength={500} rows={4}
                placeholder="Ej. Pulsera VIP fucsia, texto blanco, logo centrado y numeración."
                onChange={(event) => setDesignDetails(event.target.value)} />
              <small className="field-note">Describe el texto, ubicación del logo, colores de impresión y cualquier indicación importante.</small>
            </div>
            <div className="option">
              <label>Logo o diseño de referencia</label>
              <label className="upload">
                <b>↑</b><strong>{files.length ? `${files.length} archivo${files.length > 1 ? "s" : ""} seleccionado${files.length > 1 ? "s" : ""}` : "Selecciona tus archivos"}</strong>
                <small>PNG, JPG o PDF · máximo 3 archivos</small>
                <input type="file" accept=".png,.jpg,.jpeg,.pdf" multiple
                  onChange={(event) => setFiles(Array.from(event.target.files || []).slice(0, 3).map((item) => item.name))} />
              </label>
              {files.length > 0 && <ul className="file-list">{files.map((item) => <li key={item}>{item}</li>)}</ul>}
              <p className="upload-help">Puedes subir tu logo, diseño de referencia o la plantilla completa de la pulsera en medidas 25 × 2 cm. Puedes subir máximo 3 archivos.</p>
            </div>
            <div className="price-card">
              <div><span>Subtotal</span><strong>{money(price.subtotal)}</strong></div>
              {price.discount > 0 && <div className="saving"><span>Descuento 20%</span><strong>−{money(price.discount)}</strong></div>}
              <div className="total"><span>Total</span><strong>{money(price.total)}</strong></div>
              <small>IVA incluido</small>
            </div>
            <button className="cta full" onClick={addToCart}>Agregar al carrito <b>→</b></button>
          </div>
        </div>
      </section>

      <ChapitasConfigurator onAdd={addChapitas} />

      <section className="steps" id="comprar">
        <div className="shell">
          <div className="section-title"><span>Simple y transparente</span><h2>De tu idea a tus manos en 4 pasos</h2></div>
          <div className="step-grid">
            {[
              ["01", "Personaliza", "Elige tu producto, cantidad y adjunta tu referencia."],
              ["02", "Completa tus datos", "Elige retiro o envío y revisa el detalle de tu compra."],
              ["03", "Paga con Flow", "Selecciona el método disponible y paga de forma segura."],
              ["04", "Confirma el diseño", "Revisamos tu pedido y confirmamos contigo cómo quedará el diseño final."],
            ].map(([n, title, text]) => <article key={n}><span>{n}</span><h3>{title}</h3><p>{text}</p></article>)}
          </div>
        </div>
      </section>

      <section className="shell instagram">
        <div className="instagram-head">
          <div><span className="kicker">Síguenos en Instagram</span><h2>@grafikpublicidad.cl</h2>
            <p>Trabajos reales, nuevos colores y pedidos recién terminados.</p></div>
          <a className="outline" href={INSTAGRAM} target="_blank" rel="noreferrer">Ver perfil en Instagram ↗</a>
        </div>
        <div className="insta-grid">
          {[1,2,3,4].map((n) => <a key={n} className={`insta-post post-${n}`} href={INSTAGRAM}
            target="_blank" rel="noreferrer"><span>Ver en Instagram ↗</span></a>)}
        </div>
        <small>Vista actual del perfil. La sincronización automática se activa conectando la cuenta profesional de Meta.</small>
      </section>

      <section className="shell contact" id="contacto">
        <div><span className="kicker">Hablemos de tu pedido</span><h2>¿Necesitas ayuda antes de comprar?</h2>
          <p>Cuéntanos la fecha de tu evento, cantidad y ciudad.</p>
          <ul><li>Atención personalizada</li><li>Envíos a todo Chile</li><li>Diseño sujeto a aprobación</li></ul>
        </div>
        <form onSubmit={(event: FormEvent<HTMLFormElement>) => {
          event.preventDefault(); setContactSent(true); event.currentTarget.reset();
        }}>
          <div className="row"><label>Nombre<input required placeholder="Tu nombre" /></label>
            <label>Correo<input required type="email" placeholder="tu@correo.cl" /></label></div>
          <div className="row"><label>Teléfono<input placeholder="+56 9" /></label>
            <label>Cantidad estimada<select defaultValue="500"><option value="100">100 unidades</option>
              <option value="500">500 unidades</option><option value="1000">1.000 unidades</option>
              <option value="2000">2.000 o más</option></select></label></div>
          <label>Cuéntanos sobre tu evento<textarea required rows={5} placeholder="Fecha, ciudad y detalles..." /></label>
          <label className="check"><input type="checkbox" /> Quiero recibir promociones y novedades.</label>
          <button className="cta full">{contactSent ? "Mensaje enviado ✓" : "Enviar consulta →"}</button>
        </form>
      </section>

      <footer>
        <a className="logo" href="#inicio"><i><span /></i><span>Grafik <b>Publicidad</b></span></a>
        <p>Pulseras, chapitas y productos personalizados.</p>
        <div><a href={INSTAGRAM}>Instagram</a><a href="#contacto">Contacto</a></div>
        <small>© 2026 Grafik Publicidad · Concepción, Chile</small>
      </footer>

      {cartOpen && <div className="backdrop" onMouseDown={() => setCartOpen(false)}>
        <aside className="drawer" onMouseDown={(event) => event.stopPropagation()}>
          <div className="drawer-head"><div><span>Tu compra</span><h2>Carrito</h2></div>
            <button onClick={() => setCartOpen(false)}>×</button></div>
          {cart.length === 0 ? <div className="empty"><b>◫</b><h3>Tu carrito está vacío</h3>
            <p>Elige tus productos y personalízalos para continuar.</p></div> : <>
            <div className="cart-items">{cart.map((item,index) => <article key={index}>
              <img className="cart-product-image" src={item.product === "chapita" ? "/assets/chapitas-catalogo.png" : "/assets/pulseras-tyvek-reales.png"} alt="" /><div><strong>{itemName(item)}</strong><small>{item.designLabel}</small>
                <span>{item.quantity.toLocaleString("es-CL")} unidades · {item.color}</span>
                <small>{item.designDetails || "Sin indicaciones escritas"}</small>
                <small>{item.files.length ? item.files.join(" · ") : "Sin archivos adjuntos"}</small></div>
              <button onClick={() => setCart(cart.filter((_,i) => i !== index))}>×</button></article>)}</div>
            <div className="cart-total"><span>Total</span><strong>{money(cartTotal)}</strong></div>
            <button className="cta full" onClick={goToCheckout}>Ir al pago <b>→</b></button>
          </>}
        </aside>
      </div>}
    </main>
  );
}
