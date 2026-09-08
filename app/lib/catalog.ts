export type ChapitaKind = "alfiler" | "llavero" | "destapador";
export const CHAPITAS = {
  alfiler: { name: "Chapita alfiler", minimum: 10, price: 500, threshold: 101, bulkPrice: 400 },
  llavero: { name: "Chapita llavero", minimum: 5, price: 750, threshold: 51, bulkPrice: 650 },
  destapador: { name: "Chapita destapador llavero", minimum: 5, price: 950, threshold: 51, bulkPrice: 750 },
} as const;
export type CartItem = { product?: "tyvek" | "chapita"; variant?: ChapitaKind; quantity: number; color: string; files: string[]; designDetails: string; designLabel: string };
export const money = (value: number) => new Intl.NumberFormat("es-CL", { style: "currency", currency: "CLP", maximumFractionDigits: 0 }).format(value);
export const itemName = (item: CartItem) => item.product === "chapita" && item.variant ? `${CHAPITAS[item.variant].name} · 58 mm` : "Pulseras Tyvek";
export function itemTotal(item: CartItem): number {
  if (item.product === "chapita" && item.variant) {
    const rule = CHAPITAS[item.variant];
    return item.quantity * (item.quantity >= rule.threshold ? rule.bulkPrice : rule.price);
  }
  return (item.quantity / 100) * 10500 * (item.quantity >= 1000 ? 0.8 : 1);
}
export function validCartItem(value: unknown): value is CartItem {
  if (!value || typeof value !== "object") return false;
  const item = value as CartItem;
  if (!Number.isInteger(item.quantity) || item.quantity > 10000 || !Array.isArray(item.files) || item.files.length > 3 || !item.files.every(f => typeof f === "string") || typeof item.color !== "string" || typeof item.designDetails !== "string" || typeof item.designLabel !== "string") return false;
  if (item.product === "chapita") return !!item.variant && Object.hasOwn(CHAPITAS, item.variant) && item.quantity >= CHAPITAS[item.variant].minimum;
  return (!item.product || item.product === "tyvek") && item.quantity >= 100 && item.quantity % 100 === 0;
}
