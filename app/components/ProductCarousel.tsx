"use client";
import { useEffect, useState } from "react";
const slides = [
  { tag: "Para tus eventos", title: "Pulseras Tyvek", emphasis: "personalizadas", copy: "Identifica a tus invitados con el diseño y los colores de tu evento.", price: "100 unidades · $10.500", detail: "20% de descuento desde 1.000 unidades", image: "/assets/pulseras-tyvek-reales.png", alt: "Pulseras Tyvek personalizadas de distintos colores", href: "#personaliza", cta: "Personalizar pulseras" },
  { tag: "Para tu marca", title: "Chapitas de 58 mm", emphasis: "con tu diseño", copy: "Alfiler, llavero o destapador llavero. Pequeños detalles para llevar tu marca contigo.", price: "Desde $400 c/u", detail: "Precio alfiler desde 101 unidades", image: "/assets/chapitas-catalogo.png", alt: "Composición referencial de chapitas con alfiler y llavero personalizadas", href: "#chapitas", cta: "Personalizar chapitas" },
];
export default function ProductCarousel() {
  const [active, setActive] = useState(0);
  const [paused, setPaused] = useState(false);
  const [hover, setHover] = useState(false);
  const [focused, setFocused] = useState(false);
  const [reduced, setReduced] = useState(true);
  useEffect(() => { const media = matchMedia("(prefers-reduced-motion: reduce)"); const update = () => setReduced(media.matches); update(); media.addEventListener("change", update); return () => media.removeEventListener("change", update); }, []);
  useEffect(() => { if (paused || hover || focused || reduced) return; const timer = setInterval(() => { if (!document.hidden) setActive(i => (i + 1) % slides.length); }, 6000); return () => clearInterval(timer); }, [paused, hover, focused, reduced]);
  const choose = (index: number) => { setActive((index + slides.length) % slides.length); setPaused(true); };
  return <section className="catalog-carousel" id="inicio" aria-label="Productos destacados" aria-roledescription="carrusel" onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)} onFocusCapture={() => setFocused(true)} onBlurCapture={e => { if (!e.currentTarget.contains(e.relatedTarget)) setFocused(false); }}>
    <h1 className="grafik-sr-only">Productos personalizados para marcas y eventos</h1>
    {slides.map((s, i) => <div key={s.href} className="hero catalog-slide" hidden={i !== active} role="group" aria-roledescription="diapositiva" aria-label={`${i + 1} de 2: ${s.title}`}>
      <div className="hero-copy"><span className="eyebrow">{s.tag}</span><h2>{s.title}<em>{s.emphasis}</em></h2><p className="hero-description">{s.copy}</p><div className="hero-price"><strong>{s.price}</strong><span>{s.detail}</span></div><a className="cta" href={s.href}>{s.cta}<b aria-hidden="true">→</b></a><p className="hero-delivery">Tu diseño · Tu cantidad · Envíos a todo Chile</p></div>
      <div className="hero-art"><img src={s.image} alt={s.alt} width="1024" height="1024" /></div>
    </div>)}
    <div className="carousel-controls"><button type="button" onClick={() => choose(active - 1)} aria-label="Producto anterior">←</button>{slides.map((s,i) => <button key={s.href} type="button" aria-current={i === active ? "true" : undefined} onClick={() => choose(i)}>{s.title}</button>)}<button type="button" onClick={() => choose(active + 1)} aria-label="Producto siguiente">→</button><button type="button" aria-pressed={paused || reduced} onClick={() => { setPaused(!paused); if (reduced) setReduced(false); }}>{paused || reduced ? "Reproducir" : "Pausar"}</button></div>
  </section>;
}
