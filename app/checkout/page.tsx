"use client";

import { FormEvent, useEffect, useMemo, useState } from "react";

const PRICE = 10500;

type CartItem = {
  quantity: number;
  color: string;
  files: string[];
  designDetails: string;
  designLabel: string;
};

const money = (value: number) =>
  new Intl.NumberFormat("es-CL", {
    style: "currency",
    currency: "CLP",
    maximumFractionDigits: 0,
  }).format(value);

const itemTotal = (item: CartItem) => {
  const subtotal = (item.quantity / 100) * PRICE;
  return item.quantity >= 1000 ? subtotal * 0.8 : subtotal;
};

const colorClass = (value: string) =>
  value === "Sin color específico"
    ? "sin-color"
    : value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

export default function CheckoutPage() {
  const [cart, setCart] = useState<CartItem[]>([]);
  const [delivery, setDelivery] = useState<"retiro" | "transporte">("retiro");
  const [ready, setReady] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  useEffect(() => {
    const stored = window.sessionStorage.getItem("grafik-cart");
    if (stored) {
      try {
        setCart(JSON.parse(stored) as CartItem[]);
      } catch {
        window.sessionStorage.removeItem("grafik-cart");
      }
    }
    setReady(true);
  }, []);

  const total = useMemo(
    () => cart.reduce((sum, item) => sum + itemTotal(item), 0),
    [cart],
  );

  const submitOrder = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSubmitted(true);
  };

  if (!ready) {
    return <main className="checkout-page"><div className="checkout-loading">Cargando tu compra…</div></main>;
  }

  if (!cart.length) {
    return (
      <main className="checkout-page">
        <header className="checkout-header">
          <a className="logo" href="/"><i><span /></i><span>Grafik <b>Publicidad</b></span></a>
          <span>Pago seguro</span>
        </header>
        <section className="checkout-empty">
          <span className="kicker">Tu compra</span>
          <h1>Tu carrito está vacío</h1>
          <p>Agrega al menos un diseño de pulsera antes de continuar al pago.</p>
          <a className="cta" href="/#personaliza">Personalizar pulseras <b>→</b></a>
        </section>
      </main>
    );
  }

  if (submitted) {
    return (
      <main className="checkout-page">
        <header className="checkout-header">
          <a className="logo" href="/"><i><span /></i><span>Grafik <b>Publicidad</b></span></a>
          <span>Pago seguro con Flow</span>
        </header>
        <section className="checkout-empty">
          <b className="checkout-check">✓</b>
          <span className="kicker">Pedido preparado</span>
          <h1>Todo listo para pagar</h1>
          <p>En la tienda de WordPress, el plugin de Flow abrirá aquí sus métodos de pago seguros y confirmará el pedido después del cobro.</p>
          <a className="cta" href="/">Volver a la tienda <b>→</b></a>
        </section>
      </main>
    );
  }

  return (
    <main className="checkout-page">
      <header className="checkout-header">
        <a className="logo" href="/"><i><span /></i><span>Grafik <b>Publicidad</b></span></a>
        <span>Pago seguro con Flow</span>
      </header>

      <div className="checkout-layout">
        <section className="checkout-form-column">
          <a className="checkout-back" href="/">← Volver a la tienda</a>
          <span className="kicker">Finalizar compra</span>
          <h1>Datos de compra</h1>
          <p className="checkout-message">Revisaremos y confirmaremos tu pedido lo más pronto posible. Recibirás una confirmación de cómo quedará el diseño final.</p>

          <form id="checkout-form" onSubmit={submitOrder}>
            <fieldset>
              <legend>Contacto</legend>
              <div className="row">
                <label>Nombre completo<input required autoComplete="name" /></label>
                <label>Correo electrónico<input required type="email" autoComplete="email" /></label>
              </div>
              <label>Teléfono<input required type="tel" autoComplete="tel" placeholder="+56 9" /></label>
            </fieldset>

            <fieldset>
              <legend>Entrega</legend>
              <div className="delivery">
                <label className={delivery === "retiro" ? "selected" : ""}>
                  <input type="radio" name="delivery" checked={delivery === "retiro"} onChange={() => setDelivery("retiro")} />
                  <span><b>Retiro coordinado</b><small>Coordinaremos contigo el lugar y horario.</small></span>
                </label>
                <label className={delivery === "transporte" ? "selected" : ""}>
                  <input type="radio" name="delivery" checked={delivery === "transporte"} onChange={() => setDelivery("transporte")} />
                  <span><b>Envío por transporte</b><small>Datos requeridos para realizar el despacho.</small></span>
                </label>
              </div>

              {delivery === "transporte" && (
                <div className="shipping-fields">
                  <span>Datos de envío</span>
                  <label>RUT<input required autoComplete="off" placeholder="12.345.678-9" /></label>
                  <label>Dirección<input required autoComplete="street-address" placeholder="Calle, número, depto. o sucursal" /></label>
                  <div className="row">
                    <label>Región<select required defaultValue=""><option value="" disabled>Selecciona una región</option>
                      <option>Arica y Parinacota</option><option>Tarapacá</option><option>Antofagasta</option>
                      <option>Atacama</option><option>Coquimbo</option><option>Valparaíso</option>
                      <option>Metropolitana de Santiago</option><option>O’Higgins</option><option>Maule</option>
                      <option>Ñuble</option><option>Biobío</option><option>La Araucanía</option>
                      <option>Los Ríos</option><option>Los Lagos</option><option>Aysén</option>
                      <option>Magallanes</option>
                    </select></label>
                    <label>Ciudad<input required autoComplete="address-level2" /></label>
                  </div>
                </div>
              )}
            </fieldset>

            <label className="check"><input type="checkbox" /> Quiero recibir promociones y novedades por correo.</label>
          </form>
        </section>

        <aside className="checkout-summary">
          <div>
            <span className="kicker">Tu pedido</span>
            <h2>Detalle de la compra</h2>
          </div>
          <div className="checkout-items">
            {cart.map((item, index) => (
              <article key={`${item.designLabel}-${index}`}>
                <i className={colorClass(item.color)} />
                <div>
                  <strong>{item.designLabel}</strong>
                  <span>{item.quantity.toLocaleString("es-CL")} unidades · {item.color}</span>
                  <small>{item.designDetails || "Sin indicaciones escritas"}</small>
                  <small>{item.files.length ? item.files.join(" · ") : "Sin archivos adjuntos"}</small>
                </div>
                <strong>{money(itemTotal(item))}</strong>
              </article>
            ))}
          </div>

          <div className="summary-total">
            <span>Subtotal productos</span><strong>{money(total)}</strong>
            <span>Envío</span><strong>{delivery === "transporte" ? "Por confirmar" : "$0"}</strong>
            <b>Total</b><b>{money(total)}</b>
          </div>

          <div className="payment-box">
            <span className="kicker">Forma de pago</span>
            <label>
              <input type="radio" checked readOnly />
              <span><b>Flow</b><small>Tarjetas y métodos disponibles en el checkout seguro de Flow.</small></span>
              <strong>FLOW</strong>
            </label>
          </div>

          <button className="cta full checkout-pay" type="submit" form="checkout-form">
            Pagar {money(total)} con Flow <b>→</b>
          </button>
          <p className="payment-note">Tus datos se enviarán de forma segura. El pedido quedará confirmado después de que Flow valide el pago.</p>
        </aside>
      </div>
    </main>
  );
}
