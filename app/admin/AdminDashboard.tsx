"use client";

import { FormEvent, useState } from "react";

const money = (value: number) =>
  new Intl.NumberFormat("es-CL", {
    style: "currency",
    currency: "CLP",
    maximumFractionDigits: 0,
  }).format(value);

const orders = [
  { id: "GRAFIK-1048", customer: "Camila Rojas", detail: "500 · General azul", total: 52500, status: "Diseño pendiente" },
  { id: "GRAFIK-1047", customer: "Productora Norte", detail: "1.000 · VIP fucsia", total: 84000, status: "Esperando pago" },
  { id: "GRAFIK-1046", customer: "Club Central", detail: "300 · Staff negro", total: 31500, status: "En producción" },
  { id: "GRAFIK-1045", customer: "Valentina M.", detail: "200 · Acceso rojo", total: 21000, status: "Completado" },
];

export default function AdminDashboard({ displayName }: { displayName: string }) {
  const [saved, setSaved] = useState(false);
  const [active, setActive] = useState("Resumen");

  const saveProduct = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSaved(true);
    window.setTimeout(() => setSaved(false), 2600);
  };

  return (
    <main className="admin-shell">
      <aside className="admin-sidebar">
        <a className="logo" href="/">
          <i><span /></i><span>Grafik <b>Publicidad</b></span>
        </a>
        <nav>
          {["Resumen", "Pedidos", "Producto", "Clientes", "Contenido", "Integraciones"].map((item) => (
            <button key={item} className={active === item ? "active" : ""} onClick={() => setActive(item)}>
              <span>{item.slice(0, 1)}</span>{item}
            </button>
          ))}
        </nav>
        <div className="admin-user">
          <b>{displayName.slice(0, 1).toUpperCase()}</b>
          <span><strong>{displayName}</strong><small>Administrador</small></span>
        </div>
      </aside>

      <section className="admin-main">
        <header className="admin-top">
          <div><span>Panel privado</span><h1>{active}</h1></div>
          <div className="admin-top-actions">
            <a href="/" target="_blank">Ver tienda ↗</a>
            <a className="admin-signout" href="/signout-with-chatgpt?return_to=/">Cerrar sesión</a>
          </div>
        </header>

        <div className="admin-notice">
          <span>Preparación técnica</span>
          <p>La interfaz está lista. La operación permanente se realizará con WordPress, WooCommerce y el plugin de Flow instalado en Hosty.</p>
        </div>

        {(active === "Resumen" || active === "Pedidos") && <>
          <div className="admin-stats">
            <article><span>Pedidos nuevos</span><strong>12</strong><small>Últimos 30 días</small></article>
            <article><span>Ventas estimadas</span><strong>{money(189000)}</strong><small>Pagadas y pendientes</small></article>
            <article><span>Diseños por revisar</span><strong>4</strong><small>Requieren aprobación</small></article>
            <article><span>Clientes registrados</span><strong>38</strong><small>Con consentimiento</small></article>
          </div>

          <div className="admin-card admin-orders">
            <div className="admin-card-head"><div><span>Operación</span><h2>Pedidos recientes</h2></div><button>Exportar CSV</button></div>
            <div className="admin-table">
              <div className="admin-row admin-row-head"><span>Pedido</span><span>Cliente</span><span>Diseño</span><span>Total</span><span>Estado</span></div>
              {orders.map((order) => <div className="admin-row" key={order.id}>
                <strong>{order.id}</strong><span>{order.customer}</span><span>{order.detail}</span>
                <strong>{money(order.total)}</strong><span className={`status ${order.status.toLowerCase().replaceAll(" ", "-")}`}>{order.status}</span>
              </div>)}
            </div>
          </div>
        </>}

        {(active === "Resumen" || active === "Producto") && <div className="admin-grid">
          <form className="admin-card admin-product" onSubmit={saveProduct}>
            <div className="admin-card-head"><div><span>Catálogo</span><h2>Pulseras Tyvek</h2></div><i>Activo</i></div>
            <div className="admin-form-grid">
              <label>Precio cada 100<input type="number" defaultValue="10500" /></label>
              <label>Descuento desde<input type="number" defaultValue="1000" /></label>
              <label>Porcentaje descuento<input type="number" defaultValue="20" /></label>
              <label>Cantidad máxima<input type="number" defaultValue="10000" /></label>
            </div>
            <label>Descripción corta<textarea rows={3} defaultValue="Pulseras Tyvek personalizadas para eventos, resistentes al agua." /></label>
            <button className="admin-primary">{saved ? "Cambios preparados ✓" : "Guardar configuración"}</button>
          </form>

          <div className="admin-card admin-integrations">
            <div className="admin-card-head"><div><span>Sistema</span><h2>Integraciones</h2></div></div>
            <article><b>Flow + WooCommerce</b><span>Pasarela instalada en WordPress</span><i className="pending">Configurar</i></article>
            <article><b>odcpublicidad.cl</b><span>Dominio temporal</span><i className="pending">Actual</i></article>
            <article><b>grafikpublicidad.cl</b><span>Dominio definitivo</span><i className="pending">Próximamente</i></article>
            <article><b>Hosty cPanel</b><span>Hosting de producción</span><i className="pending">Preparado</i></article>
            <article><b>Correo comercial</b><span>Confirmaciones y campañas</span><i className="pending">Falta definir</i></article>
          </div>
        </div>}

        {["Clientes", "Contenido", "Integraciones"].includes(active) && active !== "Resumen" && <div className="admin-card admin-placeholder">
          <span>Módulo preparado</span>
          <h2>{active}</h2>
          <p>Esta sección quedará operativa cuando conectemos la base de datos y los servicios definitivos de grafikpublicidad.cl.</p>
        </div>}
      </section>
    </main>
  );
}
